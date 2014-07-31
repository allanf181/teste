<?php
//=======================================//
//           BANCO DE DADOS              //
//=======================================//
//ACESSO AO BANCO DE DADOS - MySQL (LOCAL)
// ATENÇÃO: O NOME DO BANCO DE DADOS É
// VINCULADO COM O MIGRATIONS DE BANCO.
// CASO MUDE O NOME PADRÃO (ACADEMICO),
// É NECESSÁRIO RENOMEAR A PASTA:
// LIB/MIGRATION/MIGRATIONS/ACADEMICO
define("MY_DB", 'academico', true);
define("MY_USER", 'root', true);
define("MY_PASS", '123456', true);
define("MY_HOST", 'localhost', true);
define("MY_PORT", '3306', true);

//ACESSO AO BANCO DE DADOS - NAMBEI
$DB2_DB = 'TESTEBI';
$DB2_USER = 'db2inst1';
$DB2_PASS = 'brtacad';
$DB2_HOST = '192.168.56.101';
$DB2_PORT = 50000;

//=======================================//
//           SESSAO DO SISTEMA           //
//=======================================//
$TIMEOUT = 20; // MINUTOS;
$_SESSION_NAME = "IFSP_WD_2014";
session_name($_SESSION_NAME);
session_cache_expire($TIMEOUT*36);
if (!isset($_SESSION)) { session_start(); }
ini_get('register_globals');

//=======================================//
//           ERROS DO SISTEMA            //
//=======================================//
error_reporting(E_ALL && !E_NOTICE);
ini_set("display_errors", 1);

//=======================================//
//           PATHs GLOBAIS           //
//=======================================//
// Localização do diretório raiz do sistema
define("LOCATION", '/academico', true);
define("PATH", $_SERVER['DOCUMENT_ROOT'], true);

// Diretório de Includes
define("INC", LOCATION.'/inc', true);

// Local do conector
define("MYSQL", PATH.INC.'/mysql.php', true);

// Local do conector
define("DB2", PATH.INC.'/db2.php', true);

// ================================= //
$_SESSION['LOCATION'] = PATH.LOCATION;
$_SESSION['CONFIG'] = PATH.INC.'/config.inc.php';
// ================================= //

//=======================================//
//           VARIAVEIS GLOBAIS           //
//=======================================//
// Diretório de Views
define("VIEW", LOCATION.'/view', true);

// Diretório de Views
define("CONTROLLER", PATH.LOCATION.'/controller', true);

// Diretório de Bibliotecas
define("LIB", LOCATION.'/lib', true);

// Diretório de Imagens
define("IMAGES", VIEW.'/css/images/', true);

// Diretório de Icones
define("ICONS",  VIEW.'/css/icons/', true);

// Arquivo de Funções
define("FUNCOES", PATH.INC.'/funcoes.inc.php', true);

// Arquivo de Permissão
define("PERMISSAO", PATH.INC.'/permissao.inc.php', true);

// Arquivo de Variavies Globais do Sistema
define("VARIAVEIS", PATH.INC.'/variaveis.inc.php', true);

// Arquivo de Variavies Globais do Sistema
define("MENSAGENS", PATH.INC.'/mensagens.inc.php', true);

// Arquivo de Variavies Globais do Sistema
define("CAPTCHA", 'inc/captcha', true);

?>