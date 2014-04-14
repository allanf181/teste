<?php

$conn_string = "DRIVER={IBM DB2 ODBC DRIVER};DATABASE=$DB2_DB;" .
  "HOSTNAME=$DB2_HOST;PORT=$DB2_PORT;PROTOCOL=TCPIP;UID=$DB2_USER;PWD=$DB2_PASS;";
$conn = db2_connect($conn_string, '', '');

if (!$conn){
    $REG =  "Falha ao tentar acessar o banco de dados DB2.";
    print $REG;
    mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_ERRO', 1)");
    exit;
}

?>