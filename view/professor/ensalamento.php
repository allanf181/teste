<?php
//Esse arquivo é fixo para o professor.
//Permite a visualização do ensalamento.
//Link visível no menu: PADRÃO SIM.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';

require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

$codigo = dcrip($_GET['turma']);
$subturma = dcrip($_GET['subturma']);

if ($codigo) {
    $tipo = 'turma';
}

if (!$codigo) {
    $tipo = 'professor';
    $codigo = $_SESSION["loginCodigo"];
}

// COPIA DE:
require PATH.VIEW.'/common/ensalamento.php';
