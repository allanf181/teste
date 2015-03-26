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

require CONTROLLER . "/atribuicao.class.php";
$atribuicao = new Atribuicoes();

require CONTROLLER . "/aula.class.php";
$aula = new Aulas();

$data = date("d/m/Y", time()); // data atual

$dataInicio = $_GET['dataInicio'];
$dataFim = $_GET['dataFim'];

if ($dataFim && $dataInicio) {
    $params['dataInicio'] = dataMysql($dataInicio);
    $params['dataFim'] = dataMysql($dataFim);
    $sqlAdicional .= " AND au.data >= :dataInicio AND au.data <= :dataFim ";
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= " AND at.turma=:turma ";
}

if (dcrip($_GET["disciplina"])) {
    $disciplina = dcrip($_GET["disciplina"]);
    $params['disciplina'] = $disciplina;
    $sqlAdicional .= " AND at.codigo=:disciplina ";
}

if (dcrip($_GET["turno"])) {
    $turno = dcrip($_GET["turno"]);
    $params['turno'] = $turno;
    $sqlAdicional .= " AND at.periodo=:turno ";
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsTurma['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalTurma = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}
?>

<style>
    #table {     width: 100%;
                 margin-top: -15px;
                 /*background: red;*/
                 padding: -5px;
                 border-collapse:collapse; }
    #table td, th { border: 1px solid #ccc;}

    .hover { background-color: #bfe0c5; }
    .noslim { background-color: #e1f2d0; }
</style>

<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<table align="center" width="100%" id='form'>
    <tr>
        <td style="width: 100px" align="right">Data: </td>
        <td>
            <input type="text" size="10" value="<?= $dataInicio ?>" name="dataInicio" id="dataInicio" />
            a <input type="text" size="10" value="<?= $dataFim ?>" name="dataFim" id="dataFim" />
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
    <tr>
        <td align="right">Disciplina: </td>
        <td><select name="disciplina" id="disciplina" style="width: 350px">
                <option></option>
                <?php
                $sqlAdicionaDisc = ' AND t.codigo = :turma ';
                $paramsDisc = array(':turma' => $turma, ':ano' => $ANO, ':semestre' => $SEMESTRE);
                foreach ($atribuicao->getAllAtribuicoes($paramsDisc, $sqlAdicionaDisc) as $reg) {
                    $selected = "";
                    if ($reg['atribuicao'] == $disciplina)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['atribuicao']) . "'>" . $reg['disciplina'] . $reg['bimestre'] . $reg['subturma'] . " [" . $reg['turno'] . "]</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td align="right" style="width: 30px">Turno: </td><td>
            <select name="turno" id="turno" value="<?= $turno ?>">
                <option></option>
                <?php
                require CONTROLLER . '/turno.class.php';
                $turnos = new Turnos();
                foreach ($turnos->listRegistros() as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $turno)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select>
        </td>
    </tr>
</table>
<?php
if ($turma && $dataFim && $dataInicio) {
    $sqlAdicional .= " group by p.nome, au.codigo ";
    foreach ($frequencia->getListaFrequencias($params, $sqlAdicional) as $reg) {
        $datas[] = $reg['dataFormatada'];
        $aulas[$reg['dataFormatada']][$reg['codAula']] = $reg['quantidade'];
        $disciplinas[$reg['codAula']] = $reg['disciplina'];
        $falta = $aula->listAulasAluno($reg['codAula'], $reg['codAluno'], 'sigla', $reg['data']);
        $totalFaltas[$reg['codAluno']] += substr_count($falta[0]['falta'], 'F');
        $frequencias[$reg['codAluno']][$reg['codAula']] = $falta[0]['falta'];
        $nomes[$reg['codAluno']] = $reg['aluno'] . ' (' . $reg['situacao'] . ')';
        $professores[$reg['codAula']] = $professor->getProfessor($reg['atribuicao'], 1, '', 0, 0);
    }

    function consecutive_values(array $haystack) {
        $cons = 0;
        $faltasConsecutivas = 0;
        foreach ($haystack as $f) {
            if (substr_count($f, 'F') > 0) {
                if ($cons) {
                    $faltasConsecutivas += 1 + $ant;
                    $ant = 0;
                } else {
                    $cons = 1;
                    $ant = 1;
                }
            } else {
                $cons = 0;
            }
        }
        return $faltasConsecutivas;
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
            <th align="center"><a href="#" data-placement="top" title="Faltas Totais" data-content="N&uacute;mero total de faltas no per&iacute;odo.">FT</a></th>
            <th align="center"><a href="#" data-placement="top" title="Dias de Faltas Consecutivas" data-content="N&uacute;mero total de dias com faltas consecutivas no per&iacute;odo.">DFC</a></th>
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
                            $conteudo.= "<a href='#' data-placement='top' title='" . mostraTexto($disciplinas[$codAula]) . "' data-content='" . mostraTexto($professores[$codAula]) . "<br>$n aulas' > (" . $frequencias[$c][$codAula] . ")</a>";
                        }
                        ?>
                        <td align='center' bgcolor='<?= $cor ?>'><?= $conteudo ?></td>
                        <?php
                    }
                    ?>
                    <td align='center' bgcolor='<?= $cor ?>'><?= $totalFaltas[$c] ?></td>
                    <td align='center' bgcolor='<?= $cor ?>'><?= consecutive_values($frequencias[$c]) ?></td>
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
    $(document).ready(function () {
        $("#dataInicio, #dataFim").datepicker({
            dateFormat: 'dd/mm/yy',
            defaultDate: '<?= date("d/m/Y") ?>',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior'
        });

        $("#turma, #dataInicio, #dataFim, #turno, #disciplina").change(function () {
            var turma = $('#turma').val();
            var disciplina = $('#disciplina').val();
            var turno = $('#turno').val();
            var dataInicio = $('#dataInicio').val();
            var dataFim = $('#dataFim').val();
            $('#index').load('<?= $SITE ?>?turma=' + turma + '&turno=' + turno + '&dataInicio=' + dataInicio + '&dataFim=' + dataFim + '&disciplina=' + disciplina);
        });

        $("#table").delegate('td', 'mouseover mouseleave', function (e) {
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