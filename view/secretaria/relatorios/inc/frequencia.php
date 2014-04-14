<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 7;
$orientacao = "L"; // Landscape
                   // $orientacao = "P"; //Portrait
$papel = "A3";

include LIB.'/fpdf17/pdfDiario.php';

if ($ano && $semestre) {

	$turma = dcrip($_GET["turma"]);
	$atribuicao = dcrip($_GET["atribuicao"]);
	$data = $_GET["data"];
	$restricao = ""; // padrão é sem restrição
	
	// Consulta a turma
	$resultado = mysql_query("select t.numero, c.nome from Turmas t, Cursos c where t.curso=c.codigo and t.codigo=$turma");
	$linha=mysql_fetch_array($resultado);
	
	// restrições
	if (!empty($turma))
	    $restricao.= " and t.codigo=$turma";
	if (!empty($atribuicao))
	    $restricao.= " and a.codigo=$atribuicao";	
	    
	$sql="select al.prontuario, s.nome,  al.nome, al.rg, 
			sum(au.quantidade) aulas, m.codigo, a.codigo, t.numero
			from Aulas au, Atribuicoes a, Turmas t, Situacoes s, Pessoas al
			left join Matriculas m on m.aluno=al.codigo
			left join Frequencias f on m.codigo=f.matricula
			where a.turma=t.codigo 
			and m.atribuicao=a.codigo  
			and s.codigo=m.situacao 
			and au.atribuicao=a.codigo 
			and au.codigo=f.aula
			$restricao
			and au.data <= '".dataMysql($data)."'
			group by al.codigo order by al.nome";
	
		//echo $sql;

    $resultado = mysql_query($sql);
    if (mysql_num_rows($resultado) != '') {
		$pdf = new PDF ();
		$pdf->AliasNbPages ();
		$pdf->AddPage ( $orientacao, $papel );
		$pdf->SetFont ( $fonte, '', $tamanho );
		$pdf->SetFillColor ( 255, 255, 255 );
		$pdf->SetLineWidth ( .1 );
		$pdf->rodape = $SITE_TITLE;
		
		// Cabeçalho
		$pdf->SetFont($fonte, 'B', $tamanho+5);
		$pdf->Image ( PATH.IMAGES."/logo.png", 12, 12, 80 );
		$pdf->Cell(85, 27, "", 1, 0, 'C', false);
		$pdf->Cell(230, 27, utf8_decode("R E L A T Ó R I O   D E   F R E Q U Ê N C I A"), 1, 0, 'C', false);

		$i=0;
	    while ($l = mysql_fetch_array($resultado)) {
			if (!$i) {
				$pdf->Cell(85, 13.5, utf8_decode("TURMA: $l[7]"), 1, 2, 'C', false);
				$pdf->Cell(85, 13.5, utf8_decode("$data"), 1, 2, 'C', false);

				$pdf->Ln();

				$pdf->Cell(30, $alturaLinha, utf8_decode("PRONTUÁRIO"), 1, 0, 'C', true);
				$pdf->Cell(70, $alturaLinha, utf8_decode("SITUAÇÃO"), 1, 0, 'C', true);
				$pdf->Cell(160, $alturaLinha, utf8_decode("NOME"), 1, 0, 'C', true);
				$pdf->Cell(50, $alturaLinha, utf8_decode("RG"), 1, 0, 'C', true);
				$pdf->Cell(30, $alturaLinha, utf8_decode("AULAS"), 1, 0, 'C', true);
				$pdf->Cell(30, $alturaLinha, utf8_decode("FALTAS"), 1, 0, 'C', true);
				$pdf->Cell(30, $alturaLinha, utf8_decode("FREQUÊNCIA"), 1, 0, 'C', true);
				
				$i=1;
				$pdf->SetFont($fonte, '', $tamanho+4);
			}

			$pdf->Ln();

	   	$dados = getFrequencia($l[5], $l[6]);

			$pdf->Cell(30, $alturaLinha, utf8_decode($l[0]), 1, 0, 'C', true);
			$pdf->Cell(70, $alturaLinha, utf8_decode($l[1]), 1, 0, 'C', true);
			$pdf->Cell(160, $alturaLinha, utf8_decode($l[2]), 1, 0, 'L', true);
			$pdf->Cell(50, $alturaLinha, utf8_decode($l[3]), 1, 0, 'C', true);
			$pdf->Cell(30, $alturaLinha, utf8_decode($l[4]), 1, 0, 'C', true);
			$pdf->Cell(30, $alturaLinha, utf8_decode($dados['faltas']), 1, 0, 'C', true);
			$pdf->Cell(30, $alturaLinha, utf8_decode($dados['frequencia']."%"), 1, 0, 'C', true);
	    }
	} else {
		print "Sem dados para gerar a lista.";
	}
}

mysql_close();

$pdf->Output();
?>