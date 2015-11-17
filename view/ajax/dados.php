<?php
require '../../inc/config.inc.php';
require VARIAVEIS;

$table = $_GET['t']; // TABLE

require_once CONTROLLER . "/doctrine/".$table."Controller.php";

$class = $table."Controller";
print (new $class())->getListaJson();
