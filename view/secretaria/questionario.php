<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a criação de enquetes para uma pessoa, um tipo, disciplina, turma, curso ou para todos do sistema.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;

require CONTROLLER . "/tipo.class.php";
$tipo = new Tipos();

if ($_GET['dados']) {
    require CONTROLLER . "/turma.class.php";
    $turma = new Turmas();

    require CONTROLLER . "/curso.class.php";
    $curso = new Cursos();

    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new Pessoas();

    $arr = array();

    foreach ($pessoa->listPessoasToJSON($_GET["q"]) as $reg)
        $arr[] = $reg;

    foreach ($tipo->listTiposToJSON($_GET["q"]) as $reg)
        $arr[] = $reg;

    foreach ($curso->listCursosToJSON($_GET["q"], $ANO, $SEMESTRE) as $reg)
        $arr[] = $reg;

    foreach ($turma->listTurmasToJSON($_GET["q"], $ANO, $SEMESTRE) as $reg)
        $arr[] = $reg;

    $json_response = json_encode($arr);

    if ($_GET["callback"])
        $json_response = $_GET["callback"] . "(" . $json_response . ")";

    echo $json_response;
    die;
}

foreach ($tipo->listRegistros() as $t)
    $tipoNew[] = $t['nome'];
$para = 'Digite a pessoa, <a href="#" data-content="' . implode('<br>', $tipoNew) . '" title="Voc&ecirc; pode criar question&aacute;rios para um ou mais tipos de pessoas:">um tipo</a>, curso, ou turma para criar o question&aacute;rio.';

require PERMISSAO;
require FUNCOES;

$_SESSION['SITE_RAIZ'] = $SITE;
$INDEX = '#index';

// COPIA DE:
require PATH . VIEW . '/common/questionario/questionario.php';