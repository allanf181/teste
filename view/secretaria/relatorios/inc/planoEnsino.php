<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . "/professor.class.php";
$professor = new Professores();

require CONTROLLER . "/planoEnsino.class.php";
$planoEnsino = new PlanosEnsino();

require CONTROLLER . "/planoAula.class.php";
$planoAula = new PlanosAula();


if (dcrip($_GET["atribuicao"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $params['atribuicao'] = $atribuicao;
    $sqlAdicional .= ' AND a.codigo = :atribuicao ';

    include PATH . LIB . '/fpdf17/pdfDiario.php';

    foreach ($planoEnsino->listPlanoEnsino($params, $sqlAdicional) as $reg) {
        $numeroAulaSemanal = $reg['numeroAulaSemanal'];
        $totalHoras = $reg['totalHoras'];
        $totalAulas = $reg['totalAulas'];
        $numeroProfessores = $reg['numeroProfessores'];
        $ITEM['2 - EMENTA'] = $reg['ementa'];
        $ITEM['3 - OBJETIVO'] = $reg['objetivo'];
        $ITEM['4 - CONTEÚDO PROGRAMÁTICO'] = $reg['conteudoProgramatico'];
        $ITEM['5 - METODOLOGIA'] = $reg['metodologia'];
        $ITEM['6 - RECURSOS DIDÁTICOS'] = $reg['recursoDidatico'];
        $ITEM['7 - AVALIAÇÃO'] = $reg['avaliacao'];
        $ITEM['7.1 - RECUPERAÇÃO PARALELA'] = $reg['recuperacaoParalela'];
        $ITEM[$reg['rfTitle']] = $reg["recuperacaoFinal"];
        $ITEM['8 - BIBLIOGRAFIA BÁSICA'] = $reg['bibliografiaBasica'];
        $ITEM['8.1 - BIBLIOGRAFIA COMPLEMENTAR'] = $reg['bibliografiaComplementar'];
        $disciplina = $reg['disciplina'];
        $numero = $reg['numero'];
        $CH = $reg['ch'];
        $curso = $reg['curso'];
        $modalidade = $reg['modalidade'];
    }

    if (!$ITEM)
        die('Sem dados para gerar a lista. Verifique se o plano de ensino e o plano de aula foram preenchidos.');

    $professores = $professor->getProfessor($atribuicao, '', 0, 0);

    $pdf = new PDF ();
    $fonte = 'Arial';
    $tamanho = 7;
    $alturaLinha = 7;

    function cabecalho($tipo) {
        global $pdf, $SITE_CIDADE, $curso, $disciplina, $numero, $SEMESTRE, $ANO, $numeroAulaSemanal;
        global $modalidade, $totalHoras, $totalAulas, $numeroProfessores, $professores;
        global $fonte, $tamanho, $alturaLinha;

        $orientacao = "P"; // Landscape
        $papel = "A3";

        $pdf->AliasNbPages();
        $pdf->AddPage($orientacao, $papel);
        $pdf->SetFont($fonte, '', $tamanho);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetLineWidth(.1);

        // Cabeçalho
        $pdf->SetFont($fonte, 'B', $tamanho + 5);
        $pdf->Image(PATH . IMAGES . "/logo.png", 12, 12, 80);

        $pdf->Cell(90, 27, "", 1, 0, 'C', false);
        $pdf->Cell(130, 27, utf8_decode($tipo), 1, 0, 'C', false);
        $pdf->Cell(58, 27, utf8_decode("CAMPUS: $SITE_CIDADE"), 1, 0, 'C', false);
        $pdf->Ln();
        $pdf->Cell(278, $alturaLinha, utf8_decode("1 - IDENTIFICAÇÃO"), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->Cell(278, $alturaLinha, utf8_decode("CURSO: $curso"), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->Cell(178, $alturaLinha, utf8_decode("COMPONENTE CURRICULAR: $disciplina"), 1, 0, 'L', true);
        $pdf->Cell(100, $alturaLinha, utf8_decode("CÓDIGO DISCIPLINA: $numero"), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->Cell(78, $alturaLinha, utf8_decode("SEMESTRE/ANO: $SEMESTRE/$ANO"), 1, 0, 'L', true);
        $pdf->Cell(100, $alturaLinha, utf8_decode("NÚMERO DE AULAS SEMANAIS: $numeroAulaSemanal"), 1, 0, 'L', true);
        $pdf->Cell(100, $alturaLinha, utf8_decode("ÁREA: $modalidade"), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->Cell(78, $alturaLinha, utf8_decode("TOTAL DE HORAS: $totalHoras"), 1, 0, 'L', true);
        $pdf->Cell(100, $alturaLinha, utf8_decode("TOTAL DE AULAS: $totalAulas"), 1, 0, 'L', true);
        $pdf->Cell(100, $alturaLinha, utf8_decode("NÚMERO DE PROFESSORES: $numeroProfessores"), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->Cell(278, $alturaLinha, utf8_decode("PROFESSOR(A) RESPONSÁVEL: $professores"), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->Ln();
    }

    function rodape() {
        global $pdf, $alturaLinha;
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
    }
    
    cabecalho('P L A N O   D E   E N S I N O');

    foreach ($ITEM as $chave => $valor) {
        $limit = 140;
        if (substr($chave, 0, 1) == '8')
            $limit = 100;

        $pdf->SetFont($fonte, 'B', $tamanho + 5);
        $pdf->Cell(278, $alturaLinha, utf8_decode("$chave"), 1, 0, 'L', true);
        $pdf->Ln();
        $conteudo = explode("\r\n", $valor);
        $pdf->SetFont($fonte, '', 12);
        foreach ($conteudo as $j => $trecho) {
            if (strlen($trecho) > $limit) {
                $conteudo2 = explode("\n", wordwrap(str_replace("\r\n", "; ", trim($trecho)), $limit));
                foreach ($conteudo2 as $n => $trecho2) {
                    $pdf->Cell(278, $alturaLinha, utf8_decode($trecho2), 1, 0, 'L', true);
                    $pdf->Ln();
                }
            } else {
                $pdf->Cell(278, $alturaLinha, utf8_decode($trecho), 1, 0, 'L', true);
                $pdf->Ln();
            }
        }
        if (substr($chave, 0, 1) != '7' && substr($chave, 0, 1) != '8')
            $pdf->Ln();
        if (substr($chave, 0, 3) == '7.2' || substr($chave, 0, 3) == '8.1')
            $pdf->Ln();
    }

    rodape();

    cabecalho('P L A N O   D E   A U L A');

    $limit = 125;
    foreach ($planoAula->listPlanoAulas($atribuicao) as $reg) {
        $pdf->SetFont($fonte, 'B', $tamanho + 5);
        $pdf->Cell(28, $alturaLinha, utf8_decode($reg['semana']), 1, 0, 'C', true);
        $conteudo = explode("\r\n", $reg['conteudo']);
        $pdf->SetFont($fonte, '', 12);
        $k = 0;
        foreach ($conteudo as $j => $trecho) {
            if ($k != 0)
                $pdf->Cell(28, $alturaLinha, "", 1, 0, 'C', true);
            if (strlen($trecho) > $limit) {
                $conteudo2 = explode("\n", wordwrap(str_replace("\r\n", "; ", trim($trecho)), $limit));
                foreach ($conteudo2 as $n => $trecho2) {
                    if ($n != 0)
                        $pdf->Cell(28, $alturaLinha, "", 1, 0, 'C', true);
                    $pdf->Cell(250, $alturaLinha, utf8_decode($trecho2), 1, 0, 'L', true);
                    $pdf->Ln();
                }
            } else {
                $pdf->Cell(250, $alturaLinha, utf8_decode($trecho), 1, 0, 'L', true);
                $pdf->Ln();
            }
            $k ++;
        }
        //$pdf->Ln();
    }

    rodape();
    
    $pdf->Output();
}
?>