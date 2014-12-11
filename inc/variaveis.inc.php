<?php
// SETANDO O ANO E SEMESTRE ATUAL.
if (empty($_SESSION["ano"]) || empty($_SESSION["semestre"])) {
    $_SESSION["ano"] = date('Y');
    $SEMESTRE = 1;
    if (date('m') > 7)
        $SEMESTRE = 2;
    $_SESSION["semestre"] = $SEMESTRE;
}

if (isset($_GET["ano"]))
    $_SESSION["ano"] = $_GET["ano"];

if (isset($_GET["semestre"]))
    $_SESSION["semestre"] = $_GET["semestre"];

// PADRAO, MAISCULA GLOBAL VARS
$ANO = $_SESSION["ano"];
$SEMESTRE = $_SESSION["semestre"];

// COLETANDO AS DEFINICOES DE PAPEIS DO BANCO
require CONTROLLER . "/instituicao.class.php";

$instituicao = new Instituicoes();
if ($res = $instituicao->listRegistros()) {
    $SITE_TITLE = $res[0]['nome'];
    $SITE_CIDADE = $res[0]['cidade'];
    $GED = $res[0]['ged'];
    $ADM = $res[0]['adm'];
    $SEC = $res[0]['sec'];
    $COORD = $res[0]['coord'];
    $ALUNO = $res[0]['aluno'];
    $PROFESSOR = $res[0]['prof'];
    $ENVIOFOTO = $res[0]['envioFoto'];
    $VERSAO = $res[0]['versao'];
    $VERSAOAT = $res[0]['versaoAtual'];
    $LIMITE_DIARIO_PROF = $res[0]['limiteAltDiarioProf'];
    $DIGITANOTAS = $res[0]['campiDigitaNotas'];
    $LDAP_ATIVADO = $res[0]['ldap_ativado'];
    $LDAP_CACHE = $res[0]['ldap_cache'];
    $LDAP_DROP_LEFT = $res[0]['ldap_drop_left'];
    $EMAIL = $res[0]['email_account'];
}
?>