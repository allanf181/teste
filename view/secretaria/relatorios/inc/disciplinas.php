<?php
require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/disciplina.class.php';
$disciplina = new Disciplinas();

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicional .= " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

$linha2 = $disciplina->listDisciplinas($params, $sqlAdicional);

$titulo = "Relação de Disciplinas";
$titulo2 = "";
$rodape = $SITE_TITLE;

$fonte = 'Times';
$tamanho = 12;
$alturaLinha = 10;
$orientacao = "L"; //Landscape 
//$orientacao = "P"; //Portrait 
$papel = "A4";
$colunas = array("numero", "disciplina", "curso");
$titulosColunas = array("Código", "Disciplina", "Curso");
$largura = array(30,110,110);

// gera o relatório em PDF
include PATH.LIB.'/relatorio_banco.php';
?>