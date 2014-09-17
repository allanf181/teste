<?php
require dirname(__FILE__).'/../inc/config.inc.php';
require 'fpdf17/pdf.php';

//require VARIAVEIS;

// Instanciation of inherited class
$pdf = new PDF();
$pdf->logo = PATH.VIEW."/css/images/logo.png";
$pdf->A = '12';
$pdf->L = '12';
$pdf->C = '50';
$pdf->titulo = utf8_decode($titulo);
$pdf->titulo2 = utf8_decode($titulo2);
$pdf->rodape = $rodape;
$pdf->AliasNbPages();
$pdf->AddPage($orientacao, $papel);
$pdf->SetFont($fonte, 'B', $tamanho);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetLineWidth(.1);

if (!$linha2) die ('Sem dados para gerar a lista.');

if (array_sum($largura) < 270) {
    foreach ($linha2 as $linha) {
        foreach ($titulosColunas as $coluna => $value) {
            if (strlen($linha[$coluna]) > $ultimaColuna) {
                $ultimaColuna = strlen($linha[$coluna]);
            }
            if (strlen($linha[$coluna]) > $maiorColuna) {
                $maiorColuna = strlen($linha[$coluna]);
                $iMaiorColuna = $coluna;
            }
        }
    }
    $nova = 270 - array_sum($largura);
    if ($nova > $largura[$iMaiorColuna])
        $largura[$iMaiorColuna] = $nova; // aumenta a coluna que mais utiliza caracteres
    $largura[count($largura) - 1] = 0; //ultima coluna eh 0 (automatica)
}

//CONTADOR
$pdf->Cell(10, $alturaLinha, 'N', 1, 0, 'L', true);

foreach ($titulosColunas as $coluna => $value) {
    $pdf->Cell($largura[$coluna], $alturaLinha, utf8_decode($value), 1, 0, 'L', false);
}
$pdf->Ln();

$pdf->SetFont($fonte, '', $tamanho);

$i = 1;
foreach ($linha2 as $linha) {
    if ($i % 2 == 0)
        $pdf->SetFillColor(240, 240, 240);
    else
        $pdf->SetFillColor(255, 255, 255);


    //CONTADOR
    $pdf->Cell(10, $alturaLinha, $i, 1, 0, 'L', true);

    foreach ($colunas as $coluna => $value) {
        $pdf->Cell($largura[$coluna], $alturaLinha, substr(utf8_decode($linha[$value]), 0, $largura[$coluna]-4) , 1, 0, 'L', true);
    }
    $pdf->Ln();
    $i++;
}

$pdf->Output();
?>
