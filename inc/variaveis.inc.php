<?php

// SETANDO O ANO E SEMESTRE ATUAL.
if (empty($_SESSION["ano"])) {
    $_SESSION["ano"] = date('Y');
    $semestre = 1;
    if (date('m') > 7)
        $semestre = 2;
    $_SESSION["semestre"] = $semestre;
}

if (isset($_GET["ano"]))
    $_SESSION["ano"] = $_GET["ano"];

if (isset($_GET["semestre"]))
    $_SESSION["semestre"] = $_GET["semestre"];

// FUTURAMENTE RETIRAR
$ano = $_SESSION["ano"];
$semestre = $_SESSION["semestre"];

// PADRAO, MAISCULA GLOBAL VARS
$ANO = $_SESSION["ano"];
$SEMESTRE = $_SESSION["semestre"];

// COLETANDO AS DEFINICOES DE PAPEIS DO BANCO
require CONTROLLER . "/instituicao.class.php";

$instituicao = new Instituicoes();
if ($res = $instituicao->dadosInstituicao()) {
    $SITE_TITLE = $res[0]['nome'];
    $SITE_CIDADE = $res[0]['cidade'];
    $GED = $res[0]['ged'];
    $ADM = $res[0]['adm'];
    $SEC = $res[0]['sec'];
    $COORD = $res[0]['coord'];
    $LIMITE_DIARIO_PROF = $res[0]['limiteAltDiarioProf'];
    $LIMITE_AULA_PROF = $res[0]['limiteInsAulaProf'];
    $ALUNO = $res[0]['aluno'];
    $PROFESSOR = $res[0]['prof'];
    $ENVIOFOTO = $res[0]['envioFoto'];
    $VERSAO = $res[0]['versao'];
    $VERSAOAT = $res[0]['versaoAtual'];
    $DIGITANOTAS = $res[0]['campiDigitaNotas'];
    $LDAP_ATIVADO = $res[0]['ldap_ativado'];
    $LDAP_CACHE = $res[0]['ldap_cache'];
}
// PARA MIGRAÃ‡ÃƒO
//$BD1 = "brtacademico";
//$BD2 = "academico";
?>