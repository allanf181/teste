<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Ensalamentos extends Generic {

    public function __construct() {
        //
    }

    public function listEnsalamentos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT e.codigo, p.nome as professor, d.numero as discNumero, h.nome as horario,
                        t.numero as turma, e.diaSemana, a.codigo as atribuicao,
                        IF(LENGTH(a.subturma) > 0,a.subturma,a.eventod) as subturma,
                        (SELECT nome FROM Turnos WHERE codigo = a.periodo) as turno,
                        IF(LENGTH(s.localizacao) > 0, CONCAT(s.nome, '-',s.localizacao), s.nome) as sala,
                        h.nome as horario, date_format(h.inicio, '%H:%i') as inicio,
                        date_format(h.fim, '%H:%i') as fim, d.nome as disciplina
	            	FROM Atribuicoes a, Pessoas p, Turmas t, Disciplinas d, 
                            Ensalamentos e, Horarios h, Salas s, Professores pr
	                WHERE pr.atribuicao = a.codigo
   			AND pr.professor = p.codigo
	                AND a.turma = t.codigo 
	                AND e.atribuicao = a.codigo 
	                AND h.codigo = e.horario
	                AND a.disciplina = d.codigo 
	                AND s.codigo = e.sala
	                AND t.ano = :ano
	                AND (t.semestre = :semestre OR t.semestre=0)";

        $sql .= " $sqlAdicional ";

        $sql .= "$nav";
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        else
            return false;
    }

    // MÉTODO PARA INSERÇÃO DE OBJETO
    // USADO POR: VIEW/COMMON/ENSALAMENTO.PHP
    public function getEnsalamento($codigo, $tipo, $ano, $semestre, $subturma = null) {
        $bd = new database();

        $professor = "AND pr.professor =:cod ";

        $aluno = "AND m.aluno =:cod ";
        if ($tipo == 'aluno') {
            $sql1 = ", Matriculas m";
            $sql2 = "AND m.atribuicao = a.codigo";
        }

        $atribuicao = "AND a.codigo = :cod ";

        $turma = "AND t.codigo IN (SELECT t1.codigo FROM Turmas t1 
				WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
				WHERE t2.codigo = :cod))";

        if ($subturma && !is_numeric($subturma))
            $subSQL = " AND (a.subturma = :sub OR a.subturma = 'ABCD')";
        else
            $subturma = '';

        $sql = "SELECT diaSemana, date_format(h.inicio, '%H:%i') as inicio,
                        date_format(h.fim, '%H:%i') as fim, d.numero as discNumero,
                        d.nome as disciplina, p.nome as professor,
                        IF(LENGTH(s.localizacao) > 0, CONCAT(s.nome, '-',s.localizacao), s.nome) as sala,
                        a.codigo as atribuicao, t.numero as turma,
                        IF(LENGTH(a.subturma) > 0,a.subturma,a.eventod) as subturma,
                        s.localizacao as localizacao, h.nome as horario,
                        h.codigo as horCodigo
		FROM Ensalamentos e, Disciplinas d, Salas s, Horarios h, 
                    Pessoas p, Atribuicoes a, Turmas t, Professores pr $sql1
    		WHERE a.codigo = e.atribuicao
                    AND a.disciplina = d.codigo
                    AND e.sala = s.codigo
                    AND e.horario = h.codigo
                    AND a.turma = t.codigo
                    AND pr.professor = e.professor
                    AND pr.professor = p.codigo
                    AND pr.atribuicao = a.codigo
                    AND t.ano = :ano
                    AND (t.semestre = :sem OR t.semestre = 0)
                    $sql2
                    " . $$tipo . " " . $subSQL . "
                GROUP BY diaSemana, h.inicio, h.fim, d.numero, s.nome
		ORDER BY h.inicio, h.fim";

        $params = array(':cod' => $codigo, ':ano' => $ano, ':sem' => $semestre);
        if ($subturma)
            $params = array(':cod' => $codigo, ':sub' => $subturma,
                ':ano' => $ano, ':sem' => $semestre);

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function getEnsalamentosFTD($pessoa, $ano, $semestre) {
        $bd = new database();


        $sql = "SELECT e.diaSemana, h.nome, date_format(h.inicio, '%H:%i') as ini, 
                    date_format(h.fim, '%H:%i') as fim, p.telefone, p.celular, p.email
                    FROM Ensalamentos e, Horarios h, Pessoas p, Atribuicoes a, Turmas t, Professores pr
		    WHERE h.codigo = e.horario
                    AND a.turma = t.codigo
                    AND pr.atribuicao = a.codigo
		    AND pr.professor = e.professor
		    AND p.codigo = e.professor
                    AND e.atribuicao = a.codigo
		    AND t.ano = :ano 
                    AND (t.semestre = :sem OR t.semestre = 0)
                    AND e.professor = :pessoa 
		    GROUP BY h.inicio, h.fim, e.diaSemana
		    ORDER BY e.diaSemana, h.inicio ASC";

        $params = array(':pessoa' => $pessoa, ':ano' => $ano, ':sem' => $semestre);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function getAulasByProfessor($professor, $atribuicao) {
        $bd = new database();


        $sql = "SELECT CONCAT(h.nome, ' [', date_format(h.inicio, '%H:%i'),'-', 
                    date_format(h.fim, '%H:%i'), ']') as horario, e.diaSemana
                    FROM Ensalamentos e, Horarios h, Atribuicoes a
		    WHERE h.codigo = e.horario
                    AND e.atribuicao = a.codigo
                    AND a.codigo = :atribuicao
                    AND e.professor = :professor 
		    ORDER BY e.diaSemana, h.inicio ASC";

        $params = array(':atribuicao' => $atribuicao, ':professor' => $professor);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    

}

?>