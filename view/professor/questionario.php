<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a visualização de questionário por professores.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require PERMISSAO;
require FUNCOES;

if ($_GET['dados']) {
    require CONTROLLER . "/aluno.class.php";
    $aluno = new Alunos();

   foreach($aluno->listAlunosToJSON(dcrip($_GET['atribuicao']), $_GET["q"]) as $reg)
        $arr[] = $reg;

    $json_response = json_encode($arr);
        
    if ($_GET["callback"])
        $json_response = $_GET["callback"] . "(" . $json_response . ")";

    echo $json_response;
    die;
}

require_once CONTROLLER . "/questionario.class.php";
$questionario = new Questionarios();

$_SESSION['SITE_RAIZ'] = $SITE;

// COPIA DE:
if (dcrip($_GET['atribuicao']) || dcrip($_POST['atribuicao'])) {
    $_GET['index'] = 'professor';
    require PATH . VIEW . '/common/questionario/questionario.php';
} else {
    $_GET['index'] = 'index';
    require PATH . VIEW . '/common/questionario/base.php';
}