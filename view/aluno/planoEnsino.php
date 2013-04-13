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

if (in_array($ALUNO, $_SESSION["loginTipo"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];

    require CONTROLLER . "/planoEnsino.class.php";
    $plano = new PlanosEnsino();
    $res = $plano->listPlanoEnsino($atribuicao, 'validado');
    
    if ($res) {
        $numeroAulaSemanal = $res["numeroAulaSemanal"];
	$totalHoras = $res["totalHoras"];
	$totalAulas = $res["totalAulas"];
	$numeroProfessores = $res["numeroProfessores"];
	$ITEM['2 - EMENTA'] = $res["ementa"];
	$ITEM['3 - OBJETIVO'] = $res["objetivo"];
	$ITEM['4 - CONTEÚDO PROGRAMÁTICO'] = $res["conteudoProgramatico"];
	$ITEM['5 - METODOLOGIA'] = $res["metodologia"];
	$ITEM['6 - RECURSOS DIDÁTICOS'] = $res["recursoDidatico"];
	$ITEM['7 - AVALIAÇÃO'] = $res["avaliacao"];
	$ITEM['7.1 - RECUPERAÇÃO PARALELA'] = $res["recuperacaoParalela"];
	$ITEM['7.2 - RECUPERAÇÃO FINAL/INSTRUMENTO FINAL DE AVALIAÇÃO'] = $res["recuperacaoFinal"];
	$ITEM['8 - BIBLIOGRAFIA BÁSICA'] = $res["bibliografiaBasica"];
	$ITEM['8.1 - BIBLIOGRAFIA COMPLEMENTAR'] = $res["bibliografiaComplementar"];
	$disciplina = $res["disciplina"];
	$numero = $res["numero"];
	$CH = $res["ch"];
	$modalidade = $res["modalidade"];
	$finalizado = $res["finalizado"];
	$curso = $res["curso"];
		
	$professores='';
	foreach(getProfessor(dcrip($_GET['atribuicao'])) as $key => $reg)
            $professores[] = "<a target=\"_blank\" href=".$reg['lattes'].">".$reg['nome']."</a>";
            $professor = implode(" / ", $professores);


        ?>
        <div class='professores_textarea'>
	<table border="1" width="100%" cellpadding="2" cellspacing="0">
		<tr><td colspan="2" align="center"><h3><font size="4">PLANO DE ENSINO</font></h3></td><td>CAMPUS: <b> <?php print $SITE_CIDADE; ?></b></td></tr>
		<tr><td colspan="3">1 - IDENTIFICAÇÃO</td></tr>
		<tr><td colspan="3">CURSO: <b><?php print $curso; ?></b></td></tr>
		<tr><td colspan="3">COMPONENTE CURRICULAR: <b><?php print $disciplina; ?></b></td></tr>
		<tr><td colspan="3">CÓDIGO DISCIPLINA: <b><?php print $numero; ?></b></td></tr>
		<tr><td>SEMESTRE/ANO: <b><?php print "$semestre/$ano"; ?></b></td><td>NÚMERO DE AULAS SEMANAIS: <b><?php print $numeroAulaSemanal; ?><b></td>
		<td>ÁREA: <b><?php print $modalidade; ?></b></td></tr>
		<tr><td>TOTAL DE HORAS: <b><?php print $totalHoras; ?></b></td><td>TOTAL DE AULAS: <b><?php print $totalAulas; ?></b></td>
		<td>NÚMERO DE PROFESSORES: <b><?php print $numeroProfessores; ?></b></td></tr>
		<tr><td colspan="3">PROFESSOR(A) RESPONSÁVEL: <b><?php print $professor; ?></b></td></tr>
	</table>
	<br>
        <?php
        foreach($ITEM as $chave => $valor) {
        ?>
            <table border="1" width="100%" cellpadding="2" cellspacing="0">
            <tr><td><b><?php print $chave; ?></b></td></tr>
            <tr><td><?php print nl2br($valor); ?></td></tr>
            </table>
            <br>
        <?php
        }
        ?>
        </div>
    <?php
    } else {
        print "Plano de Ensino ainda n&atilde;o disponibilizado.";
    }
}
?>