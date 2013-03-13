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
if (!$LOCATION_CRON) require CONTROLLER . "/instituicao.class.php";
if ($LOCATION_CRON) require $LOCATION_CRON . "../controller/instituicao.class.php";

$instituicao = new Instituicao();
if ($res = $instituicao->dadosInstituicao()) {
    $SITE_TITLE = $res['nome'];
    $SITE_CIDADE = $res['cidade'];
    $IPSRVUPDATE = $res['ipServidorAtualizacao'];
    $USERSRVUPDATE = $res['usuarioServidorAtualizacao'];
    $PASSSRVUPDATE = $res['senhaServidorAtualizacao'];
    $GED = $res['ged'];
    $ADM = $res['adm'];
    $SEC = $res['sec'];
    $COORD = $res['coord'];
    $LIMITE_DIARIO_PROF = $res['limiteAltDiarioProf'];
    $LIMITE_AULA_PROF = $res['limiteInsAulaProf'];
    $ALUNO = $res['aluno'];
    $PROFESSOR = $res['prof'];
    $VERSAO = $res['versao'];
    $DIGITANOTAS = $res['campiDigitaNotas'];
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