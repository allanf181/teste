<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

if (isset($_GET["codigo"])) {

    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new Pessoas();

    require CONTROLLER . "/matricula.class.php";
    $matricula = new Matriculas();

    require CONTROLLER . "/atribuicao.class.php";
    $atribuicao = new Atribuicoes();

    require CONTROLLER . "/ensalamento.class.php";
    $ensalamento = new Ensalamentos();

    if ($assinatura1 = dcrip($_GET["assinatura1"])) {
        $params['tipo'] = $assinatura1;
        $sqlAdicional = " AND pt.tipo IN ($COORD, $SEC, $GED) AND p.codigo=:tipo ";
        $res = $pessoa->listPessoasTipos($params, $sqlAdicional);
        $nomeAssinatura1 = utf8_decode($res[0]['nome']);
        $cargoAssinatura1 = utf8_decode($res[0]['tipo']);
    }

    if ($assinatura2 = dcrip($_GET["assinatura2"])) {
        $params['tipo'] = $assinatura2;
        $sqlAdicional = " AND pt.tipo IN ($COORD, $SEC, $GED) AND p.codigo=:tipo ";
        $res = $pessoa->listPessoasTipos($params, $sqlAdicional);
        $nomeAssinatura2 = utf8_decode($res[0]['nome']);
        $cargoAssinatura2 = utf8_decode($res[0]['tipo']);
    }

    $params = array('codigo' => dcrip($_GET["codigo"]));
    $sqlAdicional = ' AND m.codigo = :codigo GROUP BY m.codigo ORDER BY a.bimestre, p.nome';
    $linha = $matricula->getMatriculas($params, $sqlAdicional);

    $res = $atribuicao->getAtribuicao($linha[0]['atribuicao']);

    $params = array('ano' => $ANO, 'semestre' => $SEMESTRE, 'codigo' => $linha[0]['atribuicao']);
    $sqlAdicional = ' AND a.codigo = :codigo ORDER BY h.inicio';
    $i=0;
    $horario = $ensalamento->listEnsalamentos($params, $sqlAdicional);
    $hi = $horario[0]['inicio'];
    $h = end($horario);
    $hf = $h['fim'];
    
    $conteudo = "Atestamos, a requerimento do(a) interessado(a), que " . $linha[0]['pessoa'] . ","
            . " R.G. nº. " . $linha[0]['rg'] . ", nascido(a) em " . $linha[0]['nascimento'] . ", "
            . "é aluno(a) matriculado(a) neste Instituto, ".$res['bimestreFormat']." curso " . $linha[0]['curso'] . ", "
            . "de acordo com as Leis Federais 9394/96 de 20 de dezembro de 1996, "
            . "11.741/08 de 16 de julho de 2008 e 11.892 de 29 de dezembro de 2008. ";
    $conteudo2 = "Atestamos, outrossim, que as aulas estão previstas para o período de ".$res['dataInicioFormat']." a ".$res['dataFimFormat'].", "
            . "de segunda a sexta-feira, das $hi a $hf.";
    $conteudo3 = utf8_decode($SITE_CIDADE) . ", " . strtolower(formata(date("Y-m-d"), 1)) . ".";
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
    include PATH . LIB . '/fpdf17/pdfDiario.php';

    // Instanciation of inherited class
    $pdf = new PDF();

    $pdf->rodape = $SITE_CIDADE;
    $pdf->AliasNbPages();
    $pdf->AddPage($orientacao, $papel);
    $pdf->Image(PATH . IMAGES . "/logo_atestado.jpg", 55, 10, 100);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont($fonte, '', 12);
    $pdf->Cell(190, 20, '', 0, 1, 'C', false);
    $pdf->Cell(190, 30, utf8_decode($SITE_TITLE), 0, 1, 'C', false);
    $pdf->SetFont($fonte, '', $tamanho + 5);

    $pdf->Cell(190, 10, utf8_decode('ATESTADO DE MATRÍCULA'), 0, 1, 'C', false);
    $pdf->Ln();

    $pdf->SetFont($fonte, '', $tamanho);
    $tamanhoLinha = 75;
    $linhas = explode("\n", wordwrap($conteudo, $tamanhoLinha));
    foreach ($linhas as $value)
        if (strlen($value) >= 40)
            $pdf->Cell(190, 8, utf8_decode($value), 0, 1, 'FJ', 1);
        else
            $pdf->Cell(190, 8, utf8_decode($value), 0, 1, 'L', 1);


    $linhas = explode("\n", wordwrap($conteudo2, $tamanhoLinha));
    $pdf->Ln();
    foreach ($linhas as $value) {
        if (strlen($value) >= 40)
            $pdf->Cell(190, 8, utf8_decode($value), 0, 1, 'FJ', 1);
        else
            $pdf->Cell(190, 8, utf8_decode($value), 0, 1, 'L', 1);
    }
    $pdf->Ln();
    $pdf->Cell(190, 8, ($conteudo3), 0, 1, 'C', 1);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Cell(190, 3, ($conteudo4), 0, 1, 'C', 1);
    $pdf->Ln();
    $pdf->Cell(190, 3, ($conteudo5), 0, 1, 'C', 1);
    $pdf->Ln();
    $pdf->Cell(190, 3, ($conteudo6), 0, 1, 'C', 1);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Cell(190, 3, ($conteudo7), 0, 1, 'C', 1);
    $pdf->Ln();
    $pdf->Cell(190, 3, ($conteudo8), 0, 1, 'C', 1);
    $pdf->Ln();
    $pdf->Cell(190, 3, ($conteudo9), 0, 1, 'C', 1);

    $pdf->Output();
}
?>