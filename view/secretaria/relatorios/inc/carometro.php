<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

$turma = dcrip($_GET["turma"]);

$sql = "select t.numero, a.foto, a.nome
	from Pessoas a, PessoasTipos pt, Matriculas m, Turmas t, Atribuicoes at, Situacoes s
	where pt.pessoa = a.codigo  
	and m.situacao=s.codigo
	and m.aluno=a.codigo 
	and m.atribuicao=at.codigo
	and at.turma=t.codigo
	and pt.tipo=$ALUNO
	and foto IS NOT NULL
	and s.listar = 1
	and s.habilitar = 1
	and t.codigo = $turma
	group by a.codigo
	order by a.nome";
	//echo $sql;
    $resultado = mysql_query($sql);

if (mysql_num_rows($resultado) == '')
	die ('Nenhuma foto foi encontrada.');	

    $fonte = 'Times';
    $orientacao = "P"; //Portrait 
    $papel = "A4";

    // gera o relatório em PDF
    include PATH.LIB.'/fpdf17/pdfImage.php';

    // Instanciation of inherited class
    $pdf = new PDF_MemImage();
  
    $i=1;
    $j=1;
    $p=0;
    $pag=0;
    $op=0;
while ($l = mysql_fetch_array($resultado)) {
   	if ($pag > 14 || $pag==0) {
	    $pdf->AliasNbPages();
	    $pdf->AddPage($orientacao, $papel);
	    $pdf->SetFont($fonte, '', 16);
	    $pdf->SetFillColor(255, 255, 255);

	    $pdf->Ln();
	    $pdf->Cell(190,8,utf8_decode('C A R Ô M E T R O'),0,0,'C',1);
		$pdf->Ln();
		$pdf->Cell(190,8,utf8_decode("TURMA: ".$l[0]),1,0,'C',1);
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->SetFont($fonte, '', 8);
	 	$pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
    	$i=1;
		$j=1;
		$p=0;
		if ($pag) $op=1;
		$pag=0;
	}
	if ($p > 2) {
		$j = $j + 50;
		$i = 1;
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $pdf->Ln();
	    $p=0;
	}
    if ($op) { $j = $j + 5; $op=0; }
    $R = 30 + $i;
    $T = 34 + $j;
    $pdf->MemImage($l[1], $R, $T, 25, 30);
    $i = $i + 61;
	$pdf->Cell(63,5,abreviar(utf8_decode($l[2]), 32),1,0,'C',1);
	$p++;
	$pag++;
} 
$pdf->Output();
?>