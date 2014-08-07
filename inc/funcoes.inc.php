<?php
function getFrequenciaAbono($aluno, $atribuicao, $data) {
    // VERIFICANDO OS ABONOS DO ALUNO
        
        $sql = "SELECT f.tipo
                FROM FrequenciasAbonos f
                WHERE f.aluno = $aluno
                AND '" . $data . "' BETWEEN f.dataInicio AND f.dataFim
                AND
                (   (f.atribuicao = $atribuicao)
                    OR 
                    (
                        (f.aula = (SELECT h1.nome
                            FROM Ensalamentos e1, Horarios h1
                            WHERE e1.horario = h1.codigo
                            AND e1.atribuicao=$atribuicao)
                        )
                        OR 
                        ( f.aula =  (SELECT tu2.sigla
                            FROM Atribuicoes a2, Turmas t2, Turnos tu2
                            WHERE a2.turma = t2.codigo
                            AND t2.turno = tu2.codigo
                            AND a2.codigo=$atribuicao)
                        ) 
                    )
                )";
    //echo "$sql<br><br>";
    $res = mysql_query($sql);
    while ($l = mysql_fetch_array($res)) {
        if ($l[0] == 'A')
            $tipo['nome'] = 'Abono';
        if ($l[0] == 'R')
            $tipo['nome'] = 'Regime de Exerc&iacute;cios Domiciliares';
        if ($l[0] == 'M')
            $tipo['nome'] = 'Matr&iacute;cula ap&oacute;s inicio letivo';
        if ($l[0] == 'D')
            $tipo['nome'] = 'Dispensa';
        
        $tipo['sigla'] = $l[0];
        
        return $tipo;
    }
    return 0;
}
    
function getFrequencia($matricula, $atribuicao) {
     // CALCULANDO A FREQUENCIA
    $sql = "SELECT f.quantidade, 
			(SELECT SUM(au1.quantidade) 
					FROM Aulas au1 
					WHERE au1.atribuicao = au.atribuicao) as aulas,
			(SELECT IF( AulaPrevista, AulaPrevista, ch ) 
					FROM Atribuicoes at1, Disciplinas d 
					WHERE at1.disciplina = d.codigo
					AND at1.codigo = au.atribuicao) as CH,
			au.data, m.aluno
			FROM Pessoas p, Aulas au, Frequencias f, Matriculas m
			WHERE p.codigo = m.aluno
			AND f.matricula = m.codigo
			AND au.codigo = f.aula
			AND f.matricula = $matricula 
			AND au.atribuicao= $atribuicao";
    //echo $sql;
    $res = mysql_query($sql);
    $faltas = 0;
    while ($l = mysql_fetch_array($res)) {
        if (!getFrequenciaAbono($l[4], $atribuicao, $l[3]))
            $faltas += substr_count($l[0], 'F');
        $auladada = $l[1];
        $ch = $l[2];
    }


    if (!$auladada = mysql_result($res, 0, "aulas"))
        $auladada = 0;

    if ($faltas)
        $frequencia = 100 - (($faltas * 100) / $ch);
    else {
        $frequencia = 100;
        $faltas = 0;
    }

    $dados['CH'] = $ch;
    $dados['frequencia'] = round($frequencia, 1);
    $dados['faltas'] = $faltas;
    $dados['auladada'] = $auladada;

    return $dados;
}

function getProfessor($atribuicao) {
    $professor = '';
    $sql = "SELECT p.codigo, p.nome, p.lattes FROM Professores pr, Pessoas p WHERE p.codigo = pr.professor AND atribuicao =  $atribuicao";
    $result = mysql_query($sql);
    while ($l = mysql_fetch_array($result)) {
        if (strpos($l[2], 'http://') === FALSE)
            $url = "http://" . $l[2];
        else
            $url = $l[2];
        $professor[$l[0]]['nome'] = $l[1];
        $professor[$l[0]]['lattes'] = $url;
        $professor[$l[0]]['codigo'] = $l[0];
    }
    return $professor;
}

function getAtendimentoAluno($professor) {
    $ano = $_SESSION["ano"];
    $semestre = $_SESSION["semestre"];

    $sql = "SELECT horario, 
						SUBSTR( registro, 3, 1 ) AS diaSemana, 
						SUBSTR( registro, 4, 1 ) AS ES
						FROM FTDHorarios fh, FTDDados fd
						WHERE fd.codigo = fh.ftd
						AND SUBSTR( registro, 2, 1 ) = 4
						AND fd.ano = '$ano' 
						AND fd.semestre = '$semestre'
						AND fd.professor = $professor";
    //print "$sql<br>";
    $result = mysql_query($sql);
    while ($l = mysql_fetch_array($result)) {
        $atendimento[$l[1]][$l[2]] = $l[0];
    }
    return $atendimento;
}

function getTipoPessoa($pessoa) {
    $tipo = '';
    $sql = "SELECT tipo FROM PessoasTipos WHERE pessoa = $pessoa";
    $result = mysql_query($sql);
    while ($l = mysql_fetch_array($result))
        $tipo[] = $l[0];

    return $tipo;
}

function fecharDiario($atribuicao) {
    $erro = 0;
    $sql = "SELECT m.codigo, a.bimestre, m.aluno, t.codigo, d.numero
				FROM Atribuicoes a, Matriculas m, Disciplinas d, Turmas t
				WHERE a.codigo = m.atribuicao
				AND t.codigo = a.turma
				AND d.codigo = a.disciplina
				AND a.codigo = $atribuicao";
    //print $sql;
    $result = mysql_query($sql);
    while ($l = mysql_fetch_array($result)) {
        $dados = resultado($l[0], $atribuicao, 0, 1);
        if (!$dados['situacao']) {
            $sql = "SELECT * FROM NotasFinais WHERE atribuicao = $atribuicao 
    				AND matricula = $l[0]
    				AND bimestre = '" . $l[1] . "'";
            $result1 = mysql_query($sql);
            if (!$nota = mysql_fetch_object($result1)) {
                $sql = "INSERT INTO NotasFinais 
									VALUES (NULL, $atribuicao, '" . $l[0] . "', '" . $l[1] . "', 
									'" . $dados['mediaAvaliacao'] . "',
									'" . $dados['notaRecuperacao'] . "',
									'" . $dados['media'] . "',
									'" . $dados['faltas'] . "', '', '')";
                $res = mysql_query($sql);
            } else {
                $sql = "UPDATE NotasFinais SET
									mcc = '" . $dados['mediaAvaliacao'] . "',
									rec = '" . $dados['notaRecuperacao'] . "',
									ncc = '" . $dados['media'] . "', 
									falta='" . $dados['faltas'] . "'
									WHERE atribuicao = $atribuicao 
									AND matricula = $l[0] 
									AND bimestre = '" . $l[1] . "'";
                //print $sql;
                $res = mysql_query($sql);
            }
            if (!$res)
                $erro = 1;
        } else
            $erro = 2;

        // FECHAMENTO ANUAL BIMESTRAL
        if ($l[1] == 4) {
            $dados = resultadoBimestral($l[2], $l[3], $l[4], 1, 1);
            if (!$dados['situacao']) {
                $sql = "SELECT * FROM NotasFinais WHERE atribuicao = $atribuicao 
    				AND matricula = $l[0]
    				AND bimestre = 'M'";
                $result1 = mysql_query($sql);
                if (!$nota = mysql_fetch_object($result1)) {
                    $sql = "INSERT INTO NotasFinais 
										VALUES (NULL, $atribuicao, '" . $l[0] . "', 'M', 
										'" . $dados['mediaAvaliacao'] . "',
										'" . $dados['recuperacao'] . "',
										'" . $dados['media'] . "',
										'" . $dados['faltas'] . "', '', '')";
                    $res = mysql_query($sql);
                } else {
                    $sql = "UPDATE NotasFinais SET
										mcc = '" . $dados['mediaAvaliacao'] . "',
										rec = '" . $dados['recuperacao'] . "',
										ncc = '" . $dados['media'] . "', 
										falta='" . $dados['faltas'] . "'
										WHERE atribuicao = $atribuicao 
										AND matricula = $l[0] 
										AND bimestre = 'M'";
                    $res = mysql_query($sql);
                }
                if (!$res)
                    $erro = 1;
            }
        }
    }
    return $erro;
}

function notaBimestres($matricula, $bimestre, $atribuicao) {
    $sql = "SELECT n.nota FROM NotasFinais n
			WHERE n.atribuicao = $atribuicao
			AND n.matricula = $matricula
			AND n.bimestre = $bimestre";
    $result = mysql_query($sql);
    while ($l = mysql_fetch_array($result)) {
        $dados['nota'] = $l[0];
    }
    return $dados;
}

function resultadoBimestral($aluno, $turma, $numeroDisciplina, $final = 0, $fechamento = 0) {
    if (!$fechamento) {
        // VERIFICANDO SE O DIÁRIO FOI FINALIZADO, SE SIM, BUSCA NA TABELA DE NOTAS FINALIZADAS
        $sql = "SELECT ma.codigo, a.codigo,
					(SELECT habilitar FROM Situacoes WHERE codigo = ma.situacao),
					ma.situacao, d.numero,
					a.bimestre
				FROM Turmas t, Cursos c, Modalidades m, Matriculas ma, Atribuicoes a, 
						Pessoas p, Disciplinas d, NotasFinais n
				WHERE c.modalidade = m.codigo 
				AND ma.atribuicao = a.codigo
				AND a.turma = t.codigo
				AND p.codigo = ma.aluno
				AND c.codigo = d.curso
				AND a.disciplina = d.codigo
				AND n.atribuicao = a.codigo
				AND ma.aluno = $aluno
				AND t.codigo IN (SELECT t1.codigo FROM Turmas t1 
									WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
															WHERE t2.codigo = $turma))
				AND d.numero = '$numeroDisciplina'
				AND a.bimestre = 4";

        $res = mysql_query($sql);
        while ($l = mysql_fetch_array($res)) {
            $atribuicao = $l[1];
            if ($l[2]) {
                return $dados = resultado($l[0], $l[1], 1);
            }
        }
    }

    $sql = "SELECT ma.codigo, a.codigo,
				(SELECT habilitar FROM Situacoes WHERE codigo = ma.situacao),
				ma.situacao, d.numero,
				a.bimestre
			FROM Turmas t, Cursos c, Modalidades m, Matriculas ma, Atribuicoes a, Pessoas p, Disciplinas d
			WHERE c.modalidade = m.codigo 
			AND ma.atribuicao = a.codigo
			AND a.turma = t.codigo
			AND p.codigo = ma.aluno
			AND c.codigo = d.curso
			AND a.disciplina = d.codigo
			AND ma.aluno = $aluno
			AND t.codigo IN (SELECT t1.codigo FROM Turmas t1 
								WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
														WHERE t2.codigo = $turma))
			AND d.numero = '$numeroDisciplina'";
    //print $sql;
    $res = mysql_query($sql);
    $c = 0;
    while ($l = mysql_fetch_array($res)) {
        $c++;
        $atribuicao = $l[1];
        if ($l[2]) {
            $dados = resultado($l[0], $l[1], $final);
            $medias += $dados['media'];
            $faltas += $dados['faltas'];
            $frequencias += $dados['frequencia'];
            $calculo .= $dados['calculo'];
            $rec += $dados['recuperacao'];
            $final += $dados['final'];
        }
    }

    $media = $medias / $c;
    $frequencia = $frequencias / $c;

    $dados['mediaAvaliacao'] = $media;
    if ($arredondar)
        $dados['mediaAvaliacao'] = arredondar($media);

    if ($calculo) {
        if ($calculo == 'sub_media') {
            if ($media < $rec)
                $media = $rec;
        }

        if ($calculo == 'add_media') {
            $media = ( ($media + $rec) > 10 ) ? 10 : $media = $media + $rec;
        }

        if ($calculo == 'sub_menor_nota' || $calculo == 'add_menor_nota') {
            for ($i = 0; $i < count($medias); $i++) {
                if ($medias[$i] == min($medias) && !$achou) {
                    if ($calculo == 'add_menor_nota')
                        $medias_temp[] = ($rec + $medias[$i] > 10) ? 10 : $rec + $medias[$i];
                    else
                        $medias_temp[] = ($rec > $medias[$i]) ? $rec : $medias[$i];
                    $achou = 1;
                } else {
                    $medias_temp[] = $medias[$i];
                }
            }
            if ($tipo == 'media')
                $media = array_sum($medias_temp) / count($medias_temp);
            if ($tipo == 'peso' || $tipo == 'soma')
                $media = array_sum($medias_temp);
            if ($media > 10)
                $media = 10;
        }
    } else {  // ALUNO PRECISA DE REAVALIACAO/RECUPERACAO
        $sql = "SELECT t.nome, t.notaMaior,t.notaMenor,t.sigla,t.notaUltimBimestre
				FROM TiposAvaliacoes t, Modalidades m, Cursos c, Atribuicoes a, Turmas tu 
				WHERE t.modalidade = m.codigo 
				AND m.codigo = c.modalidade 
				AND a.turma = tu.codigo 
				AND tu.curso = c.codigo 
				AND a.codigo = $atribuicao
				AND t.tipo = 'recuperacao' AND final = 1";
        //echo $sql;
        $res = mysql_query($sql);
        $notaMaior = @mysql_result($res, 0, "t.notaMaior");
        $notaMenor = @mysql_result($res, 0, "t.notaMenor");
        $notaUltimBimestre = @mysql_result($res, 0, "t.notaUltimBimestre");

        if ($media >= $notaMaior && $media < $notaMenor) {
            $situacao[] = mysql_result($res, 0, "t.nome");
            $situacao[] = mysql_result($res, 0, "t.sigla");
            $dados['color'] = 'OliveDrab1';
        }
    }

    // SITUACAO DAS NOTAS
    $dados['situacao'] = $situacao[0];
    $dados['siglaSituacao'] = $situacao[1];
    $dados['codigoSituacao'] = $situacao[2];
    $dados['media'] = round($media, 2);
    $dados['frequencia'] = $frequencia;
    $dados['faltas'] = $faltas;

    // RETORNANDO OS DADOS
    return $dados;
}

function resultado($matricula, $atribuicao, $final = 0, $fechamento = 0) {
    if (!$fechamento) {
        // VERIFICANDO SE O DIÁRIO FOI FINALIZADO, SE SIM, BUSCA NA TABELA DE NOTAS FINALIZADAS
        if ($final == 0)
            $sqlFinal1 = "AND n.bimestre <> 'M' ";
        if ($final == 1)
            $sqlFinal1 = "AND n.bimestre = 'M' ";
        $sql = "SELECT  n.mcc,n.rec,n.ncc,n.falta,
                                (SELECT t.final FROM Avaliacoes a, TiposAvaliacoes t
                                    WHERE a.atribuicao = at.codigo
                                    AND a.tipo = t.codigo) as final,
				(SELECT SUM(au1.quantidade) FROM Aulas au1 WHERE au1.atribuicao = at.codigo) as aulas,
				(SELECT ch FROM Atribuicoes at1, Disciplinas d WHERE at1.disciplina = d.codigo AND at1.codigo = at.codigo) as CH
				FROM NotasFinais n, Atribuicoes at
				WHERE n.atribuicao = at.codigo
				AND at.codigo = $atribuicao
				AND n.matricula = $matricula
				AND at.status <> 0
				$sqlFinal1
				GROUP BY n.bimestre";
        //print "$sql<br>";
        $result = mysql_query($sql);
        if ($nota = mysql_fetch_object($result)) {
            $dados['mediaAvaliacao'] = round($nota->mcc, 2);
            $dados['notaRecuperacao'] = round($nota->rec, 2);
            $dados['recuperacao'] = round($nota->rec, 2);
            $dados['media'] = round($nota->ncc, 2);
            $dados['final'] = $nota->final;

            // CALCULANDO A FREQUENCIA
            $dados += getFrequencia($matricula, $atribuicao);

            return $dados;
        }
    }

    if ($final == 0)
        $sqlFinal = "AND t.final=0";
    // CALCULANDO AS NOTAS
    $sql = "SELECT n.nota,a.peso,t.tipo,t.calculo,at.calculo,
			t.arredondar,at.bimestre,t.final,a.sigla,at.formula
			FROM Notas n, Avaliacoes a, Atribuicoes at, TiposAvaliacoes t
			WHERE n.avaliacao = a.codigo
			AND a.atribuicao = at.codigo
			AND t.codigo = a.tipo
			AND at.codigo = $atribuicao
			AND n.matricula = $matricula
			$sqlFinal";
    //echo $sql.";<br>";
    $res = mysql_query($sql);

    if (!$res)
        return null;
    $media = 0;
    $total = 0;
    $final = 0;
    while ($l = mysql_fetch_array($res)) {
        $bimestre = $l[6];
        $tipo = $l[4];
        $arredondar = $l[5];
        $formula = $l[9];
        if ($l[2] == 'avaliacao') {
            $total++;
            if ($tipo == 'peso')
                $medias[] = $l[0] * $l[1];

            if ($tipo == 'media' || $tipo == 'soma')
                $medias[] = $l[0];

            if ($tipo == 'formula')
                $medias[$l[8]] = $l[0];
        }
        if ($l[2] == 'recuperacao' && !$l[7]) {
            $rec = $l[0];
            $calculo = $l[3];
        }
        if ($l[2] == 'recuperacao' && $l[7]) {
            $recFinal = $l[0];
            $calculoFinal = $l[3];
            $final = 1;
        }
    }

    if ($tipo == 'media' && $medias)
        $media = array_sum($medias) / $total;
    if ($tipo == 'peso')
        $media = array_sum($medias);
    if ($tipo == 'soma') {
        $media = array_sum($medias);
        if ($media > 10)
            $media = 10;
    }

    if ($tipo == 'formula') {
        require_once 'lib/PHPMathParser/Math.php';
        $math = new Math();

        foreach ($medias as $VAR => $VAL) {
            if ($VAL)
                $math->registerVariable($VAR, $VAL);
        }
        $media = $math->evaluate($formula);
    }

    if ($arredondar) {
        $media = arredondar($media);

        // PARA O DIARIO DO PROFESSOR
        $dados['mediaAvaliacao'] = arredondar($media);
        $dados['notaRecuperacao'] = arredondar($rec);
    } else {
        // PARA O DIARIO DO PROFESSOR
        $dados['mediaAvaliacao'] = $media;
        $dados['notaRecuperacao'] = $rec;
    }

    // CALCULANDO A FREQUENCIA
    $dados += getFrequencia($matricula, $atribuicao);

    if ($calculo) {
        if ($calculo == 'sub_media') {
            if ($media < $rec)
                $media = $rec;
        }

        if ($calculo == 'add_media') {
            $media = ( ($media + $rec) > 10 ) ? 10 : $media = $media + $rec;
        }

        if ($calculo == 'sub_menor_nota' || $calculo == 'add_menor_nota') {
            for ($i = 0; $i < count($medias); $i++) {
                if ($medias[$i] == min($medias) && !$achou) {
                    if ($calculo == 'add_menor_nota')
                        $medias_temp[] = ($rec + $medias[$i] > 10) ? 10 : $rec + $medias[$i];
                    else
                        $medias_temp[] = ($rec > $medias[$i]) ? $rec : $medias[$i];
                    $achou = 1;
                } else {
                    $medias_temp[] = $medias[$i];
                }
            }
            if ($tipo == 'media')
                $media = array_sum($medias_temp) / count($medias_temp);
            if ($tipo == 'peso' || $tipo == 'soma')
                $media = array_sum($medias_temp);
            if ($media > 10)
                $media = 10;
        }

        // PARA FECHAMENTO DO BIMESTRE SO INTERESSA ATE AQUI
        if ($bimestre == 4 && $final) {
            $dados['media'] = round($media, 2);
            $dados['frequencia'] = $frequencia;
            $dados['faltas'] = $faltas;
            $dados['auladada'] = $auladada;
            $dados['recuperacao'] = $recFinal;
            $dados['calculo'] = $calculoFinal;
            $dados['final'] = $final;
            return $dados;
        }
    } else {  // ALUNO PRECISA DE REAVALIACAO/RECUPERACAO
        $sql = "SELECT t.nome, t.notaMaior,t.notaMenor,t.sigla
				FROM TiposAvaliacoes t, Modalidades m, Cursos c, Atribuicoes a, Turmas tu 
				WHERE t.modalidade = m.codigo 
				AND m.codigo = c.modalidade 
				AND a.turma = tu.codigo 
				AND tu.curso = c.codigo 
				AND a.codigo = $atribuicao
				AND t.tipo = 'recuperacao'";

        $res = mysql_query($sql);
        $notaMaior = mysql_result($res, 0, "t.notaMaior");
        $notaMenor = mysql_result($res, 0, "t.notaMenor");

        if ($media >= $notaMaior && $media < $notaMenor) {
            $situacao[] = mysql_result($res, 0, "t.nome");
            $situacao[] = mysql_result($res, 0, "t.sigla");
            $dados['color'] = 'OliveDrab1';
        }
    }

    // SITUACAO DAS NOTAS
    $dados['situacao'] = $situacao[0];
    $dados['siglaSituacao'] = $situacao[1];
    $dados['codigoSituacao'] = $situacao[2];
    $dados['media'] = round($media, 2);

    // RETORNANDO OS DADOS
    return $dados;
}

function resultadoModulo($aluno, $turma) {
    $sql = "SELECT ma.codigo, a.codigo,
			ma.situacao, c.fechamento, d.numero
			FROM Turmas t, Cursos c, Modalidades m, Matriculas ma, Atribuicoes a, Pessoas p, Disciplinas d
			WHERE c.modalidade = m.codigo 
			AND ma.atribuicao = a.codigo
			AND a.turma = t.codigo
			AND p.codigo = ma.aluno
			AND c.codigo = d.curso
			AND a.disciplina = d.codigo
			AND ma.aluno = $aluno
			AND t.codigo = $turma";
    //echo $sql."<br>";
    $res = mysql_query($sql);
    if ($res)
        while ($l = mysql_fetch_array($res)) {
            if ($l[2]) {

                if ($l[3] == 's')
                    $dados = resultado($l[0], $l[1]);
                if ($l[3] == 'b')
                    $dados = resultadoBimestral($aluno, $turma, $l[4]);

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

function nDias($date) {
    return dateDiff(date('Y-m-d'), $date);
}

function dateDiff($startDate, $endDate) {
    $date1 = date_create($startDate);
    $date2 = date_create($endDate);
    $diff = date_diff($date1, $date2);
    return $diff->format("%a");
}

function hourDiff($startDate, $endDate) {
    $date1 = date_create($startDate);
    $date2 = date_create($endDate);
    $diff = date_diff($date1, $date2);
    return $diff->format("%a") * 24 + $diff->format("%h");
}

function nRange($n) {
    return date('d/m/Y', strtotime('-' . $n . ' day'));
}

function percentual($parte, $todo) {
    return @round((($parte / $todo) * 100), 2) . "%";
}

function arredondar($valor) {
    if ($valor - floor($valor) > 0 && $valor - floor($valor) <= .24)
        return floor($valor);
    else if ($valor - floor($valor) >= .25 && $valor - floor($valor) < .5)
        return floor($valor) + .5;
    else if ($valor - floor($valor) > .5 && $valor - floor($valor) <= .74)
        return floor($valor) + .5;
    else if ($valor - floor($valor) >= .75)
        return ceil($valor);
    else
        return ($valor);
}

function formata($strDate, $PDF = null) {
    // Array com os meses do ano em portuguÃƒÂªs;
    $arrMonthsOfYear = array(1 => 'Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
    if ($PDF)
        $arrMonthsOfYear = array(1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
    // Descobre o dia da semana
    $intDayOfWeek = date('w', strtotime($strDate));
    // Descobre o dia do mÃƒÂªs
    $intDayOfMonth = date('d', strtotime($strDate));
    // Descobre o mÃƒÂªs
    $intMonthOfYear = date('n', strtotime($strDate));
    // Descobre o ano
    $intYear = date('Y', strtotime($strDate));
    // Formato a ser retornado
    return $intDayOfMonth . ' de ' . $arrMonthsOfYear[$intMonthOfYear] . ' de ' . $intYear;
}

function maiusculo($texto) {
    return mb_convert_case($texto, MB_CASE_UPPER, 'UTF-8');
}

function mostraTexto($string) {
    return $string;
}

function formatarTexto($texto) {
    $string = mb_convert_case(html_entity_decode($texto, ENT_QUOTES, "UTF-8"), MB_CASE_LOWER);

    $string = preg_replace('/\s+/', ' ', $string);

    $lower_exceptions = array(
        "da" => "1", "de" => "1", "di" => "1", "do" => "1",
        "das" => "1", "des" => "1", "dis" => "1", "dos" => "1",
        "a" => "1", "e" => "1", "i" => "1", "o" => "1", "u" => "1",
        "as" => "1", "es" => "1", "is" => "1", "os" => "1", "us" => "1",
        "em" => "1", "no" => "1", "nas" => "1", "nos" => "1", "nas" => "1",
        "à" => "1", "às" => "1", "para" => "1", "por" => "1"
    );

    $higher_exceptions = array(
        "i" => "1", "ii" => "1", "iii" => "1", "iv" => "1",
        "v" => "1", "vi" => "1", "vii" => "1", "viii" => "1",
        "ix" => "1", "x" => "1"
    );

    $words = @split(" ", $string);
    $newwords = array();
    foreach ($words as $word) {
        if (@$higher_exceptions[$word])
            $word = @mb_convert_case($word, MB_CASE_UPPER);
        if (!@$lower_exceptions[$word])
            $word[0] = @mb_convert_case($word[0], MB_CASE_UPPER);
        array_push($newwords, $word);
    }
    return @join(" ", $newwords);
}

function minusculo($texto) {
    return mb_convert_case($texto, MB_CASE_LOWER);
}

function mysql_matched_rows() {
    $_kaBoom = explode(' ', mysql_info());
    return $_kaBoom[2];
}

function abreviar($texto, $tamanho) {
    if (strlen($texto) > $tamanho)
        return substr_replace($texto, '...', $tamanho);
    else
        return $texto;
}

function crip($texto) {
    return base64_encode($_SESSION["cripto"] . $texto);
}

function dcrip($texto) {
    $texto = base64_decode($texto);
    $r = explode($_SESSION["cripto"], $texto);
    if (sizeof($r) > 1)
        return $r[1];
    else
        return $r[0]; // nÃƒÂ£o criptografado
}

function genRandomString() {
    $length = 3;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = null;

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}

//formata a data EN
function dataMysql($data) {
    $data = explode('/', $data);
    $data = $data[2] . '-' . $data[1] . '-' . $data[0];
    return $data;
}

function diasDaSemana() {
    $dias[1] = 'Domingo';
    $dias[2] = 'Segunda';
    $dias[3] = 'Ter&ccedil;a';
    $dias[4] = 'Quarta';
    $dias[5] = 'Quinta';
    $dias[6] = 'Sexta';
    $dias[7] = 'S&aacute;bado';

    return $dias;
}

function meses($a) {
    switch ($a) {
        case 1: $mes = "janeiro";
            break;
        case 2: $mes = "fevereiro";
            break;
        case 3: $mes = "mar&ccedil;o";
            break;
        case 4: $mes = "abril";
            break;
        case 5: $mes = "maio";
            break;
        case 6: $mes = "junho";
            break;
        case 7: $mes = "julho";
            break;
        case 8: $mes = "agosto";
            break;
        case 9: $mes = "setembro";
            break;
        case 10: $mes = "outubro";
            break;
        case 11: $mes = "novembro";
            break;
        case 12: $mes = "dezembro";
            break;
    }
    return $mes;
}

// VERIFICANDO O DIRETORIO TEMPORARIO
if (!function_exists('sys_get_temp_dir')) {

    function sys_get_temp_dir() {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        if (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }
        $tempfile = tempnam(__FILE__, '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
        }
        return null;
    }

}

// Função para listagem recursiva de diretórios
// Entrada: diretório (string)
// Saída: Lista de Diretórios e Arquivos (Array)
// Arquivos que utilizam essa função: view/admin/permissao.php
// Autor: Naylor - 17/07
function dirToArray($dir) {
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        $arquivo = str_replace(PATH . LOCATION . '/', '', $name);

        if (!preg_match('/\/..$|\/.$|.svn|\/js\/|\/css\/|\/inc\/|index.html/', $name)) {
            if (is_dir($name)) {
                $files[$arquivo] = '';
            } else {
                $base = dirname($arquivo);
                $files[$base][] = $arquivo;
            }
        }
    }
    return $files;
}

// Função decriptação de variaveis em um Array.
// Entrada: Array
// Saída: Array
// Autor: Naylor - 29/07
function dcripArray($array) {
    foreach ($array as $key => $value) {
        if (base64_encode(base64_decode($value)) === $value) {
            if ($value)
                $new_array[$key] = dcrip($value);
        } else {
            if ($value)
                $new_array[$key] = $value;
            else
                $new_array[$key] = '';
        }
    }
    return $new_array;
}

// Função que converte um Array em Retorno de URL
// Entrada: Array
// Saída: String concatenada
// Autor: Naylor - 29/07
function mapURL($array) {
    foreach ($array as $key => $value) {
        $ret[] = "$key=$value";
    }
    return implode('&', $ret);
}

// Função que atualiza o BD-RUCKUS
function updateDataBase() {
    try {
        $argv[2] = 1;
        require 'lib/migration/ruckusWeb.php';
        $argv[0] = 'db:migrate';

        $main = new Ruckusing_FrameworkRunner($db_config, $argv);
        $ret = $main->execute();
        $argv = null;

        if (strpos($ret, 'relevant') !== false)
            return false;
        else
            return true;
    } catch (Exception $e) {
        print $e;
        return false;
    }
}

?>