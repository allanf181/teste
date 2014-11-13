<?php

require '../../../../inc/config.inc.php';
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

require CONTROLLER . "/atribuicao.class.php";
$atribuicao = new Atribuicoes();

include PATH . LIB . '/fpdf17/pdfDiario.php';

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $bimestre = dcrip($_GET["bimestre"]);
    
    $turno = dcrip($_GET["turno"]);
    
    if ($bimestre && $bimestre != 'undefined') {
        $fechamento = 'b';
    } else
        $fechamento = 's';

    $situacaoNome['N'] = 'Nota';
    $situacaoNome['F'] = 'Falta';
    $situacaoNome['SIT'] = 'Situação';
    //$situacaoNome['MG'] = 'Média Global';
    //$situacaoNome['FG'] = 'Frequência Global';

    foreach ($atribuicao->getAtribuicoesFromBoletimTurma($turma, $bimestre, $fechamento, $turno) as $reg) {
        $turnoNome = $reg['turno'];
        $turmaNome = $reg['turma'];
        $bimestres[$reg['bimestre']] = $reg['bimestre'];
        $alunos[$reg['codAluno']] = $reg['aluno'];
        $alunosProntuario[$reg['codAluno']] = $reg['prontuario'];
        $disciplinasMedia[$reg['atribuicao']] = $reg['numero'];
        $disciplinas[$reg['atribuicao']][$reg['codAluno']] = $reg['codMatricula'];
        $situacaoListar[$reg['codAluno']][$reg['atribuicao']] = $reg['listar'];

        $disciplinasNomes[$reg['atribuicao']][$reg['numero']] = $reg['disciplina'] . " - " . $prof->getProfessor($reg['atribuicao'], '', 0, 0);

        $situacaoSigla[$reg['codAluno']][$reg['atribuicao']] = $reg['sigla'];
        $situacaoNome[$reg['sigla']] = $reg['situacao'];
    }

    $pdf = new PDF ();
    $pdf->AliasNbPages();
    $pdf->AddPage($orientacao, $papel);
    $pdf->SetFont($fonte, '', $tamanho);
    $pdf->rodape = $SITE_TITLE;
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetLineWidth(.1);

    // Cabeçalho
    $pdf->SetFont($fonte, 'B', $tamanho + 5);
    $pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 80);
    $pdf->Cell(85, 27, "", 1, 0, 'C', false);
    $pdf->Cell(230, 27, utf8_decode("B O L E T I M   D A   T U R M A"), 1, 0, 'C', false);
    $pdf->Cell(85, 9, utf8_decode("TURMA: $turmaNome"), 1, 2, 'C', false);

    if ($turno)
        $pdf->Cell(85, 9, utf8_decode(strtoupper($turnoNome)), 1, 2, 'C', false);
    else
        $pdf->Cell(85, 9, '', 1, 2, 'C', false);

    if ($fechamento == 's')
        $BIM = 'SEMESTRAL';
    if (($fechamento == 'b' && $bimestre == 'final') || ($_GET["bimestre"] == 'undefined'))
        $BIM = 'FECHAMENTO ANUAL';
    if ($bimestre > 0)
        $BIM = $bimestre . 'º BIMESTRE';

    $pdf->Cell(85, 9, utf8_decode($BIM), 1, 0, 'C', false);
    $pdf->Ln();

    $pdf->SetFont($fonte, 'B', $tamanho + 2);

    $pdf->Cell(20, $alturaLinha, utf8_decode("Prontuário"), 1, 0, 'C', true);
    $pdf->Cell(85, $alturaLinha, utf8_decode("Nome"), 1, 0, 'C', true);

    foreach ($disciplinasNomes as $ddSigla) {
        $T = 285 / count($disciplinasNomes);
        foreach ($ddSigla as $dSigla => $dNome)
            $pdf->Cell($T, $alturaLinha, utf8_decode("$dSigla"), 1, 0, 'C', true);
    }

    $pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
    //$pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
    //$pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);

    $pdf->Ln();
    $pdf->Cell(20, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
    $pdf->Cell(85, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);

    foreach ($disciplinasNomes as $ddSigla) {
        $T = 285 / count($disciplinasNomes);
        foreach ($ddSigla as $dSigla => $dNome)
            $pdf->Cell($T / 2, $alturaLinha, utf8_decode("N"), 1, 0, 'C', true);
        $pdf->Cell($T / 2, $alturaLinha, utf8_decode("F"), 1, 0, 'C', true);
    }

    //$pdf->Cell(10, $alturaLinha, utf8_decode("MG"), 1, 0, 'C', true);
    //$pdf->Cell(10, $alturaLinha, utf8_decode("FG"), 1, 0, 'C', true);
    $pdf->Cell(10, $alturaLinha, utf8_decode("SIT"), 1, 0, 'C', true);

    $pdf->Ln();

    if ($alunos)
        foreach ($alunos as $c => $nome) {
            $discMediaAnual = null;
            $discQdePorAluno = null;
            $pdf->Cell(20, $alturaLinha, utf8_decode($alunosProntuario[$c]), 1, 0, 'C', true);
            $pdf->Cell(85, $alturaLinha, abreviar(utf8_decode(mostraTexto($nome)), 40), 1, 0, 'L', true);

            foreach ($disciplinas as $dCodigo => $dMatricula) {
                if ($situacaoListar[$c][$dCodigo]) {
                    if ($bimestre == 'final' && $fechamento == 'b') {
                        $dados['media'] = $nota->resultadoBimestral($c, $turma, $disciplinasMedia[$dCodigo]);
                    }
                    $dados = $nota->resultado($dMatricula[$c], $dCodigo);
                    $pdf->Cell($T / 2, $alturaLinha, utf8_decode($dados['media']), 1, 0, 'C', true);
                    $pdf->Cell($T / 2, $alturaLinha, utf8_decode($dados['faltas']), 1, 0, 'C', true);
                } else {
                    $pdf->Cell($T, $alturaLinha, utf8_decode($situacaoSigla[$c][$dCodigo]), 1, 0, 'C', true);
                }
            }

            //$dadosGlobal = $nota->resultadoModulo($c, $turma);
            //if (!$media = $dadosGlobal['mediaGlobal'])
            //    $media = '-';
            //$frequencia = round($dadosGlobal['frequenciaGlobal'], 1);
            //$frequencia = (!$frequencia) ? '-' : $frequencia . '%';

            //$pdf->Cell(10, $alturaLinha, $media, 1, 0, 'C', true);
            //$pdf->Cell(10, $alturaLinha, $frequencia, 1, 0, 'C', true);
            $pdf->Cell(10, $alturaLinha, utf8_decode($situacaoSigla[$c][$dCodigo]), 1, 0, 'C', true);

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

    $n = 0;
    $m = 0;
    for ($i = 0; $i < $TT; $i++) {
        foreach ($DISC as $dSigla => $ddSigla) {
            if (!in_array($ddSigla, $dSiglaAnt)) {
                $pdf->Cell(40, $alturaLinha, utf8_decode($ddSigla['SIGLA']), 1, 0, 'C', true);
                $pdf->Cell(240, $alturaLinha, abreviar(utf8_decode($ddSigla['NOME']), 90), 1, 0, 'L', true);
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

$pdf->Output();
?>