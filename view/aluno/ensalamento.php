<?php
//Esse arquivo é fixo para o aluno.
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

$codigo = dcrip($_GET['atribuicao']);

$tipo = 'atribuicao';
if (!$codigo) {
    $tipo = 'aluno';
    $codigo = $_SESSION["loginCodigo"];
}

// COPIA DE:
require PATH.VIEW.'/common/ensalamento.php';

?>