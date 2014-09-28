<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . "/matricula.class.php";
$matricula = new Matriculas();

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

$fonte = 'Times';
$orientacao = "P"; //Portrait 
$papel = "A4";

// gera o relatório em PDF
include PATH . LIB . '/fpdf17/pdfImage.php';

// Instanciation of inherited class
$pdf = new PDF_MemImage();

$i = 1;
$j = 1;
$p = 0;
$pag = 0;
$op = 0;

if ($turma = dcrip($_GET["turma"])) {
    $params['turma'] = $turma;
    $sqlAdicional = ' AND t.codigo =:turma ';

    if (dcrip($_GET["turno"])) {
        $turno = dcrip($_GET["turno"]);
        $params['turno'] = $turno;
        $sqlAdicional .= ' AND a.periodo = :turno ';
    }

    $sqlAdicional .= ' GROUP BY p.codigo ';

    if ($turno) {
        require CONTROLLER . '/turno.class.php';
        $turnos = new Turnos();

        $paramsTurno['codigo'] = $turno;
        $turnoNome = $turnos->listRegistros($paramsTurno);
        $titulo2 .= ' [' . $turnoNome[0]['nome'] . ']';
    }

    foreach ($matricula->getMatriculas($params, $sqlAdicional) as $reg) {
        if ($pag > 14 || $pag == 0) {
            $pdf->AliasNbPages();
            $pdf->AddPage($orientacao, $papel);
            $pdf->SetFont($fonte, '', 16);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->Ln();
            $pdf->Cell(190, 8, utf8_decode('C A R Ô M E T R O'), 0, 0, 'C', 1);
            $pdf->Ln();
            $pdf->Cell(190, 8, utf8_decode("TURMA: " . $reg['numero'] . $titulo2), 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->Ln();
            $pdf->SetFont($fonte, '', 8);
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $i = 1;
            $j = 1;
            $p = 0;
            if ($pag)
                $op = 1;
            $pag = 0;
        }
        if ($p > 2) {
            $j = $j + 50;
            $i = 1;
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $p = 0;
        }
        if ($op) {
            $j = $j + 5;
            $op = 0;
        }
        $R = 30 + $i;
        $T = 34 + $j;

        $paramsPessoa = array('codigo' => $reg['codPessoa']);
        $res = $pessoa->listRegistros($paramsPessoa);

        if ($foto = $res[0]['foto'])
            $pdf->MemImage($foto, $R, $T, 25, 30);
        $i = $i + 61;
        $pdf->Cell(63, 5, abreviar(utf8_decode($reg['pessoa']), 32), 1, 0, 'C', 1);
        $p++;
        $pag++;
    }
    $pdf->Output();
} else {
    print "SELECIONE UM TURMA...";
}
?>