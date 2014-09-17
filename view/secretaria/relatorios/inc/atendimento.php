<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

include PATH . LIB . '/fpdf17/pdfDiario.php';

require CONTROLLER . "/ftdDado.class.php";
$ftd = new FTDDados();

$pessoa = new Pessoas();

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
$pdf->Image(PATH . IMAGES . '/logo.png', 12, 12, 80);
$pdf->Cell(90, 27, "", 1, 0, 'C', false);
$pdf->Cell(100, 27, utf8_decode("A T E N D I M E N T O   DO   P R O F E S S O R"), 1, 0, 'C', false);
$pdf->Ln();

$dias = diasDaSemana();

$i = 1;
$params['tipo'] = $PROFESSOR;
$sqlAdicional = ' AND pt.tipo = :tipo ';
foreach($pessoa->listPessoasTipos($params, $sqlAdicional) as $reg) {
    if ($i % 2 == 0)
        $pdf->SetFillColor(240, 240, 240);
    else
        $pdf->SetFillColor(255, 255, 255);

    $pdf->Cell(90, 5, utf8_decode($reg['nome']), 1, 0, 'L', true);

    $j = 0;
    foreach ($ftd->getAtendimentoAluno($reg['codigo'], $ANO, $SEMESTRE) as $dia => $h) {
        if ($j == 1)
            $pdf->Cell(90, 5, utf8_decode(""), 1, 0, 'L', true);

        $diaSemana = $dias[$dia + 1];
        $diaSemana = html_entity_decode($diaSemana, ENT_QUOTES, "UTF-8");
        $ES = $h[1] . ' às ' . $h[2];
        $pdf->Cell(100, 5, utf8_decode("$diaSemana das $ES"), 1, 0, 'L', true);
        $j = 1;
        $pdf->Ln();
    }
    $pdf->Ln();
    $i++;
}
$pdf->Output();
?>