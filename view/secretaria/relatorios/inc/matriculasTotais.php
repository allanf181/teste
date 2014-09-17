<?php
require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

include PATH.LIB.'/fpdf17/pdfDiario.php';

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

if (dcrip($_GET["situacao"])) {
    $situacao = dcrip($_GET["situacao"]);
    $params['situacao'] = $situacao;
    $sqlAdicional .= ' AND s.codigo = :situacao ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicional .= " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

$sqlAdicional .= ' GROUP BY t.numero,s.nome ORDER BY c.nome, t.numero, a.bimestre ';

$campoExtra = ',COUNT(DISTINCT m.aluno) as quantidadeMatricula';

$res = $matricula->getMatriculas($params, $sqlAdicional,null,null,$campoExtra);	

$fonte = 'Times';
$orientacao = "P"; //Portrait 
$papel = "A4";
$tamanho = 5;

// gera o relatório em PDF
$pdf = new PDF ();
$pdf->AliasNbPages ();
$pdf->AddPage ( $orientacao, $papel );
$pdf->SetFont ( $fonte, '', $tamanho );
$pdf->rodape = $SITE_TITLE;
$pdf->SetFillColor ( 255, 255, 255 );
$pdf->SetLineWidth ( .1 );

// Cabeçalho
$pdf->SetFont($fonte, 'B', $tamanho+5);
$pdf->Image ( PATH.IMAGES."/logo.png", 12, 12, 80 );
$pdf->Cell(90, 27, "", 1, 0, 'C', false);
$pdf->Cell(100, 27, utf8_decode("T O T A L I Z A Ç Ã O    D E    M A T R Í C U L A S"), 1, 0, 'C', false);
$pdf->Ln();

$pdf->Cell(10, 5, utf8_decode("N."), 1, 0, 'L', true);
$pdf->Cell(100, 5, utf8_decode("Curso"), 1, 0, 'L', true);
$pdf->Cell(40, 5, utf8_decode("Situação"), 1, 0, 'L', true);
$pdf->Cell(20, 5, utf8_decode("Turma"), 1, 0, 'L', true);
$pdf->Cell(20, 5, utf8_decode("Quantidade"), 1, 0, 'L', true);
$pdf->Ln();

$pdf->SetFont($fonte, '', $tamanho+3);
$i=0;
$j=1;
foreach($res as $reg) {
	if ($i % 2 == 0)
  	$pdf->SetFillColor(240, 240, 240);
  else
  	$pdf->SetFillColor(255, 255, 255);
  
	$pdf->Cell(10, 5, $j++, 1, 0, 'L', true);
	$pdf->Cell(100, 5, utf8_decode($reg['curso']), 1, 0, 'L', true);
	$pdf->Cell(40, 5, utf8_decode($reg['situacao']), 1, 0, 'L', true);
	$pdf->Cell(20, 5, utf8_decode($reg['turma']), 1, 0, 'L', true);
	$pdf->Cell(20, 5, utf8_decode($reg['quantidadeMatricula']), 1, 0, 'C', true);

	$SIT[$reg['codSituacao']] += $reg['quantidadeMatricula'];
	$SITCOD[$reg['codSituacao']] = $reg['situacao'];

	$pdf->Ln();
  $i++;
}

$pdf->Ln();

$i=0;
foreach($SITCOD as $COD => $VAL) {
	$pdf->Cell(50, 5, utf8_decode($VAL), 1, 0, 'L', true);
	$pdf->Cell(50, 5, utf8_decode($SIT[$COD]), 1, 0, 'L', true);
	$pdf->Ln();
}

$pdf->Output();
?>


