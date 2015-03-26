<?php

//SESSAO
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_SESSION)) {
    session_start();
}

$DEBUG = 0;
$FULL = 0;
$HELP = 0;
$LOCATION_CRON = dirname(__FILE__) . '/';

require $LOCATION_CRON . '../inc/config.inc.php';

$argv = $_SERVER['argv'];
$totalArgv = count($argv);

if ($totalArgv > 1) {
    for ($x = 1; $x < $totalArgv; $x++) {
        switch ($argv[$x]) {
            case '--D':
                $DEBUG = 1;
                print "MODE DEBUG ON\n";
                break;
            case '--F':
                $FULL = 1;
                print "MODE FULL ON\n";
                break;

            case '--help':
                $HELP = 1;
                print "\nSCRIPT SYNC IFSP DB2 - NAMBEI. \n";
                print "Verifique abaixo todas as opções existentes:\n \n";
                print "--D: executa o script de sincronização em modo DEBUG, mostrando todos os erros e inserções.\n\n";
                print "--F: executa o script em modo FULL que além da sincronização, executa também a função que analisa divergência de notas entre o Nambei e o WebDiário.\n";
                print "Essa opção pode causar lentidão no sistema, utilize somente quando necessário, após fechamento das notas.\n\n";
                die;
        }
    }
    if (!$DEBUG && !$FULL && !$HELP) {
        print "Comando não reconhecido, digite --help para verificar as opções. \n";
        die;
    }
}

require("$LOCATION_CRON" . "db2Mysql.php");
require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
require("$LOCATION_CRON" . "db2Variaveis.inc.php");
require("$LOCATION_CRON" . "db2.php");

require("$LOCATION_CRON" . "db2WS.php");

if (!$DB2_FAIL) {
    require("$LOCATION_CRON" . "db2Funcoes.php");

    include ("$LOCATION_CRON" . "db2Professores.php");
    include ("$LOCATION_CRON" . "db2Alunos.php");
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

// POR FAVOR, NAO ADICIONE NENHUM SCRIPT EXTERNO
// ISSO IMPEDE A ATUALIZACAO DO GIT
?>