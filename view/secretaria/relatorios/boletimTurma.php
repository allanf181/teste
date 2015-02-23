<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar todas as notas de todos os alunos de uma determinada disciplina.
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

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

require CONTROLLER . "/atribuicao.class.php";
$atribuicao = new Atribuicoes();

require CONTROLLER . "/matriculaAlteracao.class.php";
$ma = new MatriculasAlteracoes();

if (dcrip($_GET["turma"]))
    $turma = dcrip($_GET["turma"]);

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsTurma['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalTurma = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

if ($_GET["bimestre"] != 'undefined')
    $bimestre = dcrip($_GET["bimestre"]);


if (dcrip($_GET["turno"]))
    $turno = dcrip($_GET["turno"]);
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>

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

<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<table align="center" id="form" width="100%">
    <tr><td align="right" style="width: 100px">Turma: </td><td>
            <select name="turma" id="turma" value="<?= $turma ?>" style="width: 650px">
                <option></option>
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
        </td></tr>
    <tr><td align="right" style="width: 100px">Turno: </td><td>
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
        </td></tr>    
    <?php if ($fechamento == 'b') { ?>
        <tr><td align="right">Bimestre: </td><td>
                <select name="bimestre" id="bimestre">
                    <option></option>
                    <option <?php
        if ($bimestre == '1')
            echo "selected='selected'";
        else
            echo "";
        ?> value="<?= crip(1) ?>">1º bimestre</option>
                    <option <?php
                if ($bimestre == '2')
                    echo "selected='selected'";
                else
                    echo "";
        ?> value="<?= crip(2) ?>">2º bimestre</option>
                    <option <?php
                if ($bimestre == '3')
                    echo "selected='selected'";
                else
                    echo "";
        ?> value="<?= crip(3) ?>">3º bimestre</option>
                    <option <?php
                if ($bimestre == '4')
                    echo "selected='selected'";
                else
                    echo "";
        ?> value="<?= crip(4) ?>">4º bimestre</option>
                    <option <?php
                if ($bimestre == 'final')
                    echo "selected='selected'";
                else
                    echo "";
        ?> value="<?= crip('final') ?>">Final</option>
                </select>
            </td>
        </tr>
    <?php } ?>
</table>    
<div style="text-align: center; margin-top: 10px"><a href="#" id="maximizar">Maximizar</a></div>
<?php
if ($turma && $fechamento) {
    foreach ($atribuicao->getAtribuicoesFromBoletimTurma($turma, $bimestre, $fechamento, $turno) as $reg) {
        $bimestres[$reg['bimestre']] = $reg['bimestre'];
        $alunos[$reg['codAluno']] = $reg['aluno'];
        $disciplinasMediaNome[$reg['numero']] = $reg['numero'];
        $disciplinas[$reg['atribuicao']][$reg['codAluno']] = $reg['codMatricula'];

        $matSituacao = $ma->getAlteracaoMatricula($reg['codAluno'], $reg['atribuicao'], date('Y-m-d'));
        $situacaoListar[$reg['codAluno']][$reg['atribuicao']] = $matSituacao['listar'];
        $situacaoSigla[$reg['codAluno']][$reg['atribuicao']] = $matSituacao['sigla'];
        $situacaoNome[$matSituacao['sigla']] = $matSituacao['tipo'];
        $situacaoHabilitar[$reg['codAluno']][$reg['atribuicao']] = $matSituacao['habilitar'];
        
        $professores = $professor->getProfessor($reg['atribuicao'], 1, '', 0, 0);

        $disciplinasNomes[$reg['bimestre']][$reg['atribuicao']][$reg['numero']] = $reg['disciplina'] . "<br>" . $professores . "<br>" . $reg['turno'];
        $disciplinasNomes2[$reg['atribuicao']] = $reg['disciplina'];
        $disciplnasStatusNomes[$reg['codDisciplina']][$reg['status']] = $reg['disciplina'] . " (" . $professores . ")";
        $disciplnasStatus[$reg['codDisciplina']] = $reg['status'];
    }

    $status = 0;
    if ($disciplnasStatusNomes) {
        foreach ($disciplnasStatusNomes as $dCodigo => $dNome) {
            if (!$disciplnasStatus[$dCodigo]) {
                ?>
                <font>A disciplina <?= $dNome[0] ?> n&atilde;o foi finalizada.</font><br>
                <?php
                $status++;
            }
        }
    }

    if ($status) {
        ?>
        <font color="red">N&atilde;o &eacute; poss&iacute;vel alterar a situa&ccedil;&atilde;o com di&aacute;rios abertos.</font>
        <br>
        <?php
    }
    ?>
    <br />
    <p><font color="red">Aten&ccedil;&atilde;o, a MG (M&eacute;dia Global) e a FG(Frequ&ecirc;ncia Global) podem divergir do Nambei, utilize apenas para refer&ecirc;ncia.</font></p>
    <br />
    <table id='table' style="background: #E3F0E6;">
        <colgroup class="noslim"></colgroup>
        <colgroup class="noslim"></colgroup>
        <?php
        foreach ($bimestres as $bim => $aa) {
            foreach ($disciplinasNomes[$bim] as $dSigla => $dNome) {
                ?>
                <colgroup class="slim"></colgroup>
                <?php
            }
        }
        ?>
        <tr>
            <th align="center" width="5">#</th>
            <th align="center" width="100">Nome</th>
            <?php
            if ($fechamento == 'b') {
                if ($bimestres)
                    foreach ($bimestres as $bim) {
                        $colspan = count($disciplinasNomes[$bim]);
                        ?>
                        <td align='center' style='width: 30px' colspan="<?= $colspan ?>"><b><?= $bim ?>&ordm; BIM</b></td>
                        <?php
                    }
                if ($bimestre == 'final') {
                    ?>
                    <td align='center' style='width: 30px' colspan="<?= $colspan ?>"><b>FINAL (M&Eacute;DIA ANUAL)</b></td>
                    <?php
                }
            }

            if ($bimestre != 'final' || $fechamento == 's') {
                ?>
                <td>&nbsp</td>
                <td>&nbsp</td>
                <?php
            }

            if ($fechamento != 'b') {
                foreach ($bimestres as $bim => $aa) {
                    foreach ($disciplinasNomes[$bim] as $dSigla => $dNome) {
                        ?>
                        <td>&nbsp</td>
                        <?php
                    }
                }
            }
            ?>
        </tr>
        <tr>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <?php
            if ($bimestres) {
                foreach ($bimestres as $bim => $aa) {
                    foreach ($disciplinasNomes[$bim] as $ddSigla) {
                        foreach ($ddSigla as $dSigla => $dNome) {
                            ?>
                            <td align='center' style='width: 30px'><a data-placement="top" data-content='<?= $dNome ?>' title='Disciplina'><?= $dSigla ?></a></td>
                            <?php
                        }
                    }
                }
            }

            if ($bimestre == 'final' && $fechamento == 'b') {
                foreach ($disciplinasMediaNome as $ddSigla => $dsNome) {
                    ?>
                    <td align='center' style='width: 30px'><?= $dsNome ?></td>
                    <?php
                }
            }
            ?>
            <td align='center' style='width: 30px'>
                <a title='M&eacute;dia Global'>MG</a>
            </td>
            <td align='center' style='width: 30px'>
                <a title='Frequ&ecirc;ncia Global'>FG</a>
            </td>
        </tr>
        <?php
        $i = 1;
        if ($alunos)
            foreach ($alunos as $c => $nome) {
                $dadosGlobal = null;
                $discQdePorAluno = null;
                $media = null;
                $frequencia = null;
                $mediaAnual = null;
                ?>
                <tr <?= $cdif ?>>
                    <td align='center'><?= $i ?></td>
                    <td>
                        <a href="#" rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($c) ?>' class='screenshot' title='<?= mostraTexto($nome) ?>'>
                            <img style='width: 20px; height: 20px' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($c) ?>'>
                        </a>
                        <?php
                        if ($bimestre != 'final' && $fechamento == 'b')
                            $bimestreLink = '&bimestre=' . crip($bimestre);
                        ?>
                        <a title='Clique para ver o boletim do aluno' href="javascript:$('#index').load('<?= VIEW ?>/secretaria/relatorios/boletim.php?aluno=<?= crip($c) ?>&turma=<?= crip($turma) . $bimestreLink ?>');void(0);"><?= mostraTexto($nome) ?></a>
                    </td>
                    <?php
                    foreach ($disciplinas as $dCodigo => $dMatricula) {
                        if ($situacaoListar[$c][$dCodigo]) {
                            if ($situacaoHabilitar[$c][$dCodigo]) {
                                $dados = $nota->resultado($dMatricula[$c], $dCodigo);
                                ?>
                                <td align='center'>
                                    <a title='<?= $disciplinasNomes2[$dCodigo] ?>' data-content='Faltas: <?= $dados['faltas'] ?><br>Aulas Dadas: <?= $dados['auladada'] ?><br>Frequ&ecirc;ncia (Aulas Dadas): <?= arredondar($dados['frequencia']) ?>%<br>Frequ&ecirc;ncia (Aulas Previstas): <?= arredondar($dados['frequenciaPrevista']) ?>%'><font color='<?= $dados['color'] ?>'><?= $dados['media'] ?></font></a>
                                </td>
                                <?php
                            } else {
                                ?>
                                <td align='center'>
                                    <a title='<?= $situacaoNome[$c][$dCodigo] ?>'><?= $situacaoSigla[$c][$dCodigo] ?></a>
                                </td>
                                <?php
                            }
                        } else {
                            ?>
                            <td align='center'>-</td>
                            <?php
                        }
                    }

                    if ($bimestre == 'final' && $fechamento == 'b') {
                        if ($disciplinasMediaNome)
                            foreach ($disciplinasMediaNome as $dCodigo => $dNumero) {
                                $mediaAnual = $nota->resultadoBimestral($c, $turma, $dNumero);
                                ?>
                                <td align='center'>
                                    <font color='<?= $mediaAnual['color'] ?>'><?= arredondar($mediaAnual['media']) ?></font>
                                </td>
                                <?php
                            }
                    }
                    
                    $dadosGlobal = $nota->resultadoModulo($c, $turma);

                    if (!$media = $dadosGlobal['mediaGlobal'])
                        $media = '-';
                    $frequencia = round($dadosGlobal['frequenciaGlobal'], 1);
                    $frequencia = (!$frequencia) ? '-' : $frequencia . '%';
                    ?>
                    <td align='center'>
                        <font color='<?= $dadosGlobal['color'] ?>'><b><?= $media ?></b></font>
                    </td>
                    <td align='center'><?= $frequencia ?></td>
                </tr>
                <?php
                $i++;
            }
        ?>
    </table>
    <?php
}

$_SESSION['VOLTAR'] = "index";
$_SESSION['LINK'] = VIEW . "/secretaria/relatorios/boletimTurma.php?turma=" . crip($turma) . "&bimestre=" . crip($bimestre);
?>
<script>
    if ($('#maximizar').text() == 'Maximizar') {
        $('.alunoNome').hide('slow');
        $('.alunoPrimeiroNome').show('slow');
    }
    else {
        $('.alunoNome').show('slow');
        $('.alunoPrimeiroNome').hide('slow');

    }

    $(document).ready(function () {
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

        var lastTd;
        var lastImage;
        var imagem1;
        var haItemAtivo;
        var textoLink;

        $('#atualizar').click(function () {
            Links();
        });

        if ($('#menu').is(':hidden'))
            $('#maximizar').text('restaurar');

        $("#turma, #turno, #bimestre").change(function () {
            var turma = $('#turma').val();
            var turno = $('#turno').val();
            var bimestre = $('#bimestre').val();
            $('#index').load('<?= $SITE ?>?turma=' + turma + '&turno=' + turno + '&bimestre=' + bimestre);
        });

        $('#listagem tr').dblclick(function () {
            if (lastTd)
                lastTd.css('font-size', '8pt');
            if (lastImage) {
                lastImage.css('width', '20px');
                lastImage.css('height', '20px');
            }

            if (imagem1 != $(this).find("img").attr("src") || !haItemAtivo) {
                imagem1 = $(this).find("img").attr("src");
                lastTd = $(this).find("td");
                lastImage = $(this).find("img");

                lastTd.css('font-size', '12pt');
                lastImage.css('width', '50px');
                lastImage.css('height', '50px');
                haItemAtivo = true;
            }
            else
                haItemAtivo = false;

        });

        $('#maximizar').click(gerenciaMaximizar);

        function gerenciaMaximizar() {
            if ($('#maximizar').text() == 'restaurar') {
                textoLink = 'Maximizar';
                $('#menu, #header, #menuEsquerdo').show();
                $('body').css('width', '100%');
                $('#wrap').css('margin', '0 auto');
                $('#wrap').css('background', null);
                $('#wrap').css('width', '1024px');
                $('body').css('background', '#e1f2d0 url(<?= IMAGES; ?>/bg.jpg) repeat-y top center');
                $('.right').css('width', '95%');
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