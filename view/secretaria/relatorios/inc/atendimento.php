<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

include PATH . LIB . '/fpdf17/pdfDiario.php';

require CONTROLLER . "/atendimento.class.php";
$atendimento = new Atendimento();

$fonte = 'Times';
$orientacao = "P"; //Portrait 
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
$pdf->Image(PATH . IMAGES . '/logo.png', 12, 12, 50);
$pdf->Cell(90, 17, "", 1, 0, 'C', false);
$pdf->Cell(100, 17, utf8_decode("A T E N D I M E N T O   DO   P R O F E S S O R"), 1, 0, 'C', true);
$pdf->Ln();

$i = 1;
$params['tipo'] = $PROFESSOR;
$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;

foreach ($atendimento->listAtendimento($params) as $reg) {
    if ($i % 2 == 0)
        $pdf->SetFillColor(240, 240, 240);
    else
        $pdf->SetFillColor(255, 255, 255);

    $pdf->Cell(90, 5, utf8_decode($reg['nome']), 1, 0, 'L', true);

    $j = 0;
    $conteudo = explode("\n", wordwrap(str_replace("\r\n", ";", utf8_decode($reg['horario'])), 60));
    foreach ($conteudo as $j => $trecho) {
        if($j) $pdf->Cell(90, 5, "", 1, 0, 'L', true);
        $pdf->Cell(100, 5, ($conteudo[$j]), 1, 0, 'L', true);
        $pdf->Ln();
        $j=1;
    }

    $pdf->Ln();
    $i++;
}
$pdf->Output();
?>