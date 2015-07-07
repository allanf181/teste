<?php

if (!class_exists('Notas'))
    require_once CONTROLLER . '/nota.class.php';

class NotasFinais extends Notas {

    public function fecharDiario($atribuicao) {
        $bd = new database();

        $erro = 0;
        // SELECIONA OS DADOS DA ATRIBUICAO, DISCIPLINA, MATRICULA...
        $sql = "SELECT m.codigo as matricula, a.bimestre as bimestre,
                m.aluno as aluno, t.codigo as turma, d.numero
		FROM Atribuicoes a, Matriculas m, Disciplinas d, Turmas t
		WHERE a.codigo = m.atribuicao
		AND t.codigo = a.turma
		AND d.codigo = a.disciplina
		AND a.codigo = :cod";

        $params = array(':cod' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            if (!class_exists('MatriculasAlteracoes'))
                require CONTROLLER . "/matriculaAlteracao.class.php";
            $ma = new MatriculasAlteracoes();

            foreach ($res as $reg) {
                $matSituacao = $ma->getAlteracaoMatricula($reg['aluno'], $atribuicao, date('Y-m-d'));

                if ($matSituacao['listar'] && $matSituacao['habilitar']) {

                    $dados = $this->resultado($reg['matricula'], $atribuicao, 0, 0);
                    
                    $params2 = array();
                    
                    $params2['atribuicao'] = $atribuicao;
                    $params2['matricula'] = $reg['matricula'];
                    if ($reg['bimestre'] == 0) {
                        $params2['bimestre'] = '1';
                        $reg['bimestre'] = '1';
                    } else
                        $params2['bimestre'] = $reg['bimestre'];
                    $params2['mcc'] = $dados['mediaAvaliacao'];
                    $params2['rec'] = $dados['notaRecuperacao'];
                    $params2['ncc'] = $dados['media'];
                    $params2['falta'] = $dados['faltas'];

//                    if ($dados['siglaSituacao'] != 'REF') {// ERRO DESABILITADO TEMPORARIAMENTE

                        $sql1 = "SELECT codigo,recuperacao FROM NotasFinais WHERE atribuicao = :cod 
    			AND matricula = :mat
    			AND bimestre = :bim";

                        $params1 = array(':cod' => $atribuicao,
                            ':mat' => $reg['matricula'],
                            ':bim' => $reg['bimestre']);

                        $res1 = $bd->selectDB($sql1, $params1);

                        if ($res1) {
                            $params2['codigo'] = $res1[0]['codigo'];
                            $params2['flag'] = '00';
                            $params2['retorno'] = 'Diario alterado, aguardando sincronizacao.';
                            if ($res1[0]['recuperacao'])
                                $params2['recuperacao'] = '2';
                        } else {
                            $params2['atribuicao'] = $atribuicao;
                            $params2['matricula'] = $reg['matricula'];
                        }

                        if (!$this->insertOrUpdate($params2))
                            $erro = 1;
//                    } else
//                        $erro = 2;

                    // FECHAMENTO ANUAL BIMESTRAL
                    /* if ($reg['bimestre'] == 4) {
                      $dados = $this->resultadoBimestral($reg['aluno'], $reg['turma'], $reg['numero'], 1, 1);
                      if (!$dados['situacao']) {
                      $sql = "SELECT * FROM NotasFinais
                      WHERE atribuicao = :cod
                      AND matricula = :mat
                      AND bimestre = 'M'";

                      $params = array(':cod' => $atribuicao,
                      ':mat' => $reg['matricula']);
                      $res2 = $bd->selectDB($sql, $params);

                      if ($res2)
                      $params2['codigo'] = $res2[0]['codigo'];

                      $params2['mcc'] = $dados['mediaAvaliacao'];
                      $params2['rec'] = $dados['notaRecuperacao'];
                      $params2['ncc'] = $dados['media'];

                      $params2['bimestre'] = 'M';

                      if (!$this->insertOrUpdate($params2))
                      $erro = 1;
                      }
                      } */
                }
            }
            return $erro;
        }
    }

    // USADO POR: SECRETARIA/CURSOS/NOTASFINAIS.PHP
    // LISTA AS NOTAS FINAIS DE UM CURSO
    public function listNotasFinais($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        // efetuando a consulta para listagem
        $sql = "SELECT n.codigo, p.nome as aluno, n.sincronizado, 
                        n.atribuicao, d.nome as disciplina, t.numero as turma, 
                        n.retorno, n.flag, n.ncc, n.falta,
                        IF(a.bimestre > 0, CONCAT(' [',a.bimestre,'ºBIM]'), '') as bimestre
                    FROM NotasFinais n, Atribuicoes a, Matriculas m, 
                        Turmas t, Disciplinas d, Pessoas p, Cursos c
                    WHERE n.atribuicao = a.codigo
                    AND n.matricula = m.codigo
                    AND a.turma = t.codigo
                    AND a.disciplina = d.codigo
                    AND p.codigo = m.aluno
                    AND c.codigo = t.curso ";

        $sql .= " $sqlAdicional ";
        $sql .= ' ORDER BY t.numero, d.nome, p.nome ';
        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: PROFESSOR/NOTA.PHP
    // VERIFICA SE A NOTA DO ALUNO FOI EXPORTADA
    public function checkIfExportDN($atribuicao, $matricula = null, $bimestre = null, $tipo = null) {
        $bd = new database();

        if ($bimestre == "0")
            $bimestre = "1";
        
        $params = array('atribuicao' => $atribuicao, 'bimestre' => $bimestre);

        if ($matricula) {
            $sqlMatricula = 'AND n.matricula = :matricula';
            $params['matricula'] = $matricula;
            if ($tipo)
                $sqlMatricula .= ' AND recuperacao = 2 ';
        }
        
        // efetuando a consulta para listagem
        $sql = "SELECT *, (SELECT COUNT(*) FROM Matriculas m, MatriculasAlteracoes ma 
                            WHERE m.codigo = ma.matricula 
                            AND atribuicao = n.atribuicao 
                            AND ma.data = (SELECT MAX(data) FROM MatriculasAlteracoes WHERE matricula = m.codigo) 
                            AND (ma.situacao = 1 OR ma.situacao = 100)) as total
                    FROM NotasFinais n
                    WHERE n.atribuicao = :atribuicao
                    AND n.bimestre = :bimestre
                    $sqlMatricula
                    AND (flag = 0 OR flag = 5)";
//echo $sql;
        $res = $bd->selectDB($sql, $params);
//var_dump($params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // USADO POR: PROFESSOR/NOTA.PHP
    // VERIFICA SE O ALUNO TEM RECUPERACAO
    public function checkIfRecuperacao($atribuicao, $matricula) {
        $bd = new database();

        // efetuando a consulta para listagem
        $sql = "SELECT recuperacao
                    FROM NotasFinais n
                    WHERE n.atribuicao = :atribuicao
                    AND n.matricula = :matricula
                    AND n.recuperacao >= 1
                    ORDER BY n.codigo ";

        $params = array('atribuicao' => $atribuicao, 'matricula' => $matricula);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]['recuperacao']) {
            return $res[0]['recuperacao'];
        } else {
            return false;
        }
    }
    
    // USADO POR: PROFESSOR/NOTA.PHP
    // VERIFICA SE O RODA FOI EXECUTADO
    public function checkIfRoda($atribuicao) {
        $bd = new database();

        // efetuando a consulta para listagem
        $sql = "SELECT COUNT(*) as reg, (SELECT COUNT(*) 
                                    FROM NotasFinais n1
                                    WHERE n1.atribuicao = n.atribuicao
                                    AND n1.recuperacao IS NOT NULL)  as total,
                                    (SELECT COUNT(*) 
                                    FROM NotasFinais n1
                                    WHERE n1.atribuicao = n.atribuicao
                                    AND n1.situacao IS NOT NULL and n1.situacao!='Em Curso')  as situacoes,
                        (SELECT COUNT(*) 
                                    FROM NotasFinais n1
                                    WHERE n1.atribuicao = n.atribuicao
                                    AND n1.recuperacao = 2) as totalRec,
                        (SELECT COUNT(*) 
                                    FROM NotasFinais n1
                                    WHERE n1.atribuicao = n.atribuicao
                                    AND n1.rec != 0) as notasRec
                    FROM NotasFinais n
                    WHERE n.atribuicao = :atribuicao
                    ORDER BY n.codigo ";

        $params = array('atribuicao' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res[0];
        } else {
            return false;
        }
    }
    
    // USADO POR: HOME.PHP
    // VERIFICA SE O RODA FOI EXECUTADO
    public function checkIfRodaDisciplinas($professor) {
        $bd = new database();

        // efetuando a consulta para listagem
        $sql = "SELECT n.atribuicao, d.nome
                    FROM NotasFinais n, Atribuicoes a, Professores p, Disciplinas d
                    WHERE n.atribuicao = a.codigo
                    AND p.atribuicao = a.codigo
                    AND a.disciplina = d.codigo
                    AND p.professor = :professor
                    AND n.recuperacao = 1
                    ORDER BY n.codigo ";

        $params = array('professor' => $professor);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // DISCIPLINAS QUE AGUARDAM O RODA
    public function getDisciplinasRoda() {
        $bd = new database();

        // efetuando a consulta para listagem
        $sql = "select pe.nome professor, d.nome disciplina, t.numero turma, c.nome curso, m.nome modalidade, a.codigo
            from Cursos c, Turmas t, Pessoas pe, Professores p, Disciplinas d, NotasFinais n, Atribuicoes a, Modalidades m 
            where c.modalidade=m.codigo and t.curso = c.codigo and a.turma=t.codigo and pe.codigo=p.professor 
            and p.atribuicao=a.codigo and a.disciplina=d.codigo 
            and a.codigo=n.atribuicao and (n.flag=0 or (n.flag=5 and n.situacao='Em Curso' and n.recuperacao is null)) 
            group by a.codigo order by pe.nome"; 
        

//        $params = array('atribuicao' => $atribuicao, 'matricula' => $matricula);
//        $res = $bd->selectDB($sql, $params);
        $res = $bd->selectDB($sql);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    
    
    // VERIFICA SE A ATRIBUICAO ESTÁ AGUARDANDO O RODA
    public function isAguardandoRoda($atribuicao) {
        $bd = new database();

        // efetuando a consulta para listagem
        $sql = "select count(*) n
            from NotasFinais n
            where n.atribuicao=:atribuicao 
            and (n.flag=0 or (n.flag=5 and n.situacao='Em Curso' and n.recuperacao is null)) ";        

        $params = array('atribuicao' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
//        $res = $bd->selectDB($sql);

        if ($res[0]['n']>0) {
            return true;
        } else {
            return false;
        }
    }        
}

?>