<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar todas as notas de todos os alunos de uma determinada disciplina.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';

require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$data = date("d/m/Y", time()); // data atual
$turma = "";

if (isset($_GET["turma"]))
    $turma = dcrip($_GET["turma"]);
if (isset($_GET["bimestre"]))
    $bimestre = dcrip($_GET["bimestre"]);
?>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>

<script>
    function Links() {
        var turma = $('#campoTurma').val();
        var bimestre = $('#campoBimestre').val();
        $('#index').load('<?php print $SITE; ?>?turma=' + turma + '&bimestre=' + bimestre);
    }

    $(document).ready(function() {
        var lastTd;
        var lastImage;
        var imagem1;
        var haItemAtivo;
        var textoLink;

        $('#atualizar').click(function() {
            Links();
        });

        if ($('#menu').is(':hidden'))
            $('#maximizar').text('restaurar');

        $("#campoTurma").change(function() {
            Links();
        });

        $("#campoBimestre").change(function() {
            Links();
        });

        $('#boletimTurma tr').dblclick(function() {
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
                $('body').css('background', '#e1f2d0 url(<?php print IMAGES; ?>/bg.jpg) repeat-y top center');
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
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<table align="center" id="form" width="100%">
    <tr><td align="right" style="width: 100px">Turma: </td><td>
            <select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>" style="width: 650px">
                <?php
                if (in_array($COORD, $_SESSION["loginTipo"]))
                    $restricaoCoord = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador=" . $_SESSION['loginCodigo'] . ")";
                $sql = "SELECT t.codigo, t.numero, c.nome, c.fechamento, m.nome, m.codigo 
                from Turmas t, Cursos c, Modalidades m 
                where t.curso = c.codigo 
                and c.modalidade = m.codigo 
                and t.ano=$ano and (t.semestre=$semestre OR t.semestre=0) 
                $restricaoCoord
                order by c.nome, t.numero";
                $resultado = mysql_query($sql);
                $selected = ""; // controla a alteração no campo select
                if (mysql_num_rows($resultado)) {
                    echo "<option></option>";
                    while ($linha = mysql_fetch_array($resultado)) {
                        if ($linha[0] == $turma) {
                            $selected = "selected";
                            $fechamento = $linha[3];
                        }
                        if ($linha[5] < 1000 || $linha[5] >= 2000)
                            $linha[2] = "$linha[2] [$linha[4]]";
                        echo "<option $selected value='" . crip($linha[0]) . "'>[$linha[1]] $linha[2]</option>";
                        $selected = "";
                    }
                }
                else {
                    echo "<option>Não há turmas cadastrados neste semestre/ano letivo</option>";
                }
                ?>
            </select>
        </td></tr>
    <?php if ($fechamento == 'b') { ?>
        <tr><td align="right">Bimestre: </td><td>
                <select name="campoBimestre" id="campoBimestre">
                    <option></option>
                    <option <?php if ($bimestre == '1')
        echo "selected='selected'";
    else
        echo "";
        ?> value="<?= crip(1) ?>">1º bimestre</option>
                    <option <?php if ($bimestre == '2')
                        echo "selected='selected'";
                    else
                        echo "";
                    ?> value="<?= crip(2) ?>">2º bimestre</option>
                    <option <?php if ($bimestre == '3')
                        echo "selected='selected'";
                    else
                        echo "";
        ?> value="<?= crip(3) ?>">3º bimestre</option>
                    <option <?php if ($bimestre == '4')
        echo "selected='selected'";
    else
        echo "";
        ?> value="<?= crip(4) ?>">4º bimestre</option>
                    <option <?php if ($bimestre == 'final')
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
if (!empty($_GET["turma"])) {
    // restrição de bimestre
    if ($bimestre != 'final' && $fechamento == 'b')
        $sqlBimestre = " IN (SELECT t1.codigo FROM Turmas t1 
			WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
			WHERE t2.codigo = $turma)) AND a.bimestre=$bimestre ";
    else
        $sqlBimestre = " = $turma";

    // consulta no banco	
    $sql = "SELECT 	al.codigo, al.nome, d.codigo, d.numero, d.nome, m.situacao, a.status,
					m.codigo, a.codigo, s.listar, s.habilitar, s.nome, s.sigla, a.bimestre
			FROM Atribuicoes a 
			LEFT JOIN Disciplinas d on a.disciplina=d.codigo 
			LEFT JOIN Matriculas m on m.atribuicao=a.codigo 
			LEFT JOIN Pessoas al on m.aluno=al.codigo
			LEFT JOIN Situacoes s on m.situacao=s.codigo
			WHERE a.turma 
			$sqlBimestre 
			ORDER BY a.bimestre, d.nome, al.nome";
    //echo $sql;
    $resultado = mysql_query($sql);
    if ($resultado)
        while ($l = mysql_fetch_array($resultado)) {
            $bimestres[$l[13]] = $l[13];
            $alunos[$l[0]] = $l[1];
            $disciplinasMedia[$l[8]] = $l[3];
            $disciplinasMediaNome[$l[3]] = $l[3];
            $disciplinas[$l[8]][$l[0]] = $l[7];
            $situacaoListar[$l[0]][$l[8]] = $l[9];
            $situacaoHabilitar[$l[0]][$l[8]] = $l[10];
            $situacaoNome[$l[0]][$l[8]] = $l[11];
            $situacaoSigla[$l[0]][$l[8]] = $l[12];

            $professores = '';
            foreach (getProfessor($l[8]) as $key => $reg)
                $professores[] = $reg['nome'];
            $professor = implode(" / ", $professores);

            $disciplinasNomes[$l[13]][$l[8]][$l[3]] = $l[4] . " - " . $professor;
            $disciplnasStatusNomes[$l[2]][$l[6]] = $l[4] . " (" . $professor . ")";
            $disciplnasStatus[$l[2]] = $l[6];
        }


    $status = 0;
    if ($disciplnasStatusNomes)
        foreach ($disciplnasStatusNomes as $dCodigo => $dNome) {
            if (!$disciplnasStatus[$dCodigo]) {
                print "<font>A disciplina $dNome[0] n&atilde;o foi finalizada.</font><br>\n";
                $status++;
            }
        }

    if ($status)
        print "<font color=\"red\">N&atilde;o &eacute; poss&iacute;vel alterar a situa&ccedil;&atilde;o com di&aacute;rios abertos.</font><br>\n";

    print "<table id=\"boletimTurma\" cellpadding=\"0\" cellspacing=\"0\" border=\"1\" align=\"center\" width=\"100%\" style=\"background:#ECF5E2\">\n";
    print "<tr><th align=\"center\" width=\"10\">#</th><th align=\"center\">Nome</th>\n";

    if ($fechamento == 'b') {
        if ($bimestres)
            foreach ($bimestres as $bim) {
                $colspan = count($disciplinasNomes[$bim]);
                print "<td align='center' style='width: 30px' colspan=\"$colspan\"><b>$bim&ordm; BIM</b></td>";
            }
        if ($bimestre == 'final')
            print "<td align='center' style='width: 30px' colspan=\"$colspan\"><b>FINAL (M&Eacute;DIA ANUAL)</b></td>";
    }

    if ($bimestre != 'final' || $fechamento == 's') {
        print "<td>&nbsp;</td>\n";
        print "<td>&nbsp;</td>\n";
    }

    if ($fechamento != 'b')
        foreach ($bimestres as $bim => $aa)
            foreach ($disciplinasNomes[$bim] as $dSigla => $dNome)
                print "<td>&nbsp;</td>\n";

    print "</tr>\n";
    print "<tr><td>&nbsp</td><td>&nbsp</td>\n";

    if ($bimestres)
        foreach ($bimestres as $bim => $aa)
            foreach ($disciplinasNomes[$bim] as $ddSigla)
                foreach ($ddSigla as $dSigla => $dNome)
                    print "<td align='center' style='width: 30px'><a title='$dNome'>" . $dSigla . "</a></td>";

    if ($bimestre == 'final' && $fechamento == 'b')
        foreach ($disciplinasMediaNome as $ddSigla => $dsNome)
            print "<td align='center' style='width: 30px'>$dsNome</td>";

    print "<td align='center' style='width: 30px'><a title='M&eacute;dia Global'>MG</a></td>\n";
    print "<td align='center' style='width: 30px'><a title='Frequ&ecirc;ncia Global'>FG</a></td>\n";

    print "</tr>\n";
    $i = 1;

    if ($alunos)
        foreach ($alunos as $c => $nome) {
            $dadosGlobal = null;
            $discQdePorAluno = null;
            $media = null;
            $frequencia = null;
            $mediaAnual = null;
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            print "<tr $cdif><td align='center'>$i</td>\n";
            print "<td>\n";
            print "<a href=\"#\" rel='" . INC . "/file.inc.php?type=pic&id=" . crip($c) . "' class='screenshot nav' title='" . mostraTexto($nome) . "'><img style='width: 20px; height: 20px' alt='Embedded Image' src='" . INC . "/file.inc.php?type=pic&id=" . crip($c) . "'></a>\n";
            if ($bimestre != 'final' && $fechamento == 'b')
                $bimestreLink = '&bimestre=' . crip($bimestre);
            print "<a href=\"javascript:$('#index').load('view/aluno/boletim.php?aluno=" . crip($c) . "&turma=" . crip($turma) . $bimestreLink . "'); void(0);\">" . mostraTexto($nome) . "</a>\n";
            print "</a>\n";
            print "</td>";

            foreach ($disciplinas as $dCodigo => $dMatricula) {
                if ($situacaoListar[$c][$dCodigo]) {
                    if ($situacaoHabilitar[$c][$dCodigo]) {
                        $dados = resultado($dMatricula[$c], $dCodigo);
                        print "<td align='center'><a title='Situa&ccedil;&atilde;o: " . $dados['situacao'] . "<br>Faltas: " . $dados['faltas'] . "<br>Aulas Dadas: " . $dados['auladada'] . "<br>Frequ&ecirc;ncia: " . arredondar($dados['frequencia']) . "%'><font color='" . $dados['color'] . "'>" . $dados['media'] . "</font></a></td>";
                    } else {
                        print "<td align='center'><a title='" . $situacaoNome[$c][$dCodigo] . "'>" . $situacaoSigla[$c][$dCodigo] . "</a></td>";
                    }
                } else
                    print "<td align='center'>-</td>";
            }

            if ($bimestre == 'final' && $fechamento == 'b') {
                if ($disciplinasMediaNome)
                    foreach ($disciplinasMediaNome as $dCodigo => $dNumero) {
                        $mediaAnual = resultadoBimestral($c, $turma, $dNumero);
                        print "<td align='center'><font color='" . $mediaAnual['color'] . "'>" . arredondar($mediaAnual['media']) . "</font></td>";
                    }
            }

            $dadosGlobal = resultadoModulo($c, $turma);

            if (!$media = $dadosGlobal['mediaGlobal'])
                $media = '-';
            $frequencia = round($dadosGlobal['frequenciaGlobal'], 1);
            $frequencia = (!$frequencia) ? '-' : $frequencia . '%';

            echo "<td align='center'><font color='" . $dadosGlobal['color'] . "'><b>$media</b></font></td>";
            echo "<td align='center'>$frequencia</td>";

            print "</tr>\n";
            $i++;
        }
    print "</table>\n";
}

$_SESSION['VOLTAR'] = "index";
$_SESSION['LINK'] = VIEW . "/secretaria/relatorios/boletimTurma.php?turma=" . crip($turma) . "&bimestre=" . crip($bimestre);
?>

<div style="text-align: center; margin-top: 10px"><a id="atualizar" href="#" title="Atualizar"><img class="botao" src="<?php print ICONS; ?>/sync.png" /></a></div>

<script>
    if ($('#maximizar').text() == 'Maximizar') {
        $('.alunoNome').hide('slow');
        $('.alunoPrimeiroNome').show('slow');
    }
    else {
        $('.alunoNome').show('slow');
        $('.alunoPrimeiroNome').hide('slow');

    }
</script>