<?php

require '../../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require FUNCOES;

// diÃ¡rio
if (date("m") < 8)
    $semestre = 1;
else
    $semestre = 2;
$ano = date("Y");

$restricao = ""; // sem restriÃ§Ã£o

if (!empty($_GET ["atribuicao"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $restricao = " and a.atribuicao=$atribuicao";
}

// $titulo = "DiÃ¡rio de Classe";
$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 4;
$orientacao = "L"; // Landscape
// $orientacao = "P"; //Portrait
$papel = "A3";

$largura = array(
    5,
    12,
    60,
    36,
    280
);
$larguraDia = 4; // campos de dias

include PATH . LIB . '/fpdf17/pdfDiario.php';

$sql = "SELECT DATE_FORMAT(data, '%m'), c.nome, d.ch,
	t.numero, tu.nome, t.semestre, d.nome, d.numero, a.bimestre,
	a.calculo, c.fechamento, c.nomeAlternativo, a.subturma, m.nome, m.codigo
	FROM Atribuicoes a, Aulas au, Cursos c, Modalidades m, Disciplinas d, Turmas t, Turnos tu 
	WHERE au.atribuicao = a.codigo 
	AND d.codigo = a.disciplina 
	AND d.curso = c.codigo
	AND c.modalidade = m.codigo
	AND t.codigo = a.turma 
	AND tu.codigo = t.turno
	AND a.codigo = $atribuicao";
//print $sql;						
$result = mysql_query($sql);
if (mysql_num_rows($result) != '') {
    for ($i = 0; $i < mysql_num_rows($result); ++$i) {
        $numeroTurma = mysql_result($result, $i, "d.numero") . ' ' . mysql_result($result, $i, "t.numero") . ' ' . mysql_result($result, $i, "a.subturma");
        $numeroDisciplina = mysql_result($result, $i, "d.numero");
        $semestre = mysql_result($result, $i, "t.semestre") . " SEMESTRE";
        $disciplina = mysql_result($result, $i, "d.nome");
        $bimestre = mysql_result($result, $i, "a.bimestre");
        $CH = mysql_result($result, $i, "d.ch");
        $bimTexto = ($bimestre) ? " - $bimestre BIMESTRE - " : "";
        $calculo = mysql_result($result, $i, "a.calculo");
        $fechamento = mysql_result($result, $i, "c.fechamento");

        $curso = (mysql_result($result, $i, "c.nomeAlternativo")) ? mysql_result($result, $i, "c.nomeAlternativo") : mysql_result($result, $i, "c.nome");
        if ((mysql_result($result, $i, "m.codigo") < 1000 || mysql_result($result, $i, "m.codigo") >= 2000) && !mysql_result($result, $i, "c.nomeAlternativo"))
            $curso = mysql_result($result, $i, "m.nome") . ' - ' . mysql_result($result, $i, "c.nome");

        $professores = '';
        foreach (getProfessor($atribuicao) as $key => $reg)
            $professores[] = $reg['nome'];
        $professor = implode(" / ", $professores);

        if ($fechamento == 'a')
            $bimestreEsemestre = 'ANUAL';
        if ($fechamento == 'b')
            $bimestreEsemestre = $bimestre . "º BIM / $semestre";
        if ($fechamento == 's')
            $bimestreEsemestre = $semestre;
    }
} else {
    die('Nenhuma aula foi registrada.');
}

$CAMPO_ESTATICO = 7;

// avalicao
$result2 = mysql_query("SELECT a.codigo,a.sigla,a.nome,
    			date_format(a.data, '%d/%m/%Y') as data
			FROM Avaliacoes a, TiposAvaliacoes t 
			WHERE a.tipo = t.codigo 
			AND atribuicao = $atribuicao 
			AND t.tipo <> 'recuperacao' 
                        ORDER BY a.sigla, a.codigo");
$qde_avaliacao = mysql_num_rows($result2);

$totalDias = 80 - $qde_avaliacao - $CAMPO_ESTATICO;
$REG2[] = '';
$linha = 0;

$pdf = new PDF ();

function cabecalho() {
    // Cabeçalho
    global $pdf, $largura, $alturaLinha, $fonte, $tamanho, $larguraDia, $SITE_TITLE, $orientacao, $papel;

    $pdf->AliasNbPages();
    $pdf->AddPage($orientacao, $papel);
    $pdf->SetFont($fonte, '', $tamanho);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetLineWidth(.1);
    $pdf->Image(PATH . IMAGES . "/logo.png", 12, 9, 50);
    $pdf->rodape = $SITE_TITLE;

    $pdf->SetFont($fonte, '', $tamanho + 5);
    $pdf->Cell(230, $alturaLinha, utf8_decode("D I Á R I O   D E   C L A S S E"), 0, 0, 'R', false);
    $pdf->SetFont($fonte, '', $tamanho + 3);
    $pdf->Cell(165, $alturaLinha, utf8_decode("Data: ____/____/____ "), 0, 0, 'R', false);
    $pdf->SetFont($fonte, '', $tamanho);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    // 1ª LINHA

    $pdf->Cell($largura[0] + $largura[1] + $largura[2] + $largura[3], $alturaLinha, utf8_decode("DISCIPLINA"), 0, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 27, $alturaLinha, utf8_decode("PROFESSOR"), 0, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 8, $alturaLinha, utf8_decode("BIMEST. / SEMEST."), 0, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 3, $alturaLinha, utf8_decode("ANO"), 0, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 24.3, $alturaLinha, utf8_decode("NOME DO CURSO"), 0, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 5, $alturaLinha, utf8_decode("TURMA"), 0, 0, 'C', true);
    $pdf->Ln();

    global $disciplina, $professor, $bimestreEsemestre, $curso, $numeroTurma, $ano;
    // 2ª LINHA
    $alturaLinha+=3;
    $pdf->Cell($largura[0] + $largura[1] + $largura[2] + $largura[3], $alturaLinha, utf8_decode(mostraTexto($disciplina)), 1, 0, 'L', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 27, $alturaLinha, utf8_decode(mostraTexto($professor)), 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 8, $alturaLinha, utf8_decode($bimestreEsemestre), 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 3, $alturaLinha, utf8_decode($ano), 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 24.3, $alturaLinha, utf8_decode(mostraTexto($curso)), 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell($larguraDia * 5.2, $alturaLinha, utf8_decode($numeroTurma), 1, 0, 'C', true);
    $pdf->Ln();
    $alturaLinha-=3;
    $pdf->Cell($larguraDia * 8, $alturaLinha, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Ln();

    // 3ª LINHA
    global $result2, $qde_avaliacao, $atribuicao, $totalDias;

    $sql = "SELECT DATE_FORMAT(data, '%d') as dia, 
		DATE_FORMAT(data, '%m') as mes, quantidade, codigo 
		FROM Aulas 
		WHERE atribuicao = $atribuicao 
		ORDER BY data, codigo";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) != 0) {
        $pdf->SetFont($fonte, '', $tamanho - 2);
        for ($i = 0; $i < mysql_num_rows($result); ++$i) {
            $quantidade = mysql_result($result, $i, "quantidade");
            if ($quantidade <= 2)
                $quantidade = 3;
            $quantidadeTotal += $quantidade;
        }
    }

    $totalDias = intval((293 - $quantidadeTotal) / 4);
    $totalDias -= $qde_avaliacao * 2;
    $M = ($totalDias * 4) + $quantidadeTotal - 4;
    $N = (12) + ($qde_avaliacao * 4);

    $pdf->SetFont($fonte, '', $tamanho - 1);
    $pdf->Cell(78, $alturaLinha - 3, utf8_decode(""), 'LRT', 0, 'C', true);
    $pdf->Cell($M, $alturaLinha - 3, '', 'LRT', 0, 'C', true);
    $pdf->Ln();

    // 4ª LINHA
    $pdf->Cell(78, $alturaLinha, utf8_decode("Nome do Aluno"), 'LR', 0, 'C', true);
    $pdf->Cell($M, $alturaLinha, utf8_decode("M E S E S"), 'LR', 0, 'C', true);
    $pdf->Cell($larguraDia * 1, $alturaLinha, '', 0, 0, 'C', true);

    $pdf->Ln();

    // 5ª LINHA
    if (mysql_num_rows($result) != 0) {
        $pdf->Cell(78, 5, utf8_decode(""), 0, 0, 'C', true);

        $pdf->SetFont($fonte, '', $tamanho - 2);

        // imprime o MES
        for ($i = 0; $i < mysql_num_rows($result); ++$i) {
            $mes = mysql_result($result, $i, "mes");
            $quantidade = mysql_result($result, $i, "quantidade");
            if ($quantidade <= 2)
                $quantidade = 3;

            $pdf->Cell($quantidade, $alturaLinha, utf8_decode("$mes"), 1, 0, 'C', true);
            $linha ++;
        }

        // completa quadros
        for ($j = 1; $j < ($totalDias); $j ++) {
            $pdf->Cell(4, $alturaLinha, "", 1, 0, 'C', true);
        }

        $pdf->Cell(4, $alturaLinha, "", 0, 0, 'C', true);
        $pdf->SetFont($fonte, '', $tamanho - 1);
        $pdf->Cell($N, $alturaLinha, utf8_decode("Avaliações"), 1, 0, 'C', true);
        $pdf->Cell($N, $alturaLinha, utf8_decode("Notas"), 1, 0, 'C', true);
        $pdf->Cell($larguraDia * 2, $alturaLinha, utf8_decode("Faltas"), 1, 0, 'C', true);

        // Pular linha
        $pdf->Ln();
        $linha = 0;

        $pdf->Cell(5, 5, utf8_decode("Nº"), 0, 0, 'C', true);
        $pdf->Cell(53, 5, utf8_decode(""), 0, 0, 'C', true);
        $pdf->Cell(12, 5, utf8_decode("Prontuário"), 0, 0, 'C', true);
        $pdf->Cell(8, 5, utf8_decode("Dia"), 0, 0, 'R', true);


        // imprime o dia
        $pdf->SetFont($fonte, '', $tamanho - 2);
        for ($i = 0; $i < mysql_num_rows($result); $i ++) {
            $dia = mysql_result($result, $i, "dia");
            $quantidade = mysql_result($result, $i, "quantidade");
            if ($quantidade <= 2)
                $quantidade = 3;

            $pdf->Cell($quantidade, $alturaLinha, utf8_decode("$dia"), 1, 0, 'C', true);
            $linha++;
        }

        // completa quadros
        for ($j = 1; $j < ($totalDias); $j ++) {
            $pdf->Cell(4, $alturaLinha, "", 1, 0, 'C', true);
        }

        $pdf->Cell(4, $alturaLinha, "", 0, 0, 'C', true);
        $pdf->SetFont($fonte, '', $tamanho);
        $pdf->Cell($N, $alturaLinha, "", 0, 0, 'C', true);
        $pdf->Cell($N, $alturaLinha, "", 0, 0, 'C', true);
        $pdf->Cell($larguraDia * 2, $alturaLinha, "", 0, 0, 'C', true);
    }
    $pdf->Ln();
}

// Pular linha
$linha = 0;
// Mostrando os alunos
$sql = "SELECT p.nome, m.codigo, p.prontuario, s.nome, s.listar, s.habilitar, m.aluno
			FROM Matriculas m, Atribuicoes a, Pessoas p, Situacoes s
			WHERE a.codigo = m.atribuicao 
			AND p.codigo = m.aluno 
			AND s.codigo = m.situacao
			AND a.codigo = $atribuicao
			ORDER BY p.nome";
$result = mysql_query($sql);

for ($i = 0; $i < mysql_num_rows($result); ++$i) {

    if ($i == 0 || $i == 55)
        cabecalho();

    $nome = mysql_result($result, $i, "p.nome");
    $snome = mysql_result($result, $i, "s.nome");
    $slistagem = mysql_result($result, $i, "s.listar");
    $shabilitar = mysql_result($result, $i, "s.habilitar");
    $prontuario = mysql_result($result, $i, "p.prontuario");
    $matricula = mysql_result($result, $i, "m.codigo");
    $aluno = mysql_result($result, $i, "m.aluno");
    $pdf->Cell($larguraDia, $alturaLinha, $i + 1, 1, 0, 'C', true);

    $pdf->SetFont($fonte, '', $tamanho);

    $pdf->Cell(53, $alturaLinha, utf8_decode(mostraTexto($nome)), 1, 0, 'L', true);
    $pdf->Cell(15, $alturaLinha, "$prontuario", 1, 0, 'C', true);
    $pdf->Cell(6, $alturaLinha, "", 1, 0, 'C', true);

    if ($shabilitar) {
        if ($slistagem) {
            // Verificar Frequencia
            $sql = "SELECT (
			SELECT quantidade
			FROM Frequencias
			WHERE aula = a.codigo
			AND matricula = $matricula
			) as freq, quantidade as auladada,a.data
			FROM Aulas a
			WHERE a.atribuicao = $atribuicao
			ORDER BY data, codigo";
            //print "$sql <br>";
            $faltas = mysql_query($sql);
            $quantidadeTotal=0;
            for ($j = 0; $j < mysql_num_rows($faltas); $j++) {
                $quantidade = mysql_result($faltas, $j, "auladada");
                if ($quantidade <= 2)
                    $quantidade = 3;
                $quantidadeTotal += $quantidade;
                if (!$A = getFrequenciaAbono($aluno, $atribuicao, mysql_result($faltas, $j, "a.data"))) {
                    $falta = mysql_result($faltas, $j, "freq");
                    if ($falta) {
                        $F = $falta;
                    } else {
                        $F = str_repeat('*', mysql_result($faltas, $j, "auladada"));
                    }
                } else {
                    $F = $A['sigla'];
                }

                $pdf->SetFont($fonte, '', $tamanho - 3);
                $pdf->Cell($quantidade, $alturaLinha, $F, 1, 0, 'C', true);
                $linha ++;
            }

            // completa quadros
            for ($j = 1; $j < ($totalDias); $j ++) {
                $pdf->Cell(4, $alturaLinha, "", 1, 0, 'C', true);
            }

            $pdf->Cell($larguraDia, $alturaLinha, "", 0, 0, 'C', true);

            if ($qde_avaliacao != 0) {
                for ($n = 0; $n < $qde_avaliacao; ++$n) {
                    $avaliacao = mysql_result($result2, $n, "a.sigla");

                    $pdf->SetFont($fonte, '', 6);
                    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode($avaliacao), 1, 0, 'C', true);
                }
            }

            $pdf->SetFont($fonte, '', 4);
            $pdf->Cell($larguraDia, $alturaLinha, utf8_decode("MCC"), 1, 0, 'C', true);
            $pdf->Cell($larguraDia, $alturaLinha, utf8_decode("REC"), 1, 0, 'C', true);
            $pdf->Cell($larguraDia, $alturaLinha, utf8_decode("NCC"), 1, 0, 'C', true);
            $pdf->SetFont($fonte, '', 5);

            // Verificar Avalicao do Aluno
            $sql = "SELECT n.nota, a.peso,
			(SELECT COUNT(*) FROM Avaliacoes WHERE atribuicao=$atribuicao AND tipo NOT IN (SELECT codigo FROM TiposAvaliacoes WHERE tipo = 'recuperacao') ) as total
			FROM Notas n, Atribuicoes at, Avaliacoes a
			WHERE n.avaliacao = a.codigo
			AND a.atribuicao = at.codigo
			AND a.atribuicao = $atribuicao
			AND n.matricula = $matricula
			AND a.tipo NOT IN (SELECT codigo FROM TiposAvaliacoes WHERE tipo = 'recuperacao')
                        ORDER BY a.sigla, a.codigo";
            //print "$sql<br><br>";
            $notas = mysql_query($sql);
            for ($j = 0; $j < mysql_num_rows($notas); $j++) {
                $nota = mysql_result($notas, $j, "n.nota");
                $peso = mysql_result($notas, $j, "a.peso");
                $qdeAval = mysql_result($notas, $j, "total");
                $pdf->Cell($larguraDia, $alturaLinha, $nota, 1, 0, 'C', true);
            }

            for ($t = $j; $t < $qdeAval; $t++)
                $pdf->Cell($larguraDia, $alturaLinha, '-', 1, 0, 'C', true);

            $linha ++;

            $MEDIAS = resultado($matricula, $atribuicao);

            // FECHAMENTO DE NOTAS E FALTAS
            $pdf->Cell($larguraDia, $alturaLinha, $MEDIAS['mediaAvaliacao'], 1, 0, 'C', true);
            $pdf->Cell($larguraDia, $alturaLinha, $MEDIAS['notaRecuperacao'], 1, 0, 'C', true);
            $pdf->Cell($larguraDia, $alturaLinha, $MEDIAS['media'], 1, 0, 'C', true);
            $pdf->Cell(8, $alturaLinha, $MEDIAS['faltas'], 1, 0, 'C', true);
        } else {
            $pdf->Cell((($totalDias*4)+$quantidadeTotal)-4, $alturaLinha, mostraTexto(utf8_decode($snome)), 1, 0, 'C', true);
        }
    } else {
        $pdf->Cell((($totalDias*4)+$quantidadeTotal)-4, $alturaLinha, mostraTexto(utf8_decode($snome)), 1, 0, 'C', true);
    }
    // Pular linha
    $pdf->Ln();
    $linha = 0;
}
$pdf->Ln();

// /// VERSO
$pdf->AliasNbPages();
$pdf->AddPage($orientacao, $papel);
$pdf->SetFont($fonte, '', $tamanho + 5);

$larg1 = 15;
$larg2 = 30;
$larg3 = 185;
$larg4 = 150;
$larg5 = 8;
$larg6 = 150;

$pdf->Cell($larg1 + $larg2, $alturaLinha + 2, utf8_decode("AULAS"), 'LRT', 0, 'C', true);
$pdf->Cell($larg3, $alturaLinha + 2, utf8_decode(""), 'TLR', 0, 'C', true);
$pdf->Cell($larg4, $alturaLinha + 2, utf8_decode(""), 'TLR', 0, 'C', true);
$pdf->Ln();

$pdf->Cell($larg1 + $larg2, $alturaLinha + 2, utf8_decode("DADAS"), 'LRB', 0, 'C', true);
$pdf->Cell($larg3, $alturaLinha + 2, utf8_decode("BASES / CONHECIMENTOS DESENVOLVIDOS"), 'LR', 0, 'C', true);
$pdf->Cell($larg4, $alturaLinha + 2, utf8_decode("ATIVIDADES E AVALIAÇÕES"), 'LR', 0, 'C', true);
$pdf->Ln();

$pdf->Cell($larg1, $alturaLinha + 2, utf8_decode("MÊS"), 1, 0, 'C', true);
$pdf->Cell($larg2, $alturaLinha + 2, utf8_decode("DIA"), 1, 0, 'C', true);
$pdf->Cell($larg3, $alturaLinha + 2, utf8_decode(""), 'BLR', 0, 'C', true);
$pdf->Cell($larg4, $alturaLinha + 2, utf8_decode(""), 'BLR', 0, 'C', true);
$pdf->Ln();

$pdf->SetFont($fonte, '', $tamanho);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetLineWidth(.1);

// Mostrando o conteúdo da disciplina ministrado
$sql = "SELECT au.conteudo, au.quantidade, DATE_FORMAT(au.data, '%m') as mes,
		DATE_FORMAT(au.data, '%d') as dia, a.observacoes, a.competencias, au.atividade
		FROM Aulas au, Atribuicoes a, Disciplinas d, Cursos c, Modalidades m
		WHERE a.codigo = au.atribuicao
		AND d.codigo = a.disciplina 
		AND d.curso = c.codigo
		AND c.modalidade = m.codigo
		AND a.codigo = $atribuicao
		ORDER BY au.data";

//print $sql;
$resultado = mysql_query($sql);
$linha = mysql_fetch_array($resultado);

$limit = 105; // tamanho limite
$limit2 = 120;

$observ = $linha[4];
$competencias = $linha[5];

$obs = explode("\n", wordwrap($observ, $limit2));
$comp = explode("\n", wordwrap($competencias, $limit2));

$k = 0;
$j = 0;
for ($i = 0; $i < mysql_num_rows($resultado); ++$i) {
    $dias = null;
    $aulasDadas = null;
    $conteudo = mysql_result($resultado, $i, "au.conteudo");
    $mes = mysql_result($resultado, $i, "mes");
    $dia = mysql_result($resultado, $i, "dia");
    $aulasDadas = mysql_result($resultado, $i, "au.quantidade");

    for ($a = 0; $a < $aulasDadas; ++$a)
        $dias .= ($a != $aulasDadas - 1) ? $dia . ',' : $dia;

    $ATV = explode("\n", wordwrap(mysql_result($resultado, $i, "au.atividade"), $limit - 10));
    $conteudo = explode("\n", wordwrap(str_replace("\r\n", ";", trim($conteudo)), $limit));

    $REG = ($ATV > $conteudo) ? $ATV : $conteudo;
    foreach ($REG as $j => $trecho) {
        if ($j == 0) {
            $pdf->Cell($larg1, $alturaLinha, utf8_decode($mes), 1, 0, 'C', true);
            $pdf->Cell($larg2, $alturaLinha, utf8_decode($dias), 1, 0, 'C', true);
        } else {
            $pdf->Cell($larg1, $alturaLinha, "", 1, 0, 'C', true);
            $pdf->Cell($larg2, $alturaLinha, "", 1, 0, 'C', true);
        }
        $pdf->SetFont('Courier', '', $tamanho);
        $pdf->Cell($larg3, $alturaLinha, utf8_decode($conteudo[$j]), 1, 0, 'L', true);
        $pdf->Cell($larg4, $alturaLinha, utf8_decode($ATV[$j]), 1, 0, 'L', true);
        $k ++;
        $pdf->SetFont($fonte, '', $tamanho);
        $pdf->Ln();
    }

    if ($i == 40) {
        $k = 0;
        $pdf->AddPage($orientacao, $papel);
        $ALT_OBS = 18;
    }
}

//COMPLETA AS LINHAS PARA PREENCHER A FOLHA

while ($k < 40) {
    $pdf->Cell($larg1, $alturaLinha, "//", 1, 0, 'C', true);  //mes
    $pdf->Cell($larg2, $alturaLinha, "//", 1, 0, 'C', true);  //dia
    $pdf->Cell($larg3, $alturaLinha, "//////////////////////////////////", 1, 0, 'L', true); //conteÃºdo ministrado
    $pdf->Cell($larg4, $alturaLinha, "//////////////////////////////////", 1, 0, 'L', true);         //avaliaÃ§Ãµes
    $pdf->Ln();
    $k++;
}

// LINHA EM BRANCO TRANSVERSAL
$pdf->Cell($larg1, $alturaLinha, '', '', 0, 'C', true);
$pdf->Cell($larg2, $alturaLinha, '', '', 0, 'C', true);
$pdf->Cell($larg3, $alturaLinha, '', '', 0, 'C', true);
$pdf->Ln();

// OBSERVAÃ‡Ã•ES E COMPETÃŠNCIAS
$pdf->SetFont('Courier', '', $tamanho);
$l = 0;
$pdf->Cell(8, $alturaLinha, '', 'LRT', 0, 'C', true);
while ($l <= 12) {
    if ($l > 0)
        $pdf->Cell(8, $alturaLinha, '', 'LR', 0, 'C', true);
    $pdf->Cell($larg6 * 1.213, $alturaLinha, utf8_decode($obs[$l]), 1, 0, 'L', true);
    if ($l == 0)
        $pdf->Cell($larg5, $alturaLinha, '', 'LRT', 0, 'L', true);
    else
        $pdf->Cell($larg5, $alturaLinha, '', 'LR', 0, 'L', true);
    $pdf->Cell($larg6 * 1.213, $alturaLinha, utf8_decode($comp[$l]), 1, 0, 'L', true);
    $pdf->Ln();
    $l++;
    if ($l >= 13)
        break;
}

if ($l <= 10) {
    while ($l <= 12) {
        if ($l == 12)
            $pdf->Cell(8, $alturaLinha, '', 'LRB', 0, 'L', true);
        else
            $pdf->Cell(8, $alturaLinha, '', 'LR', 0, 'L', true);

        $pdf->Cell($larg6 * 1.213, $alturaLinha, '', 1, 0, 'C', true);
        if ($l == 12)
            $pdf->Cell($larg5, $alturaLinha, '', 'LRB', 0, 'L', true);
        else
            $pdf->Cell($larg5, $alturaLinha, '', 'LR', 0, 'L', true);
        $pdf->Cell($larg6 * 1.213, $alturaLinha, '', 1, 0, 'C', true);
        $pdf->Ln();
        $l++;
    }
}
$pdf->Ln();

// TEXTOS ROTACIONADOS
$pdf->SetFont($fonte, '', $tamanho + 4);
$pdf->RotatedText(15, 230 - $ALT_OBS, utf8_decode('OBSERVAÇÕES'), 90);
$pdf->RotatedText(206, 243 - $ALT_OBS, utf8_decode('COMPETÊNCIAS DESENV.'), 90);

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(100, $alturaLinha, utf8_decode(str_repeat("_", 40)), 0, 0, 'R', false);
$pdf->Cell(165, $alturaLinha, utf8_decode(str_repeat("_", strlen($professor) + 10)), 0, 0, 'R', false);
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(70, $alturaLinha, utf8_decode("COORDENADOR"), 0, 0, 'R', false);
$pdf->Cell(185, $alturaLinha, utf8_decode($professor), 0, 0, 'R', false);

mysql_close();

$pdf->Output();
?>