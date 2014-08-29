<?php

require '../../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require FUNCOES;

// Preparando os dados
function createArray($Arr) {
    foreach ($Arr as $r) {
        list($k, $v) = explode('-', $r);
        $T[$k] = $v;
    }
    return $T;
}

$fonte = 'Arial';
$tamanho = 7;
$alturaLinha = 7;
$orientacao = "P"; // Landscape
// $orientacao = "P"; //Portrait
$papel = "A4";

include PATH . LIB . '/fpdf17/pdfDiario.php';

$pdf = new PDF ();

if (dcrip($_GET["professor"])) {
    $professor = dcrip($_GET["professor"]);

    $detalhada = $_GET["detalhada"];

    if (dcrip($_GET["professor"]) != 'Todos')
        $sqlProfessor = " and fd.professor = " . dcrip($_GET["professor"]);

    $sql = "SELECT fd.codigo FROM FTDDados fd, Pessoas p
        	WHERE p.codigo = fd.professor
        	AND ano = '$ano'
                AND (semestre = '$semestre' OR semestre = 0)
		$sqlProfessor
		AND (fd.finalizado <> '' AND fd.finalizado <> '0000-00-00 00:00:00')
		ORDER BY p.nome";
    //print $sql;
    $r = mysql_query($sql);
    if (mysql_num_rows($r) > 0) {
        while ($linha = mysql_fetch_array($r)) {
            $codigo = $linha[0];
            $sql = "SELECT p.nome, p.prontuario, fd.ano, fd.semestre, fd.telefone, fd.celular,
			fd.email, fd.area, fd.regime, fd.observacao, fd.TP, fd.TPT, fd.TD,
			fd.TDT, fd.ITE, fd.ITS, fd.A, fd.AT, fd.AtvDocente, fd.Projetos,
			fd.Intervalos, fd.Total, fh.registro, fh.horario, fd.finalizado
			FROM Pessoas p, FTDDados fd, FTDHorarios fh
			WHERE p.codigo = fd.professor
			AND fd.codigo = fh.ftd
			AND fd.codigo = $codigo
			ORDER BY p.nome, fh.horario";
            //echo $sql;
            $resultado = mysql_query($sql);
            if ($resultado)
                while ($l = mysql_fetch_array($resultado)) {
                    $nome = $l[0];
                    $prontuario = $l[1];
                    $ano = $l[2];
                    $semestre = $l[3];
                    $telefone = $l[4];
                    $celular = $l[5];
                    $email = $l[6];
                    $area = $l[7];
                    $regime = $l[8];
                    $obs = $l[9];
                    $TP = createArray(explode(',', $l[10]));
                    $TPT = createArray(explode(',', $l[11]));
                    $TD = createArray(explode(',', $l[12]));
                    $TDT = createArray(explode(',', $l[13]));
                    $ITE = createArray(explode(',', $l[14]));
                    $ITS = createArray(explode(',', $l[15]));
                    $A = createArray(explode(',', $l[16]));
                    $AT = $l[17];
                    $AtvDocente = $l[18];
                    $Projetos = $l[19];
                    $Intervalos = $l[20];
                    $Total = $l[21];
                    $registro = $l[22];
                    $horario = $l[23];
                    $finalizado = $l[24];
                    $horarios[$registro] = $horario; // FTD Detalhada

                    if (substr($registro, 3, 1) == 1) { // achando a primeira entrada
                        $k = substr($registro, 0, 1) . substr($registro, 2, 1) . substr($registro, 3, 1);
                        if (!$first_in[$k])
                            $first_in[$k] = $horario;
                    }
                    if (substr($registro, 3, 1) == 2) { // achando a ultima saida
                        $k = substr($registro, 0, 1) . substr($registro, 2, 1) . substr($registro, 3, 1);
                        $last_out[$k] = $horario;
                    }
                }

            $pdf->AliasNbPages();
            $pdf->AddPage($orientacao, $papel);
            $pdf->SetFont($fonte, '', $tamanho);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetLineWidth(.1);

            // Cabeçalho
            $pdf->SetFont($fonte, 'B', $tamanho + 2);
            $pdf->Image(PATH . IMAGES . "/logo.png", 11, 11, 45);
            $pdf->Cell(46, 15, "", 1, 0, 'C', false);
            if ($detalhada)
                $D = '- DETALHADA';
            $pdf->Cell(86, 15, utf8_decode("FTD - FOLHA DE TRABALHO DOCENTE $D"), 1, 0, 'C', false);
            $pdf->SetFont($fonte, 'B', $tamanho + 2);
            $pdf->Cell(60, 5, abreviar(utf8_decode($SITE_TITLE), 33), 1, 2, 'C', false);
            $pdf->Cell(60, 5, abreviar(utf8_decode($SITE_CIDADE), 33), 1, 2, 'C', false);
            $pdf->Cell(60, 5, abreviar(utf8_decode("Ano/Semestre: $semestre/$ano"), 33), 1, 0, 'C', false);

            $pdf->Ln();
            $pdf->Ln();

            $pdf->SetFont($fonte, 'B', $tamanho + 2);

            $pdf->Cell(20, $alturaLinha - 2, utf8_decode("Docente:"), 0, 0, 'L', true);
            $pdf->Cell(80, $alturaLinha - 2, utf8_decode("$nome"), 0, 0, 'L', true);
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

            if (!$detalhada) { // FTP RESUMIDA
                $dias = diasDaSemana();
                unset($dias[1]);
                ksort($dias);

                $pdf->SetFillColor(192, 192, 192);

                $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);
                foreach ($dias as $dCodigo => $dNome) {
                    $pdf->Cell(24, $alturaLinha, html_entity_decode("$dNome"), 1, 0, 'C', true);
                }
                $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);

                $pdf->Ln();
                $pdf->Cell(24, $alturaLinha, html_entity_decode("Per&iacute;odo"), 1, 0, 'C', true);

                for ($i = 1; $i <= 6; $i++) {
                    $pdf->SetFont($fonte, '', $tamanho);
                    $pdf->Cell(12, $alturaLinha, html_entity_decode("Entrada"), 1, 0, 'C', true);
                    $pdf->Cell(12, $alturaLinha, html_entity_decode("Sa&iacute;da"), 1, 0, 'C', true);
                }
                $pdf->SetFont($fonte, 'B', $tamanho + 2);

                $pdf->Cell(24, $alturaLinha, html_entity_decode("Total Per&iacute;odo"), 1, 0, 'C', true);
                $pdf->Ln();

                for ($p = 1; $p <= 2; $p++) {
                    $pdf->Cell(24, $alturaLinha * 2, "", 1, 0, 'C', true);

                    $pdf->SetFillColor(255, 255, 255);
                    for ($c = 1; $c <= 6; $c++) {
                        $pdf->Cell(12, $alturaLinha, html_entity_decode($first_in[$p . $c . '1']), 1, 0, 'C', true);
                        $pdf->Cell(12, $alturaLinha, html_entity_decode($last_out[$p . $c . '2']), 1, 0, 'C', true);
                    }
                    $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);

                    $pdf->Ln();
                    $pdf->Cell(24, $alturaLinha - 14, html_entity_decode($p . "&ordm;"), 1, 0, 'C', true);

                    $pdf->SetFillColor(192, 192, 192);
                    for ($c = 1; $c <= 6; $c++) {
                        $pdf->Cell(24, $alturaLinha, html_entity_decode($TD[$p . "TDP" . $c]), 1, 0, 'C', true);
                    }
                    $pdf->Cell(24, $alturaLinha, html_entity_decode($TPT[$p . "TP"]), 1, 0, 'C', true);
                    $pdf->Ln();
                }
                $pdf->SetFillColor(255, 255, 255);

                if ($obs) {
                    // Observcao a ser incluida na FTD
                    $pdf->Cell(24, $alturaLinha, utf8_decode("Observação:"), 1, 0, 'C', true);
                    $pdf->MultiCell(144, $alturaLinha, utf8_decode("$obs"), 1, 1, 'L', true);
                }

                $pdf->Ln();

                $pdf->Cell(24, $alturaLinha, "", 0, 0, 'C', true);
                $pdf->SetFillColor(192, 192, 192);
                $pdf->Cell(144, $alturaLinha, html_entity_decode("Carga hor&aacute;ria di&aacute;ria"), 1, 0, 'C', true);
                $pdf->Ln();

                // carga horaria total
                $pdf->SetFillColor(255, 255, 255);
                $pdf->Cell(24, $alturaLinha, "", 0, 0, 'C', true);
                for ($c = 1; $c <= 6; $c++) {
                    $pdf->Cell(24, $alturaLinha, html_entity_decode($TDT["TD" . $c]), 1, 0, 'C', true);
                }

                $pdf->Ln();

                // carga horario semanal
                $pdf->Cell(24, $alturaLinha, "", 0, 0, 'C', true);
                $pdf->SetFillColor(192, 192, 192);
                $pdf->Cell(48, $alturaLinha, html_entity_decode("Carga hor&aacute;ria semanal"), 1, 0, 'L', true);
                $pdf->SetFillColor(255, 255, 255);

                $pdf->Cell(20, $alturaLinha, html_entity_decode($Total), 1, 0, 'C', true);

                $pdf->Ln();
                $pdf->Ln();

                $pdf->Cell(24, $alturaLinha, "", 0, 0, 'C', true);
                $MSG = "Declaro que, al&eacute;m do hor&aacute;rio indicado nesta FTD, cumpro o hor&aacute;rio de	prepara&ccedil;&atilde;o did&aacute;tica previsto na Portaria n&ordm;1535/2011 para meu Regime de Trabalho.";
                $pdf->MultiCell(144, $alturaLinha, html_entity_decode($MSG), 2, 0, 'C', true);
                $pdf->Cell(24, $alturaLinha, "", 0, 0, 'C', true);
                $MSG = "Declaro tamb&eacute;m que garantirei a compatibilidade de hor&aacute;rio entre minha dedica&ccedil;&atilde;o ao IFSP e compromissos assumidos com outras institui&ccedil;&otilde;es, desde que permiitidos por lei.";
                $pdf->MultiCell(144, $alturaLinha, html_entity_decode($MSG), 2, 0, 'C', true);

                $pdf->Ln();

                $pdf->Ln();
                $pdf->Cell(120, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, $SITE_CIDADE . ', ' . html_entity_decode(formata($finalizado)), 0, 0, 'R', true);
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Cell(120, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, str_repeat('_', 38), 0, 0, 'R', true);
                $pdf->Ln();
                $pdf->Cell(120, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, utf8_decode("$nome"), 0, 0, 'C', true);
                $pdf->SetFont($fonte, '', $tamanho);
                $pdf->Ln();
                $pdf->Cell(120, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, html_entity_decode("Docente"), 0, 0, 'C', true);
                $pdf->Ln();
                $pdf->Cell(120, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, html_entity_decode("Respons&aacute;vel pelo preenchimento."), 0, 0, 'C', true);

                $pdf->Ln();
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Ln();

                $pdf->SetFont($fonte, 'B', $tamanho + 2);

                $pdf->Cell(70, $alturaLinha, str_repeat('_', 38), 0, 0, 'R', true);
                $pdf->Cell(10, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, str_repeat('_', 38), 0, 0, 'R', true);
                $pdf->Ln();

                $pdf->Cell(70, $alturaLinha, utf8_decode("GERENTE/DIRETOR"), 0, 0, 'C', true);
                $pdf->Cell(10, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, utf8_decode("Coordenador da área $area"), 0, 0, 'C', true);

                $pdf->SetFont($fonte, '', $tamanho);
                $pdf->Ln();
                $pdf->Cell(70, $alturaLinha, html_entity_decode("Respons&aacute;vel pelo envio."), 0, 0, 'C', true);
                $pdf->Cell(10, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(70, $alturaLinha, html_entity_decode("Respons&aacute;vel pela confer&ecirc;ncia."), 0, 0, 'C', true);
            } else { // FTP DETALHADA
                $fonte = 'Arial';
                $tamanho = 5;
                $alturaLinha = 4.7;
                $dias = diasDaSemana();
                $pdf->SetFillColor(192, 192, 192);


                $dias[0] = '&ordm; PER&Iacute;ODO';
                $dias[8] = 'TOTAL';
                unset($dias[1]);

                $atividade[7] = 'Aula';
                $atividade[13] = 'Aula';
                $atividade[19] = 'Atendimento';
                $atividade[25] = 'Dedução - 270';
                $atividade[31] = 'Reuniões';
                $atividade[37] = 'Projeto Interno';
                $atividade[43] = 'Projeto Externo';
                $atividade[49] = 'Complem. Aula';

                ksort($dias);

                foreach ($dias as $dCodigo => $dNome) {
                    $col = (!$dCodigo) ? 1 : 2;
                    if (!$dCodigo)
                        $dNome = '1' . $dNome;
                    $pdf->Cell(24, $alturaLinha, html_entity_decode($dNome), 1, 0, 'C', true);
                }
                $pdf->Ln();

                foreach ($dias as $dCodigo => $dNome) {
                    if ($dCodigo == 0)
                        $pdf->Cell(24, $alturaLinha, "ATIVIDADES", 1, 0, 'C', true);
                    elseif ($dCodigo == 8)
                        $pdf->Cell(24, $alturaLinha, html_entity_decode("PER&Iacute;ODO"), 1, 0, 'C', true);
                    else {
                        $pdf->Cell(12, $alturaLinha, "E", 1, 0, 'C', true);
                        $pdf->Cell(12, $alturaLinha, "S", 1, 0, 'C', true);
                    }
                }
                $pdf->Ln();

                for ($p = 1; $p <= 2; $p++) {
                    $pdf->Cell(24, $alturaLinha, "Aula", 1, 0, 'C', true);
                    $c = 1;
                    $l = 1;
                    for ($i = 1; $i <= 54; $i++) {
                        if ($c >= 7) {
                            $pdf->Cell(24, $alturaLinha, utf8_decode($atividade[$i]), 1, 0, 'C', true);
                            $c = 1;
                        }
                        $IE = $p . $l . $c . '1';
                        $IS = $p . $l . $c . '2';
                        $pdf->SetFont($fonte, '', $tamanho + 2);
                        $pdf->SetFillColor(255, 255, 255);
                        $pdf->Cell(12, $alturaLinha, utf8_decode($horarios[$IE]), 1, 0, 'C', true);
                        $pdf->Cell(12, $alturaLinha, utf8_decode($horarios[$IS]), 1, 0, 'C', true);
                        $pdf->SetFont($fonte, 'B', $tamanho + 3);
                        $pdf->SetFillColor(192, 192, 192);

                        if ($c == 6) { //TOTAL PERÍODO
                            $pdf->Cell(24, $alturaLinha, utf8_decode($TP[$p . 'T' . $l]), 1, 0, 'C', true);
                            $pdf->Ln();
                            $l++;
                        }
                        $c++;
                    }

                    //TOTAL PERIODO NA HORIZONTAL
                    $pdf->Cell(24, $alturaLinha, utf8_decode(""), 1, 0, 'C', true);
                    for ($c = 1; $c <= 6; $c++) {
                        $pdf->Cell(24, $alturaLinha, utf8_decode($TD[$p . 'TDP' . $c]), 1, 0, 'C', true);
                    }
                    $pdf->Cell(24, $alturaLinha, utf8_decode($TPT[$p . "TP"]), 1, 0, 'C', true); // RESULTADO TOTAL PERIODO
                    $pdf->Ln();
                    $pdf->Ln();

                    // 2 PERIODO ////
                    $pdf->SetFont($fonte, 'B', $tamanho + 4);
                    if ($p == 1) {
                        foreach ($dias as $dCodigo => $dNome) {
                            $col = (!$dCodigo) ? 1 : 2;
                            if (!$dCodigo)
                                $dNome = '2' . $dNome;
                            $pdf->Cell(24, $alturaLinha, html_entity_decode($dNome), 1, 0, 'C', true);
                        }
                        $pdf->Ln();

                        foreach ($dias as $dCodigo => $dNome) {
                            if ($dCodigo == 0)
                                $pdf->Cell(24, $alturaLinha, "ATIVIDADES", 1, 0, 'C', true);
                            elseif ($dCodigo == 8)
                                $pdf->Cell(24, $alturaLinha, html_entity_decode("PER&Iacute;ODO"), 1, 0, 'C', true);
                            else {
                                $pdf->Cell(12, $alturaLinha, "E", 1, 0, 'C', true);
                                $pdf->Cell(12, $alturaLinha, "S", 1, 0, 'C', true);
                            }
                        }
                        $pdf->Ln();
                    }
                }

                //TOTAL DIARIO
                $pdf->Cell(24, $alturaLinha, html_entity_decode("TOTAL DI&Aacute;RIO"), 1, 0, 'C', true);
                $pdf->SetFont($fonte, 'B', $tamanho + 3);
                for ($i = 1; $i <= 6; $i++) {
                    $pdf->Cell(24, $alturaLinha, utf8_decode($TDT['TD' . $i]), 1, 0, 'C', true);
                }
                $pdf->Cell(24, $alturaLinha, utf8_decode($AT), 1, 0, 'C', true);
                $pdf->Ln();
                $pdf->Ln();

                //INTERVALO
                $pdf->SetFont($fonte, 'B', $tamanho + 4);
                foreach ($dias as $dCodigo => $dNome) {
                    $col = (!$dCodigo) ? 1 : 2;
                    if (!$dCodigo)
                        $dNome = 'INTERVALO';
                    if ($dCodigo < 8) {
                        $pdf->Cell(24, $alturaLinha, html_entity_decode($dNome), 1, 0, 'C', true);
                    } elseif ($dCodigo == 8) {
                        $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);
                    }
                }

                $pdf->Ln();
                $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);
                foreach ($dias as $dCodigo => $dNome) {
                    if ($dCodigo < 7) {
                        $pdf->Cell(12, $alturaLinha, "E", 1, 0, 'C', true);
                        $pdf->Cell(12, $alturaLinha, "S", 1, 0, 'C', true);
                    } elseif ($dCodigo == 8) {
                        $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);
                    }
                }
                $pdf->Ln();

                $pdf->SetFont($fonte, 'B', $tamanho + 3);
                $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);
                for ($i = 1; $i <= 6; $i++) {
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Cell(12, $alturaLinha, utf8_decode($ITE['IE' . $i]), 1, 0, 'C', true);
                    $pdf->Cell(12, $alturaLinha, utf8_decode($ITS['IS' . $i]), 1, 0, 'C', true);
                }
                $pdf->SetFillColor(192, 192, 192);
                $pdf->Cell(24, $alturaLinha, "", 1, 0, 'C', true);

                $atividade[1] = 'Aula';
                $atividade[2] = 'Atendimento';
                $atividade[3] = 'Dedução - 270';
                $atividade[4] = 'Reunião de Área';
                $atividade[5] = 'Projeto Interno';
                $atividade[6] = 'Projeto Externo';
                $atividade[7] = 'Complemen. Aula';
                $atividade[8] = 'Dedução Intervalos';
    
                $pdf->Ln();
                $pdf->Ln();

                $pdf->Cell(40, $alturaLinha, "Atividade", 1, 0, 'C', true);
                $pdf->Cell(30, $alturaLinha, "Horas/Semana", 1, 0, 'C', true);
                $pdf->Ln();

                for ($a = 1; $a <= 8; $a++) {
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetFont($fonte, '', $tamanho + 3);
                    $pdf->Cell(40, $alturaLinha, utf8_decode($atividade[$a]), 1, 0, 'C', true);
                    $pdf->Cell(30, $alturaLinha, utf8_decode($A['A' . $a]), 1, 0, 'C', true);

                    if ($a == 1) {
                        $pdf->Cell(10, $alturaLinha, "", 0, 0, 'C', true);
                        $pdf->Cell(40, $alturaLinha, utf8_decode("Atividade Docente"), 1, 0, 'C', true);
                        $pdf->Cell(30, $alturaLinha, utf8_decode($AtvDocente), 1, 0, 'C', true);
                    }

                    if ($a == 2) {
                        $pdf->Cell(10, $alturaLinha, "", 0, 0, 'C', true);
                        $pdf->Cell(40, $alturaLinha, utf8_decode("Projetos"), 1, 0, 'C', true);
                        $pdf->Cell(30, $alturaLinha, utf8_decode($Projetos), 1, 0, 'C', true);
                    }

                    if ($a == 3) {
                        $pdf->Cell(10, $alturaLinha, "", 0, 0, 'C', true);
                        $pdf->Cell(40, $alturaLinha, utf8_decode("Intervalos"), 1, 0, 'C', true);
                        $pdf->Cell(30, $alturaLinha, utf8_decode($Intervalos), 1, 0, 'C', true);
                    }

                    if ($a == 4) {
                        $pdf->SetFont($fonte, 'B', $tamanho + 4);
                        $pdf->Cell(10, $alturaLinha, "", 0, 0, 'C', true);
                        $pdf->SetFillColor(192, 192, 192);
                        $pdf->Cell(40, $alturaLinha, utf8_decode("Total"), 1, 0, 'C', true);
                        $pdf->Cell(30, $alturaLinha, utf8_decode($Total), 1, 0, 'C', true);
                    }
                    $pdf->Ln();
                }
                $pdf->SetFont($fonte, 'B', $tamanho + 4);
                $pdf->SetFillColor(192, 192, 192);
                $pdf->Cell(40, $alturaLinha, utf8_decode("Carga horária total"), 1, 0, 'C', true);
                $pdf->Cell(30, $alturaLinha, utf8_decode($AT), 1, 0, 'C', true);

                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetFont($fonte, 'B', $tamanho + 6);
                $pdf->Cell(90, $alturaLinha, utf8_decode($SITE_CIDADE) . ', ' . html_entity_decode(formata($finalizado)), 0, 0, 'C', true);

                if ($obs) {
                    $pdf->Ln();
                    $pdf->Ln();
                    $pdf->SetFont($fonte, 'B', $tamanho + 2);
                    // Observcao a ser incluida na FTD
                    $pdf->Cell(24, $alturaLinha, utf8_decode("Observação:"), 1, 0, 'C', true);
                    $pdf->MultiCell(168, $alturaLinha, utf8_decode("$obs"), 1, 1, 'C', true);
                }

                $pdf->SetFont($fonte, 'B', $tamanho + 4);
                $pdf->Ln();
                $pdf->Ln();

                // ASSINATURAS
                $pdf->Cell(60, $alturaLinha, str_repeat('_', 28), 0, 0, 'R', true);
                $pdf->Cell(2, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(60, $alturaLinha, str_repeat('_', 28), 0, 0, 'R', true);
                $pdf->Cell(2, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(60, $alturaLinha, str_repeat('_', 28), 0, 0, 'R', true);
                $pdf->Ln();

                $pdf->Cell(60, $alturaLinha, utf8_decode("Docente"), 0, 0, 'C', true);
                $pdf->Cell(2, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(60, $alturaLinha, utf8_decode("Coordenador da área"), 0, 0, 'C', true);
                $pdf->Cell(2, $alturaLinha, "", 0, 0, 'R', true);
                $pdf->Cell(60, $alturaLinha, utf8_decode("Gerente"), 0, 0, 'C', true);
            }
        }
    } else {
        print utf8_decode("FTD está em processo de correção ou não foi finalizada pelo Docente!");
        die;
    }
}

mysql_close();

$pdf->Output();
?>