<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/matricula.class.php';
$matricula = new Matriculas();

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

if (dcrip($_GET["turno"])) {
    $turno = dcrip($_GET["turno"]);
    $params['turno'] = $turno;
    $sqlAdicional .= ' AND a.periodo = :turno ';
}

if (dcrip($_GET["situacao"])) {
    $situacao = dcrip($_GET["situacao"]);
    $params['situacao'] = $situacao;
    $sqlAdicional .= ' AND s.codigo = :situacao ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicional .= " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

$sqlAdicional .= " GROUP BY m.codigo ORDER BY a.bimestre, p.nome ";

$linha2 = $matricula->getMatriculas($params, $sqlAdicional);

$titulo = str_repeat(" ", 30) . "Relatório de Matrículas";
if ($turma)
    $titulo2 = 'Turma ' . $linha2[0]['turma'];

if ($turno) {
    require CONTROLLER . '/turno.class.php';
    $turnos = new Turnos();

    $paramsTurno['codigo'] = $turno;
    $turnoNome = $turnos->listRegistros($paramsTurno);
    $titulo2 .= ' [' . $turnoNome[0]['nome'] . ']';
}

$rodape = $SITE_TITLE;
$fonte = 'Times';
$tamanho = 12;
$alturaLinha = 10;
$orientacao = "L"; //Landscape 
$papel = "A4";

$titulosColunas = array("Prontuário", "Nome", "Situação", "Turma", "Curso");
$colunas = array("prontuario", "pessoa", "situacao", "turma", "curso");

$largura = array(22, 80, 50, 20, 100);

// gera o relatório em PDF
include PATH . LIB . '/relatorio_banco.php';
?>