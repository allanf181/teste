<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de avisos para os alunos.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
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

require MENSAGENS;
require PERMISSAO;
require SESSAO;

$para = 'Digite o nome do aluno para enviar a mensagem.';
$DIV = '#professor';
$SITE .= '?atribuicao='.$_GET['atribuicao'];
$_POST['atribuicao'] = $_GET['atribuicao'];
$params['atribuicao'] = dcrip($_GET['atribuicao']);

// COPIA DE:
require PATH.VIEW.'/common/aviso.php';