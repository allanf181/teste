<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

if (!class_exists('Notas'))
    require CONTROLLER . "/notaFinal.class.php";

class Atribuicoes extends Generic {

    // USADO POR: PROFESSOR/AVALIACAO.PHP
    // Retorna dados da atribuicao (Disciplina, Turma, etc..)
    // Pode ser colocado com função no MySQL futuramente
    public function insertIfNotCalculo($codigo) {
        $bd = new database();

        $sql = "SELECT calculo FROM Atribuicoes a WHERE a.codigo = :cod";
        $params = array(':cod' => $codigo);

        $res = $bd->selectDB($sql, $params);
        if ($res[0]['calculo'] == null) {
            $params = array('codigo' => crip($codigo), 'calculo' => crip('peso'));
            $res = $this->insertOrUpdate($params);
            return true;
        } else {
            return false;
        }
    }

    // USADO POR: ALUNO/ALUNO.PHP, PROFESSOR/PROFESSOR.PHP, PROFESSOR/PLANO.PHP
    // BOLETIM.PHP
    // Retorna dados da atribuicao (Disciplina, Turma, etc..)
    // Pode ser colocado com função no MySQL futuramente
    public function getAtribuicao($codigo, $LIMITE_DIARIO_PROF = 0) {
        $bd = new database();

        $sql = "SELECT d.nome as disciplina, t.numero as turma, a.status, a.prazo,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                a.bimestre as bimestre, c.fechamento as fechamento,
        	t.semestre as semestre, t.ano as ano, t.codigo as turmaCodigo, a.subturma as subturma,
                c.fechamento as fechamento, a.observacoes as observacoes, a.competencias as competencias,
                m.codigo as codModalidade, m.nome as modalidade, d.ch as CH, a.aulaPrevista as aulaPrevista,
                IF(a.prazo, date_format(a.prazo, '%H:%i de %d/%m/%Y'), '') as prazoFormat,
                DATEDIFF(NOW(), a.dataInicio) as diarioAberto,
                DATEDIFF(a.prazo, NOW()) as prazoDiff,
                date_format( DATE_ADD(a.dataFim, INTERVAL $LIMITE_DIARIO_PROF DAY), '%d/%m/%Y') as dataFimFormat,
                date_format( DATE_SUB(NOW(), INTERVAL 365 DAY), '%d/%m/%Y') as dataInicioCal,
                date_format( a.prazo, '%d/%m/%Y') as dataFimCal,
                DATEDIFF( DATE_ADD(a.dataFim, INTERVAL $LIMITE_DIARIO_PROF DAY), NOW()) as dataFimDiff
                FROM Disciplinas d, Turmas t, Cursos c, Turnos tu, Modalidades m, Atribuicoes a
                WHERE a.disciplina=d.codigo
                AND t.curso=c.codigo
                AND a.turma=t.codigo
                AND t.turno=tu.codigo
                AND m.codigo = c.modalidade
                AND a.codigo=:cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]) {
            if ($res[0]['prazoFormat']) {
                $res[0]['inicioCalendar'] = $res[0]['dataInicioCal'];
                $res[0]['fimCalendar'] = $res[0]['dataFimCal'];
            } else {
                $res[0]['inicioCalendar'] = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") - 365, date("Y")));
                $res[0]['fimCalendar'] = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $LIMITE_DIARIO_PROF, date("Y")));
            }

            if (!$res[0]['bimestre'] && !$res[0]['semestre'])
                $res[0]['bimestreNome'] = 'ANUAL';
            elseif ($res[0]['bimestre'] && $res[0]['semestre'])
                $res[0]['bimestreNome'] .= 'º BIMESTRE';
            elseif (!$res[0]['bimestre'] && $res[0]['semestre'])
                $res[0]['bimestreNome'] = 'SEMESTRAL';

            $dataExpirou = false;

            //ALTERANDO O STATUS SE O PRAZO EXPIROU, POREM A DATA FINAL
            //AINDA NÂO FOI ATINGIDA
            if ($res[0]['prazoDiff'] && $res[0]['prazoDiff'] <= 0 && $res[0]['dataFimDiff'] >= 0) {
                $params1['codigo'] = crip($codigo);
                $params1['prazo'] = crip('NULL');
                $params1['status'] = crip(0);
                $res[0]['status'] = 0;
                $res[0]['prazo'] = null;
                $this->insertOrUpdate($params1);
            }

            //ALTERANDO O STATUS SE O PRAZO EXPIROU
            //E SE A DATA FINAL FOI ATINGIDA OU NAO HA PRAZO
            //E A DATA FINAL FOI ATINGIDA
            if ($res[0]['status'] == 0 && (!$res[0]['prazoDiff'] && $res[0]['dataFimDiff'] < 0) || ($res[0]['prazoDiff'] && $reg[0]['prazoDiff'] < 0 && $reg[0]['dataFimDiff'] < 0)) {
                $params1['codigo'] = crip($codigo);
                $params1['status'] = crip(4);
                $this->insertOrUpdate($params1);
                $res[0]['status'] = 4;
                $dataExpirou = true;
            }

            //ALTERANDO O STATUS SE O STATUS FOR DIFERENTE DE ZERO
            //NAO HA PRAZO E A DATA FINAL AINDA NAO FOI ATINGIDA
            //E O FECHAMENTO NAO FOR MANUAL
            if ($res[0]['status'] > 3 && !$res[0]['prazoDiff'] && $res[0]['dataFimDiff'] >= 0) {
                $params1['codigo'] = crip($codigo);
                $params1['status'] = crip(0);
                $this->insertOrUpdate($params1);
                $res[0]['status'] = 0;
                $dataExpirou = false;
            }

            // SEM LIMITE CASO O FECHAMENTO NÂO FOR MANUAL
            if (!$LIMITE_DIARIO_PROF && $res[0]['status'] > 3) {
                $res[0]['status'] = 0;
                $res[0]['prazo'] = null;
                $res[0]['prazoDiff'] = -1;
                $dataExpirou = false;
            }

            //DIARIO AINDA NAO COMECOU
            if ($LIMITE_DIARIO_PROF && $res[0]['diarioAberto'] < 0) {
                $dataExpirou = true;
                $res[0]['status'] = 100;
            }

            if ($res[0]['status'] != 0)
                $dataExpirou = true;

            $_SESSION['dataExpirou'] = $dataExpirou;

            //RETORNA A INFO DA ATRIBUICAO
            if ($res[0]['status'] == 1)
                $res[0]['info1'] = 'STATUS_DIARIO_1';
            if ($res[0]['status'] == 2)
                $res[0]['info1'] = 'STATUS_DIARIO_2';
            if ($res[0]['status'] == 3)
                $res[0]['info1'] = 'STATUS_DIARIO_3';
            if ($res[0]['status'] == 4)
                $res[0]['info1'] = 'STATUS_DIARIO_4';
            if ($res[0]['status'] == 100)
                $res[0]['info1'] = 'STATUS_DIARIO_100';
            if ($res[0]['prazoDiff'] && !$res[0]['status']) {
                $res[0]['info1'] = 'STATUS_DIARIO_101';
                $res[0]['info2'] = $res[0]['prazoFormat'];
            }

            return $res[0];
        } else {
            return false;
        }
    }

    // LISTA AS ATRIBUICOES DO PROFESSOR/ALUNO
    // USADO POR: INDEX.PHP (PARA MONTAR O MENU DO PROFESSOR)
    public function getAtribuicoesFromPapel($codigo, $papel, $ano, $menu = null) {
        $bd = new database();

        $professor = "SELECT t.ano as ano, t.semestre as semestre, 
                        a.bimestre as bimestre, a.codigo as atribuicao, 
                        d.nome as disciplina, d.numero as numero, 
                        t.numero as turma, 
                        IF(LENGTH(a.subturma) > 0,a.subturma,a.eventod) as subturma, 
                        IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, 
                            IF(m.codigo < 1000 OR m.codigo > 2000, CONCAT(c.nome,' [',m.nome,']'), c.nome)) 
                        as curso,
                        m.codigo as codigoModalidade, m.nome as modalidade,
                        (SELECT nome FROM Ensalamentos e, Horarios h
                            WHERE e.horario = h.codigo 
                            AND e.atribuicao = a.codigo LIMIT 1) as hora                        
			FROM Disciplinas d, Turmas t, Atribuicoes a, 
                            Professores pr, Cursos c, Modalidades m
			WHERE d.codigo = a.disciplina 
			AND t.codigo = a.turma
			AND c.codigo = t.curso 
			AND m.codigo = c.modalidade
			AND pr.atribuicao = a.codigo
			AND pr.professor=:cod 
                        AND (t.semestre=1 OR t.semestre=2 OR t.semestre=0)
                        AND t.ano = :ano
			ORDER BY t.ano,t.semestre,a.bimestre,d.numero,a.grupo";

        $aluno = "SELECT t.ano as ano, t.semestre as semestre, 
                        at.bimestre as bimestre,
                        at.codigo as atribuicao, d.nome as disciplina,
                        d.numero as numero,
                        IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo,c.nome) as curso
			FROM Matriculas m, Turmas t, Pessoas p, Cursos c, 
                            Atribuicoes at, Disciplinas d 
			WHERE m.aluno=p.codigo 
			AND at.turma=t.codigo 
			AND m.atribuicao=at.codigo
			AND t.curso=c.codigo
			AND at.disciplina = d.codigo
			AND p.codigo=:cod
                        AND (t.semestre=1 OR t.semestre=2 OR t.semestre=0)
                        AND t.ano = :ano
			ORDER BY d.numero, at.bimestre";
        $params = array(':cod' => $codigo, ':ano' => $ano);
        $res = $bd->selectDB($$papel, $params);
        if (!$menu)
            return $res;

        if ($res) {
            foreach ($res as $reg) {
                if (isset($reg['subturma']))
                    $reg['numero'] = $reg['numero'] . ' [' . $reg['turma'] . '-' . $reg['subturma'] . ']';

                if ($reg['hora']) {
                    preg_match('#\[(.*?)\]#', $reg['hora'], $match);
                    $reg['numero'] .= ' [' . $match[1] . ']';
                }

                // SE FOR ANUAL
                if ($reg['semestre'] == 0)
                    $disciplinas[$reg['ano']]['A']['A'][$reg['atribuicao']] = array($reg['disciplina'], $reg['atribuicao'], $reg['numero'], $reg['curso']);

                // SE FOR SEMESTRAL
                if ($reg['bimestre'] == 0)
                    $disciplinas[$reg['ano']][$reg['semestre']]['S'][$reg['atribuicao']] = array($reg['disciplina'], $reg['atribuicao'], $reg['numero'], $reg['curso']);

                // SE FOR BIMESTRAL
                if ($reg['bimestre'] != 0)
                    $disciplinas[$reg['ano']][$reg['semestre']]['B'][$reg['bimestre']][$reg['atribuicao']] = array($reg['disciplina'], $reg['atribuicao'], $reg['numero'], $reg['curso']);
            }
            return $disciplinas;
        }
        else {
            return false;
        }
    }

    // USADO POR: PROFESSOR/NOTA.PHP
    // Lista as atribuicoes dos bimestres do mesmo número da disciplina
    public function listAtribuicoesOfBimestre($atribuicao) {
        $bd = new database();

        $sql = "SELECT a2.codigo, a2.bimestre 
                    FROM Atribuicoes a2 
                    WHERE a2.turma IN (
				SELECT t1.codigo FROM Turmas t1 
				WHERE t1.numero IN ( SELECT t.numero 
                                                FROM Atribuicoes a, Turmas t 
                                                WHERE a.turma = t.codigo 
                                                AND a.codigo = :att)
                                )
                    AND a2.disciplina = (SELECT a3.disciplina 
                                               FROM Atribuicoes a3 
                                                WHERE a3.codigo = :att)
                    AND a2.subturma = (SELECT a4.subturma 
                                            FROM Atribuicoes a4 
                                            WHERE a4.codigo = :att)";
        $params = array(':att' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: SECRETARIA/PLANO.PHP
    // Retorna os bimestres de uma atribuicao
    public function getFechamento($turma) {
        $bd = new database();

        $sql = "SELECT a.bimestre as bimestre,
                IF(t.semestre = 0,'ANUAL', IF(a.bimestre = 0, 'SEMESTRAL',CONCAT(a.bimestre,'º BIM'))) as nome
                FROM Atribuicoes a, Turmas t 
                WHERE t.codigo=a.turma 
                AND t.codigo=:turma
                GROUP BY a.bimestre";

        $params = array(':turma' => $turma);

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // LISTA TODAS ATRIBUICOES
    // USADO POR: SECRETARIA/PRAZOS/DIARIO.PHP
    public function getAllAtribuicoes($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT IF(a.bimestre > 0, CONCAT(' [', a.bimestre,'º BIM]'), '') as bimestre, a.codigo as atribuicao, 
                       d.nome as disciplina, d.numero as numero, 
                        t.numero as turma, a.status,
                        IF(LENGTH(a.subturma) > 0,CONCAT(' [',a.subturma,']'),CONCAT(' [',a.eventod,']')) as subturma, 
                        IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, 
                            IF(m.codigo < 1000 OR m.codigo > 2000, CONCAT(c.nome,' [',m.nome,']'), c.nome)) 
                        as curso, a.prazo,
                        m.codigo as codigoModalidade, m.nome as modalidade,
                        DATEDIFF(a.prazo,NOW()) as prazoDiff
                FROM Disciplinas d, Turmas t, Atribuicoes a, 
                           Cursos c, Modalidades m, Professores p
		WHERE d.codigo = a.disciplina 
		AND t.codigo = a.turma
		AND c.codigo = t.curso 
		AND m.codigo = c.modalidade
                AND p.atribuicao = a.codigo
                AND (t.semestre=:semestre OR t.semestre=0)
                AND t.ano = :ano";

        $sql .= " $sqlAdicional ";

        $sql .= " GROUP BY a.codigo ORDER BY d.nome, a.bimestre ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            $i = 0;
            foreach ($res as $reg) {
                //VERIFICANDO SE O PRAZO FOI FINALIZADO E ALTERA NA ATRIBUICAO
                if ($reg['prazo'] != '0000-00-00 00:00:00' && $reg['prazo'] < 0) {
                    mysql_query("UPDATE Atribuicoes SET status=4,prazo='' WHERE codigo = " . $linha[0]);
                    $reg['prazo'] = '0000-00-00 00:00:00';
                }

                if ($reg['prazo'] != '0000-00-00 00:00:00' && $reg['prazoDiff'] > 0) {
                    $origem = ($reg['prazoDiff'] * 24) . "h";
                } else {
                    if ($reg['status'] == 1)
                        $origem = "Coord";
                    if ($reg['status'] == 2)
                        $origem = "Prof";
                    if ($reg['status'] == 3)
                        $origem = "Secre";
                    if ($reg['status'] == 4)
                        $origem = "SYS";
                }
                if (!$origem)
                    $origem = 'Aberto';

                $res[$i]['prazo'] = $reg['prazo'];
                $res[$i]['origem'] = $origem;
                $i++;
            }
            return $res;
        } else {
            return false;
        }
    }

    // ALTERA O PRAZO DOS DIARIOS
    // USADO POR: SECRETARIA/PRAZOS/DIARIO.PHP
    public function changePrazo($params) {
        $bd = new database();

        $atribuicoes = explode(',', $params["codigo"]);

        foreach ($atribuicoes as $atribuicao) {
            if ($params['botao'] == 'fechou') {
                $params_new = array('codigo' => $atribuicao, 'status' => 1, 'prazo' => 'NULL');
                if ($this->insertOrUpdate($params_new))
                    $ok++;

                //ALTERAR NOTASFINAIS PARA SINCRONIZAR NOVAMENTE
                $nota = new NotasFinais();
                if ($nota->fecharDiario($atribuicao)) {
                    $params_nota = array('codigo' => $atribuicao);
                    $sql = "UPDATE NotasFinais SET sincronizado='' WHERE atribuicao=:codigo AND flag <> 5";
                    $bd->updateDB($sql, $params_nota);
                }
            }

            if ($params['botao'] == 'liberou') {
                $params_new = array('codigo' => $atribuicao);
                $sql = "UPDATE Atribuicoes SET prazo=DATE_ADD(NOW(), INTERVAL 1 DAY), status='0' WHERE codigo=:codigo";
                if ($res = $bd->updateDB($sql, $params_new))
                    $ok++;
            }

            //REGISTRANDO NA TABELA PRAZOSDIARIO O MOTIVO
            $params_pd = array('atribuicao' => $atribuicao);
            $sql = 'SELECT codigo,motivo FROM PrazosDiarios WHERE atribuicao = :atribuicao AND dataConcessao IS NULL';
            if ($res = $bd->selectDB($sql, $params_pd)) {
                $params_pd['codigo'] = $res[0]['codigo'];
                $params_pd['motivo'] = $params['pessoa'] . ', '.$params['botao'].' o diário. Motivo: ' . $params['motivo'] . '<br>Motivo do Professor: ' . $res[0]['motivo'];
            } else {
                $params_pd['data'] = date('Y-m-d H:i:s');
                $params_pd['motivo'] = $params['pessoa'] . ', '.$params['botao'].' o diário. Motivo: ' . $params['motivo'];
            }
            $params_pd['dataConcessao'] = date('Y-m-d H:i:s');
            $ret = $this->insertOrUpdate($params_pd, 'PrazosDiarios');
        }

        $rs['TIPO'] = 'UPDATE';
        $rs['RESULTADO'] = $ok;
        $rs['STATUS'] = 'OK';

        return $rs;
    }

}

?>