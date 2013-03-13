<?php
require '../../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require FUNCOES;

include PATH.LIB.'/fpdf17/pdfDiario.php';

$curso = dcrip($_GET["curso"]);

// restrições
if (!empty($curso))
	$restricao.= " and c.codigo=$curso";
	    
if (!empty($turma))
  $restricao.= " and t.codigo=$turma";

if (!empty($situacao))
  $restricao.= " and s.codigo=$situacao";
	    
$sql = "SELECT SUBSTRING(c.nome, 1, 62), s.nome, t.numero, COUNT(DISTINCT m.aluno), s.codigo
				FROM Matriculas m, Atribuicoes a, Turmas t, Situacoes s, Cursos c
				WHERE
				m.atribuicao = a.codigo
				AND a.turma = t.codigo
				AND s.codigo = m.situacao
				AND c.codigo = t.curso
				$restricao
				GROUP BY t.numero,s.nome
        ORDER BY c.nome, t.numero, a.bimestre";
$resultado = mysql_query($sql);

if (mysql_num_rows($resultado) == '')
	die ('Nenhum registro foi encontrado.');	

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
while ($l = mysql_fetch_array($resultado)) {
	if ($i % 2 == 0)
  	$pdf->SetFillColor(240, 240, 240);
  else
  	$pdf->SetFillColor(255, 255, 255);
  
	$pdf->Cell(10, 5, $j++, 1, 0, 'L', true);
	$pdf->Cell(100, 5, utf8_decode($l[0]), 1, 0, 'L', true);
	$pdf->Cell(40, 5, utf8_decode($l[1]), 1, 0, 'L', true);
	$pdf->Cell(20, 5, utf8_decode($l[2]), 1, 0, 'L', true);
	$pdf->Cell(20, 5, utf8_decode($l[3]), 1, 0, 'C', true);

	$SIT[$l[4]] += $l[3];
	$SITCOD[$l[4]] = $l[1];

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


