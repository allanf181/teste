<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe o Formulário de Preferência de Atividades cadastradas e finalizadas pelos Professores.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$tabela = 'RIT';

// COPIA DE:
require PATH.VIEW.'/secretaria/atribuicao_docente/common/td.php';