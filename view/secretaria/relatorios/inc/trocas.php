<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/aulaTroca.class.php';
$aulaTroca = new AulasTrocas();

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

$sqlAdicional = " AND coordenadorAceite = 'S'";

$linha2 = $aulaTroca->listTrocas($params, $sqlAdicional);

$titulo = str_repeat(" ", 30) . "Relatório de Trocas/Reposições";
if ($turma)
    $titulo2 = 'Turma ' . $linha2[0]['turma'];

$rodape = $SITE_TITLE;
$fonte = 'Times';
$tamanho = 11;
$alturaLinha = 10;
$orientacao = "L"; //Landscape 
$papel = "A4";

$titulosColunas = array("Tipo", "Data", "Professor", "Disciplina", "Turma", "Curso");
$colunas = array("tipo", "dataTrocaFormatada", "professor", "disciplina", "turma", "curso");

$largura = array(22, 25, 60, 60, 20, 80);

// gera o relatório em PDF
include PATH . LIB . '/relatorio_banco.php';
?>