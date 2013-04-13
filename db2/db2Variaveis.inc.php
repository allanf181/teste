<?php

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

$ano = $_SESSION["ano"];
$semestre = $_SESSION["semestre"];

// COLETANDO AS DEFINICOES DE PAPEIS DO BANCO
$resultado = mysql_query("SELECT * FROM Instituicoes");
$SITE_TITLE = @mysql_result($resultado, 0, "nome");
$SITE_CIDADE = @mysql_result($resultado, 0, "cidade");
$IPSRVUPDATE = @mysql_result($resultado, 0, "ipServidorAtualizacao");
$USERSRVUPDATE = @mysql_result($resultado, 0, "usuarioServidorAtualizacao");
$PASSSRVUPDATE = @mysql_result($resultado, 0, "senhaServidorAtualizacao");
$GED = @mysql_result($resultado, 0, "ged");
$ADM = @mysql_result($resultado, 0, "adm");
$SEC = @mysql_result($resultado, 0, "sec");
$COORD = @mysql_result($resultado, 0, "coord");
$LIMITE_DIARIO_PROF = @mysql_result($resultado, 0, "limiteAltDiarioProf");
$LIMITE_AULA_PROF = @mysql_result($resultado, 0, "limiteInsAulaProf");
$ALUNO = @mysql_result($resultado, 0, "aluno");
$PROFESSOR = @mysql_result($resultado, 0, "prof");
$VERSAO = @mysql_result($resultado, 0, "versao");
$DIGITANOTAS = @mysql_result($resultado, 0, "campiDigitaNotas");
$VERSAOAT = @mysql_result($resultado, 0, "versaoAtual");
?>