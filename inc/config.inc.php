<?php
//=======================================//
//           BANCO DE DADOS              //
//=======================================//
//ACESSO AO BANCO DE DADOS - MySQL (LOCAL)
// ATENÇÃO: O NOME DO BANCO DE DADOS É
// VINCULADO COM O MIGRATIONS DE BANCO.
// CASO MUDE O NOME PADRÃO (ACADEMICO),
// É NECESSÁRIO RENOMEAR A PASTA:
// LIB/MIGRATION/MIGRATIONS/ACADEMICO
define("MY_DB", 'academico', true);
define("MY_USER", 'root', true);
define("MY_PASS", '123456', true);
define("MY_HOST", 'localhost', true);
define("MY_PORT", '3306', true);

//ACESSO AO BANCO DE DADOS - NAMBEI
$DB2_DB = 'TESTEBI';
$DB2_USER = 'db2inst1';
$DB2_PASS = 'brtacad';
$DB2_HOST = '192.168.56.101';
$DB2_PORT = 50000;

require 'config.system.php';

?>