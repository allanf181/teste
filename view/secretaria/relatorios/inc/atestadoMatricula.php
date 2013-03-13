<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

//echo "<br>codigo: ".$_GET["codigo"];
if (isset($_GET["codigo"])) {

    $codigo = dcrip($_GET["codigo"]);

    $assinatura1 = dcrip($_GET["assinatura1"]);
    $sql = "SELECT upper(p.nome), t.nome
            	FROM Pessoas p, PessoasTipos pt, Tipos t 
				WHERE pt.pessoa = p.codigo AND pt.tipo = t.codigo 
				AND t.codigo IN ($COORD, $SEC, $GED) AND p.codigo=$assinatura1";
    $res = mysql_query($sql);
    $linha = mysql_fetch_row($res);
    $nomeAssinatura1 = utf8_decode($linha[0]);//nome da assinatura
    $cargoAssinatura1 = $linha[1];//cargo da assinatura

    $assinatura2 = dcrip($_GET["assinatura2"]);
    $sql = "SELECT upper(p.nome), t.nome
            	FROM Pessoas p, PessoasTipos pt, Tipos t 
				WHERE pt.pessoa = p.codigo AND pt.tipo = t.codigo 
				AND t.codigo IN ($COORD, $SEC, $GED) AND p.codigo=$assinatura2";
    $res = mysql_query($sql);
    $linha = mysql_fetch_row($res);
    $nomeAssinatura2 = utf8_decode($linha[0]);//nome da assinatura
    $cargoAssinatura2 = $linha[1];//cargo da assinatura
    

// Consulta a turma
    $sql = "SELECT Matriculas.codigo, Pessoas.nome, Pessoas.rg, 
    		date_format(Pessoas.nascimento, '%d/%m/%Y'), Cursos.nome, 
    		Turmas.numero, Situacoes.nome, date_format(Matriculas.data, '%d/%m/%Y') data, 
    		date_format(Atribuicoes.dataInicio, '%d/%m/%Y'), date_format(Atribuicoes.dataFim, '%d/%m/%Y'), Turnos.sigla,
    		Cursos.nomeAlternativo, (SELECT cidade FROM Instituicoes LIMIT 1), Turmas.semestre, Atribuicoes.bimestre
			FROM Matriculas, Pessoas, Turmas, Cursos, Turnos, Situacoes, Atribuicoes
			WHERE Matriculas.situacao=Situacoes.codigo 
			and Matriculas.aluno = Pessoas.codigo 
			and Atribuicoes.turma = Turmas.codigo 
			and Matriculas.atribuicao = Atribuicoes.codigo 
			and Turmas.curso = Cursos.codigo 
			and Turmas.turno = Turnos.codigo 
			and Matriculas.codigo=$codigo";

    //echo $sql;
    $resultado = mysql_query($sql);
    $linha = mysql_fetch_array($resultado);

	// inicio e fim da disciplina
    $sql = "SELECT codigo,nome,date_format(inicio, '%Hh%i'),date_format(fim, '%Hh%i') FROM Horarios ORDER BY inicio";
  	$resultado = mysql_query($sql);
  	$i=0;
    while ($horario = mysql_fetch_array($resultado)) {
    	$busca = '['.$linha[10].']';
    	if (strpos($horario[1],$busca) !== false) {
			if ($i==0) $HI = $horario[2];
			$HF = $horario[3];
			$i++;
		}
    }
    if ($linha[11]) $linha[4] = $linha[11];
	$linha[2] = trim($linha[2]);

	$instituicao = utf8_decode($SITE_TITLE);
	$linha[12] = utf8_decode($linha[12]);
	 
	if ($linha[14] == 0) $SEM = 'no '.$linha[13].'º semestre do ';
	if ($linha[14] > 0) $SEM = 'no '.$linha[14].'º Bimestre do ';
	if (!$linha[13] && $linha[14] == 0) $SEM = 'no';
	
    $conteudo="Atestamos, a requerimento do(a) interessado(a), que $linha[1], R.G. nº. $linha[2], nascido(a) em $linha[3], é aluno(a) matriculado(a) neste Instituto, $SEM curso $linha[4], de acordo com as Leis Federais 9394/96 de 20 de dezembro de 1996, 11.741/08 de 16 de julho de 2008 e 11.892 de 29 de dezembro de 2008. ";
    $conteudo2 = "Atestamos, outrossim, que as aulas estão previstas para o período de $linha[8] a $linha[9], de segunda a sexta-feira, das $HI a $HF.";
    $conteudo3 = "$linha[12], " . strtolower(formata(date("Y-m-d"), 1)).".";
    $conteudo4 = "_____________________________________";
    $conteudo5 = $nomeAssinatura1;
    $conteudo6 = $cargoAssinatura1;

	if ($nomeAssinatura2) {
	    $conteudo7 = "_____________________________________";
	    $conteudo8 = $nomeAssinatura2;
	    $conteudo9 = $cargoAssinatura2;
	}

    $fonte = 'Times';
    $tamanho = 15;
    $alturaLinha = 10;
    //$orientacao = "L"; //Landscape 
    $orientacao = "P"; //Portrait 
    $papel = "A4";

    // gera o relatório em PDF
    include PATH.LIB.'/fpdf17/pdfDiario.php';

    // Instanciation of inherited class
    $pdf = new PDF();
	$pdf->logo = PATH.IMAGES."/logo_atestado.jpg";
	$pdf->A = '55';
	$pdf->L = '15';
	$pdf->C = '100';
    $pdf->rodape = $SITE_TITLE;
    $pdf->AliasNbPages();
    $pdf->AddPage($orientacao, $papel);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Ln();
    $pdf->SetFont($fonte, '', 12);
    $pdf->Cell(190,10,$instituicao,0,1,'C',1);
    $pdf->SetFont($fonte, '', $tamanho+5);
    $pdf->Ln();
    $pdf->Cell(190,8,utf8_decode('ATESTADO DE MATRÍCULA'),0,1,'C',1);
    $pdf->Ln();

    $pdf->SetFont($fonte, '', $tamanho);
    $tamanhoLinha=75;
    $linhas = explode( "\n", wordwrap( $conteudo, $tamanhoLinha));
    foreach ($linhas as $value)
        if (strlen($value)>=40)
        $pdf->Cell(190,8,utf8_decode($value),0,1,'FJ',1);
        else
        $pdf->Cell(190,8,utf8_decode($value),0,1,'L',1);


    $linhas = explode( "\n", wordwrap( $conteudo2, $tamanhoLinha));
    $pdf->Ln();
    foreach ($linhas as $value){
        if (strlen($value)>=40)
        $pdf->Cell(190,8,utf8_decode($value),0,1,'FJ',1);
        else
        $pdf->Cell(190,8,utf8_decode($value),0,1,'L',1);
    }
    $pdf->Ln();
    $pdf->Cell(190,8,($conteudo3),0,1,'C',1);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Cell(190,3,($conteudo4),0,1,'C',1);
    $pdf->Ln();
    $pdf->Cell(190,3,($conteudo5),0,1,'C',1);
    $pdf->Ln();
    $pdf->Cell(190,3,($conteudo6),0,1,'C',1);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Cell(190,3,($conteudo7),0,1,'C',1);
    $pdf->Ln();
    $pdf->Cell(190,3,($conteudo8),0,1,'C',1);
    $pdf->Ln();
    $pdf->Cell(190,3,($conteudo9),0,1,'C',1);

    $pdf->Output();
}
?>