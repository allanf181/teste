<?php
//Esse arquivo é fixo para o aluno.
//Tela principal de acesso do aluno.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

?>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

<?php
if ($_GET["atribuicao"]) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];

    require CONTROLLER . "/atribuicao.class.php";
    $att = new Atribuicoes();
    $res = $att->getAtribuicao($atribuicao);

    $bimestre = "SEMESTRAL";
    $fechamento = $res['fechamento'];
    $_SESSION['semestre'] = $res['semestre'];
    $_SESSION['ano'] = $res['ano'];

    if ($fechamento == 'a')
        $bimestre = "ANUAL";

    $numeroBimestre = null;
    if ($res['bimestre'] != "" && $res['bimestre'] > 0) {
        $numeroBimestre = $res['bimestre'];
        $bimestre = abreviar($res['bimestre'] . "&ordm; BIMESTRE", 100);
    }
    ?>
    <h2><?= abreviar($res['disciplina'] . ": " . $res['turma'] . "/" . $res['curso'], 150) ?></h2>
    <div style="float: left">
        <a title='IN&Iacute;CIO' href="javascript:$('#index').load('<?= VIEW; ?>/aluno/aluno.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);">
            <img src='<?= ICONS ?>/home.png'>
        </a>
    </div>
    <div>
        <h2 id='titulo_disciplina_modalidade'><?= $bimestre ?></h2>
    </div>
    <table width="100%" align="center" border="0">        
        <tr align='center'>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/aula.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/aulas.png' /><br />Aulas</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/avaliacao.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/avaliacoes.png' /><br />Avalia&ccedil;&otilde;es</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/arquivo.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/arquivo.png' /><br />Material de Aula</a></td>    
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/ensalamento.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/horario.png' /><br />Hor&aacute;rio da Disciplina</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/aviso.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/aviso.png' /><br />Avisos</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/chat.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);"><div id="imageChat"><img style='width: 70px' src='<?= INC ?>/file.inc.php?type=chat&atribuicao=<?= crip($atribuicao) ?>' /></div>Chat (Atendimento)</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/atvAcadEmica.php?aluno=<?= crip($aluno) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/atvAcadEmicas.png' /><br />Atividades Acad&ecirc;micas</a></td>

            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/boletim.php?turma=<?= crip($res['turmaCodigo']) ?>&aluno=<?= crip($aluno) ?>&bimestre=<?= crip($numeroBimestre) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/boletim.png' /><br />Boletim Escolar</a></td>
            <?php
            if ($bimestre == "ANUAL" || $bimestre == "SEMESTRAL" || $bimestre == "1&ordm; BIMESTRE") {
                ?>
                <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#aluno').load('<?= VIEW ?>/aluno/planoEnsino.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);"><img style='width: 70px' src='<?= IMAGES ?>/planoEnsino.png' /><br />Plano de Ensino</a></td>
            <?php } ?>
        </tr>
        <tr><td colspan="9" align='center'>
                <hr>
                PROFESSOR(ES): <br />
                <?php
                require CONTROLLER . "/professor.class.php";
                $professor = new Professores();

                print $professor->getProfessor($atribuicao, 1, '<br>', 1, 1);
                ?>
                <hr>
        </tr></tr>
    </table>
    <?php
    if ($codModalidade != 1004 && $codModalidade != 1006 && $codModalidade != 1007 && ($bimestre == 4 || $bimestre == 0))
        print "<font color=\"red\">A Recupera&ccedil;&atilde;o Final / Reavalia&ccedil;&atilde;o ser&aacute; realizada pelo Nambei e n&atilde;o estar&aacute; dispon&iacute;vel no Webdi&aacute;rio.</font>";
}
?>
<div id="aluno"></div>
<script>
    $('#aluno').load('<?php print VIEW . "/aluno/aviso?atribuicao=" . crip($atribuicao); ?>');
</script>