<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/atribuicao.class.php';
$atribuicao = new Atribuicoes();

include PATH . LIB . '/fpdf17/pdfDiario.php';

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

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicional .= " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;
$res = $atribuicao->getAtribuicaoDocente($params, $sqlAdicional);

$fonte = 'Times';
$orientacao = "L"; //Portrait 
$papel = "A4";
$tamanho = 5;

// gera o relatório em PDF
$pdf = new PDF ();
$pdf->AliasNbPages();
$pdf->AddPage($orientacao, $papel);
$pdf->SetFont($fonte, '', $tamanho);
$pdf->rodape = $SITE_TITLE;
$pdf->SetFillColor(255, 255, 255);
$pdf->SetLineWidth(.1);

// Cabeçalho
$pdf->SetFont($fonte, 'B', $tamanho + 5);
$pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 50);
$pdf->Cell(90, 15, "", 0, 0, 'C', false);
$pdf->Cell(100, 20, utf8_decode("A T R I B U I Ç Ã O     D O C E N T E"), 0, 0, 'C', true);
$pdf->Ln();

$dias = diasDaSemana();

$pdf->SetFont($fonte, 'B', $tamanho + 5);
$pdf->Cell(10, 5, utf8_decode("N."), 1, 0, 'L', true);
$pdf->Cell(20, 5, utf8_decode("Prontuário"), 1, 0, 'L', true);
$pdf->Cell(72, 5, utf8_decode("Professor"), 1, 0, 'L', true);
$pdf->Cell(72, 5, utf8_decode("Disciplina"), 1, 0, 'L', true);
$pdf->Cell(32, 5, utf8_decode("Dia da Semana"), 1, 0, 'L', true);
$pdf->Cell(22, 5, utf8_decode("Sala"), 1, 0, 'L', true);
$pdf->Cell(42, 5, utf8_decode("Horário"), 1, 0, 'L', true);
$pdf->Cell(10, 5, utf8_decode("Total"), 1, 0, 'L', true);
$pdf->Ln();

$pdf->SetFont($fonte, '', $tamanho + 3);

$i=1;
$c=1;
foreach ($res as $reg) {
   
    if ($i % 2 == 0)
        $pdf->SetFillColor(240, 240, 240);
    else
        $pdf->SetFillColor(255, 255, 255);

    $pdf->Cell(10, 5, $i, 1, 0, 'L', true);
    $pdf->Cell(20, 5, utf8_decode($reg['prontuario']), 1, 0, 'L', true);
    $pdf->Cell(72, 5, utf8_decode($reg['pessoa']), 1, 0, 'L', true);
    $pdf->Cell(72, 5, utf8_decode($reg['disciplina']), 1, 0, 'L', true);
    $pdf->Cell(32, 5, html_entity_decode($dias[$reg['diaSemana']]), 1, 0, 'L', true);
    $pdf->Cell(22, 5, utf8_decode($reg['sala']), 1, 0, 'L', true);
    $pdf->Cell(42, 5, utf8_decode($reg['sala']), 1, 0, 'L', true);

    if ($reg['prontuario'] <> @$res[$i]['prontuario']) {
        $pdf->Cell(10, 5, utf8_decode($c), 1, 0, 'L', true);
        $c = 1;
    } else {
        $pdf->Cell(10, 5, '', 0, 0, 'L', true);
        $c++;
    }

    $i++;
    $pdf->Ln();
}

$pdf->Output();
?>