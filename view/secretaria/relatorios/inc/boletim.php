<?php
require '../../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require FUNCOES;

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 7;
$orientacao = "P"; // Landscape
                   // $orientacao = "P"; //Portrait
$papel = "A4";

include PATH.LIB.'/fpdf17/pdfDiario.php';

$pdf = new PDF ();
$pdf->rodape = $SITE_TITLE;

if (dcrip($_GET["turma"]) && dcrip($_GET["aluno"])) {
	$turmaCod = dcrip($_GET["turma"]);
	if (dcrip($_GET["aluno"]) != 'Todos')
		$sqlAluno = " and al.codigo = ".dcrip($_GET["aluno"]);

	$situacaoNome['N'] = 'Nota';
	$situacaoNome['F'] = 'Falta';
	$situacaoNome['FQ'] = 'Frequência';
	$situacaoNome['SIT'] = 'Situação';

    $r = mysql_query("SELECT al.codigo, al.nome FROM Pessoas al, Atribuicoes a, Matriculas m, Turmas t
           							WHERE t.codigo = a.turma
           							AND m.atribuicao = a.codigo
           							AND m.aluno = al.codigo 
           							AND t.codigo = $turmaCod 
           							$sqlAluno
           							GROUP BY al.codigo
           							ORDER BY al.nome");
    if (mysql_num_rows($r) > 0) {
    	while ($linha = mysql_fetch_array($r)) {
		    // consulta no banco	
			$sql = "SELECT al.codigo, al.nome, d.codigo, d.numero, d.nome, d.codigo, a.status,
							m.codigo, a.codigo, s.listar, s.habilitar, s.nome, s.sigla, a.bimestre, al.prontuario,
							t.numero, c.nome
							FROM Atribuicoes a 
							LEFT JOIN Disciplinas d on a.disciplina=d.codigo 
							LEFT JOIN Matriculas m on m.atribuicao=a.codigo 
							LEFT JOIN Pessoas al on m.aluno=al.codigo
							LEFT JOIN Situacoes s on m.situacao=s.codigo
							LEFT JOIN Turmas t on t.codigo=a.turma
							LEFT JOIN Cursos c on c.codigo=t.curso
							WHERE a.turma IN (SELECT t1.codigo FROM Turmas t1 
										WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
																WHERE t2.codigo = $turmaCod)) 
							and al.codigo = ".$linha[0]."
							ORDER BY a.bimestre, d.nome, al.nome";
		    //echo $sql;
		    $resultado = mysql_query($sql);
		    if ($resultado)
			    while ($l = mysql_fetch_array($resultado)) {
			    	$bimestres[$l[13]] = $l[13];
			    	$aluno = $l[1];
			    	$curso = $l[16];
			    	$turma = $l[15];
			    	$disciplinas[$l[13]][$l[3]]['aluno'] = $l[0];
			    	$disciplinas[$l[13]][$l[3]]['matricula'] = $l[7];
			    	$disciplinas[$l[13]][$l[3]]['numero'] = $l[3];
			    	$disciplinas[$l[13]][$l[3]]['atribuicao'] = $l[8];
			    	$situacaoListar[$l[8]] = $l[9];
			    	$situacaoNome[$l[12]] = $l[11];
			    	$situacaoSigla[$l[3]] = $l[12];
			    	$disciplinasNomes[$l[2]][$l[3]] = $l[4];
			    	$bimestres[$l[13]] = $l[13];
			    }
			
			$pdf->AliasNbPages ();
			$pdf->AddPage ( $orientacao, $papel );
			$pdf->SetFont ( $fonte, '', $tamanho );
			$pdf->SetFillColor ( 255, 255, 255 );
			$pdf->SetLineWidth ( .1 );
			
			// Cabeçalho
			$pdf->SetFont($fonte, 'B', $tamanho+5);
			$pdf->Image ( PATH.IMAGES."/logo.png", 12, 12, 75 );
			$pdf->Cell(83, 30, "", 1, 0, 'C', false);
			$pdf->Cell(50, 30, utf8_decode("B O L E T I M"), 1, 0, 'C', false);
			$pdf->SetFont($fonte, 'B', $tamanho+2);
			$pdf->Cell(60, 10, abreviar(utf8_decode("$aluno"),33), 1, 2, 'C', false);
			$pdf->Cell(60, 10, abreviar(utf8_decode($curso),33), 1, 2, 'C', false);
			$pdf->Cell(60, 10, abreviar(utf8_decode($turma),33), 1, 0, 'C', false);
			$pdf->Ln();
			
			$pdf->SetFont($fonte, 'B', $tamanho+2);
		
		    $pdf->Cell(13, $alturaLinha, utf8_decode("Código"), 1, 0, 'C', true);
		    $pdf->Cell(70, $alturaLinha, utf8_decode("Disciplina"), 1, 0, 'C', true);
		
			for($i=1; $i <= count($bimestres); $i++) {
				if (count($bimestres) > 1) $BIM = $i."º BIM";
				else $BIM = 'Semestre';
				$pdf->Cell(80/count($bimestres), $alturaLinha, utf8_decode($BIM), 1, 0, 'C', true);
			}
			$pdf->Cell(20, $alturaLinha, utf8_decode("MÉDIA"), 1, 0, 'C', true);
			$pdf->Cell(10, $alturaLinha, utf8_decode("SIT"), 1, 0, 'C', true);
		
			$pdf->Ln();
		    $pdf->Cell(13, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
		    $pdf->Cell(70, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
		
			for($i=1; $i <= count($bimestres); $i++) {
				$pdf->Cell((80/count($bimestres)/2), $alturaLinha, utf8_decode("N"), 1, 0, 'C', true);
				$pdf->Cell((80/count($bimestres)/2), $alturaLinha, utf8_decode("F"), 1, 0, 'C', true);
			}
			$pdf->Cell(10, $alturaLinha, utf8_decode("N"), 1, 0, 'C', true);
			$pdf->Cell(10, $alturaLinha, utf8_decode("FQ"), 1, 0, 'C', true);	
			$pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);	
		
			$pdf->Ln();
			foreach ($disciplinasNomes as $dCodigo => $dNumero) {
				foreach ($dNumero as $dSigla => $dNome) {
					$pdf->Cell(13, $alturaLinha, utf8_decode("$dSigla"), 1, 0, 'C', true);
					$pdf->Cell(70, $alturaLinha, abreviar(utf8_decode("$dNome"), 39), 1, 0, 'L', true);
					for($b=1; $b <= count($bimestres); $b++) {
						if ($disciplinas[$b][$dSigla]) {
							   	if ($situacaoListar[$disciplinas[$b][$dSigla]['atribuicao']]) {
						   			$dados = resultado($disciplinas[$b][$dSigla]['matricula'], $disciplinas[$b][$dSigla]['atribuicao']);
									$pdf->Cell((80/count($bimestres)/2), $alturaLinha, utf8_decode($dados['media']), 1, 0, 'C', true);
									$pdf->Cell((80/count($bimestres)/2), $alturaLinha, utf8_decode($dados['faltas']), 1, 0, 'C', true);
								} else {
									$pdf->Cell(20, $alturaLinha, utf8_decode($situacaoSigla[$ddCodigo]) , 1, 0, 'C', true);
								}
								$alunoCodigo = $disciplinas[$b][$dSigla]['aluno'];
									
						} else {
							$pdf->Cell((80/count($bimestres)/2), $alturaLinha, utf8_decode("-"), 1, 0, 'C', true);
							$pdf->Cell((80/count($bimestres)/2), $alturaLinha, utf8_decode("-"), 1, 0, 'C', true);
						}
					}
				   	$dadosBim = resultadoBimestral($alunoCodigo, $turmaCod, $dSigla);
					$pdf->Cell(10, $alturaLinha, utf8_decode($dadosBim['media']), 1, 0, 'C', true);
					$pdf->Cell(10, $alturaLinha, utf8_decode(intval($dadosBim['frequencia']).'%'), 1, 0, 'C', true);
					$pdf->Cell(10, $alturaLinha, utf8_decode($situacaoSigla[$dSigla]) , 1, 0, 'C', true);
					$pdf->Ln();
				}
			}
		
			$pdf->Ln();
			
			$pdf->Cell(80, $alturaLinha, utf8_decode("LEGENDA"), 1, 0, 'C', true);
			$pdf->Ln();
		
			foreach ($situacaoNome as $sSigla => $sNome) {
				$pdf->Cell(20, $alturaLinha, utf8_decode($sSigla), 1, 0, 'C', true);
				$pdf->Cell(60, $alturaLinha, utf8_decode($sNome), 1, 0, 'L', true);
				$pdf->Ln();		
			}
		}
	}
}
mysql_close();

$pdf->Output();
?>