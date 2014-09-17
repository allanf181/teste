<?php
require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . "/aula.class.php";
$aula = new Aulas();

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';
}

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;
$sqlAdicional.= ' AND t.ano=:ano AND (t.semestre=:semestre OR t.semestre=0) GROUP BY a.codigo ORDER BY p.nome ';

$linha2 = $aula->listLancamentoAula($params, $sqlAdicional);

$titulo = "LANÇAMENTO DE AULAS POR DISCIPLINA";
$titulo2 = "";
$rodape = $SITE_TITLE;

$fonte = 'Times';
$tamanho = 10;
$alturaLinha = 10;
$orientacao = "L"; //Landscape
//$orientacao = "P"; //Portrait
$papel = "A4";
$colunas = array("professor", "disciplina", "ch", "aulas", "numero", "curso");
$titulosColunas = array("PROFESSOR", "DISCIPLINA", "C. HORÁRIA", "QDE. AULAS", "TURMA", "CURSO");
$largura = array(70,80,22,23,20,60);

// gera o relatÃ³ em PDF
include PATH.LIB.'/relatorio_banco.php';
?>