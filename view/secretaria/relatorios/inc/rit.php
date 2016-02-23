<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . "/tdDado.class.php";
$dados = new TDDados();

require CONTROLLER . "/tdAtvECmt.class.php";
$atvECmt = new TDAtvECmt();

require CONTROLLER . "/tdComponente.class.php";
$componente = new TDComponente();

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 5;
$orientacao = "P";
$papel = "A4";

include PATH . LIB . '/fpdf17/rotation.php';
$pdf = new PDF ();

if (dcrip($_GET["professor"])) {
    $professor = dcrip($_GET["professor"]);
    if ($professor != 'Todos') {
        $params['codigo'] = $professor;
        $sqlAdicional .= ' AND p.codigo = :codigo ';
    }

    $params['ano'] = $ANO;
    $params['semestre'] = $SEMESTRE;

    if (dcrip($_GET["pano"])) {
        $params['ano'] = dcrip($_GET["pano"]);
    }
    if (dcrip($_GET["psemestre"])) {
        $params['semestre'] = dcrip($_GET["psemestre"]);
    }

    $params['modelo'] = 'RIT';

    $sqlAdicional .= " AND modelo = :modelo ORDER BY p.nome ";

    $res = $dados->listModelo($params, $sqlAdicional, null, null);
    if ($res) {
        foreach ($res as $reg) {
            //IMPORTA PARÃMETROS DA FPA
            $params['modelo'] = 'FPA';
            $resFPA = $dados->listModelo($params, $sqlAdicional, null, null);
            extract(array_map("htmlspecialchars", $resFPA[0]), EXTR_OVERWRITE);

            $apelido = ($apelido) ? "$nome ($apelido)" : $nome;

            //VERIFICA SE ESTA FINALIZADO OU VALIDADO
            $finalizado = $reg['finalizado'];
            if (!$reg['finalizado'] || $reg['finalizado'] == '0000-00-00 00:00:00')
                $pdf->setWaterText(null, null, "NAO FOI FINALIZADO");
            else if (!$reg['valido'] || $reg['valido'] == '0000-00-00 00:00:00')
                $pdf->setWaterText(null, null, "NAO FOI VALIDADO");

            $codigo = $reg['codigo'];
            $horario = $reg['horario'];

            //LISTA COMPONENTES
            $resC = $componente->listComponentes($codigo);
            //LISTA ATIVIDADES
            $resAtv = $atvECmt->listAtvECmt($codigo, 'atv');
            //LISTA COMPLEMENTACAO
            $resComp = $atvECmt->listAtvECmt($codigo, 'cmp');

            $pdf->AliasNbPages();
            $pdf->AddPage($orientacao, $papel);
            $pdf->SetFont($fonte, '', $tamanho);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetLineWidth(.1);

            // Cabeçalho
            $pdf->SetFont($fonte, 'B', $tamanho + 2);
            $pdf->Image(PATH . IMAGES . "/logo.png", 11, 11, 45);
            $pdf->Cell(46, 15, "", 1, 0, 'C', false);
            $pdf->Cell(86, 15, utf8_decode("Relatório Individual de Trabalho Docente - RIT"), 1, 0, 'C', false);
            $pdf->SetFont($fonte, 'B', $tamanho + 2);
            $pdf->Cell(60, 5, abreviar(utf8_decode($SITE_TITLE), 33), 1, 2, 'C', false);
            $pdf->Cell(60, 5, abreviar(utf8_decode($SITE_CIDADE), 33), 1, 2, 'C', false);
            $pdf->Cell(60, 5, abreviar(utf8_decode("Semestre/Ano: $semestre/$ano"), 33), 1, 0, 'C', false);
            $pdf->Ln();
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(65, 5, utf8_decode("(Anexo III - Resolução nº 109 de 4 de novembro de 2015)"), 0, 0, 'C', false);
            $pdf->Ln();
            $pdf->Ln();

            $pdf->SetFont($fonte, 'B', $tamanho);

            $pdf->Cell(20, $alturaLinha - 2, utf8_decode("Docente:"), 0, 0, 'L', true);
            $pdf->Cell(80, $alturaLinha - 2, utf8_decode("$apelido"), 0, 0, 'L', true);
            $pdf->Cell(15, $alturaLinha - 2, utf8_decode("Área:"), 0, 0, 'L', true);
            $pdf->Cell(77, $alturaLinha - 2, utf8_decode("$area"), 0, 0, 'L', true);
            $pdf->Ln();
            $pdf->Cell(20, $alturaLinha - 2, utf8_decode("Prontuário:"), 0, 0, 'L', true);
            $pdf->Cell(80, $alturaLinha - 2, utf8_decode("$prontuario"), 0, 0, 'L', true);
            $pdf->Cell(15, $alturaLinha - 2, utf8_decode("Email:"), 0, 0, 'L', true);
            $pdf->Cell(77, $alturaLinha - 2, utf8_decode("$email"), 0, 0, 'L', true);
            $pdf->Ln();
            $pdf->Cell(20, $alturaLinha - 2, utf8_decode("Telefone:"), 0, 0, 'L', true);
            $pdf->Cell(30, $alturaLinha - 2, utf8_decode("$telefone"), 0, 0, 'L', true);
            $pdf->Cell(15, $alturaLinha - 2, utf8_decode("Celular:"), 0, 0, 'L', true);
            $pdf->Cell(35, $alturaLinha - 2, utf8_decode("$celular"), 0, 0, 'L', true);
            $pdf->Cell(15, $alturaLinha - 2, utf8_decode("Regime:"), 0, 0, 'L', true);
            $pdf->Cell(77, $alturaLinha - 2, utf8_decode("$regime"), 0, 0, 'L', true);
            $pdf->Ln();

            $pdf->Ln();
            $pdf->SetFont($fonte, 'B', $tamanho);

            $pdf->Cell(180, 5, utf8_decode("Atividades de Ensino (Componentes Curriculares ministrados)"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Cell(15, 5, utf8_decode("Sigla"), 1, 0, 'C', true);
            $pdf->Cell(60, 5, utf8_decode("Nome"), 1, 0, 'C', true);
            $pdf->Cell(60, 5, utf8_decode("Curso"), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode("Turno"), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode("Aulas"), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode("Oferta"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            for ($t = 0; $t <= 9; $t++) {
                switch ($resC[$t]['referencia']){
                    case 1: $referencia = "1º Sem";break;
                    case 2: $referencia = "2º Sem";break;
                    case 3: $referencia = "1ºe2º Sem";break;
                    case 4: $referencia = "Anual";break;
                    default: $referencia = "";
                }
                $pdf->Cell(15, 3, utf8_decode($resC[$t]['sigla']), 1, 0, 'L', true);
                $pdf->Cell(60, 3, utf8_decode($resC[$t]['nome']), 1, 0, 'L', true);
                $pdf->Cell(60, 3, utf8_decode($resC[$t]['curso']), 1, 0, 'L', true);
                $pdf->Cell(15, 3, utf8_decode($resC[$t]['turno']), 1, 0, 'L', true);
                $pdf->Cell(15, 3, utf8_decode($resC[$t]['aulas']), 1, 0, 'L', true);
                $pdf->Cell(15, 3, utf8_decode($referencia), 1, 0, 'L', true);
                $pdf->Ln();
                $tAulas += $resC[$t]['aulas'];
                if ($resC[$t]['aulas'])
                    $disc++;
            }

            $tAulas = round($tAulas * substr($duracaoAula, 3, 2) / 60);
            $totalGeral = $tAulas * 2;
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(160, 5, utf8_decode("Temo total dedicado às aulas (Total em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tAulas, 1, 0, 'C', true);
            $pdf->Ln();

//            if ($disc > 4) {
//                $tAulas = $tAulas + ($disc - 4);
//                $totalGeral = $totalGeral + ($disc - 4);
//            }
//            $pdf->Cell(160, 5, utf8_decode("Organização do Ensino (em horas)"), 1, 0, 'R', true);
//            $pdf->Cell(20, 5, $tAulas, 1, 0, 'C', true);
//            $pdf->Ln();
//            $pdf->Cell(160, 5, utf8_decode("Tempo total dedicado à Aulas e Organização de Ensino (em horas)"), 1, 0, 'R', true);
//            $pdf->Cell(20, 5, $totalGeral, 1, 0, 'C', true);
//            $pdf->Ln();
            $pdf->Ln();

            $pdf->Cell(140, 5, utf8_decode("Atividades de Apoio ao Ensino no período considerado neste relatório"), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode("Duração(h)"), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode("Referência"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            for ($t = 0; $t <= 6; $t++) {
                switch ($resComp[$t]['referencia']){
                    case 1: $referencia = "1º Sem";break;
                    case 2: $referencia = "2º Sem";break;
                    case 3: $referencia = "1ºe2º Sem";break;
                    case 4: $referencia = "Anual";break;
                    default: $referencia = "";
                }
                $pdf->Cell(140, 3, utf8_decode($resAtv[$t]['descricao']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($resAtv[$t]['aulas']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($referencia), 1, 0, 'L', true);
                $tAtv += $resAtv[$t]['aulas'];
                $pdf->Ln();
            }
            $totalGeral += $tAtv;
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(160, 5, utf8_decode("Atividades de Apoio ao Ensino (Total em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tAtv, 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Ln();

            $pdf->Cell(140, 5, utf8_decode("Complementação de Atividades no período considerado neste relatório"), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode("Duração(h)"), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode("Referência"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            for ($t = 0; $t <= 6; $t++) {
                switch ($resComp[$t]['referencia']){
                    case 1: $referencia = "1º Sem";break;
                    case 2: $referencia = "2º Sem";break;
                    case 3: $referencia = "1ºe2º Sem";break;
                    case 4: $referencia = "Anual";break;
                    default: $referencia = "";
                }
                $pdf->Cell(140, 3, utf8_decode($resComp[$t]['descricao']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($resComp[$t]['aulas']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($referencia), 1, 0, 'L', true);
                $tComp += $resComp[$t]['aulas'];
                $pdf->Ln();
            }
            $totalGeral += $tComp;
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(160, 5, utf8_decode("Complementação de Atividades (Total em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tComp, 1, 0, 'C', true);

            $pdf->Ln();
            $pdf->Ln();
            $pdf->Cell(160, 5, utf8_decode("Total de horas semanais (obrigatoriamente 20h ou 40h, dependendo do regime de trabalho)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $totalGeral, 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Ln();

            $pdf->Cell(180, 5, utf8_decode("Alterações em relação ao PIT (Justificativas)"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho - 1);
            $conteudo = explode("\n", wordwrap(str_replace("\r\n", ";", trim($horario)), 180));

            foreach ($conteudo as $j => $trecho) {
                $pdf->Cell(180, $alturaLinha, utf8_decode($conteudo[$j]), 1, 0, 'L', true);
                $pdf->Ln();
            }

            $pdf->Ln();
            $pdf->SetFont($fonte, 'B', $tamanho);
            if (!empty($finalizado) && $finalizado != "0000-00-00 00:00:00")
                $pdf->Cell(60, $alturaLinha, utf8_decode($SITE_CIDADE) . ', ' . html_entity_decode(formata($finalizado)), 0, 0, 'L', true);
            $pdf->Ln();

            $pdf->Cell(100, $alturaLinha, str_repeat('_', 38), 0, 0, 'R', true);
            $pdf->Cell(70, $alturaLinha, str_repeat('_', 38), 0, 0, 'R', true);
            $pdf->Ln();
            $pdf->Cell(140, $alturaLinha, utf8_decode("$nome"), 0, 0, 'C', true);
            $pdf->Cell(1, $alturaLinha, utf8_decode("Presidente CAAD"), 0, 0, 'C', true);
            $pdf->SetFont($fonte, '', $tamanho);

            $pdf->Ln();
            $pdf->Ln();

            $pdf->SetFont($fonte, 'B', $tamanho + 2);
            $pdf->Cell(180, 5, utf8_decode("Parecer da Comissão de Área para Atividade Docente"), 'LRTB', 0, 'C', true);
            $pdf->Ln();
            $pdf->Cell(180, 30, "", 'LR', 0, 'L', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            $pdf->Cell(180, 5, utf8_decode("Resultado:  [  ] Homologado  [  ] Devolução para ajustes [  ] Indeferido      ___________________                ________________________________ "), 'LR', 0, 'L', true);
            $pdf->Ln();
            $pdf->Cell(180, 1, utf8_decode("                                                                                                                                     Data                                               Presidente da CAAD"), 'LR', 0, 'L', true);
            $pdf->Ln();
            $pdf->Cell(180, 5, utf8_decode(""), 'LRB', 1, 'L', false);
        }
    } else {
        print utf8_decode("RIT está em processo de correção ou não foi finalizado pelo Docente!");
        die;
    }
}

$pdf->Output();
?>