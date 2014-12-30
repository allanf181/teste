<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

include PATH . LIB . '/fpdf17/pdfDiario.php';

require CONTROLLER . "/atvAcadRegistro.class.php";
$atvRegistro = new AtvAcadRegistros();

require CONTROLLER . "/atvAcadItem.class.php";
$atvItem = new AtvAcadItens();

require CONTROLLER . '/aluno.class.php';
$alunos = new Alunos();

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c2.codigo = :curso ';
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

if (dcrip($_GET["aluno"])) {
    $aluno = dcrip($_GET["aluno"]);
    $params['aluno'] = $aluno;
    $sqlAdicional .= ' AND a.codigo = :aluno ';
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

// Cabeçalho
$pdf->SetFont($fonte, 'B', $tamanho + 5);
$pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 60);
$pdf->Cell(85, 20, "", 1, 0, 'C', false);
$pdf->Cell(190, 20, utf8_decode("REGISTRO DE ATIVIDADES ACADÊMICO-CIENTÍFICO-CULTURAIS"), 1, 0, 'C', false);
$pdf->Ln();
$pdf->Cell(275, 5, utf8_decode(""), 1, 0, 'C', true);
$pdf->Ln();

$campoExtra = ',a.codigo as aluno,t.codigo as turma';
foreach ($alunos->listAlunos($params, $sqlAdicional, $campoExtra) as $regAlunos) {
    $paramsReg['aluno'] = $regAlunos['aluno'];
    $sqlAdicionalReg = ' AND p.codigo = :aluno AND ra.aluno IS NOT NULL ';

    $i = 1;
    $has = 0;
    foreach ($atvRegistro->listSituacao($paramsReg, $sqlAdicionalReg) as $reg) {
        if ($i == 1) {
            $pdf->Ln();
            $pdf->SetFont($fonte, 'B', $tamanho + 2);
            $pdf->SetFillColor(190, 190, 190);
            $pdf->Cell(15, 5, utf8_decode("ALUNO:"), 1, 0, 'L', true);
            $pdf->Cell(260, 5, utf8_decode($regAlunos['nome']), 1, 0, 'L', true);

            $pdf->Ln();
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell(20, 5, utf8_decode("Nº ATIVIDADE"), 1, 0, 'C', true);
            $pdf->Cell(140, 5, utf8_decode("ATIVIDADE"), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode("SEM/ANO"), 1, 0, 'C', true);
            $pdf->Cell(25, 5, utf8_decode("CH NO SEMESTRE"), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode("CH NO CURSO"), 1, 0, 'C', true);
            $pdf->Cell(55, 5, utf8_decode("CH CIENTÍFICA-CULTURAL-ACADÊMICA"), 1, 0, 'C', true);
            $pdf->Ln();
        }
        $pdf->SetFont($fonte, '', $tamanho + 2);

        if ($i % 2 == 0)
            $pdf->SetFillColor(255, 255, 255);
        else
            $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(20, 5, utf8_decode($reg['codigo']), 1, 0, 'C', true);
        $pdf->Cell(140, 5, utf8_decode(abreviar($reg['atividade'], 140)), 1, 0, 'L', true);
        $pdf->Cell(15, 5, utf8_decode($reg['semAno']), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode($reg['CHSem'] . 'h/[' . $reg['CHminSem'] . '-' . $reg['CHmaxSem'] . ']h'), 1, 0, 'C', true);
        $pdf->Cell(20, 5, utf8_decode($reg['CHCurso'] . 'h/' . $reg['CHTotal'] . 'h'), 1, 0, 'C', true);
        $pdf->Cell(55, 5, utf8_decode('[' . $reg['CHCientifica'] . 'h/' . $reg['CHminCientifica'] . 'h] [' . $reg['CHCultural'] . 'h/' . $reg['CHminCultural'] . 'h] [' . $reg['CHAcademica'] . 'h/' . $reg['CHminAcademica'] . 'h]'), 1, 0, 'C', true);

        $i++;
        $pdf->Ln();
        $has = 1;
    }

    if ($has) {
        $pdf->SetFont($fonte, 'B', $tamanho + 2);

        if ($sit = $atvRegistro->status($reg)) {
            $pdf->Cell(20, 5, utf8_decode("SITUAÇÃO:"), 1, 0, 'L', true);
            $pdf->Cell(255, 5, abreviar(utf8_decode(html_entity_decode(ereg_replace('<br>', ' ', $sit))),230), 1, 0, 'L', true);
        }
        $pdf->Ln();
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(20, 5, utf8_decode(""), 1, 0, 'C', true);
        $pdf->Cell(215, 5, utf8_decode("ITEM(NS) ENTREGUES"), 1, 0, 'C', true);
        $pdf->Cell(15, 5, utf8_decode("SEM/ANO"), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode("CH / CH MÁXIMA"), 1, 0, 'C', true);
        $pdf->Ln();

        $i = 1;
        foreach ($atvRegistro->listRegistros($paramsReg, $sqlAdicionalReg) as $reg) {
            $pdf->SetFont($fonte, '', $tamanho + 2);

            if ($i % 2 == 0)
                $pdf->SetFillColor(255, 255, 255);
            else
                $pdf->SetFillColor(250, 250, 250);
            $pdf->Cell(20, 5, utf8_decode(abreviar($reg['atvAcademica'], 50)), 1, 0, 'C', true);
            $pdf->Cell(215, 5, utf8_decode(abreviar('[' . $reg['tipo'] . '] ' . $reg['item'], 220)), 1, 0, 'L', true);
            $pdf->Cell(15, 5, utf8_decode($reg['semestre'] . '/' . $reg['ano']), 1, 0, 'C', true);
            $pdf->Cell(25, 5, utf8_decode($reg['CH'] . 'h/' . $reg['CHLimite'] . 'h'), 1, 0, 'C', true);

            $i++;
            $pdf->Ln();
        }
    }
}

$pdf->Output();
?>