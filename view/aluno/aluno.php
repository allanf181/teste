<?php 
//Esse arquivo é fixo para o aluno. Não entra em permissões
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;

if (!in_array($ALUNO, $_SESSION["loginTipo"])) {
    print "Você não tem permissão. :(";
    die;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>

<table width="100%" align="center" border="0">
<?php
if ($_GET["atribuicao"]) {
    $atribuicao=dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];

    require CONTROLLER . "/atribuicao.class.php";
    $att = new Atribuicoes();
    $res = $att->getAtribuicao($atribuicao);
    
    $bimestre="SEMESTRAL";
    $fechamento=$res['fechamento'];
    $_SESSION['semestre']=$res['semestre'];
    $_SESSION['ano']=$res['ano'];

    if ($fechamento == 'a') $bimestre="ANUAL";

    $numeroBimestre=null;
    if ($res['bimestre']!="" && $res['bimestre']>0){
        $numeroBimestre=$res['bimestre'];
        $bimestre=abreviar($res['bimestre']."&ordm; BIMESTRE", 100);
    }

    ?>
    <h2><?=abreviar($res['disciplina'].": ".$res['turma']."/".$res['curso'], 150)?></h2>
    <h2 id='titulo_disciplina_modalidade'><?=$bimestre?><br></h2><br />
    <tr align='center'>
    <td><a class='nav professores_item' href="javascript:$('#aluno').load('<?=VIEW?>/aluno/aula.php?atribuicao=<?=crip($atribuicao)?>'); void(0);"><img style='width: 100px' src='<?=IMAGES?>/aulas.png' /><br />Aulas</a></td>
    <td><a class='nav professores_item' href="javascript:$('#aluno').load('<?=VIEW?>/aluno/avaliacao.php?atribuicao=<?=crip($atribuicao)?>'); void(0);"><img style='width: 100px' src='<?=IMAGES?>/avaliacoes.png' /><br />Avalia&ccedil;&otilde;es</a></td>
    <td><a class='nav professores_item' href="javascript:$('#aluno').load('<?=VIEW?>/aluno/ensalamento?atribuicao=<?=crip($atribuicao)?>'); void(0);"><img style='width: 100px' src='<?=IMAGES?>/horario.png' /><br />Hor&aacute;rio da Disciplina</a></td>
    <td><a class='nav professores_item' href="javascript:$('#aluno').load('<?=VIEW?>/aluno/aviso?atribuicao=<?=crip($atribuicao)?>'); void(0);"><img style='width: 100px' src='<?=IMAGES?>/aviso.png' /><br />Avisos</a></td>
    <td><a class='nav professores_item' href="javascript:$('#aluno').load('<?=VIEW?>/aluno/boletim.php?turma=<?=crip($res['turmaCodigo'])?>&aluno=<?=crip($aluno)?>&bimestre=<?=crip($numeroBimestre)?>'); void(0);"><img style='width: 100px' src='<?=IMAGES?>/boletim.png' /><br />Boletim Escolar</a></td>
    <?php
    if ($bimestre=="SEMESTRAL" || $bimestre=="1&ordm; BIMESTRE") {
        ?>
    	<td><a class='nav professores_item' href="javascript:$('#aluno').load('<?=VIEW?>/aluno/planoEnsino.php?atribuicao=<?=crip($atribuicao)?>'); void(0);"><img style='width: 100px' src='<?=IMAGES?>/planoEnsino.png' /><br />Plano de Ensino</a></td>
        <?php } ?>
    </tr>
    <tr><td colspan=10 align='center'>
    <hr>
    PROFESSOR(ES): <br />
    <?php
    foreach(getProfessor($atribuicao) as $key => $reg) {
        ?>
        <a href='#' rel='<?=INC?>/file.inc.php?type=pic&id=<?=crip($reg['codigo'])?>&timestamp=<?=time()?>' class='screenshot' title='Teste'>
        <img style='width: 20px; height: 20px' alt='Embedded Image' src='<?=INC?>/file.inc.php?type=pic&id=<?=crip($reg['codigo'])?>&timestamp=<?=time()?>' /></a>
        <a title='Curr&iacute;culo Lattes' target="_blank" href="<?=$reg['lattes']?>"><?=$reg['nome']?></a>
        <br>
        <?php
    }
       
    ?>
    <hr>
    </tr></tr>
    </table>
    <?php
}

?>
<div id="aluno"></div>
<script>
    $('#aluno').load('<?php print VIEW."/aluno/aviso?atribuicao=".crip($atribuicao); ?>');
</script>