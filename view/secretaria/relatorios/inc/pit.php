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

    $sqlAdicional .= " AND modelo = 'PIT' ";

    $res = $dados->listModelo($params, null, null, $sqlAdicional);
    if ($res) {
        foreach ($res as $reg) {
            $codigo = $reg['codigo'];
            $nome = $reg['nome'];
            $prontuario = $reg['prontuario'];
            $ano = $reg['ano'];
            $semestre = $reg['semestre'];
            $telefone = $reg['telefone'];
            $celular = $reg['celular'];
            $email = $reg['email'];
            $area = $reg['area'];
            $regime = $reg['regime'];
            $apelido = ($reg['apelido']) ? "$nome (".$reg['apelido'].")" : $nome;
            $horario = $reg['horario'];
            $horario1 = $reg['horario1'];
            $horario2 = $reg['horario2'];
            $horario3 = $reg['horario3'];
            $subHorario = $reg['subHorario'];
            $duracaoAula = $reg['duracaoAula'];
            $dedicarEnsino = $reg['dedicarEnsino'];

            //VERIFICA SE ESTA FINALIZADO OU VALIDADO
            $finalizado = $reg['finalizado'];
            if (!$reg['finalizado'] || $reg['finalizado'] == '0000-00-00 00:00:00')
                $pdf->setWaterText(null, null, "NAO FOI FINALIZADO");
            else if (!$reg['valido'] || $reg['valido'] == '0000-00-00 00:00:00')
                $pdf->setWaterText(null, null, "NAO FOI VALIDADO");


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
            $pdf->Cell(86, 15, utf8_decode("Plano Individual de Trabalho Docente - PIT"), 1, 0, 'C', false);
            $pdf->SetFont($fonte, 'B', $tamanho + 2);
            $pdf->Cell(60, 5, abreviar(utf8_decode($SITE_TITLE), 33), 1, 2, 'C', false);
            $pdf->Cell(60, 5, abreviar(utf8_decode($SITE_CIDADE), 33), 1, 2, 'C', false);
            $pdf->Cell(60, 5, abreviar(utf8_decode("Semestre/Ano: $SEMESTRE/$ANO"), 33), 1, 0, 'C', false);
            $pdf->Ln();
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(65, 5, utf8_decode("(Anexo II - Resolução nº 112 de 7 outubro de 2014)"), 0, 0, 'C', false);
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

            $dias = diasDaSemana();
            $dias[0] = 'Aula';
            $dias[3] = 'Terça';
            $dias[7] = 'Sábado';
            unset($dias[1]);

            if ($subHorario) {
                for ($p = 1; $p <= 3; $p++) {
                    $h = 'horario' . $p;
                    $h = explode(',', $$h);
                    $ini = $h[1];
                    for ($l = 1; $l <= 31; $l++) {
                        $m = substr($duracaoAula, 3, 2);
                        $fim = date('H:i', strtotime($ini . " + $m minute"));
                        $aula[$p . '' . $l] = "$ini - $fim";

                        $ini = $fim;

                        if ($h[2] == $ini)
                            $ini = date('H:i', strtotime($ini . " + " . substr($h[0], 3, 2) . " minute"));

                        $l += 5;
                    }
                }
            } else {
                for ($p = 1; $p <= 3; $p++) {
                    $aula[$p . '1'] = '1';
                    $aula[$p . '7'] = '2';
                    $aula[$p . '13'] = '3';
                    $aula[$p . '19'] = '4';
                    $aula[$p . '25'] = '5';
                    $aula[$p . '31'] = '6';
                }
            }

            ksort($dias);

            //IMPRIME O DIA DA SEMANA
            foreach ($dias as $dCodigo => $dNome) {
                $pdf->Cell(25.7, 5, utf8_decode("$dNome"), 1, 0, 'C', true);
            }
            $pdf->Ln();

            $periodo[1] = 'Matutino';
            $periodo[2] = 'Vespertino';
            $periodo[3] = 'Noturno';

            $pdf->SetFont($fonte, 'B', $tamanho);
            $n=0;
            for ($p = 1; $p <= 3; $p++) {
                $pdf->Cell(25.7, 5, utf8_decode($periodo[$p]), 1, 0, 'C', true);
                $c = 7;
                $l = 0;
                $horarios = explode(',', $horario);
                for ($i = 1; $i <= 36; $i++) { // LINHAS DA TABELA
                    if ($c >= 7) {
                        $pdf->Ln();
                        $pdf->Cell(25.7, 5, utf8_decode($aula[$p . $i]), 1, 0, 'C', true);
                        $c = 1;
                        $l++;
                    }
                    $pdf->Cell(25.7, 5, utf8_decode($horarios[$n]), 1, 0, 'C', true);
                    $c++;
                    $n++;
                }
                $pdf->Ln();
                $pdf->Ln();
            }
            $x = '';
            if ($dedicarEnsino)
                $x = 'X';
            $pdf->Cell(3, 2.5, "$x", 1, 0, 'L', true);
            $pdf->Cell(170, 3, utf8_decode("Sim, desejo dedicar-me prioritariamente a atividades de ensino."), 0, 0, 'L', false);
            $pdf->Ln();
            $pdf->Ln();
            $pdf->SetFont($fonte, 'B', $tamanho);

            $pdf->Cell(180, 5, utf8_decode("Atividades de Ensino (Regência de Aulas)"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Cell(20, 5, utf8_decode("Sigla"), 1, 0, 'C', true);
            $pdf->Cell(60, 5, utf8_decode("Nome"), 1, 0, 'C', true);
            $pdf->Cell(60, 5, utf8_decode("Curso"), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode("Período"), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode("Aulas"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            for ($t = 0; $t <= 9; $t++) {
                $pdf->Cell(20, 3, utf8_decode($resC[$t]['sigla']), 1, 0, 'L', true);
                $pdf->Cell(60, 3, utf8_decode($resC[$t]['nome']), 1, 0, 'L', true);
                $pdf->Cell(60, 3, utf8_decode($resC[$t]['curso']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($resC[$t]['periodo']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($resC[$t]['aulas']), 1, 0, 'L', true);
                $pdf->Ln();
                $tAulas += $resC[$t]['aulas'];
            }

            $tAulas = round($tAulas * substr($duracaoAula, 3, 2) / 60);
            $totalGeral = $tAulas * 2;
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(160, 5, utf8_decode("Regência de Aulas (em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tAulas, 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Cell(160, 5, utf8_decode("Organização do Ensino (em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tAulas, 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Cell(160, 5, utf8_decode("Tempo total dedicado à Aulas e Organização de Ensino (em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tAulas*2, 1, 0, 'C', true);            
            $pdf->Ln();
            $pdf->Ln();

            $pdf->Cell(180, 5, utf8_decode("Atividades de Apoio ao Ensino"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            for ($t = 0; $t <= 6; $t++) {
                $pdf->Cell(160, 3, utf8_decode($resAtv[$t]['descricao']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($resAtv[$t]['aulas']), 1, 0, 'L', true);
                $tAtv += $resAtv[$t]['aulas'];
                $pdf->Ln();
            }
            $totalGeral += $tAtv;
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(160, 5, utf8_decode("Atividades de Apoio ao Ensino (em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tAtv, 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Ln();

            $pdf->Cell(180, 5, utf8_decode("Complementação de Atividades"), 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            for ($t = 0; $t <= 6; $t++) {
                $pdf->Cell(160, 3, utf8_decode($resComp[$t]['descricao']), 1, 0, 'L', true);
                $pdf->Cell(20, 3, utf8_decode($resComp[$t]['aulas']), 1, 0, 'L', true);
                $tComp += $resComp[$t]['aulas'];
                $pdf->Ln();
            }
            $totalGeral += $tComp;
            $pdf->SetFont($fonte, 'B', $tamanho);
            $pdf->Cell(160, 5, utf8_decode("Complementação de Atividades (em horas)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $tComp, 1, 0, 'C', true);

            $pdf->Ln();
            $pdf->Ln();
            $pdf->Cell(160, 5, utf8_decode("Total de horas semanais (obrigatoriamente 20h ou 40h, dependendo do regime de trabalho)"), 1, 0, 'R', true);
            $pdf->Cell(20, 5, $totalGeral, 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->Ln();

            $pdf->Cell(60, $alturaLinha, utf8_decode($SITE_CIDADE) . ', ' . html_entity_decode(formata($finalizado)), 0, 0, 'L', true);
            $pdf->Ln();
            $pdf->Ln();

            $pdf->SetFont($fonte, 'B', $tamanho);

            $pdf->Cell(100, $alturaLinha, str_repeat('_', 38), 0, 0, 'R', true);
            $pdf->Cell(70, $alturaLinha, str_repeat('_', 38), 0, 0, 'R', true);
            $pdf->Ln();
            $pdf->Cell(140, $alturaLinha, utf8_decode("$nome"), 0, 0, 'C', true);
            $pdf->Cell(1, $alturaLinha, utf8_decode("Presidente CAAD"), 0, 0, 'C', true);
            $pdf->SetFont($fonte, '', $tamanho);
            
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();

            $pdf->SetFont($fonte, 'B', $tamanho+2);
            $pdf->Cell(180, 10, utf8_decode("Parecer da Comissão de Área para Atividade Docente"), 1, 0, 'C', true);
            $pdf->Ln();           
            $pdf->Cell(180, 50, "", 1, 0, 'L', true);
            $pdf->Ln();
            $pdf->SetFont($fonte, '', $tamanho);
            $pdf->Cell(180, 10, utf8_decode("Resultado:  [  ] Homologado  [  ] Devolução para ajustes no preenchimento em ____/_____/______           Ass.: ______________________ (Presidente da CAAD)"), 1, 0, 'L', true);

        }
    } else {
        print utf8_decode("PIT está em processo de correção ou não foi finalizado pelo Docente!");
        die;
    }
}

$pdf->Output();
?>