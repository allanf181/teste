<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

include PATH . LIB . '/fpdf17/pdfDiario.php';

require CONTROLLER . "/bolsa.class.php";
$bolsa = new Bolsas();

require CONTROLLER . "/bolsaAluno.class.php";
$bolsaAluno = new BolsasAlunos();

require CONTROLLER . "/bolsaDisciplina.class.php";
$bolsaDisciplina = new BolsasDisciplinas();

require CONTROLLER . "/bolsaRelatorio.class.php";
$bolsaRelatorio = new BolsasRelatorios();

if (dcrip($_GET["codigo"])) {
    $codigo = dcrip($_GET["codigo"]);
    $params['codigo'] = $codigo;
    $sqlAdicional .= ' AND b.codigo = :codigo ';
}

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

$resAluno = $bolsaAluno->listAlunos($params, $sqlAdicional);
foreach ($resAluno as $reg) {
    $alunos[$reg['aluno']] = $reg['aluno'];
    $titulo = $reg['titulo'];
}

$resDisc = $bolsaDisciplina->listDisciplinas($params, $sqlAdicional);
foreach ($resDisc as $reg)
    $disciplinas[$reg['disciplina']] = $reg['disciplina'] . ' (' . $reg['curso'] . ')';

// Cabeçalho
$pdf->SetFont($fonte, 'B', $tamanho + 5);
$pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 60);
$pdf->Cell(85, 20, "", 1, 0, 'C', false);
$pdf->Cell(190, 20, utf8_decode("RELATÓRIO DE ATIVIDADES - BOLSAS"), 1, 0, 'C', true);
$pdf->Ln();
$pdf->Cell(275, 5, utf8_decode(""), 0, 0, 'C', false);
$pdf->Ln();

$pdf->Cell(40, 5, utf8_decode('Título [Professor]:'), 1, 0, 'C', true);
$pdf->Cell(235, 5, utf8_decode($titulo), 1, 0, 'L', true);
$pdf->Ln();

//ALUNOS
$conteudoAluno = explode("\n", wordwrap(str_replace("\r\n", ";", trim(implode(', ', $alunos))), 150));
foreach ($conteudoAluno as $j => $trecho) {
    if ($j == 0) {
        $pdf->Cell(40, 5, utf8_decode('Aluno(s):'), 1, 0, 'C', true);
        $pdf->SetFont($fonte, '', $tamanho + 5);
    } else {
        $pdf->Cell(40, 5, "", 1, 0, 'C', true);
    }

    $pdf->Cell(235, 5, utf8_decode($conteudoAluno[$j]), 1, 0, 'L', true);
    $pdf->Ln();
}

$pdf->SetFont($fonte, 'B', $tamanho + 5);

//DISCIPLINAS
$conteudoDisc = explode("\n", wordwrap(str_replace("\r\n", ";", trim(implode(', ', $disciplinas))), 150));
foreach ($conteudoDisc as $j => $trecho) {
    if ($j == 0) {
        $pdf->Cell(40, 5, utf8_decode('Disciplina(s):'), 1, 0, 'C', true);
        $pdf->SetFont($fonte, '', $tamanho + 5);
    } else {
        $pdf->Cell(40, 5, "", 1, 0, 'C', true);
    }

    $pdf->Cell(235, 5, utf8_decode($conteudoDisc[$j]), 1, 0, 'L', true);
    $pdf->Ln();
}
$pdf->Ln();

$pdf->SetFont($fonte, 'B', $tamanho + 2);
$pdf->SetFillColor(190, 190, 190);
$pdf->Cell(40, 5, utf8_decode("ALUNO"), 1, 0, 'L', true);
$pdf->Cell(15, 5, utf8_decode("DATA"), 1, 0, 'L', true);
$pdf->Cell(55, 5, utf8_decode("ASSUNTO"), 1, 0, 'L', true);
$pdf->Cell(125, 5, utf8_decode("DESCRIÇÃO"), 1, 0, 'L', true);
$pdf->Cell(40, 5, utf8_decode("ASSINATURA"), 1, 0, 'L', true);
$pdf->Ln();

$pdf->SetFont($fonte, '', $tamanho + 2);
$i = 0;
$sqlAdicional .= ' ORDER BY br.data ASC ';
foreach ($bolsaRelatorio->listRelatorios($params, $sqlAdicional) as $reg) {
    if ($i % 2 == 0)
        $pdf->SetFillColor(240, 240, 240);
    else
        $pdf->SetFillColor(255, 255, 255);

    $pdf->Cell(40, 5, utf8_decode(abreviar($reg['aluno'], 30)), 1, 0, 'L', true);
    $pdf->Cell(15, 5, utf8_decode($reg['data']), 1, 0, 'L', true);

    $assunto = explode("\n", wordwrap(str_replace("\r\n", ";", trim($reg['assunto'])), 50));
    $descricao = explode("\n", wordwrap(str_replace("\r\n", ";", trim($reg['descricao'])), 145));

    $REG = ($descricao > $assunto) ? $descricao : $assunto;
    foreach ($REG as $j => $trecho) {
        if ($j != 0) {
            $pdf->Cell(40, $alturaLinha, "", 0, 0, 'C', false);
            $pdf->Cell(15, $alturaLinha, "", 0, 0, 'C', false);
        }
        $pdf->Cell(55, 5, utf8_decode($assunto[$j]), 1, 0, 'L', true);
        $pdf->Cell(125, 5, utf8_decode($descricao[$j]), 1, 0, 'L', true);
        if ($j == count($REG) - 1)
            $pdf->Cell(40, 5, "", 1, 0, 'L', true);
        $pdf->Ln();
    }
    $i++;
    $pdf->Ln();
}
$pdf->Cell(35, 5, utf8_decode($SITE_CIDADE.', '.formata(date('m/d/Y'))), 0, 0, 'R', false);
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(100, 5, utf8_decode(str_repeat("_", 40)), 0, 0, 'R', false);
$pdf->Ln();
$pdf->Cell(80, 5, utf8_decode("PROFESSOR"), 0, 0, 'R', false);

$pdf->Output();
?>