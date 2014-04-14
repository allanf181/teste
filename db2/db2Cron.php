<?php
//SESSAO

if (!isset($_SESSION)) { session_start(); }

$_SESSION['timeout'] = 'CRON';

$DEBUG = 0;
$LOCATION_CRON = dirname(__FILE__).'/';

require $LOCATION_CRON.'../inc/config.inc.php';

if (isset($argv[1])) $DEBUG=1;

$_SERVER['REQUEST_URI']='null';
$_POST['opcao']='null';
$_SESSION['URL']='null';
$_SESSION['URLANT'] = 'null';
$_SESSION['loginCodigo']='null';
$_SERVER["SERVER_NAME"] = 'null';
$_SERVER['HTTP_X_FORWARDED_FOR'] = 'null';

include ("$LOCATION_CRON"."db2Alunos.php");
include ("$LOCATION_CRON"."db2Professores.php");
include ("$LOCATION_CRON"."db2Horarios.php");

include ("$LOCATION_CRON"."db2CursosDisciplinasNovos.php");
include ("$LOCATION_CRON"."db2TurmasNovos.php");
include ("$LOCATION_CRON"."db2AtribuicoesNovos.php");
include ("$LOCATION_CRON"."db2MatriculasNovos.php");

include ("$LOCATION_CRON"."db2CursosDisciplinas.php");
include ("$LOCATION_CRON"."db2Turmas.php");
include ("$LOCATION_CRON"."db2Atribuicoes.php");
include ("$LOCATION_CRON"."db2Matriculas.php");

include ("$LOCATION_CRON"."db2Notas.php");

// CHECANDO A VERSAO DO SISTEMA
include("$LOCATION_CRON"."../inc/mysqlAtualizacao.php");
$resultado = mysql_query("SELECT versao FROM BrtAtualizacao.versao");
 if (mysql_num_rows($resultado) != '') {
 	$versao = mysql_result($resultado, 0, "versao");
 	
 	$conexao = mysql_connect($servidor, $usuario, $senha) or die (mysql_error());
	mysql_set_charset('utf8');           
	mysql_select_db($bd);		
	mysql_query("UPDATE Instituicoes SET versao = '$versao'");
}

// DELETANDO LOGS ANTIGOS
mysql_query("DELETE FROM Logs WHERE datediff(now(), data) > 30");

?>