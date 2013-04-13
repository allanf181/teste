<?php
//SESSAO
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_SESSION)) { session_start(); }

$DEBUG = 0;
$LOCATION_CRON = dirname(__FILE__).'/';

require $LOCATION_CRON.'../inc/config.inc.php';

if (isset($argv[1])) $DEBUG=1;

require("$LOCATION_CRON"."db2Mysql.php");
require("$LOCATION_CRON"."db2.php");
require("$LOCATION_CRON"."db2Funcoes.php");
require("$LOCATION_CRON"."db2Variaveis.inc.php");
require("$LOCATION_CRON"."../inc/funcoes.inc.php");

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

include ("$LOCATION_CRON"."db2Dipensas.php");

include ("$LOCATION_CRON"."db2DigitaNotas.php");

include ("$LOCATION_CRON"."db2Notas.php");

// CHECANDO A VERSAO DO SISTEMA
$conexao = mysql_connect("$IPSRVUPDATE", "$USERSRVUPDATE", "$PASSSRVUPDATE") or die (mysql_error());
mysql_set_charset('utf8');
mysql_select_db("BrtAtualizacao");

$resultado = mysql_query("SELECT versao FROM BrtAtualizacao.versao");
 if (mysql_num_rows($resultado) != '') {
 	$versao = mysql_result($resultado, 0, "versao");

        include("$LOCATION_CRON"."db2Mysql.php");
	mysql_query("UPDATE Instituicoes SET versaoAtual = '$versao'");
}

// DELETANDO LOGS ANTIGOS
mysql_query("DELETE FROM Logs WHERE datediff(now(), data) > 30");

?>