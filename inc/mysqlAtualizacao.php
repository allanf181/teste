<?php

# Conecta ao sgbd do servidor de atualizacoes
$conexao = mysql_connect("$IPSRVUPDATE", "$USERSRVUPDATE", "$PASSSRVUPDATE") or die (mysql_error());
mysql_set_charset('utf8');
mysql_select_db("BrtAtualizacao");
 	
?>