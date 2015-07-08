<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . "/professor.class.php";
$prof = new Professores();

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

require CONTROLLER . "/aula.class.php";
$aula = new Aulas();

require CONTROLLER . '/atribuicao.class.php';
$att = new Atribuicoes();

require CONTROLLER . '/avaliacao.class.php';
$aval = new Avaliacoes();

require CONTROLLER . '/situacao.class.php';
$situacao = new Situacoes();

if (dcrip($_GET["atribuicao"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $params['atribuicao'] = $atribuicao;
    $sqlAdicional .= ' AND at.codigo = :atribuicao ';
}

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 4;
$orientacao = "L"; // Landscape
$papel = "A3";

$largura = array(
    5,
    12,
    60,
    36,
    280
);
$larguraDia = 4; // campos de dias


include PATH . LIB . '/fpdf17/rotation.php';

$res = $att->getAtribuicao($atribuicao);
$numeroTurma = $res['numeroDisciplina'] . ' ' . $res['turma'] . ' ' . $res['subturma'];
$numeroDisciplina = $res['numero'];
$ano = $res['ano'];
$semestre = $res['semestre'] . " SEMESTRE";
$disciplina = $res['disciplina'];
$bimestre = $res['bimestre'];
$CH = $res['ch'];
$bimTexto = ($bimestre) ? " - $bimestre BIMESTRE - " : "";
$calculo = $res['calculo'];
$fechamento = $res['fechamento'];
$curso = $res['curso'];
$status = $res['status'];

if ($fechamento == 'a')
    $bimestreEsemestre = 'ANUAL';
if ($fechamento == 'b')
    $bimestreEsemestre = $bimestre . "º BIM / $semestre";
if ($fechamento == 's')
    $bimestreEsemestre = $semestre;

if (!$res) {
    die('Nenhuma aula foi registrada.');
}

$professor = $prof->getProfessor($atribuicao, 1, '', 0, 0);

$avaliacoes = $aval->getQdeAvaliacoes($params, " AND t.tipo <> 'recuperacao' ");
$qde_avaliacao = $avaliacoes['avalCadastradas'];

$pdf = new PDF ();

// MARCA D'ÁGUA
if (!$status)
    $pdf->setWaterText("ESSE DIARIO NAO FOI FINALIZADO", "DIARIO ABERTO");

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
    global $aula, $qde_avaliacao, $atribuicao, $totalDias, $aulas;

    $aulas = $aula->listAulasProfessor($atribuicao, 'ORDER BY a.data ASC, a.codigo');
    // imprime o MES
    foreach ($aulas as $reg) {
        if ($mes = $reg['mes']) {
            $quantidade = $reg['quantidade'];
            if ($quantidade <= 2)
                $quantidade = 3;

            $quantidades += $quantidade;
        }
    }

    $quantidadeTotal = $quantidades;
    $totalDias = intval((295 - $quantidadeTotal) / 4);
    $totalDias -= $qde_avaliacao * 3;
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
    $pdf->Cell(78, 5, utf8_decode(""), 0, 0, 'C', true);

    $pdf->SetFont($fonte, '', $tamanho - 2);

    // imprime o MES
    foreach ($aulas as $reg) {
        if ($mes = $reg['mes']) {
            $quantidade = $reg['quantidade'];
            if ($quantidade <= 2)
                $quantidade = 3;

            $pdf->Cell($quantidade, $alturaLinha, utf8_decode("$mes"), 1, 0, 'C', true);
        }
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

    $pdf->Cell(5, 5, utf8_decode("Nº"), 0, 0, 'C', true);
    $pdf->Cell(53, 5, utf8_decode(""), 0, 0, 'C', true);
    $pdf->Cell(12, 5, utf8_decode("Prontuário"), 0, 0, 'C', true);
    $pdf->Cell(8, 5, utf8_decode("Dia"), 0, 0, 'R', true);


    // imprime o dia
    $pdf->SetFont($fonte, '', $tamanho - 2);
    foreach ($aulas as $reg) {
        if ($dia = $reg['dia']) {
            $quantidade = $reg['quantidade'];
            if ($quantidade <= 2)
                $quantidade = 3;

            $pdf->Cell($quantidade, $alturaLinha, utf8_decode("$dia"), 1, 0, 'C', true);
        }
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

    $pdf->Ln();
}

// Mostrando os alunos
$situacoes = array();
$i = 0;
$params = array('atribuicao' => $atribuicao);
$sqlAdicional = ' WHERE a.codigo=:atribuicao GROUP BY al.codigo ORDER BY al.nome ';
foreach ($aula->listAlunosByAula($params, $sqlAdicional) as $reg) {
    if ($i == 0 || $i == 55)
        cabecalho();

    $nome = $reg['aluno'];
    $prontuario = $reg['prontuario'];
    $matricula = $reg['matricula'];
    $aluno = $reg['codAluno'];
    $pdf->Cell($larguraDia, $alturaLinha, $i + 1, 1, 0, 'C', true);

    $pdf->SetFont($fonte, '', $tamanho);

    $pdf->Cell(53, $alturaLinha, utf8_decode(mostraTexto($nome)), 1, 0, 'L', true);
    $pdf->Cell(15, $alturaLinha, "$prontuario", 1, 0, 'C', true);
    $pdf->Cell(6, $alturaLinha, "", 1, 0, 'C', true);

    // Verificar Frequencia
    $quantidadeTotal = 0;
    foreach ($aulas as $reg) {
        $resAula = $aula->listAulasAluno($reg['codigo'], $aluno, 'sigla');
        $quantidade = $resAula[0]['auladada'];
        if ($quantidade <= 2)
            $quantidade = 3;
        $quantidadeTotal += $quantidade;

        $situacoes[$resAula[0]['falta']] = $resAula[0]['falta'];
        $pdf->SetFont($fonte, '', $tamanho - 3);
        $pdf->Cell($quantidade, $alturaLinha, $resAula[0]['falta'], 1, 0, 'C', true);
    }

    // completa quadros
    for ($j = 1; $j < ($totalDias); $j ++) {
        $pdf->Cell(4, $alturaLinha, "", 1, 0, 'C', true);
    }

    $pdf->Cell($larguraDia, $alturaLinha, "", 0, 0, 'C', true);

    $listaAvaliacoes = $aval->getAvaliacoes($atribuicao);
    if ($qde_avaliacao != 0) {
        foreach ($listaAvaliacoes as $reg) {
            $pdf->SetFont($fonte, '', 6);
            $pdf->Cell($larguraDia, $alturaLinha, utf8_decode($reg['sigla']), 1, 0, 'C', true);
        }
    }

    $pdf->SetFont($fonte, '', 4);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode("MCC"), 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode("REC"), 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, utf8_decode("NCC"), 1, 0, 'C', true);
    $pdf->SetFont($fonte, '', 5);


    $params = array(':aluno' => $aluno, ':atribuicao' => $atribuicao);
    $sqlAdicional = " AND ti.tipo <> 'recuperacao' ";
    // Verificar Avalicao do Aluno
    foreach ($aval->listAvaliacoesAluno($params, $sqlAdicional) as $reg) {
        if ($reg['nome']) {
            if ($reg['peso'] && $reg['calculo'] == 'PESO')
                $reg['nota'] = round($reg['nota'] * $reg['peso'], 1);
            $pdf->Cell($larguraDia, $alturaLinha, $reg['nota'], 1, 0, 'C', true);
        }
    }

    for ($t = $j; $t < $qde_avaliacao; $t++)
        $pdf->Cell($larguraDia, $alturaLinha, '-', 1, 0, 'C', true);

    $MEDIAS = $nota->resultado($matricula, $atribuicao);

    // FECHAMENTO DE NOTAS
    $pdf->Cell($larguraDia, $alturaLinha, $MEDIAS['mediaAvaliacao'], 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, $MEDIAS['notaRecuperacao'], 1, 0, 'C', true);
    $pdf->Cell($larguraDia, $alturaLinha, $MEDIAS['media'], 1, 0, 'C', true);
    // FALTAS
    $pdf->Cell(8, $alturaLinha, $MEDIAS['faltas'], 1, 0, 'C', true);

    $i++;

    // Pular linha
    $pdf->Ln();
}

//IMPRIMINDO AS SITUACOES EXISTENTES NO DIÁRIO
foreach ($situacao->listRegistros() as $reg) {
    if (in_array($reg['sigla'], $situacoes)) {
        $sigla = '| ' . $reg['sigla'] . ': ' . $reg['nome'];
        $pdf->Cell(strlen($sigla), $alturaLinha, $sigla, 0, 0, 'C', true);
    }
}

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

$limit = 120; // tamanho limite

$observ = $aulas[0]['observacoes'];
$competencias = $aulas[0]['competencias'];

// INCLUSAO DOS NOMES DAS AVALIAÇÕES NO CAMPO DE OBSERVAÇÕES
$observ .= "Avaliações aplicadas:\n";
foreach ($listaAvaliacoes as $i) {    
    $observ .= ($i['data'].": ".$i['nome']."(".$i['sigla'].")\n");
}

$k = 0;
$j = 0;
foreach ($aulas as $reg) {
    $dias = null;
    $aulasDadas = null;
    $conteudo = $reg['conteudo'];
    $mes = $reg['mes'];
    $dia = $reg['dia'];
    $aulasDadas = $reg['quantidade'];

    for ($a = 0; $a < $aulasDadas; ++$a)
        $dias .= ($a != $aulasDadas - 1) ? $dia . ',' : $dia;

    $ATV = explode("\n", wordwrap($reg['atividade'], $limit - 25));
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
        $k++;
        $pdf->SetFont($fonte, '', $tamanho);
        $pdf->Ln();
    }
}

// OBSERVACOES E COMPETENCIAS
if ($observ || $competencias) {
// LINHA EM BRANCO TRANSVERSAL
    $pdf->Cell($larg1, $alturaLinha, '', '', 0, 'C', true);
    $pdf->Cell($larg2, $alturaLinha, '', '', 0, 'C', true);
    $pdf->Cell($larg3, $alturaLinha, '', '', 0, 'C', true);
    $pdf->Ln();

    $pdf->SetFont('Courier', 'B', $tamanho + 2);
    $pdf->Cell($larg1 + $larg3, $alturaLinha, utf8_decode('OBSERVAÇÕES'), 1, 0, 'C', true);
    $pdf->Cell($larg2 + $larg4, $alturaLinha, utf8_decode('COMPETÊNCIAS DESENVOLVIDAS'), 1, 0, 'C', true);
    $pdf->Ln();

    $obs1 = explode("\n", wordwrap(str_replace("\r\n", ";", trim($observ)), $limit));
    $comp1 = explode("\n", wordwrap(str_replace("\r\n", ";", trim($competencias)), $limit));

    $REG = ($obs1 > $comp1) ? $obs1 : $comp1;
    foreach ($REG as $j => $trecho) {
        $pdf->SetFont('Courier', '', $tamanho);
        $pdf->Cell($larg1 + $larg3, $alturaLinha, utf8_decode($obs1[$j]), 1, 0, 'L', true);
        $pdf->Cell($larg2 + $larg4, $alturaLinha, utf8_decode($comp1[$j]), 1, 0, 'L', true);
        $k++;
        $pdf->SetFont($fonte, '', $tamanho);
        $pdf->Ln();
    }
}

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(100, $alturaLinha, utf8_decode(str_repeat("_", 40)), 0, 0, 'R', false);
$pdf->Cell(165, $alturaLinha, utf8_decode(str_repeat("_", strlen($professor) + 10)), 0, 0, 'R', false);
$pdf->Ln();
$pdf->Cell(70, $alturaLinha, utf8_decode("COORDENADOR"), 0, 0, 'R', false);
$pdf->Cell(185, $alturaLinha, utf8_decode($professor), 0, 0, 'R', false);

$pdf->Output();
?>