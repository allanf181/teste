<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 7;
$orientacao = "L"; // Landscape
$papel = "A3";

include PATH . LIB . '/fpdf17/pdfDiario.php';

require CONTROLLER . '/frequencia.class.php';
$frequencia = new Frequencias();

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND at.turma = :turma ';
}

if (dcrip($_GET["atribuicao"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $params['atribuicao'] = $atribuicao;
    $sqlAdicional .= ' AND at.codigo = :atribuicao ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicional .= " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

$data = $_GET["data"];

$sqlAdicional .= ' group by p.codigo ';

if ($turma) {
    $pdf = new PDF ();
    $pdf->AliasNbPages();
    $pdf->AddPage($orientacao, $papel);
    $pdf->SetFont($fonte, '', $tamanho);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetLineWidth(.1);
    $pdf->rodape = $SITE_TITLE;

// Cabeçalho
    $pdf->SetFont($fonte, 'B', $tamanho + 5);
    $pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 80);
    $pdf->Cell(85, 28, "", 1, 0, 'C', false);
    $pdf->Cell(200, 28, utf8_decode("R E L A T Ó R I O   D E   F R E Q U Ê N C I A"), 1, 0, 'C', false);

    $i = 0;

    foreach ($frequencia->getListaFrequencias($params, $sqlAdicional) as $reg) {

        if (!$i) {
            $pdf->Cell(115, 7, utf8_decode($reg['disciplina']), 1, 2, 'C', false);
            $pdf->Cell(115, 7, utf8_decode("Turma ".$reg['turma'].' ('.$reg['subturma'].') - '.$reg['bimestreFormat']), 1, 2, 'C', false);
            $pdf->Cell(115, 7, utf8_decode("TURMA: ".$reg['turma']), 1, 2, 'C', false);
            $pdf->Cell(115, 7, utf8_decode("$data"), 1, 0, 'C', false);
            $pdf->Ln();
            $pdf->Ln();

            $pdf->Cell(30, $alturaLinha, utf8_decode("PRONTUÁRIO"), 1, 0, 'C', true);
            $pdf->Cell(70, $alturaLinha, utf8_decode("SITUAÇÃO"), 1, 0, 'C', true);
            $pdf->Cell(160, $alturaLinha, utf8_decode("NOME"), 1, 0, 'C', true);
            $pdf->Cell(50, $alturaLinha, utf8_decode("RG"), 1, 0, 'C', true);
            $pdf->Cell(30, $alturaLinha, utf8_decode("AULAS"), 1, 0, 'C', true);
            $pdf->Cell(30, $alturaLinha, utf8_decode("FALTAS"), 1, 0, 'C', true);
            $pdf->Cell(30, $alturaLinha, utf8_decode("FREQUÊNCIA"), 1, 0, 'C', true);

            $i = 1;
            $pdf->SetFont($fonte, '', $tamanho + 4);
        }

        $pdf->Ln();

        $dados = $frequencia->getFrequencia($reg['codMatricula'], $reg['atribuicao']);
        $pdf->Cell(30, $alturaLinha, utf8_decode($reg['prontuario']), 1, 0, 'C', true);
        $pdf->Cell(70, $alturaLinha, utf8_decode($reg['situacao']), 1, 0, 'C', true);
        $pdf->Cell(160, $alturaLinha, utf8_decode($reg['aluno']), 1, 0, 'L', true);
        $pdf->Cell(50, $alturaLinha, utf8_decode($reg['rg']), 1, 0, 'C', true);
        $pdf->Cell(30, $alturaLinha, utf8_decode($dados['auladada']), 1, 0, 'C', true);
        $pdf->Cell(30, $alturaLinha, utf8_decode($dados['faltas']), 1, 0, 'C', true);
        $pdf->Cell(30, $alturaLinha, utf8_decode($dados['frequencia'] . "%"), 1, 0, 'C', true);
    }

    $pdf->Output();
} else {
    print "TURMA NAO SELECIONADA";
}
?>