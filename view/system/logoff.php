<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Logoff do Sistema
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require '../../inc/config.inc.php';
require VARIAVEIS;

// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}

@session_unset($_SESSION_NAME);
@session_destroy($_SESSION_NAME); 

print "Aguarde... O sistema est&aacute; removendo sua sess&atilde;o...";

print "<script type=\"text/javascript\">\n ";
print " window.location='index.php'; \n";
print "</script>\n";
    
?>
