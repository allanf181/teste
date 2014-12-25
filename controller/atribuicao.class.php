<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

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
    // BOLETIM.PHP, RELATORIOS/ATESTADOMATRICULA.PHP
    // Retorna dados da atribuicao (Disciplina, Turma, etc..)
    // Pode ser colocado com função no MySQL futuramente
    public function getAtribuicao($codigo, $LIMITE_DIARIO_PROF = 0) {
        $bd = new database();

        if ($LIMITE_DIARIO_PROF)
            $inicio = $LIMITE_DIARIO_PROF;
        else
            $inicio = 365;

        $sql = "SELECT d.nome as disciplina, t.numero as turma, a.status, a.prazo,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                IF(a.bimestre = 0 AND t.semestre <> 0, CONCAT('no ',t.semestre,'º semestre do '),
                    CONCAT('no ',a.bimestre,'º bimestre do ')) as bimestreFormat,
                a.bimestre as bimestre, c.fechamento as fechamento,d.numero as numeroDisciplina,
        	t.semestre as semestre, t.ano as ano, t.codigo as turmaCodigo, a.subturma as subturma,
                c.fechamento as fechamento, a.observacoes as observacoes, a.competencias as competencias,
                m.codigo as codModalidade, m.nome as modalidade, d.ch as CH, a.aulaPrevista as aulaPrevista,
                IF(a.prazo, date_format(a.prazo, '%H:%i de %d/%m/%Y'), '') as prazoFormat,
                DATEDIFF(NOW(), a.dataInicio) as diarioAberto,
                DATEDIFF(a.prazo, NOW()) as prazoDiff,
                date_format( a.dataInicio, '%d/%m/%Y') as dataInicioFormat,
                date_format( DATE_ADD(a.dataFim, INTERVAL $LIMITE_DIARIO_PROF DAY), '%d/%m/%Y') as dataFimFormat,
                DATEDIFF( DATE_ADD(a.dataFim, INTERVAL $LIMITE_DIARIO_PROF DAY), NOW()) as dataFimDiff
                FROM Disciplinas d, Turmas t, Cursos c, Turnos tu, Modalidades m, Atribuicoes a, Matriculas ma
                WHERE a.disciplina=d.codigo
                AND t.curso=c.codigo
                AND a.turma=t.codigo
                AND t.turno=tu.codigo
                AND m.codigo = c.modalidade
                AND ma.atribuicao = a.codigo
                AND a.codigo=:cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]) {
            if ($res[0]['prazoFormat']) {
                $res[0]['inicioCalendar'] = $res[0]['dataInicioFormat'];
                $res[0]['fimCalendar'] = $res[0]['dataFimFormat'];
            } else {
                $res[0]['inicioCalendar'] = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") - $inicio, date("Y")));
                $res[0]['fimCalendar'] = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $inicio, date("Y")));
            }

            if (!$res[0]['bimestre'] && !$res[0]['semestre'])
                $res[0]['bimestreNome'] = 'ANUAL';
            elseif ($res[0]['bimestre'] && $res[0]['semestre'])
                $res[0]['bimestreNome'] .= $res[0]['bimestre'] . 'º BIMESTRE';
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
            if ($res[0]['prazoDiff'] && $res[0]['prazoDiff'] >= 0 && !$res[0]['status']) {
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
                        (SELECT nome FROM Turnos tu WHERE tu.codigo = a.periodo) as turno
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
			ORDER BY t.ano,t.semestre,a.bimestre,d.numero";

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

                if ($reg['turno'])
                    $reg['numero'] .= ' [' . $reg['turno'][0] . ']';

                // SE FOR ANUAL
                if ($reg['semestre'] == 0)
                    $disciplinas[$reg['ano']]['A']['A'][$reg['atribuicao']] = array($reg['disciplina'], $reg['atribuicao'], $reg['numero'], $reg['curso'], $reg['turno']);

                // SE FOR SEMESTRAL
                if ($reg['bimestre'] == 0)
                    $disciplinas[$reg['ano']][$reg['semestre']]['S'][$reg['atribuicao']] = array($reg['disciplina'], $reg['atribuicao'], $reg['numero'], $reg['curso'], $reg['turno']);

                // SE FOR BIMESTRAL
                if ($reg['bimestre'] != 0)
                    $disciplinas[$reg['ano']][$reg['semestre']]['B'][$reg['bimestre']][$reg['atribuicao']] = array($reg['disciplina'], $reg['atribuicao'], $reg['numero'], $reg['curso'], $reg['turno']);
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

    // LISTA TODAS ATRIBUICOES
    // USADO POR: SECRETARIA/PRAZOS/DIARIO.PHP
    public function getAllAtribuicoes($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT IF(a.bimestre > 0, CONCAT(' [', a.bimestre,'º BIM]'), '') as bimestre, a.codigo as atribuicao, 
                       d.nome as disciplina, d.numero as numero, 
                        t.numero as turma, a.status, IF(a.bimestre > 0, CONCAT(' [', a.bimestre,'º BIM]'), '') as bimestreFormat,
                        IF(LENGTH(a.subturma) > 0,CONCAT(' [',a.subturma,']'),CONCAT(' [',a.eventod,']')) as subturma, 
                        IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, 
                            IF(m.codigo < 1000 OR m.codigo > 2000, CONCAT(c.nome,' [',m.nome,']'), c.nome)) 
                        as curso, a.prazo, (SELECT nome FROM Turnos WHERE codigo = a.periodo) as turno,
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
                $origem = null;

                //VERIFICANDO SE O PRAZO FOI FINALIZADO E ALTERA NA ATRIBUICAO
                if ($reg['prazo'] != '0000-00-00 00:00:00' && $reg['prazo'] < 0) {
                    mysql_query("UPDATE Atribuicoes SET status=4,prazo='' WHERE codigo = " . $linha[0]);
                    $reg['prazo'] = '0000-00-00 00:00:00';
                }

                if ($reg['prazo'] != '0000-00-00 00:00:00' && $reg['prazoDiff'] > 0) {
                    $origem = 'Diário aberto por ' . ($reg['prazoDiff'] * 24) . "h";
                } else {
                    if ($reg['status'] == 1)
                        $origem = "Fechado pela Coordenador.";
                    if ($reg['status'] == 2)
                        $origem = "Fechado pelo Professor.";
                    if ($reg['status'] == 3)
                        $origem = "Fechado pela Secretaria.";
                    if ($reg['status'] == 4)
                        $origem = "Fechado automaticamente pelo Sistema por atingir o prazo.";
                }
                if (!$origem)
                    $origem = 'Diário aberto para alterações.';

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
                if ($res = $this->insertOrUpdate($params_new))
                    $ok++;

                //ALTERAR NOTASFINAIS PARA SINCRONIZAR NOVAMENTE
                if (!class_exists('NotasFinais'))
                    require CONTROLLER . "/notaFinal.class.php";
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

                //ENVIANDO EMAIL
                if ($res) {
                    if ($res['STATUS'] == 'OK') {
                        $resAtt = $this->listRegistros(array('codigo' => $atribuicao));
                        if (!class_exists('Pessoas'))
                            require CONTROLLER . "/pessoa.class.php";
                        $pessoa = new Pessoas();
                        if ($email = $pessoa->getEmailFromAtribuicao($resAtt[0]['codigo'])) {
                            if (!class_exists('LogEmails'))
                                require CONTROLLER . "/logEmail.class.php";
                            $logEmail = new LogEmails();
                            $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Seu di&aacute;rio foi aberto pelo coordenador.', $email);
                        }
                    }
                }
            }

            //REGISTRANDO NA TABELA LogSolicitacoes O MOTIVO
            $params_pd = array('atribuicao' => $atribuicao);
            $sql = "SELECT codigo,solicitacao FROM LogSolicitacoes WHERE nomeTabela = 'DIARIO' AND codigoTabela = :atribuicao AND (dataConcessao IS NULL OR dataConcessao = '0000-00-00 00:00:00')";
            $res = $bd->selectDB($sql, $params_pd);

            $params_pd = array();
            $params_pd['nomeTabela'] = 'DIARIO';
            $params_pd['codigoTabela'] = $atribuicao;
            $params_pd['dataConcessao'] = date('Y-m-d H:i:s');

            if ($res) {
                foreach ($res as $reg) {
                    $params_pd['codigo'] = $reg['codigo'];
                    $params_pd['solicitacao'] = $params['pessoa'] . ', ' . $params['botao'] . ' o diário. Motivo: ' . $params['motivo'] . '<br>Motivo do Professor: ' . $res[0]['solicitacao'];
                    $ret = $this->insertOrUpdate($params_pd, 'LogSolicitacoes');
                }
            } else {
                $params_pd['solicitante'] = $params['codPessoa'];
                $params_pd['dataSolicitacao'] = date('Y-m-d H:i:s');
                $params_pd['solicitacao'] = $params['pessoa'] . ', ' . $params['botao'] . ' o diário. Motivo: ' . $params['motivo'];
                $ret = $this->insertOrUpdate($params_pd, 'LogSolicitacoes');
            }
        }

        $rs['TIPO'] = 'UPDATE';
        $rs['RESULTADO'] = $ok;
        $rs['STATUS'] = 'OK';

        return $rs;
    }

    // LISTA OS DADOS PARA ESTATISTICAS DO USO DO SISTEMA
    // USADO POR: ADMIN/USOSISTEMA.PHP
    public function getDadosUsoSistema($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT p.nome,
                SUM((SELECT COUNT(*) FROM Aulas au WHERE au.atribuicao = a.codigo)) as aula,
                SUM((SELECT COUNT(*) FROM Frequencias f, Aulas au WHERE au.codigo = f.aula AND au.atribuicao = a.codigo)) as frequencia,
                SUM((SELECT COUNT(*) FROM Avaliacoes av WHERE av.atribuicao = a.codigo)) as avaliacao,
                SUM((SELECT COUNT(*) FROM Avaliacoes av, Notas n WHERE av.codigo = n.avaliacao AND av.atribuicao = a.codigo)) as nota,
                (SELECT date_format(data, '%d/%m/%Y') FROM Aulas ad WHERE ad.atribuicao = a.codigo ORDER BY data DESC LIMIT 1) as ultAula
                FROM Atribuicoes a, Disciplinas d, Turmas t, Professores pr, Pessoas p
                WHERE a.disciplina = d.codigo
                AND t.codigo = a.turma
                AND pr.atribuicao = a.codigo
                AND p.codigo = pr.professor        
                AND (t.semestre=:semestre OR t.semestre=0)
                AND t.ano = :ano";

        $sql .= " $sqlAdicional ";

        $sql .= " GROUP BY pr.professor ORDER BY aula DESC, frequencia DESC, avaliacao DESC, nota DESC ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                if ($reg['aula'] || $reg['frequencia'] || $reg['avaliacao'] || $reg['nota'])
                    $uso++;
                $count++;
            }
            $uso = round(($uso * 100) / $count);
            $res[0]['uso'] = $uso;
            return $res;
        } else {
            return false;
        }
    }

    // RETORNA OS DADOS DO BOLETIM TURMA
    // USADO POR: SECRETARIA/RELATORIOS/BOLETIMTURMA.PHP, INC/BOLETIMTURMA.PHP
    public function getAtribuicoesFromBoletimTurma($turma, $bimestre = null, $fechamento = null, $turno = null) {
        $bd = new database();

        $params = array('turma' => $turma);

        if ($bimestre != 'final' && $fechamento == 'b') {
            $sqlAdicional = " IN (SELECT t1.codigo FROM Turmas t1 
                        	WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
                		WHERE t2.codigo = :turma)) AND a.bimestre=:bimestre ";
            $params['bimestre'] = $bimestre;
        } else if ($bimestre == 'final' && $fechamento == 'b') {
            $sqlAdicional = " IN (SELECT t1.codigo FROM Turmas t1 
                                WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
                                WHERE t2.codigo = :turma)) ";
        } else {
            $sqlAdicional = " = :turma ";
        }

        if ($turno) {
            $sqlAdicionalTurno = " AND a.periodo = :turno ";
            $params['turno'] = $turno;
        }

        $sql = "SELECT 	al.codigo as codAluno, al.nome as aluno, 
                        d.codigo as codDiciplina, d.numero as numero, 
                        d.nome as disciplina, a.status,
                	m.codigo as codMatricula, a.codigo as atribuicao,
                        a.bimestre, al.prontuario, t.nome as turno,
                        (SELECT numero FROM Turmas where codigo = a.turma) as turma
			FROM Atribuicoes a 
			LEFT JOIN Disciplinas d on a.disciplina=d.codigo 
			LEFT JOIN Matriculas m on m.atribuicao=a.codigo 
			LEFT JOIN Pessoas al on m.aluno=al.codigo
                        LEFT JOIN Turnos t on t.codigo = a.periodo
			WHERE a.turma 
			$sqlAdicional
                        $sqlAdicionalTurno
			ORDER BY a.bimestre, d.nome, al.nome";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: RELATORIOS.PHP
    // Lista os fechamentos existentes
    public function getFechamentos($turma) {
        $bd = new database();

        $sql = "select a.bimestre, t.semestre
                from Atribuicoes a, Turmas t
                where t.codigo=a.turma
                and t.codigo=:turma
                GROUP BY a.bimestre";

        $params = array(':turma' => $turma);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            $new_res = array();
            $i = 0;
            foreach ($res as $reg) {
                if ($reg['semestre'] != 0 && $reg['bimestre'] != 0) {
                    $new_res[$i]['nome'] = $reg['bimestre'] . 'º Bimestre';
                    $new_res[$i]['value'] = $reg['bimestre'];
                    $i++;
                }
            }
            if ($i) {
                $new_res[$i]['nome'] = 'Final';
                $new_res[$i]['value'] = 'final';
            }
            return $new_res;
        } else {
            return false;
        }
    }

    // LISTA AS ATRIBUICOES DE DOCENTES
    // USADO POR: RELATORIOS/DOCENTES.PHP
    public function getAtribuicaoDocente($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT DISTINCT p.prontuario, p.nome as pessoa, d.nome as disciplina,
                    e.diaSemana, s.nome as sala, d.codigo as codDisciplina,
                    CONCAT(DATE_FORMAT(h.inicio, '%h:%i'), ' - ', DATE_FORMAT(h.fim, '%h:%i')) as horario
                    FROM Ensalamentos e, Atribuicoes a, Professores pr, Pessoas p,
                        Disciplinas d, Horarios h, Turmas t, Salas s, Cursos c
                    WHERE e.atribuicao = a.codigo
                    AND pr.atribuicao = a.codigo
                    AND pr.professor = p.codigo
                    AND d.codigo = a.disciplina
                    AND h.codigo = e.horario
                    AND s.codigo = e.sala
                    AND t.codigo = a.turma
                    AND t.curso = c.codigo
                    AND t.semestre = :semestre
                    AND t.ano = :ano";

        $sql .= " $sqlAdicional ";

        $sql .= "ORDER BY p.nome, d.nome, e.diaSemana, h.inicio, s.nome";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // LISTA AS ATRIBUICOES DE DOCENTES
    // USADO POR: RELATORIOS/LISTAGEM.PHP
    public function getADToJSON($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $res = $this->getAtribuicaoDocente($params, $sqlAdicional, $item, $itensPorPagina);

        foreach ($res as $reg) {
            if ($reg['prontuario'] != $pront) {
                if ($total) {
                    $item1[] = $pront;
                    $item2[] = $total;
                    $item3[] = count($disciplina);
                }
                $total = 0;
                $disciplina = array();
            } else {
                $total++;
                $disciplina[$reg['codDisciplina']] = $disciplina[$reg['codDisciplina']] + 1;
            }
            $pront = $reg['prontuario'];
        }

        $graph_data = array('item1Name' => 'Prontuário', 'item1' => $item1,
            'item2Name' => 'Aulas', 'item2' => $item2,
            'item3Name' => 'Disciplinas', 'item3' => $item3,
            'title' => 'Atribuição Docente', 'titleY'=>'Quantidade', 'titleX'=>'Prontuário');

        return json_encode($graph_data);
    }

    // RETORNA OS DADOS DO BOLETIM INDIVIDUAL
    // USADO POR: SECRETARIA/RELATORIOS/BOLETIM.PHP
    public function getAtribuicoesFromBoletim($turma, $aluno = null) {
        $bd = new database();

        if ($aluno) {
            $params['aluno'] = $aluno;
            $sqlAdicional = ' AND al.codigo = :aluno ';
        }

        $sql = "SELECT al.codigo as codAluno, al.nome as aluno, d.codigo as codDisciplina, 
                        d.numero as numeroDisciplina, d.nome as disciplina, a.status,
			m.codigo as matricula, a.codigo as atribuicao, a.bimestre,
			t.numero as turma, al.prontuario,
                        IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso
		FROM Atribuicoes a 
		LEFT JOIN Disciplinas d on a.disciplina=d.codigo 
		LEFT JOIN Matriculas m on m.atribuicao=a.codigo 
		LEFT JOIN Pessoas al on m.aluno=al.codigo
		LEFT JOIN Turmas t on t.codigo=a.turma
		LEFT JOIN Cursos c on c.codigo=t.curso
		WHERE a.turma IN (SELECT t1.codigo FROM Turmas t1 
				WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
				WHERE t2.codigo = :turma)) 
                $sqlAdicional
		ORDER BY a.bimestre, d.nome, al.nome";

        $params['turma'] = $turma;
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function listSolicitacoesDiarios($params = null, $item = null, $itensPorPagina = null, $sqlAdicional = null) {
        $bd = new database();

        $nav = null;
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";

        $sql = "SELECT l.solicitacao, p.nome, d.nome as disciplina, a.codigo as atribuicao, c.nome as curso, "
                . "c.codigo as codCurso, t.codigo as codTurma, p.codigo as codPessoa,"
                . "date_format(l.dataSolicitacao, '%d/%m/%Y %H:%i') as dataSolicitacao "
                . "FROM LogSolicitacoes l, Atribuicoes a, Pessoas p, Turmas t, Disciplinas d, Cursos c "
                . "WHERE l.codigoTabela = a.codigo "
                . "AND l.nomeTabela = 'DIARIO' "
                . "AND a.turma = t.codigo "
                . "AND p.codigo = l.solicitante "
                . "AND a.disciplina = d.codigo "
                . "AND t.curso = c.codigo "
                . "AND t.ano = :ano "
                . "AND (t.semestre = 0 OR t.semestre = :semestre) "
                . "AND (dataConcessao IS NULL OR dataConcessao = '0000-00-00 00:00:00')";

        $sql .= " $sqlAdicional ";
        $sql .= " ORDER BY l.codigo DESC LIMIT 1 ";
        $sql .= " $nav ";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;

        return false;
    }

}

?>