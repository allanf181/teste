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


define('FPDF_FONTPATH', PATH . LIB . '/fpdf17/font');

include PATH . LIB . '/fpdf17/rotation.php';
$pdf = new PDF ();

if (dcrip($_GET["atribuicao"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $params['atribuicao'] = $atribuicao;
    $sqlAdicional .= ' AND a.codigo = :atribuicao ';

    $rfTitle = $planoEnsino->getTipoRecuperacao($atribuicao);

    foreach ($planoEnsino->listPlanoEnsino($params, $sqlAdicional) as $reg) {
        $numeroAulaSemanal = $reg['numeroAulaSemanal'];
        $totalHoras = $reg['totalHoras'];
        $totalAulas = $reg['totalAulas'];
        $numeroProfessores = $reg['numeroProfessores'];
        $ITEM['2 - EMENTA'] = $reg['ementa'];
        $ITEM['3.1 - OBJETIVO GERAL'] = $reg['objetivoGeral'];
        $ITEM['3.2 - OBJETIVO ESPECÍFICO/COMPETÊNCIAS'] = $reg['objetivoEspecifico'];
        $ITEM['4 - CONTEÚDO PROGRAMÁTICO'] = $reg['conteudoProgramatico'];
        $ITEM['5 - METODOLOGIA'] = $reg['metodologia'];
        $ITEM['6 - RECURSOS DIDÁTICOS'] = $reg['recursoDidatico'];
        $ITEM['7 - AVALIAÇÃO'] = $reg['avaliacao'];
        $ITEM['7.1 - RECUPERAÇÃO PARALELA'] = $reg['recuperacaoParalela'];
        $ITEM[mb_strtoupper($rfTitle, 'UTF-8')] = $reg["recuperacaoFinal"];
        $ITEM['8 - BIBLIOGRAFIA BÁSICA'] = $reg['bibliografiaBasica'];
        $ITEM['8.1 - BIBLIOGRAFIA COMPLEMENTAR'] = $reg['bibliografiaComplementar'];
        $disciplina = $reg['disciplina'];
        $numero = $reg['numero'];
        $CH = $reg['ch'];
        $curso = $reg['curso'];
        $modalidade = $reg['modalidade'];

        //VERIFICA SE ESTA FINALIZADO OU VALIDADO
        if (!$reg['finalizado'] || $reg['finalizado'] == '00/00/0000 00:00')
            $pdf->setWaterText(null, null, "NAO FOI FINALIZADO");
        else if (!$reg['valido'] || $reg['valido'] == '00/00/0000 00:00')
            $pdf->setWaterText(null, null, "NAO FOI VALIDADO");
    }

    if (!$ITEM)
        die('Sem dados para gerar a lista. Verifique se o plano de ensino e o plano de aula foram preenchidos.');

    $professores = $professor->getProfessor($atribuicao, 1, '', 0, 0);

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
        $pdf->Cell(58, 27, utf8_decode("CÂMPUS \n: $SITE_CIDADE"), 1, 0, 'C', false);
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
        $pdf->Cell(278, $alturaLinha, utf8_decode("PROFESSOR(ES) RESPONSÁVEL(IS): $professores"), 1, 0, 'L', true);
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
        $pdf->SetFont($fonte, '', $tamanho+3);
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
        if (substr($chave, 0, 1) != '3' && substr($chave, 0, 1) != '7' && substr($chave, 0, 1) != '8')
            $pdf->Ln();
        if (substr($chave, 0, 3) == '3.2' || substr($chave, 0, 3) == '7.2' || substr($chave, 0, 3) == '8.1')
            $pdf->Ln();
    }

    rodape();

    cabecalho('P L A N O   D E   A U L A S');
    
    $limit = 76;
    // 2 - INSTRUMENTOS E CRITERIOS DE AVALIACAO DA APRENDIZAGEM
    $borda = 'LRT';
    $pdf->Cell(278, $alturaLinha, utf8_decode("2 - INSTRUMENTOS E CRITÉRIOS DE AVALIAÇÃO DA APRENDIZAGEM"), 'LRTB', 1, 'L');
    pdfAdd($pdf, $reg['instrumentos'], $fonte, '', $tamanho +3, $alturaLinha, 175, $borda, $secao2=true); // CONTEÚDO SECAO2
    $pdf->Ln();
    
    $pdf->SetFont($fonte, 'B', $tamanho+5);
    
    // 3 - DESENVOLVIMENTO DAS AULAS
    $borda = 'LRT';
    $pdf->Cell(278, $alturaLinha, utf8_decode("3 - DESENVOLVIMENTO DAS AULAS"), 1, 0, 'L', true);
    $pdf->Ln();
    $pdf->Cell(28, $alturaLinha, 'Semana* **', $borda, 0, 'L', true); 
    pdfAdd($pdf, "Conteúdo", $fonte, 'B', $tamanho+5, $alturaLinha, $limit, $borda); // TÍTULO
    $borda = 'LRT';
    pdfAdd($pdf, "Metodologias de Ensino e Recursos", $fonte, 'B',$tamanho+5, $alturaLinha, $limit, $borda); // TÍTULO
    $pdf->Ln();

    $res = $planoAula->listPlanoAulas($atribuicao);
    
    $dataInicio=0;
    foreach ($res as $reg) {
        $pdf->SetFont($fonte, 'B', $tamanho + 3);
                    
        // CONTEÚDO  
        
        if (count(explode("\r\n", $reg['conteudo']))>1 || (($reg['metodologia']!="" || $reg['criterio']!="")))
            $borda='LRT';
        
        if(end($res) == $reg){
            $borda = 'LRTB';
        }

        if ((strlen($reg['metodologia']) < $limit) AND (strlen($reg['conteudo']) < $limit) ){
            $borda = 'LRT';
        }
        
        $pdf->Cell(28, $alturaLinha, utf8_decode($reg['semana']), $borda, 0, 'C', true); // SEMANA
        
        
        if ($reg['metodologia']=="" && $reg['criterio']=="")
            $borda = 'LRT';
        
        if ($dataInicio==0)
            $dataInicio=$reg['dataInicio']; // DATA DA PRIMEIRA AULA
        
        $dataInicio = getProximaAula($dataInicio,$planoEnsino->getDataFim($atribuicao), $atribuicao);    // BUSCA A PROXIMA DATA DE AULA    
        
        addPlanoAula($pdf, $reg['conteudo'], $fonte, '',$tamanho+3, $alturaLinha, $limit, $borda,$reg['metodologia'], $dataInicio); // CONTEUDO
        
        $dataInicio = date("Y-m-d", strtotime("+1 day", strtotime($dataInicio)));  // ACRESCENTA UM DIA NA DATA
        
    }
    $pdf->Write(5, utf8_decode('* estas datas são uma previsão, de acordo com o horário de aula e o calendário escolar cadastrados pela secretaria.'));
    $pdf->Ln();
    $pdf->Write(5, utf8_decode('** caso não tenha sido possível alocar todas as aulas dentro do calendário escolar, datas posteriores podem ter sido utilizadas.'));
    $pdf->Ln();

    rodape();

    $pdf->Output();
}

//FUNCAO RESPONSAVEL POR COLUNAS VERTICAIS DO PLANO DE AULAS
function addPlanoAula($pdf, $conteudo, $fonte, $bold, $tamanho, $alturaLinha, $limit, $borda, $metodologia = '', $dataAula){
    $conteudo = explode("\r\n", trim($conteudo));
    $metodologia = explode("\r\n", trim($metodologia));
    $conteudoSeparado= array();
    $metodologiaSeparada= array();
    
    $pdf->SetFont($fonte, $bold, $tamanho);
    $k = 0;
    $indice = 0;
    $temp = $borda;
    
    //SEPARA A SEQUENCIA DO TEXTO DE CONTEUDO E METODOLOGIA 
    foreach ($conteudo as $j => $trecho) {
        $borda='LR';
        if (sizeof($conteudo)==$j+1)
            $borda=$temp;

        if ($k != 0)
            $pdf->Cell(28, $alturaLinha, "", $borda, 0, 'C', true);
        
        if (strlen($trecho) > $limit) {
            $conteudo2 = explode("\n", wordwrap(str_replace("\r\n", "; ", trim($trecho)), $limit));

            foreach ($conteudo2 as $n => $trecho2) {
                
                $conteudoSeparado[$indice] = $trecho2;
                $indice++;
            }
        } else {
                $conteudoSeparado[$indice] = $trecho;
                $indice++;
        }
    }
    $indice = 0;
    
    foreach ($metodologia as $j => $trecho) {
        $borda='LR';
        if (sizeof($metodologia)==$j+1)
            $borda=$temp;

        if ($k != 0)
            $pdf->Cell(28, $alturaLinha, "", $borda, 0, 'C', true);
        if (strlen($trecho) > $limit) {
            $metodologia2 = explode("\n", wordwrap(str_replace("\r\n", "; ", trim($trecho)), $limit));
            
            foreach ($metodologia2 as $n => $trecho2) {
                $metodologiaSeparada[$indice] = $trecho2;
                 $indice ++;
            }
        } else {
                $metodologiaSeparada[$indice] = $trecho;
                $indice ++;
        }
        
        $k ++;
    }
   
    //DISPOE DE MANEIRA LINEAR O CONTEUDO E METODOLOGIA NO PLANO DE AULAS
    for ($i = 0; ($i <= sizeof($conteudoSeparado)) OR ($i <= sizeof($metodologiaSeparada)); $i++){
        $borda = 'LR';
        $bordaData = 'LR';            

        if ($i == 0){
            $borda = 'LRT';
            $bordaData = 'LRT';
        }
        if ($i >= sizeof($conteudoSeparado) && $i >= sizeof($metodologiaSeparada)){
            $borda = 'LRB';
            $bordaData = 'LRB';
        }
        if ($i == 1)
            $pdf->Cell(28, $alturaLinha, date('d/m', strtotime($dataAula)), $bordaData, 0, 'C', true);
        if ($i > 1)
            $pdf->Cell(28, $alturaLinha, "", $bordaData, 0, 'C', true);
        
    
        $pdf->Cell(125, $alturaLinha, utf8_decode($conteudoSeparado[$i]), $borda, 0, 'L', true);
        $pdf->Cell(125, $alturaLinha, utf8_decode($metodologiaSeparada[$i]), $borda, 0, 'L', true);
        $pdf->Ln();        
    }
}
    


//FUNCAO ANTERIOR A NOVA IN. COLUNAS HORIZONTAIS
function pdfAdd($pdf, $conteudo, $fonte, $bold, $tamanho, $alturaLinha, $limit, $borda, $secao2 = false){
    // ADICIONA A LINHA DE CONTEÚDO NO PLANO DE AULA
    $conteudo = explode("\r\n", $conteudo);
    $pdf->SetFont($fonte, $bold, $tamanho);
    $k = 0;
    $temp = $borda;
    foreach ($conteudo as $j => $trecho) {
        $borda='LR';
        if (sizeof($conteudo)==$j+1)
            $borda=$temp;

        if ($k != 0)
            $pdf->Cell(28, $alturaLinha, "", $borda, 0, 'C', true);
        
        if (strlen($trecho) > $limit) {
            $conteudo2 = explode("\n", wordwrap(str_replace("\r\n", "; ", trim($trecho)), $limit));
            foreach ($conteudo2 as $n => $trecho2) {
                
                if ($trecho2 == end($conteudo2)){
                    $borda = 'LRB';      
                } else if ($n == 0){
                    $borda = 'LRT';
                } else {
                    $borda = 'LR';
                }
                
                if ($secao2) {
                    $pdf->Cell(278, $alturaLinha, utf8_decode($trecho2), $borda, 0, 'L', true); 
                    $pdf->Ln(); 
                }else{
                    if ($n != 0)
                        $pdf->Cell(28, $alturaLinha, "", $borda, 0, 'C', true);
                    $pdf->Cell(135, $alturaLinha, utf8_decode($trecho2), $borda, 0, 'L', true);
                }
            }
        } else {
            if ($secao2) {
                $pdf->Cell(278, $alturaLinha, utf8_decode($trecho), 'LRTB', 0, 'L', true);
                $pdf->Ln(); 
         }else{
                $pdf->Cell(125, $alturaLinha, utf8_decode($trecho), $borda, 0, 'L', true);
            }
        }
        $k ++;
    }
}

// VERIFICA QUAL A PROXIMA DATA DE AULA
function getProximaAula($data, $dataFim, $atribuicao){
    require_once CONTROLLER . "/ensalamento.class.php";
    $ensalamento = new Ensalamentos();

    require_once CONTROLLER . "/calendario.class.php";
    $calendario = new Calendarios();

    while (strtotime($data) < strtotime($dataFim)){
        if ($calendario->isDiaLetivo($data) && $ensalamento->getQdeAulaDiaSemana($atribuicao, date('w', strtotime($data))+1)>0)
            return $data;

        $data = date("Y-m-d", strtotime("+1 day", strtotime($data)));  
    }
    
    return date("Y-m-d", strtotime("+1 day", strtotime($dataFim))); // UM DIA APÓS FINAL DO PERIODO LETIVO
}


?>