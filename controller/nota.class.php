<?php

if (!class_exists('Frequencias'))
    require CONTROLLER . "/frequencia.class.php";

class Notas extends Frequencias {

    //Funcao para Inserir Notas
    public function putNotas($params) {
        $c = 0;
        foreach ($params['matricula'] as $matricula => $nota) {
            $new_params['codigo'] = $params['codigo'][$matricula];
            $new_params['avaliacao'] = $params['avaliacao'];
            $new_params['matricula'] = $matricula;
            if ($nota == '0')
                $nota = '0.0'; //PDO NAO ACEITA ZERO PARA STRING
            $new_params['nota'] = $nota;

            $res = $this->insertOrUpdate($new_params);
            if ($res)
                $c++;
        }
        $rs['TIPO'] = 'UPDATE';
        $rs['RESULTADO'] = $c;
        $rs['STATUS'] = 'OK';
        return $rs;
    }

    // Funcao utilizada para gerar resultado dos 4 Bimestres
    public function resultadoBimestral($aluno, $turma, $numeroDisciplina, $final = 0, $fechamento = 0) {
        $bd = new database();

        // PEGANDO AS MATRICULAS E ATRIBUICOES DOS 4 BIMESTRES
        $sql = "SELECT ma.codigo as matricula, a.codigo as atribuicao,
		(SELECT habilitar FROM Situacoes s, MatriculasAlteracoes m1 
                    WHERE m1.matricula = ma.codigo 
                    AND s.codigo = m1.situacao 
                    ORDER BY m1.data DESC LIMIT 1) as habilitado,
                d.numero as numero,
		a.bimestre as bimestre
		FROM Turmas t, Cursos c, Modalidades m, Matriculas ma, 
                    Atribuicoes a, Pessoas p, Disciplinas d
		WHERE c.modalidade = m.codigo 
		AND ma.atribuicao = a.codigo
		AND a.turma = t.codigo
		AND p.codigo = ma.aluno
		AND c.codigo = d.curso
		AND a.disciplina = d.codigo
		AND ma.aluno = :aluno
		AND t.codigo IN (SELECT t1.codigo FROM Turmas t1 
			WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
			WHERE t2.codigo = :turma and t2.ano=t.ano))
		AND d.numero = :numDisc";

        $params = array(':aluno' => $aluno,
            ':turma' => $turma,
            ':numDisc' => $numeroDisciplina);
        $res = $bd->selectDB($sql, $params);

        $c = 0;
        foreach ($res as $reg) {
            $c++;
            $atribuicao = $reg['atribuicao'];
            if ($reg['habilitado']) {
                //BUSCANDO AS MEDIAS DOS BIMESTRES DE CADA ALUNO
                $dados = $this->resultado($reg['matricula'], $reg['atribuicao'], $final, $fechamento);
                $medias += $dados['media'];
                $faltas += $dados['faltas'];
                $frequencias += $dados['frequencia'];
                $calculo .= $dados['calculo'];
                $rec += $dados['recuperacao'];
                $final += $dados['final'];

                if ($reg['bimestre'] == 4)
                    $notaUltimoBimestre = $dados['media'];
            }
        }

        // MEDIA DAS MEDIAS DOS BIMESTRES
        $media = $medias / $c;

        // MEDIA DAS FREQUENCIAS DO BIMESTRE
        $frequencia = $frequencias / $c;

        if ($calculo) { // SE TEM RECUPERACAO
            $media = $this->calcMedia($calculo, $media, $medias, $rec);
        } else {  // ALUNO PRECISA DE REAVALIACAO FINAL
            $dadosRec = $this->checkIfRec($atribuicao, $media, $final, $notaUltimoBimestre);
            if ($dadosRec)
                $dados = array_merge($dados, $dadosRec);
        }

        // SITUACAO DAS NOTAS
        $dados['media'] = arredondar($media);
        $dados['frequencia'] = $frequencia;
        $dados['faltas'] = $faltas;
        
        // VERIFICANDO SE HÁ REAVALIAÇÃO
        if (!empty($dados['notaReavaliacao']) && $dados['notaReavaliacao']>$dados['media']){
            $dados['media'] = $dados['notaReavaliacao'];
            $dados['origemMedia'] = 'reavaliacao';
        }
//debug($dados);
        // RETORNANDO OS DADOS
        return $dados;
    }

    function resultado($matricula, $atribuicao, $final = 0, $fechamento = 0) {
        $bd = new database();

        if ($fechamento) {
            // VERIFICANDO SE O DIÁRIO FOI FINALIZADO, SE SIM, BUSCA NA TABELA DE NOTAS FINALIZADAS
            if ($final == 0)
                $sqlFinal1 = "AND n.bimestre <> 'M' ";
            if ($final == 1)
                $sqlFinal1 = "AND n.bimestre = 'M' ";

            $sql = "SELECT  n.mcc,n.rec,n.ncc,n.falta,
                        (SELECT t.final FROM Avaliacoes a, TiposAvaliacoes t
                            WHERE a.atribuicao = at.codigo
                            AND a.tipo = t.codigo AND t.final = 1) as final,
        		(SELECT SUM(au1.quantidade) FROM Aulas au1 
                            WHERE au1.atribuicao = at.codigo) as aulas,
			(SELECT ch FROM Atribuicoes at1, Disciplinas d 
                            WHERE at1.disciplina = d.codigo 
                            AND at1.codigo = at.codigo) as CH
                    FROM NotasFinais n, Atribuicoes at
                    WHERE n.atribuicao = at.codigo
                    AND at.codigo = :att
                    AND n.matricula = :matricula
                    AND at.status <> 0
                    $sqlFinal1
                    GROUP BY n.bimestre";
            $params = array(':att' => $atribuicao,
                ':matricula' => $matricula);
            $res = $bd->selectDB($sql, $params);

            foreach ($res as $reg) {
                // CALCULANDO A FREQUENCIA
                $dados = $this->getFrequencia($matricula, $atribuicao);

                // PARA O DIARIO E NOTAFINAL
                $dados['mediaAvaliacao'] = round($reg['mcc'], 2);
                $dados['notaRecuperacao'] = round($reg['rec'], 2);

                $dados['recuperacao'] = round($reg['rec'], 2);
                $dados['media'] = round($reg['ncc'], 2);
                $dados['faltas'] = round($reg['falta'], 2);
                $dados['final'] = $reg['final'];
                return $dados;
            }
        }

        if ($final == 0)
            $sqlFinal = " AND t.final=0 ";
        // CALCULANDO AS NOTAS
        $sql = "SELECT n.nota as nota,a.peso as peso,t.tipo as tipo,
                        t.calculo as calculo, at.calculo as calculoAtt,
			t.arredondar as arredondar, at.bimestre as bimestre,
                        t.final as final, a.sigla as sigla, at.formula as formula,
                        (SELECT a1.sigla FROM Avaliacoes a1 WHERE a1.codigo = a.substitutiva) as sub, t.sigla as siglaTipo, nf.ncc ncc, nf.mcc mcc
			FROM Notas n, Avaliacoes a, TiposAvaliacoes t, Atribuicoes at
                        LEFT JOIN NotasFinais nf ON nf.atribuicao=at.codigo AND nf.matricula=:matricula
			WHERE n.avaliacao = a.codigo
			AND a.atribuicao = at.codigo
			AND t.codigo = a.tipo
			AND at.codigo = :att
			AND n.matricula = :matricula
			$sqlFinal
                        ORDER BY substitutiva ASC ";

        $params = array(':att' => $atribuicao,
            ':matricula' => $matricula);
        $res = $bd->selectDB($sql, $params);
//debug($res);

        $media = 0;
        $total = 0;
        $final = 0;

        foreach ($res as $reg) {
            $ncc = $reg['ncc'];
            $bimestre = $reg['bimestre'];
            $tipo = $reg['calculoAtt'];
            $arredondar = $reg['arredondar'];
            $formula = $reg['formula'];
            if ($reg['tipo'] == 'avaliacao') {
                if ($tipo == 'peso')
                    $medias[$reg['sigla']] = $reg['nota'] * $reg['peso'];

                if ($tipo == 'media' || $tipo == 'soma') {
                    $medias[$reg['sigla']] = $reg['nota'];
                    if ($reg['nota'])
                        $total++;
                }

                if ($tipo == 'formula')
                    $medias[$reg['sigla']] = $reg['nota'];
            }

            if ($reg['tipo'] == 'pontoExtra') {
                $pontoExtra[] = $reg['nota'];
            }

            if ($reg['tipo'] == 'substitutiva') {
                if ($tipo == 'peso') {
                    if ($medias[$reg['sub']] < ($reg['nota'] * $reg['peso']))
                        $medias[$reg['sub']] = $reg['nota'] * $reg['peso'];
                } else {
                    if ($medias[$reg['sub']] < $reg['nota'])
                        $medias[$reg['sub']] = $reg['nota'];
                }
            }

            if ($reg['tipo'] == 'recuperacao' && $reg['siglaTipo']!='REF' && !$reg['final']) {
                $rec = $reg['nota'];
                $calculo = $reg['calculo'];
            }
            if ($reg['tipo'] == 'recuperacao' && $reg['siglaTipo']!='REF' && $reg['final']) {
                $rec = $reg['nota'];
                $calculo = $reg['calculo'];
                $final = 1;
            }
            if ($bimestre==4 && $reg['tipo'] == 'recuperacao' && $reg['siglaTipo']=='REF'){
                $notaReavaliacao=$reg['nota'];
            }
            else if ($bimestre==0 && $reg['tipo'] == 'recuperacao' && $reg['siglaTipo']=='REF'){
                $rec=$reg['nota'];
            }
        }

        if ($tipo == 'media' && $medias)
            $media = array_sum($medias) / $total;
        if ($tipo == 'peso')
            $media = array_sum($medias);
        if ($tipo == 'soma') {
            $media = array_sum($medias);
        }

        if ($tipo == 'formula') {
            $media = $this->doFormula($formula, $medias);
        }

        // ADICIONANDO OS PONTOS EXTRAS
        $media += array_sum($pontoExtra);

        if ($arredondar)
            $media = arredondar($media);

        // CALCULANDO A FREQUENCIA
        $dados = $this->getFrequencia($matricula, $atribuicao);

        // GARANTINDO A MEDIA MENOR QUE 10
        if ($media > 10)
            $media = 10;
//debugSQL($sql, $params);
        // ARMAZENANDO A MEDIA DAS AVALIACOES PARA O DIARIO
        $dados['mediaAvaliacao'] = arredondar($media);
        $dados['notaRecuperacao'] = arredondar($rec);
        $dados['notaReavaliacao'] = $notaReavaliacao;
        if ($reg['ncc']!=$reg['mcc'])
            $dados['notaArredondada'] = $reg['ncc'];

        if ($calculo) { // SE TEM RECUPERACAO
            $media = $this->calcMedia($calculo, $media, $medias, $rec, $tipo, $formula);

            // PARA FECHAMENTO DO BIMESTRE SO INTERESSA ATE AQUI
            if ($bimestre == 4 && $final) {
                $dados['media'] = $media;
                $dados['recuperacao'] = $rec;
                $dados['calculo'] = $calculo;
                $dados['final'] = $final;
                return $dados;
            }
        } else {  // ALUNO PRECISA DE RECUPERACAO
            $dadosRec = $this->checkIfRec($atribuicao, $media);
            if ($dadosRec)
                $dados = array_merge($dadosRec, $dados);
        }

        // REGISTRANDO A MEDIA
        $dados['media'] = $media;
        
        // VERIFICA SE O A MEDIA JA FOI EXPORTADA
        if (isset($ncc))
            $dados['media'] = $ncc;
        
//        // VERIFICANDO SE HÁ ARREDONDAMENTO
//        if (!empty($dados['notaArredondada']) && $dados['notaArredondada']>=0){
//            $dados['media'] = $dados['notaArredondada'];
//            $dados['origemMedia'] = 'arredondamento';
//        }
//
//        // VERIFICANDO SE HÁ RECUPERACAO
//        if (!empty($dados['notaRecuperacao']) && $dados['notaRecuperacao']>$dados['media']){
//            $dados['media'] = $dados['notaRecuperacao'];
//            $dados['origemMedia'] = 'recuperacao';
//        }

        // VERIFICANDO SE HÁ REAVALIAÇÃO
//        if (!empty($dados['notaReavaliacao']) && $dados['notaReavaliacao']>$dados['media']){
//            $dados['media'] = $dados['notaReavaliacao'];
//            $dados['origemMedia'] = 'reavaliacao';
//        }
$dados['atribuicao']=$atribuicao;
        // RETORNANDO OS DADOS
        return $dados;
    }

    function resultadoModulo($aluno, $turma) {
        $bd = new database();

        $sql = "SELECT ma.codigo as matricula, a.codigo as atribuicao,
			c.fechamento as fechamento,
                        d.numero as numero,
                        (SELECT habilitar FROM Situacoes s, MatriculasAlteracoes m1 
                            WHERE m1.matricula = ma.codigo 
                            AND s.codigo = m1.situacao 
                            ORDER BY m1.data DESC LIMIT 1) as situacao
			FROM Turmas t, Cursos c, Modalidades m, Matriculas ma, 
                            Atribuicoes a, Pessoas p, Disciplinas d
			WHERE c.modalidade = m.codigo 
			AND ma.atribuicao = a.codigo
			AND a.turma = t.codigo
			AND p.codigo = ma.aluno
			AND c.codigo = d.curso
			AND a.disciplina = d.codigo
			AND ma.aluno = :aluno
			AND t.codigo = :turma";
        $params = array(':aluno' => $aluno,
            ':turma' => $turma);
        $res = $bd->selectDB($sql, $params);

        foreach ($res as $reg) {
            if ($reg['situacao']) {
                if ($reg['fechamento'] == 's' || $reg['fechamento'] == 'a')
                    $dados = $this->resultado($reg['matricula'], $reg['atribuicao']);
                if ($reg['fechamento'] == 'b')
                    $dados = $this->resultadoBimestral($aluno, $turma, $reg['numero'], 0, 1);

                $medias[] = $dados['media'];
                $frequencias[] = $dados['frequencia'];
            }
        }

        $frequencia = array_sum($frequencias) / count($frequencias);
        $dadosGlobais['frequenciaGlobal'] = $frequencia;

        $media = array_sum($medias) / count($medias);
        $dadosGlobais['mediaGlobal'] = round($media, 2);
        return $dadosGlobais;
    }

    private function checkIfRec($atribuicao, $media, $final = null, $notaUltimoBimestre = null) {
        $bd = new database();
        if ($final)
            $final = 'AND final = 1';
        else
            $final = null;

        $sql = "SELECT t.nome, t.notaMaior,t.notaMenor,t.sigla,
                        t.notaUltimBimestre
			FROM TiposAvaliacoes t, Modalidades m, 
                            Cursos c, Atribuicoes a, Turmas tu 
			WHERE t.modalidade = m.codigo 
			AND m.codigo = c.modalidade 
			AND a.turma = tu.codigo 
			AND tu.curso = c.codigo 
			AND a.codigo = :att
			AND t.tipo = 'recuperacao' 
                        $final";
        $params = array(':att' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        $notaMaior = $res[0]['notaMaior'];
        $notaMenor = $res[0]['notaMenor'];

        if (($media >= $notaMaior && $media < $notaMenor) ||
                ($final && $notaUltimoBimestre < $res[0]['notaUltimBimestre'])
        ) {
            $dados['situacao'] = $res[0]['nome'];
            $dados['siglaSituacao'] = $res[0]['sigla'];
            $dados['color'] = 'OliveDrab1';
        }
        return $dados;
    }

    private function calcMedia($calculo, $media, $medias, $rec, $tipo = null, $formula = null) {
        $bd = new database();

        if ($calculo == 'sub_media') {
            if ($media < $rec)
                $media = $rec;
        }

        if ($calculo == 'add_media') {
            $media = ( ($media + $rec) > 10 ) ? 10 : $media = $media + $rec;
        }

        if ($calculo == 'sub_menor_nota') {
            $key = array_search($this->minValue($medias), $medias);
            $medias[$key] = $rec;
        }

        if ($calculo == 'add_menor_nota') {
            $key = array_search($this->minValue($medias), $medias);
            $medias[$key] = $medias[$key] + $rec;
        }

        if ($calculo == 'sub_menor_nota' || $calculo == 'add_menor_nota') {
            if ($tipo == 'media')
                $media = array_sum($medias) / count($medias);
            if ($tipo == 'peso' || $tipo == 'soma')
                $media = array_sum($medias);
            if ($tipo == 'formula')
                $media = $this->doFormula($formula, $medias);

            if ($media > 10)
                $media = 10;
        }

        return $media;
    }

    private function minValue($array) {
        if (!count($array))
            return false;
        else {
            $min = false;
            foreach ($array AS $value) {
                if (is_numeric($value)) {
                    $curval = floatval($value);
                    if ($curval < $min || $min === false)
                        $min = $curval;
                }
            }
        }

        return $min;
    }

    private function doFormula($formula, $medias) {
        try {
            require_once PATH . LIB . '/PHPMathParser/Math.php';
            $math = new Math();

            foreach ($medias as $VAR => $VAL) {
                if ($VAL)
                    $math->registerVariable($VAR, $VAL);
            }
            $media = $math->evaluate($formula);
        } catch (Exception $e) {
            $callers = debug_backtrace();
            //print_r($callers);

            foreach ($callers as $key => $value) {
                if (strpos($value['file'], 'boletim') !== false) {
                    $is_boletim = 1;
                }
            }

            if (!$is_boletim) {
                print "<div class=\"flash error\" id=\"flash_error\">"
                        . "Existe um erro na f&oacute;rmula: $formula"
                        . "<br>Erro encontrado: " . $e->getMessage() . ""
                        . "<br />Verifique se faltou algum $ em alguma vari&aacute;vel ou algum sinal ou ponto estranho."
                        . "<br />Verifique ainda se todos os par&ecirc;nteses foram devidamente fechados.</div><br />";
                //die;
            }
        }
        return $media;
    }

    
}

?>