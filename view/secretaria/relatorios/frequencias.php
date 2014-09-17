<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar o percentual de frequência de todos os alunos de uma determinada disciplina.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/professor.class.php";
$professor = new Professores();

require CONTROLLER . "/frequencia.class.php";
$frequencia = new Frequencias();

$data = date("d/m/Y", time()); // data atual

if (isset($_GET["mes"]))
    $mes = $_GET["mes"];

if (dcrip($_GET["turma"]))
    $turma = dcrip($_GET["turma"]);

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsTurma['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalTurma = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}
?>

<style>
    table {     width: 100%;
                margin-top: -15px;
                /*background: red;*/
                padding: -5px;
                border-collapse:collapse; }
    td, th { border: 1px solid #ccc;}

    .hover { background-color: #bfe0c5; }
    .noslim { background-color: #e1f2d0; }
</style>

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<table align="center" width="100%" id='form'>
    <tr>
        <td align="right" style="width: 100px">M&ecirc;s:</td>
        <td>
            <select id="mes" name="mes" value='<?= $mes ?>'>
                <?php
                foreach (array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro") as $n => $nomeMes) {
                    $selected = "";
                    if ($n == $mes)
                        $selected = "selected='selected'";
                    echo "<option $selected value='$n'>$nomeMes</option>\n";
                }
                ?>

            </select>
        </td>
    </tr>
    <tr>
        <td align="right">Turma: </td>
        <td>
            <select name="turma" id="turma" value="<?= $turma ?>">
                <?php
                require CONTROLLER . '/turma.class.php';
                $turmas = new Turmas();
                $paramsTurma[':ano'] = $ANO;
                $paramsTurma[':semestre'] = $SEMESTRE;
                foreach ($turmas->listTurmas($paramsTurma, $sqlAdicionalTurma) as $reg) {
                    $selected = "";
                    if ($reg['codTurma'] == $turma) {
                        $selected = "selected";
                        $fechamento = $reg['fechamento'];
                    }
                    print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                }
                ?>
            </select>
        </td>
    </tr>  
</table>
<?php
if ($turma) {
    $mes = $mes + 1;
    $params['turma'] = $turma;
    $sqlAdicional = "WHERE at.turma=:turma AND date_format(au.data, '%m')=$mes group by p.nome, au.codigo";
    foreach ($frequencia->getListaFrequencias($params, $sqlAdicional) as $reg) {
        $datas[] = $reg['dataFormatada'];
        $aulas[$reg['dataFormatada']][$reg['codAula']] = $reg['quantidade'];
        $disciplinas[$reg['codAula']] = $reg['disciplina'];
        $frequencias[$reg['codAluno']][$reg['codAula']] = ($A = $frequencia->getFrequenciaAbono($reg['matricula'], $reg['atribuicao'], $reg['data'])) ? $A['sigla'] : $reg['frequencia'];
        $nomes[$reg['codAluno']] = $reg['aluno'];
        $professores[$reg['codAula']] = $professor->getProfessor($reg['atribuicao'], '', 0, 0);
    }
}

if (!empty($turma)) {
    ?>
    <div style="text-align: center; margin-top: 10px"><a href="#" id="maximizar">Maximizar</a></div>
    <br />

    <table id="table" border="1">
        <colgroup class="noslim"></colgroup>
        <colgroup class="noslim"></colgroup>
        <?php
        if ($datas)
            foreach (array_unique($datas) as $data) {
                ?>
                <colgroup class="slim"></colgroup>
                <?php
            }
        ?>
        <tr>
            <th align="center" width="10">#</th>
            <th align="center"  style='width: 300px'>Nome</th>    
            <?php
            if ($datas) {
                foreach (array_unique($datas) as $data) {
                    $d[] = $data;
                    ?>
                    <th align='center'>
                        <?php
                        foreach ($aulas[$data] as $cod => $n) {
                            $textoData = $data;
                        }
                        ?>
                        <?= $textoData ?>
                    </th>
                    <?php
                }
            }
            ?>
        </tr>
        <?php
        $i = 1;
        if ($nomes) {
            foreach ($nomes as $c => $nome) {
                ?>
                <tr>
                    <td align='center'><?= $i ?></td>
                    <td><?= mostraTexto($nome) ?></td>
                    <?php
                    foreach ($aulas as $data => $codAulas) {
                        $conteudo = "";
                        $cor = '';
                        foreach ($codAulas as $codAula => $n) {
                            if ($frequencias[$c][$codAula] > 0 && $frequencias[$c][$codAula] <= $codAulas)
                                $cor = '#FFCCCC';
                            $conteudo.= "<a href='#' title='" . mostraTexto($disciplinas[$codAula]) . "<br>" . mostraTexto($professores[$codAula]) . "<br>$n aulas' > (" . $frequencias[$c][$codAula] . ")</a>";
                        }
                        ?>
                        <td align='center' bgcolor='<?= $cor ?>'><?= $conteudo ?></td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
                $i++;
            }
        }
        ?>
    </table>
    <?php
}
?>
<script>
    $(document).ready(function() {
        $("#turma, #mes").change(function() {
            var turma = $('#turma').val();
            var mes = $('#mes').val();
            $('#index').load('<?= $SITE ?>?turma=' + turma + '&mes=' + mes);
        });

        $("#table").delegate('td', 'mouseover mouseleave', function(e) {
            if (e.type == 'mouseover') {
                $(this).parent().addClass("hover");
                $("colgroup").eq($(this).index()).addClass("hover");
            }
            else {
                $(this).parent().removeClass("hover");
                $("colgroup").eq($(this).index()).removeClass("hover");
            }
        });

        $('#maximizar').click(gerenciaMaximizar);
        var textoLink;

        if ($('#menu').is(':hidden'))
            $('#maximizar').text('restaurar');

        function gerenciaMaximizar() {
            if ($('#maximizar').text() == 'restaurar') {
                textoLink = 'Maximizar';
                $('#menu, #header, #menuEsquerdo').show();
                $('body').css('width', '100%');
                $('#wrap').css('margin', '0 auto');
                $('#wrap').css('background', null);
                $('#wrap').css('width', '1024px');
                $('body').css('background', '#e1f2d0 url(<?= IMAGES; ?>/bg.jpg) repeat-y top center');
                $('.right').css('width', '794px');
                $('#maximizar').text(textoLink);
                $('#titulo').css('width', '100%');
                $('.alunoNome').hide('fast');
                $('.alunoPrimeiroNome').show('fast');

            }
            else {
                textoLink = 'restaurar';
                $('#menu, #header, #menuEsquerdo').hide();
                $('body').css('width', '100%');
                $('#wrap').css('margin', '0');
                $('#wrap').css('background', null);
                $('#wrap').css('width', '100%');
                $('body').css('background', 'white');
                $('.right').css('width', '100%');
                $('#maximizar').text(textoLink);
                $('#titulo').css('width', '250px');
                $('.alunoNome').show('fast');
                $('.alunoPrimeiroNome').hide('fast');
            }
        }

        if ($('#maximizar').text() == 'restaurar')
            $('#titulo').css('width', '250px');
        else
            $('#titulo').css('width', '100%');

    });
</script>