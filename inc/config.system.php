<?php

//=======================================//
//           SESSAO DO SISTEMA           //
//=======================================//
$TIMEOUT = 20; // MINUTOS;
$_SESSION_NAME = "IFSP_WD_2014";
session_name($_SESSION_NAME);
session_cache_expire($TIMEOUT*90);
if (!isset($_SESSION)) { session_start(); }
ini_get('register_globals');
ini_set('max_execution_time', 60);

//=======================================//
//           ERROS DO SISTEMA            //
//=======================================//
error_reporting(E_ALL && !E_NOTICE );
ini_set("display_errors", 1);

//=======================================//
//           PATHs GLOBAIS           //
//=======================================//
// Localização do diretório raiz do sistema
define("LOCATION", '/academico', true);

// Diretório de Includes
define("INC", LOCATION.'/inc', true);

//PATH DO DOCUMENT ROOT
define("PATH", str_replace(INC, '', __DIR__), true);

// Local do conector
define("MYSQL", PATH.INC.'/mysql.php', true);

// Local do conector
define("DB2", PATH.INC.'/db2.php', true);

// ================================= //
$_SESSION['LOCATION'] = PATH.LOCATION;
$_SESSION['CONFIG'] = PATH.INC.'/config.inc.php';
// ================================= //

//=======================================//
//        REQUISITOS DO SISTEMA          //
//=======================================//
$EXTENSIONS[] = 'gd';
$EXTENSIONS[] = 'curl';
$EXTENSIONS[] = 'ibm_db2';

//=======================================//
//           VARIAVEIS GLOBAIS           //
//=======================================//
// Diretório de Views
define("VIEW", LOCATION.'/view', true);

// Diretório dos Arquivos enviados por professores
// para alunos.
// Pode ser alterado para um NFS ou Storage
// SEGURANÇA: Não colocar esse diretório dentro da view
define("ARQUIVOS", PATH.LOCATION.'/arquivo', true);

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

// Arquivo de Sessão
define("SESSAO", PATH.INC.'/sessao.inc.php', true);

// Arquivo de Permissão
define("PERMISSAO", PATH.INC.'/permissao.inc.php', true);

// Arquivo de Variavies Globais do Sistema
define("VARIAVEIS", PATH.INC.'/variaveis.inc.php', true);

// Arquivo de Variavies Globais do Sistema
define("MENSAGENS", PATH.INC.'/mensagens.inc.php', true);

// Arquivo de Variavies Globais do Sistema
define("CAPTCHA", 'inc/captcha', true);

?>