<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

include PATH . LIB . '/fpdf17/pdfDiario.php';

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

$fonte = 'Times';
$orientacao = "L"; //Portrait 
$papel = "A4";
$tamanho = 5;

$pdf = new PDF ();
$pdf->AliasNbPages();
$pdf->AddPage($orientacao, $papel);
$pdf->SetFont($fonte, '', $tamanho);
$pdf->rodape = $SITE_TITLE;
$pdf->SetFillColor(255, 255, 255);
$pdf->SetLineWidth(.1);

// Cabeçalho
$pdf->SetFont($fonte, 'B', $tamanho + 5);
$pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 60);
$pdf->Cell(85, 20, "", 1, 0, 'C', false);
$pdf->Cell(190, 20, utf8_decode("RELATÓRIO  DE  TROCAS / REPOSIÇÕES"), 1, 0, 'C', false);
$pdf->Ln();
$pdf->Cell(275, 5, utf8_decode(""), 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(5, 5, utf8_decode("N"), 1, 0, 'C', true);
$pdf->Cell(20, 5, utf8_decode("TIPO"), 1, 0, 'C', true);
$pdf->Cell(20, 5, utf8_decode("DATA"), 1, 0, 'C', true);
$pdf->Cell(40, 5, utf8_decode("SOLICITANTE"), 1, 0, 'C', true);
$pdf->Cell(25, 5, utf8_decode("DISCIPLINA"), 1, 0, 'C', true);
$pdf->Cell(40, 5, utf8_decode("PROFESSOR SUB."), 1, 0, 'C', true);
$pdf->Cell(20, 5, utf8_decode("TURMA"), 1, 0, 'C', true);
$pdf->Cell(60, 5, utf8_decode("CURSO"), 1, 0, 'C', true);
$pdf->Cell(45, 5, utf8_decode("AULA"), 1, 0, 'C', true);
$pdf->Ln();

$i = 1;
foreach ($aulaTroca->listTrocas($params, $sqlAdicional) as $reg) {
    if ($i % 2 == 0)
        $pdf->SetFillColor(240, 240, 240);
    else
        $pdf->SetFillColor(255, 255, 255);

    //$reg['aula'] = str_replace(',', '\n', $reg['aula']);
    
    $pdf->Cell(5, 5, utf8_decode($i), 1, 0, 'C', true);
    $pdf->Cell(20, 5, utf8_decode($reg['tipo']), 1, 0, 'C', true);
    $pdf->Cell(20, 5, utf8_decode($reg['dataTrocaFormatada']), 1, 0, 'C', true);
    $pdf->Cell(40, 5, utf8_decode(abreviar($reg['professor'], 20)), 1, 0, 'C', true);
    $pdf->Cell(25, 5, utf8_decode($reg['discNumero']), 1, 0, 'C', true);
    $pdf->Cell(40, 5, utf8_decode(abreviar($reg['professorSub'], 20)), 1, 0, 'C', true);
    $pdf->Cell(20, 5, utf8_decode($reg['turma']), 1, 0, 'C', true);
    $pdf->Cell(60, 5, utf8_decode(abreviar($reg['curso'], 34)), 1, 0, 'C', true);
    
    $aula = explode(',', $reg['aula']);
    foreach($aula as $a) {
        if ($j == 1)
            $pdf->Cell(230, 5, utf8_decode(""), 0, 0, 'L', false);
        
        $pdf->Cell(45, 5, utf8_decode("$a"), 1, 0, 'L', true);
        $j = 1;
        
        $pdf->Ln();
    }
    $i++;
}

$pdf->Output();
?>