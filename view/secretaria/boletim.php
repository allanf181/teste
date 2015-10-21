<?php
//Esse arquivo é fixo para o aluno.
//Visualização do Boletim do Aluno.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

// COPIA DE:
require PATH.VIEW.'/common/boletim.php';

?>