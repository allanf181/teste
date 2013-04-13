<?php

if (!$LOCATION_CRON) {
    require '../inc/config.inc.php';
}

# Conecta ao sgbd
$conexao = mysql_connect(MY_HOST, MY_USER, MY_PASS) or die (mysql_error());
# Configura o charset 
mysql_set_charset('utf8');
# Seleciona o bd  
mysql_select_db(MY_DB);
?>