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
            foreach ($res as $reg) {
                $dados = $this->resultado($reg['matricula'], $atribuicao, 0, 1);

                $params2['atribuicao'] = $atribuicao;
                $params2['matricula'] = $reg['matricula'];
                if ($reg['bimestre'] == 0)
                    $params2['bimestre'] = '00';
                else
                    $params2['bimestre'] = $reg['bimestre'];
                $params2['mcc'] = $dados['mediaAvaliacao'];
                $params2['rec'] = $dados['notaRecuperacao'];
                $params2['ncc'] = $dados['media'];
                $params2['falta'] = $dados['faltas'];

                if ($dados['siglaSituacao'] != 'IFA' && $dados['siglaSituacao'] != 'REF' ) {

                    $sql1 = "SELECT codigo FROM NotasFinais WHERE atribuicao = :cod 
    			AND matricula = :mat
    			AND bimestre = :bim";

                    $params1 = array(':cod' => $atribuicao,
                        ':mat' => $reg['matricula'],
                        ':bim' => $reg['bimestre']);

                    $res1 = $bd->selectDB($sql1, $params1);

                    if ($res1)
                        $params2['codigo'] = $res1[0]['codigo'];
                    else {
                        $params2['atribuicao'] = $atribuicao;
                        $params2['matricula'] = $reg['matricula'];
                    }

                    if (!$this->insertOrUpdate($params2))
                        $erro = 1;
                } else
                    $erro = 2;

                // FECHAMENTO ANUAL BIMESTRAL
                if ($reg['bimestre'] == 4) {
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

                        $params2['bimestre'] = 'M';

                        if (!$this->insertOrUpdate($params2))
                            $erro = 1;
                    }
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
                        n.retorno, n.flag,
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

}

?>