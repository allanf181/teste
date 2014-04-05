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

include ("$LOCATION_CRON"."db2DigitaNotas.php");

include ("$LOCATION_CRON"."db2Notas.php");

include ("$LOCATION_CRON"."db2Dispensas.php");

// PEGA A CIDADE PRINCIPAL
$sql = "SELECT c.nome as city, COUNT(c.nome) registros 
            FROM Pessoas p, Cidades c 
            WHERE c.codigo = p.cidade 
            GROUP BY c.nome 
            ORDER by registros DESC LIMIT 1";
$result = @mysql_query($sql);
$cidPr = @mysql_fetch_object($result);

//VERIFICA O USO DO SISTEMA
require("$LOCATION_CRON"."../controller/atribuicao.class.php");
$atribuicao = new Atribuicoes();

$params = array('ano' => $ano, 'semestre' => $semestre);
$res = $atribuicao->getDadosUsoSistema($params);
foreach ($res as $reg) {
    if ($reg['aula'] || $reg['frequencia'] || $reg['avaliacao'] || $reg['nota'])
        $uso++;
    $count++;
}
$uso = round(($uso * 100) / $count);

// INSERINDO INFOS VIA WS
require $LOCATION_CRON.'../lib/nusoap/lib/nusoap.php';
$client = new nusoap_client("https://200.133.218.2:80/wsWD/server.wsdl", true);
$client->setCredentials("WebDiarioWDWS", "W3bD1ari0_WS_WD_##!!", "basic");

if ($client) {
$result = $client->call("setversion", array("nome" => "$SITE_TITLE", 
                                            "cidade" => "$SITE_CIDADE",
                                            "digitaNotas" => "$DIGITANOTAS",
                                            "versao" => "$VERSAO",
                                            "cidadePredominante" => $cidPr->city,
                                            "uname" => php_uname(),
                                            "hostname" => gethostname(),
                                            "usoSistema" => $uso));
}

if ($result)
    mysql_query("UPDATE Instituicoes SET versaoAtual = '$result'");

// DELETANDO LOGS ANTIGOS
mysql_query("DELETE FROM Logs WHERE datediff(now(), data) > 30");

?>