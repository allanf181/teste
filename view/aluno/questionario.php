<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a visualização de questionário por alunos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

$_SESSION['SITE_RAIZ'] = $SITE;

if(dcrip($_GET['atribuicao']))
    $_GET['index'] = 'aluno';
else
    $_GET['index'] = 'index';

// COPIA DE:
require PATH.VIEW.'/common/questionario/base.php';