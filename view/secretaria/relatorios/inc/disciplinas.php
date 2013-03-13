<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

// lista os alunos cadastrados

$restricao = ""; // padrão é sem restrição

if (!empty($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $restricao = " and c.codigo=$curso";
}

$sql = "select d.numero, d.modulo, d.nome, SUBSTRING(c.nome, 1, 50) as curso
from Disciplinas d, Cursos c where d.curso = c.codigo $restricao order by d.nome";

//print $sql;

$titulo = "Relação de Disciplinas";
$titulo2 = "";
$rodape = $SITE_TITLE;

$fonte = 'Times';
$tamanho = 12;
$alturaLinha = 10;
$orientacao = "L"; //Landscape 
//$orientacao = "P"; //Portrait 
$papel = "A4";
$colunas = array("d.numero", "d.modulo", "d.nome","curso");
$titulosColunas = array("Código", "Módulo", "Disciplina", "Curso");
$largura = array(30,20,110,110);

// gera o relatório em PDF
include PATH.LIB.'/relatorio_banco.php';
?>