<?php

//SESSAO
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_SESSION)) {
    session_start();
}

$DEBUG = 0;
$FULL = 0;
$LOCATION_CRON = dirname(__FILE__) . '/';

require $LOCATION_CRON . '../inc/config.inc.php';

if (isset($argv[1]) && $argv[1] == '-help') {
    print "\nSCRIPT SYNC IFSP DB2 - NAMBEI. \n";
    print "Verifique abaixo todas as opções existentes:\n \n";
    print "-D: executa o script de sincronização em modo DEBUG, mostrando todos os erros e inserções.\n\n";
    print "-F: executa o script em modo FULL que além da sincronização, executa também a função que analisa divergência de notas entre o Nambei e o WebDiário.\n";
    print "Essa opção pode causar lentidão no sistema, utilize somente quando necessário, após fechamento das notas.\n\n";
    die;
} else {
    print "Comando não reconhecido, digite -help para verificar as opções. \n";
    die;
}

if (isset($argv[1]) && $argv[1] == '-D') {
    $DEBUG = 1;
} else {
    print "Comando não reconhecido, digite -help para verificar as opções. \n";
    die;
}

if (isset($argv[2]) && $argv[2] == '-F') {
    $FULL = 1;
} else {
    print "Comando não reconhecido, digite -help para verificar as opções. \n";
}

require("$LOCATION_CRON" . "db2Mysql.php");
require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
require("$LOCATION_CRON" . "db2Variaveis.inc.php");
require("$LOCATION_CRON" . "db2.php");

if (!$DB2_FAIL) {
    require("$LOCATION_CRON" . "db2Funcoes.php");

    include ("$LOCATION_CRON" . "db2Alunos.php");
    include ("$LOCATION_CRON" . "db2Professores.php");
    include ("$LOCATION_CRON" . "db2Horarios.php");

    include ("$LOCATION_CRON" . "db2CursosDisciplinasNovos.php");
    include ("$LOCATION_CRON" . "db2TurmasNovos.php");
    include ("$LOCATION_CRON" . "db2AtribuicoesNovos.php");
    include ("$LOCATION_CRON" . "db2MatriculasNovos.php");

    include ("$LOCATION_CRON" . "db2CursosDisciplinas.php");
    include ("$LOCATION_CRON" . "db2Turmas.php");
    include ("$LOCATION_CRON" . "db2Atribuicoes.php");
    include ("$LOCATION_CRON" . "db2Matriculas.php");

    include ("$LOCATION_CRON" . "db2DigitaNotas.php");
    include ("$LOCATION_CRON" . "db2Notas.php");
    include ("$LOCATION_CRON" . "db2Dispensas.php");

    if ($FULL)
        include ("$LOCATION_CRON" . "db2NotasDivergentes.php");
}

//METRICAS DO SISTEMA
mysql_set_charset('utf8');
// PEGA A CIDADE PRINCIPAL
$sql = "SELECT c.nome as city, COUNT(c.nome) registros 
            FROM Pessoas p, Cidades c 
            WHERE c.codigo = p.cidade 
            GROUP BY c.nome 
            ORDER by registros DESC LIMIT 1";
$result = @mysql_query($sql);
$cidPr = @mysql_fetch_object($result);

//VERIFICA O USO DO SISTEMA
require CONTROLLER . "/atribuicao.class.php";
$atribuicao = new Atribuicoes();
$params = array('ano' => $ANO, 'semestre' => $SEMESTRE);
$res = $atribuicao->getDadosUsoSistema($params);

// INSERINDO INFOS VIA WS
require $LOCATION_CRON . '../lib/nusoap/lib/nusoap.php';
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
        "usoSistema" => $res[0]['uso']));
}

if ($result)
    mysql_query("UPDATE Instituicoes SET versaoAtual = '$result'");

// DELETANDO LOGS ANTIGOS
mysql_query("DELETE FROM Logs WHERE origem LIKE 'CRON%' AND datediff(now(), data) > 30");

//ENVIA OS BOLETINS DIÁRIOS
require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();
$logEmail->send();
?>