<?php
//require("funcoes.inc.php");

# Variáveis de conexão

$servidor = "localhost";
$usuario = "root"; 
$senha = "";
$bd = "brtacademico"; 


# Conecta ao sgbd
$conexao = mysql_connect($servidor, $usuario, $senha) or die (mysql_error());
# Configura o charset 
//mysql_set_charset('utf8');
mysql_set_charset('latin1');
# Seleciona o bd  
mysql_select_db($bd);   

?>