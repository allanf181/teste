<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

if (! empty ( $_GET ["atribuicao"] )) {
	$atribuicao = dcrip($_GET["atribuicao"]);
}

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 7;
$orientacao = "P"; // Landscape
                   // $orientacao = "P"; //Portrait
$papel = "A3";


include PATH.LIB.'/fpdf17/pdfDiario.php';

$sql = "SELECT pe.numeroAulaSemanal, pe.totalHoras, pe.totalAulas, pe.numeroProfessores,
							pe.ementa, pe.objetivo, pe.conteudoProgramatico, pe.metodologia, d.numero,
							pe.recursoDidatico, pe.avaliacao, pe.recuperacaoParalela, pe.recuperacaoFinal,
							pe.bibliografiaBasica, pe.bibliografiaComplementar, pa.semana, pa.conteudo,
							d.nome, d.ch, c.nome, m.nome, c.nomeAlternativo
							FROM PlanosEnsino pe, PlanosAula pa, Atribuicoes a, Disciplinas d,
							Cursos c, Modalidades m, Turmas t
							WHERE pe.atribuicao = pa.atribuicao 
							AND d.codigo = a.disciplina
							AND a.turma = t.codigo
							AND pe.atribuicao = a.codigo
							AND pa.atribuicao = a.codigo
							AND t.curso = c.codigo
							AND c.modalidade = m.codigo
							AND a.codigo = $atribuicao";

$result = mysql_query ( $sql );

for($i = 0; $i < mysql_num_rows ( $result ); ++ $i) {
	$numeroAulaSemanal = mysql_result ( $result, $i, "pe.numeroAulaSemanal" );
	$totalHoras = mysql_result ( $result, $i, "pe.totalHoras" );
	$totalAulas = mysql_result ( $result, $i, "pe.totalAulas" );
	$numeroProfessores = mysql_result ( $result, $i, "pe.numeroProfessores" );
	$ITEM['2 - EMENTA'] = mysql_result ( $result, $i, "pe.ementa" );
	$ITEM['3 - OBJETIVO'] = mysql_result ( $result, $i, "pe.objetivo" );
	$ITEM['4 - CONTEÚDO PROGRAMÁTICO'] = mysql_result ( $result, $i, "pe.conteudoProgramatico" );
	$ITEM['5 - METODOLOGIA'] = mysql_result ( $result, $i, "pe.metodologia" );
	$ITEM['6 - RECURSOS DIDÁTICOS'] = mysql_result ( $result, $i, "pe.recursoDidatico" );
    $ITEM['7 - AVALIAÇÃO'] = mysql_result ( $result, $i, "pe.avaliacao" );
    $ITEM['7.1 - RECUPERAÇÃO PARALELA'] = mysql_result ( $result, $i, "pe.recuperacaoParalela" );
    $ITEM['7.2 - RECUPERAÇÃO FINAL/INSTRUMENTO FINAL DE AVALIAÇÃO'] = mysql_result ( $result, $i, "pe.recuperacaoFinal" );
    $ITEM['8 - BIBLIOGRAFIA BÁSICA'] = mysql_result ( $result, $i, "pe.bibliografiaBasica" );
    $ITEM['8.1 - BIBLIOGRAFIA COMPLEMENTAR'] = mysql_result ( $result, $i, "pe.bibliografiaComplementar" );
    $semana[$i][mysql_result($result, $i, "pa.semana")] = mysql_result ( $result, $i, "pa.conteudo" );
    $disciplina = mysql_result ( $result, $i, "d.nome" );
    $numero = mysql_result ( $result, $i, "d.numero" );
    $CH = mysql_result ( $result, $i, "d.ch" );
		$curso = (mysql_result($result, $i, "c.nomeAlternativo")) ? mysql_result($result, $i, "c.nomeAlternativo") : mysql_result($result, $i, "c.nome");
    $modalidade = mysql_result ( $result, $i, "m.nome" );

		$professores='';			
		foreach(getProfessor($atribuicao) as $key => $reg)
			$professores[] = $reg['nome'];
		$professor = implode(" / ", $professores);
}

if (!$ITEM) die ('Sem dados para gerar a lista.');

$pdf = new PDF ();
$pdf->AliasNbPages ();
$pdf->AddPage ( $orientacao, $papel );
$pdf->SetFont ( $fonte, '', $tamanho );
$pdf->SetFillColor ( 255, 255, 255 );
$pdf->SetLineWidth ( .1 );

// Cabeçalho
$pdf->SetFont($fonte, 'B', $tamanho+5);
$pdf->Image ( PATH.IMAGES."/logo.png", 12, 12, 80 );

$pdf->Cell(90, 27, "", 1, 0, 'C', false);
$pdf->Cell(130, 27, utf8_decode("P L A N O   D E   E N S I N O"), 1, 0, 'C', false);
$pdf->Cell(58, 27, utf8_decode("CAMPUS: $SITE_CIDADE"), 1, 0, 'C', false);
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("1 - IDENTIFICAÇÃO"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("CURSO: $curso"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(178, $alturaLinha, utf8_decode("COMPONENTE CURRICULAR: $disciplina"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("CÓDIGO DISCIPLINA: $numero"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(78, $alturaLinha, utf8_decode("SEMESTRE/ANO: $semestre/$ano"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("NÚMERO DE AULAS SEMANAIS: $numeroAulaSemanal"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("ÁREA: $modalidade"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(78, $alturaLinha, utf8_decode("TOTAL DE HORAS: $totalHoras"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("TOTAL DE AULAS: $totalAulas"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("NÚMERO DE PROFESSORES: $numeroProfessores"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("PROFESSOR(A) RESPONSÁVEL: $professor"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Ln();

foreach($ITEM as $chave => $valor) {
	$limit = 140;
	if (substr($chave, 0, 1) == '8') $limit = 100;

	$pdf->SetFont($fonte, 'B', $tamanho+5);
	$pdf->Cell(278, $alturaLinha, utf8_decode("$chave"), 1, 0, 'L', true);
	$pdf->Ln();
	$conteudo = explode ( "\r\n",  $valor );
	$pdf->SetFont ( $fonte, '', 12 );
	foreach ( $conteudo as $j => $trecho ) {
		if (strlen($trecho) > $limit) {
			$conteudo2 = explode ( "\n", wordwrap ( str_replace ( "\r\n", "; ", trim ( $trecho ) ), $limit ) );
			foreach ( $conteudo2 as $n => $trecho2 ) {
				$pdf->Cell ( 278, $alturaLinha, utf8_decode ( $trecho2 ), 1, 0, 'L', true );
				$pdf->Ln ();
			}
		} else {
			$pdf->Cell ( 278, $alturaLinha, utf8_decode ( $trecho ), 1, 0, 'L', true );
			$pdf->Ln ();
		}
	}
	if (substr($chave, 0, 1) != '7' && substr($chave, 0, 1) != '8') $pdf->Ln();
	if (substr($chave, 0, 3) == '7.2' || substr($chave, 0, 3) == '8.1') $pdf->Ln();
}

$pdf->Ln();
$pdf->Cell(139, $alturaLinha, utf8_decode("PROFESSOR(A)"), 1, 0, 'C', true);
$pdf->Cell(139, $alturaLinha, utf8_decode("COORDENADOR(A) DE ÁREA/CURSO"), 1, 0, 'C', true);
$pdf->Ln();
$pdf->Cell(39, $alturaLinha, utf8_decode("DATA"), 1, 0, 'C', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("ASSINATURA"), 1, 0, 'C', true);
$pdf->Cell(39, $alturaLinha, utf8_decode("DATA"), 1, 0, 'C', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("ASSINATURA"), 1, 0, 'C', true);
$pdf->Ln();
$pdf->Cell(39, 15, utf8_decode(""), 1, 0, 'L', true);
$pdf->Cell(100, 15, utf8_decode(""), 1, 0, 'L', true);
$pdf->Cell(39, 15, utf8_decode(""), 1, 0, 'L', true);
$pdf->Cell(100, 15, utf8_decode(""), 1, 0, 'L', true);

///// PLANO DE AULA
$pdf->AliasNbPages();
$pdf->AddPage($orientacao, $papel);
$pdf->SetFont($fonte, '', $tamanho+5);

// Cabeçalho
$pdf->SetFont($fonte, 'B', $tamanho+5);
$pdf->Image ( PATH.IMAGES."/logo.png", 12, 12, 80 );
$pdf->Cell(90, 27, "", 1, 0, 'C', false);
$pdf->Cell(130, 27, utf8_decode("P L A N O   D E   A U L A"), 1, 0, 'C', false);
$pdf->Cell(58, 27, utf8_decode("CAMPUS: $SITE_CIDADE"), 1, 0, 'C', false);
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("1 - IDENTIFICAÇÃO"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("CURSO: $curso"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(178, $alturaLinha, utf8_decode("COMPONENTE CURRICULAR: $disciplina"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("CÓDIGO DISCIPLINA: $numero"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(78, $alturaLinha, utf8_decode("SEMESTRE/ANO: $semestre/$ano"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("NÚMERO DE AULAS SEMANAIS: $numeroAulaSemanal"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("ÁREA: $modalidade"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(78, $alturaLinha, utf8_decode("TOTAL DE HORAS: $totalHoras"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("TOTAL DE AULAS: $totalAulas"), 1, 0, 'L', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("NÚMERO DE PROFESSORES: $numeroProfessores"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("PROFESSOR(A) RESPONSÁVEL: $professor"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("2 - CONTEÚDO PROGRAMÁTICO"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(278, $alturaLinha, utf8_decode("CONTEÚDO DESENVOLVIDO"), 1, 0, 'L', true);
$pdf->Ln();
$pdf->Cell(28, $alturaLinha, utf8_decode("SEMANA"), 1, 0, 'C', true);
$pdf->Cell(250, $alturaLinha, utf8_decode("DESCRIÇÃO DO CONTEÚDO/BASES TECNOLÓGICAS"), 1, 0, 'C', true);
$pdf->Ln();

$limit = 125;
foreach($semana as $chave => $valor) {
	foreach($valor as $chave1 => $valor1) {
		$pdf->SetFont($fonte, 'B', $tamanho+5);
		$pdf->Cell(28, $alturaLinha, utf8_decode("$chave1"), 1, 0, 'C', true);
		$conteudo = explode ( "\r\n",  $valor1 );
		$pdf->SetFont ( $fonte, '', 12 );
		$k=0;
		foreach ( $conteudo as $j => $trecho ) {
			if ($k != 0) $pdf->Cell(28, $alturaLinha, "", 1, 0, 'C', true);
			if (strlen($trecho) > $limit) {
				$conteudo2 = explode ( "\n", wordwrap ( str_replace ( "\r\n", "; ", trim ( $trecho ) ), $limit ) );
				foreach ( $conteudo2 as $n => $trecho2 ) {
					if ($n != 0) $pdf->Cell(28, $alturaLinha, "", 1, 0, 'C', true);
					$pdf->Cell ( 250, $alturaLinha, utf8_decode ( $trecho2 ), 1, 0, 'L', true );
					$pdf->Ln ();
				}

			} else {
				$pdf->Cell ( 250, $alturaLinha, utf8_decode ( $trecho ), 1, 0, 'L', true );
				$pdf->Ln ();
			}
			$k ++;
		}
	}
	$pdf->Ln ();
}

$pdf->Ln();
$pdf->Ln();
$pdf->Cell(139, $alturaLinha, utf8_decode("PROFESSOR(A)"), 1, 0, 'C', true);
$pdf->Cell(139, $alturaLinha, utf8_decode("COORDENADOR(A) DE ÁREA/CURSO"), 1, 0, 'C', true);
$pdf->Ln();
$pdf->Cell(39, $alturaLinha, utf8_decode("DATA"), 1, 0, 'C', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("ASSINATURA"), 1, 0, 'C', true);
$pdf->Cell(39, $alturaLinha, utf8_decode("DATA"), 1, 0, 'C', true);
$pdf->Cell(100, $alturaLinha, utf8_decode("ASSINATURA"), 1, 0, 'C', true);
$pdf->Ln();
$pdf->Cell(39, 15, utf8_decode(""), 1, 0, 'L', true);
$pdf->Cell(100, 15, utf8_decode(""), 1, 0, 'L', true);
$pdf->Cell(39, 15, utf8_decode(""), 1, 0, 'L', true);
$pdf->Cell(100, 15, utf8_decode(""), 1, 0, 'L', true);

mysql_close();

$pdf->Output();
?>