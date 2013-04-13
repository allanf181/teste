<?php
// VERIFICANDO A SESSÃO DO USUÁRIO
if (isset($_SESSION['timeout']) && $_SESSION['timeout'] + $TIMEOUT * 60 < time() && $_SESSION['timeout'] != 'CRON') {
    $_SESSION['timeout'] = time();
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#index').load('<?php print VIEW; ?>/logoff.php');
        });
    </script>
    <?php
} else {
    $_SESSION['timeout'] = time();
}

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

$ano = $_SESSION["ano"];
$semestre = $_SESSION["semestre"];

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
    $VERSAO = $res[0]['versao'];
    $VERSAOAT = $res[0]['versaoAtual'];
    $DIGITANOTAS = $res[0]['campiDigitaNotas'];
}

// GERA O VOLTAR AUTOMATICAMENTE
if (strripos($_SERVER['REQUEST_URI'], 'menu=professor') != true &&
        strripos($_SERVER['REQUEST_URI'], 'relatorios') != true) {
    if (isset($_SESSION['URL']))
        $VOLTAR = $_SESSION['URL'];

    if (!isset($_POST['opcao']) && !isset($_GET['opcao'])) {
        $VOLTAR = null;
        $_SESSION['URLANT'] = $VOLTAR;
        $_SESSION['URL'] = str_replace('/academico/', '', $_SERVER['REQUEST_URI']);
    } else {
        $VOLTAR = $_SESSION['URLANT'];
    }
}

// PARA MIGRAÃ‡ÃƒO
//$BD1 = "brtacademico";
//$BD2 = "academico";
?>