<?php
require '../../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require FUNCOES;

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 7;
$orientacao = "L"; // Landscape
                   // $orientacao = "P"; //Portrait
$papel = "A3";

require CONTROLLER . "/professor.class.php";
$prof = new Professores();

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

include PATH.LIB.'/fpdf17/pdfDiario.php';

if (dcrip($_GET["turma"]) && dcrip($_GET["bimestre"])) {
	$turma = dcrip($_GET["turma"]);
	$bimestre = dcrip($_GET["bimestre"]);

	// restrição de bimestre
	if ($bimestre == 'semestre' || $_GET["bimestre"] == 'undefined') {
		$bimestre = 0;
		$fechamento = 's';
	} else {
		$fechamento = 'b';
	}
    if ($bimestre!='final' && $fechamento == 'b')
    	$sqlBimestre = " and a.bimestre=$bimestre";
    if ($bimestre=='final' && $fechamento == 'b') $sqlBimestre=" and a.bimestre=3";;

	$situacaoNome['N'] = 'Nota';
	$situacaoNome['F'] = 'Falta';
	$situacaoNome['SIT'] = 'Situação';
	$situacaoNome['MG'] = 'Média Global';
	$situacaoNome['FG'] = 'Frequência Global';
        
    // consulta no banco	
	$sql = "SELECT 	al.codigo, al.nome, d.codigo, d.numero, d.nome, a.disciplina, a.status,
					m.codigo, a.codigo, s.listar, s.habilitar, s.nome, s.sigla, a.bimestre, al.prontuario,
					(SELECT numero FROM Turmas where codigo = a.turma)
					FROM Atribuicoes a 
					LEFT JOIN Disciplinas d on a.disciplina=d.codigo 
					LEFT JOIN Matriculas m on m.atribuicao=a.codigo 
					LEFT JOIN Pessoas al on m.aluno=al.codigo
					LEFT JOIN Situacoes s on m.situacao=s.codigo
					WHERE a.turma IN (SELECT t1.codigo FROM Turmas t1 
								WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
														WHERE t2.codigo = $turma)) 
					$sqlBimestre
					ORDER BY a.bimestre, d.nome, al.nome";
    //echo $sql;
    $resultado = mysql_query($sql);
    if ($resultado)
	    while ($l = mysql_fetch_array($resultado)) {
	    	$turmaNome = $l[15];
	    	$bimestres[$l[13]] = $l[13];
	    	$alunos[$l[0]] = $l[1];
	    	$alunosProntuario[$l[0]] = $l[14];
	    	$disciplinasMedia[$l[8]] = $l[3];
	    	$disciplinas[$l[8]][$l[0]] = $l[7];
	    	$situacaoListar[$l[0]][$l[8]] = $l[9];
			
	    	$disciplinasNomes[$l[8]][$l[3]] = $l[4]." - ".$prof->getProfessor($l[8], '', 0, 0);

	    	$situacaoSigla[$l[0]][$l[8]] = $l[12];
	    	$situacaoNome[$l[12]] = $l[11];
	    }
	
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
	$pdf->Cell(85, 27, "", 1, 0, 'C', false);
	$pdf->Cell(230, 27, utf8_decode("B O L E T I M   D A   T U R M A"), 1, 0, 'C', false);
	$pdf->Cell(85, 13.5, utf8_decode("TURMA: $turmaNome"), 1, 2, 'C', false);

	if ($fechamento == 's') $BIM = 'SEMESTRAL';
	if ( ($fechamento == 'b' && $bimestre == 'final') || ($_GET["bimestre"] == 'undefined')) $BIM = 'FECHAMENTO ANUAL';
	if ($bimestre > 0) $BIM = $bimestre.'º BIMESTRE';
	$pdf->Cell(85, 13.5, utf8_decode($BIM), 1, 0, 'C', false);
	$pdf->Ln();
	
	$pdf->SetFont($fonte, 'B', $tamanho+2);

    $pdf->Cell(20, $alturaLinha, utf8_decode("Prontuário"), 1, 0, 'C', true);
    $pdf->Cell(65, $alturaLinha, utf8_decode("Nome"), 1, 0, 'C', true);

	foreach ($disciplinasNomes as $ddSigla) {
		$T = 285/count($disciplinasNomes);
		foreach ($ddSigla as $dSigla => $dNome)
			$pdf->Cell($T, $alturaLinha, utf8_decode("$dSigla"), 1, 0, 'C', true);
	}

	$pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
	$pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
	$pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);

	$pdf->Ln();
    $pdf->Cell(20, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
    $pdf->Cell(65, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);

	foreach ($disciplinasNomes as $ddSigla) {
		$T = 285/count($disciplinasNomes);
		foreach ($ddSigla as $dSigla => $dNome)
			$pdf->Cell($T/2, $alturaLinha, utf8_decode("N"), 1, 0, 'C', true);
			$pdf->Cell($T/2, $alturaLinha, utf8_decode("F"), 1, 0, 'C', true);
	}
	
	$pdf->Cell(10, $alturaLinha, utf8_decode("MG"), 1, 0, 'C', true);
	$pdf->Cell(10, $alturaLinha, utf8_decode("FG"), 1, 0, 'C', true);
	$pdf->Cell(10, $alturaLinha, utf8_decode("SIT"), 1, 0, 'C', true);

	$pdf->Ln();

	if ($alunos )
	foreach ($alunos as $c => $nome) {
		$discMediaAnual = null;
		$discQdePorAluno = null;
		$pdf->Cell(20, $alturaLinha, utf8_decode($alunosProntuario[$c]), 1, 0, 'C', true);
		$pdf->Cell(65, $alturaLinha, abreviar(utf8_decode(mostraTexto($nome)),36), 1, 0, 'L', true);

        foreach ($disciplinas as $dCodigo => $dMatricula) {
        	if ($situacaoListar[$c][$dCodigo]) {
        		if ($bimestre == 'final' && $fechamento=='b') {
        			$dados['media'] = $nota->resultadoBimestral($c, $turma, $disciplinasMedia[$dCodigo]);
				}
   				$dados = $nota->resultado($dMatricula[$c], $dCodigo);
				$pdf->Cell($T/2, $alturaLinha, utf8_decode($dados['media']), 1, 0, 'C', true);
				$pdf->Cell($T/2, $alturaLinha, utf8_decode($dados['faltas']), 1, 0, 'C', true);
            } else {
				$pdf->Cell($T, $alturaLinha, utf8_decode($situacaoSigla[$c][$dCodigo]) , 1, 0, 'C', true);
			}
		}

		$dadosGlobal = $nota->resultadoModulo($c, $turma);
		if (!$media = $dadosGlobal['mediaGlobal']) $media = '-';
		$frequencia = round($dadosGlobal['frequenciaGlobal'], 1);
		$frequencia = (!$frequencia) ? '-' : $frequencia.'%';
	    
	    $pdf->Cell(10, $alturaLinha, $media , 1, 0, 'C', true);
	    $pdf->Cell(10, $alturaLinha, $frequencia , 1, 0, 'C', true);
	    $pdf->Cell(10, $alturaLinha, utf8_decode($situacaoSigla[$c][$dCodigo]) , 1, 0, 'C', true);

		$pdf->Ln();
	}

	$pdf->Ln();
	
	$pdf->Cell(280, $alturaLinha, utf8_decode("DISCIPLINAS"), 1, 0, 'C', true);
	$pdf->Cell(120, $alturaLinha, utf8_decode("LEGENDA"), 1, 0, 'C', true);
	$pdf->Ln();

	if (count($disciplinasNomes) > count($situacaoNome))
		$TT = count($disciplinasNomes);
	else
		$TT = count($situacaoNome);

	foreach ($disciplinasNomes as $ddSigla)
		foreach ($ddSigla as $dSigla => $dNome)
			$DISC[] = array('SIGLA' => $dSigla, 'NOME' => $dNome);
	
	$n=0;
	$m=0;	
	for($i=0; $i < $TT; $i++) {
		foreach ($DISC as $dSigla => $ddSigla) {
			if (!in_array($ddSigla, $dSiglaAnt)) { 
				$pdf->Cell(40, $alturaLinha, utf8_decode($ddSigla['SIGLA']), 1, 0, 'C', true);
				$pdf->Cell(240, $alturaLinha, abreviar(utf8_decode($ddSigla['NOME']),90), 1, 0, 'L', true);
				$dSiglaAnt[] = $ddSigla;
				$m++;
				break;
			}
		}

		foreach ($situacaoNome as $sSigla => $sNome) {
			if (!in_array($sSigla, $sSiglaAnt)) { 
				$n++;
				if ($n > $m) {
					$pdf->Cell(40, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
					$pdf->Cell(240, $alturaLinha, utf8_decode(""), 1, 0, 'L', true);
					$m++;
				}
				$pdf->Cell(40, $alturaLinha, utf8_decode($sSigla), 1, 0, 'C', true);
				$pdf->Cell(80, $alturaLinha, utf8_decode($sNome), 1, 0, 'L', true);
				$sSiglaAnt[] = $sSigla;
				break;
			}
		}
		$pdf->Ln();		
	}
}

mysql_close();

$pdf->Output();
?>