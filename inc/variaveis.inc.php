<?php

require_once CONTROLLER . "/pessoa.class.php";
$p = new Pessoas();

// SETANDO O ANO E SEMESTRE ATUAL.
if (empty($_SESSION["anoAtual"]) || empty($_SESSION["semestreAtual"]) || isset($_GET['retorno'])) {
    $_SESSION["anoAtual"] = date('Y');
    $SEMESTRE = 1;
    if (date('m') > 7)
        $SEMESTRE = 2;
    $_SESSION["semestreAtual"] = $SEMESTRE;
    $_SESSION['ano']=$_SESSION['anoAtual'];
    $_SESSION['semestre']=$_SESSION['semestreAtual'];
    
    if (isset($_GET['retorno'])){
        // ATUALIZA OS DADOS DE ANO/SEMESTRE NO REGISTRO DA PESSOA
        $p->updateAnoSemestre($_SESSION['loginCodigo'], $_SESSION['ano'], $_SESSION['semestre']);
    }
}

// SALVA O ANO/SEMESTRE ATUAL NO PERFIL DA PESSOA
$_SESSION['anoOuSemestreAlterado']=false;

if (isset($_GET["ano"]) || isset($_GET["semestre"])){
    $_SESSION["ano"] = $_GET["ano"];
    $_SESSION["semestre"] = $_GET["semestre"];
    
    // ATUALIZA OS DADOS DE ANO/SEMESTRE NO REGISTRO DA PESSOA
    $p->updateAnoSemestre($_SESSION['loginCodigo'], $_SESSION['ano'], $_SESSION['semestre']);
}

$dados = $p->getAnoSemestre($_SESSION['loginCodigo']);

if (!empty($dados[0])){
    $_SESSION['ano']=$dados[0];
    $_SESSION['semestre']=$dados[1];
}

// VALIDA O SEMESTRE/ANO ATUAL PARA INCLUIR AVISO AO USUÁRIO DE QUE O SEMESTRE/ANO É DIFERENTE DO ATUAL
if ($_SESSION['ano']!=$_SESSION['anoAtual'] || $_SESSION['semestre']!=$_SESSION['semestreAtual']){
    $_SESSION['anoOuSemestreAlterado']=true;
}


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
    $SSP = $res[0]['ssp'];
    $CAE = $res[0]['cae'];
    $ENVIOFOTO = $res[0]['envioFoto'];
    $VERSAO = $res[0]['versao'];
    $VERSAOAT = $res[0]['versaoAtual'];
    $LIMITE_DIARIO_PROF = $res[0]['limiteAltDiarioProf'];
    $DIGITANOTAS = $res[0]['campiDigitaNotas'];
    $LDAP_ATIVADO = $res[0]['ldap_ativado'];
    $LDAP_CACHE = $res[0]['ldap_cache'];
    $LDAP_DROP_LEFT = $res[0]['ldap_drop_left'];
    $LDAP_PASS = $res[0]['ldap_pass'];
    $EMAIL = $res[0]['email_account'];
    $LINK_RECUPERAR_SENHA = $res[0]['link_recuperar_senha'];

    //INFORMAR O SISTEMA TODOS OS PAPÉIS EXISTENTES.
    $PAPEIS = array('GED' => 'Ger&ecirc;ncia Educacional',
        'ADM' => 'Administrador',
        'SEC' => 'Secretaria',
        'COORD' => 'Coordenador',
        'ALUNO' => 'Aluno',
        'PROFESSOR' => 'Professor',
        'SSP' => 'Servi&ccedil;o Sociopedag&oacute;gico',
        'CAE' => 'Coordenadoria de Apoio ao Ensino');
}
?>