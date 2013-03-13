<?php
require '../../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require FUNCOES;

include PATH.LIB.'/fpdf17/pdfDiario.php';

$curso = dcrip($_GET["curso"]);

// restrições
if (!empty($curso))
	$restricao.= " and c2.codigo=$curso";
	    	
if (!empty($turma))
  $restricao.= " and t.codigo=$turma";
	    	
$sql="SELECT DISTINCT p.prontuario, p.nome, d.nome, e.diaSemana, s.nome, 
					CONCAT(DATE_FORMAT(h.inicio, '%h:%i'), ' - ', DATE_FORMAT(h.fim, '%h:%i'))
					FROM Ensalamentos e, Atribuicoes a, Professores pr, Pessoas p, Disciplinas d, Horarios h, Turmas t, Salas s
					WHERE e.atribuicao = a.codigo
					AND pr.atribuicao = a.codigo
					AND pr.professor = p.codigo
					AND d.codigo = a.disciplina
					AND h.codigo = e.horario
					AND s.codigo = e.sala
					AND t.codigo = a.turma
					AND t.semestre = $semestre
					AND t.ano = $ano
					ORDER BY p.nome, d.nome, e.diaSemana, h.inicio, s.nome";
$resultado = mysql_query($sql);

if (mysql_num_rows($resultado) == '')
	die ('Nenhum registro foi encontrado.');	

$fonte = 'Times';
$orientacao = "L"; //Portrait 
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
$pdf->Image ( PATH.IMAGES."/logo.png", 12, 12, 50 );
$pdf->Cell(90, 15, "", 0, 0, 'C', false);
$pdf->Cell(100, 20, utf8_decode("A T R I B U I Ç Ã O     D O C E N T E"), 0, 0, 'C', true);
$pdf->Ln();
	    
$dias = diasDaSemana();

$pdf->SetFont ( $fonte, 'B', $tamanho+5 );
$pdf->Cell(10, 5, utf8_decode("N."), 1, 0, 'L', true);
$pdf->Cell(15, 5, utf8_decode("Prontuário"), 1, 0, 'L', true);
$pdf->Cell(72, 5, utf8_decode("Professor"), 1, 0, 'L', true);
$pdf->Cell(72, 5, utf8_decode("Disciplina"), 1, 0, 'L', true);
$pdf->Cell(32, 5, utf8_decode("Dia da Semana"), 1, 0, 'L', true);
$pdf->Cell(22, 5, utf8_decode("Sala"), 1, 0, 'L', true);
$pdf->Cell(42, 5, utf8_decode("Horário"), 1, 0, 'L', true);
$pdf->Ln();

$pdf->SetFont ( $fonte, '', $tamanho+3);

$i = 1;
$j=1;
while ($l = mysql_fetch_array($resultado)) {
	if ($i % 2 == 0)
  	$pdf->SetFillColor(240, 240, 240);
  else
  	$pdf->SetFillColor(255, 255, 255);

 	$pdf->Cell(10, 5, $j++, 1, 0, 'L', true);
	$pdf->Cell(15, 5, utf8_decode($l[0]), 1, 0, 'L', true);
	$pdf->Cell(72, 5, utf8_decode($l[1]), 1, 0, 'L', true);
	$pdf->Cell(72, 5, utf8_decode($l[2]), 1, 0, 'L', true);
	$pdf->Cell(32, 5, html_entity_decode ($dias[$l[3]]), 1, 0, 'L', true);
	$pdf->Cell(22, 5, utf8_decode($l[4]), 1, 0, 'L', true);
	$pdf->Cell(42, 5, utf8_decode($l[5]), 1, 0, 'L', true);
	$pdf->Ln();
 $i++;

}

$pdf->Output();
?>