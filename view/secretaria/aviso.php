<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a inserção de avisos para uma pessoa, disciplina, turma, curso ou para todos do sistema.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;

if ($_GET['dados']) {
    require CONTROLLER . "/turma.class.php";
    $turma = new Turmas();

    require CONTROLLER . "/curso.class.php";
    $curso = new Cursos();
    
    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new Pessoas();

    $arr = array();

    foreach($pessoa->listPessoasToJSON($_GET["q"]) as $reg)
        $arr[] = $reg;

    foreach($curso->listCursosToJSON($_GET["q"], $ANO, $SEMESTRE) as $reg)
        $arr[] = $reg;    

    foreach($turma->listTurmasToJSON($_GET["q"], $ANO, $SEMESTRE) as $reg)
        $arr[] = $reg;

    $json_response = json_encode($arr);

    if ($_GET["callback"])
        $json_response = $_GET["callback"] . "(" . $json_response . ")";

    echo $json_response;
    die;
}

require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$para = 'Digite a pessoa, curso ou turma para enviar a mensagem';
$DIV = '#index';
$SITE .= '?';

// COPIA DE:
require PATH.VIEW.'/common/aviso.php';