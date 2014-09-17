<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 7;
$orientacao = "P"; // Landscape
$papel = "A4";

include PATH . LIB . '/fpdf17/pdfDiario.php';

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

require CONTROLLER . '/atribuicao.class.php';
$atribuicao = new Atribuicoes();

require CONTROLLER . '/aluno.class.php';
$alunos = new Alunos();

$pdf = new PDF ();
$pdf->rodape = $SITE_TITLE;

if (dcrip($_GET["turma"])) {
    $turmaCod = dcrip($_GET["turma"]);
    $params['turma'] = $turmaCod;
    $sqlAdicional .= ' AND t.codigo = :turma ';

    if (dcrip($_GET["aluno"])) {
        $params['aluno'] = dcrip($_GET["aluno"]);
        $sqlAdicional .= ' AND a.codigo = :aluno ';
    }

    $situacaoNome['N'] = 'Nota';
    $situacaoNome['F'] = 'Falta';
    $situacaoNome['FQ'] = 'Frequência';
    $situacaoNome['SIT'] = 'Situação';

    $campoExtra = ',a.codigo as aluno,t.codigo as turma';
    foreach ($alunos->listAlunos($params, $sqlAdicional, $campoExtra) as $regAlunos) {
        foreach ($atribuicao->getAtribuicoesFromBoletim($regAlunos['turma'], $regAlunos['aluno']) as $reg) {
            $bimestres[$reg['bimestre']] = $reg['bimestre'];
            $aluno = $reg['aluno'];
            $curso = $reg['curso'];
            $turma = $reg['turma'];
            $disciplinas[$reg['bimestre']][$reg['numeroDisciplina']]['aluno'] = $reg['codAluno'];
            $disciplinas[$reg['bimestre']][$reg['numeroDisciplina']]['matricula'] = $reg['matricula'];
            $disciplinas[$reg['bimestre']][$reg['numeroDisciplina']]['numero'] = $reg['numeroDisciplina'];
            $disciplinas[$reg['bimestre']][$reg['numeroDisciplina']]['atribuicao'] = $reg['atribuicao'];
            $situacaoListar[$reg['atribuicao']] = $reg['listar'];
            $situacaoNome[$reg['sigla']] = $reg['situacao'];
            $situacaoSigla[$reg['numeroDisciplina']] = $reg['sigla'];
            $disciplinasNomes[$reg['codDisciplina']][$reg['numeroDisciplina']] = $reg['disciplina'];
            $bimestres[$reg['bimestre']] = $reg['bimestre'];
        }

        $pdf->AliasNbPages();
        $pdf->AddPage($orientacao, $papel);
        $pdf->SetFont($fonte, '', $tamanho);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetLineWidth(.1);

        // Cabeçalho
        $pdf->SetFont($fonte, 'B', $tamanho + 5);
        $pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 75);
        $pdf->Cell(83, 30, "", 1, 0, 'C', false);
        $pdf->Cell(50, 30, utf8_decode("B O L E T I M"), 1, 0, 'C', false);
        $pdf->SetFont($fonte, 'B', $tamanho + 2);
        $pdf->Cell(60, 10, abreviar(utf8_decode("$aluno"), 33), 1, 2, 'C', false);
        $pdf->Cell(60, 10, abreviar(utf8_decode($curso), 33), 1, 2, 'C', false);
        $pdf->Cell(60, 10, abreviar(utf8_decode($turma), 33), 1, 0, 'C', false);
        $pdf->Ln();

        $pdf->SetFont($fonte, 'B', $tamanho + 2);

        $pdf->Cell(13, $alturaLinha, utf8_decode("Código"), 1, 0, 'C', true);
        $pdf->Cell(70, $alturaLinha, utf8_decode("Disciplina"), 1, 0, 'C', true);

        for ($i = 1; $i <= count($bimestres); $i++) {
            if (count($bimestres) > 1)
                $BIM = $i . "º BIM";
            else
                $BIM = 'Semestre';
            $pdf->Cell(80 / count($bimestres), $alturaLinha, utf8_decode($BIM), 1, 0, 'C', true);
        }
        $pdf->Cell(20, $alturaLinha, utf8_decode("MÉDIA"), 1, 0, 'C', true);
        $pdf->Cell(10, $alturaLinha, utf8_decode("SIT"), 1, 0, 'C', true);

        $pdf->Ln();
        $pdf->Cell(13, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
        $pdf->Cell(70, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);

        for ($i = 1; $i <= count($bimestres); $i++) {
            $pdf->Cell((80 / count($bimestres) / 2), $alturaLinha, utf8_decode("N"), 1, 0, 'C', true);
            $pdf->Cell((80 / count($bimestres) / 2), $alturaLinha, utf8_decode("F"), 1, 0, 'C', true);
        }
        $pdf->Cell(10, $alturaLinha, utf8_decode("N"), 1, 0, 'C', true);
        $pdf->Cell(10, $alturaLinha, utf8_decode("FQ"), 1, 0, 'C', true);
        $pdf->Cell(10, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);

        $pdf->Ln();
        foreach ($disciplinasNomes as $dCodigo => $dNumero) {
            foreach ($dNumero as $dSigla => $dNome) {
                $pdf->Cell(13, $alturaLinha, utf8_decode("$dSigla"), 1, 0, 'C', true);
                $pdf->Cell(70, $alturaLinha, abreviar(utf8_decode("$dNome"), 39), 1, 0, 'L', true);
                for ($b = 1; $b <= count($bimestres); $b++) {
                    if ($disciplinas[$b][$dSigla]) {
                        if ($situacaoListar[$disciplinas[$b][$dSigla]['atribuicao']]) {
                            $dados = $nota->resultado($disciplinas[$b][$dSigla]['matricula'], $disciplinas[$b][$dSigla]['atribuicao']);
                            $pdf->Cell((80 / count($bimestres) / 2), $alturaLinha, utf8_decode($dados['media']), 1, 0, 'C', true);
                            $pdf->Cell((80 / count($bimestres) / 2), $alturaLinha, utf8_decode($dados['faltas']), 1, 0, 'C', true);
                        } else {
                            $pdf->Cell(20, $alturaLinha, utf8_decode($situacaoSigla[$ddCodigo]), 1, 0, 'C', true);
                        }
                        $alunoCodigo = $disciplinas[$b][$dSigla]['aluno'];
                    } else {
                        $pdf->Cell((80 / count($bimestres) / 2), $alturaLinha, utf8_decode("-"), 1, 0, 'C', true);
                        $pdf->Cell((80 / count($bimestres) / 2), $alturaLinha, utf8_decode("-"), 1, 0, 'C', true);
                    }
                }
                $dadosBim = $nota->resultadoBimestral($alunoCodigo, $turmaCod, $dSigla);
                $pdf->Cell(10, $alturaLinha, utf8_decode($dadosBim['media']), 1, 0, 'C', true);
                $pdf->Cell(10, $alturaLinha, utf8_decode(intval($dadosBim['frequencia']) . '%'), 1, 0, 'C', true);
                $pdf->Cell(10, $alturaLinha, utf8_decode($situacaoSigla[$dSigla]), 1, 0, 'C', true);
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

$pdf->Output();
?>