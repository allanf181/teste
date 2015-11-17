<?php
require '../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/controleListagens.class.php";
$controller = new ControleListagens();

// LISTAGEM OCORRENCIAS
if ($_GET["item"]) {
    $dados = array('pessoa' => $_SESSION['loginCodigo'], 'data' => 'NOW()', 'item' => $_GET['item']);
    $ret = $controller->insertOrUpdate($dados);    
}
else{
    echo "erro";
}