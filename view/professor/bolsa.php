<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite acompanhar os relatórios de atividades das bolsas que participa.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$_SESSION['SITE_RAIZ'] = $SITE;

// COPIA DE:
require PATH . VIEW . '/common/bolsas/bolsa.php';