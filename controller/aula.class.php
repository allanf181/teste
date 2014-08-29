<?php

if (!class_exists('Frequencias'))
    require_once CONTROLLER . '/frequencia.class.php';

class Aulas extends Frequencias {

    public function __construct() {
        //
    }

    // LISTA OS CONTEUDOS DAS AULAS DO ALUNO
    // USADO POR: VIEW/ALUNO/AULA.PHP
    public function listAulasAluno($aluno, $atribuicao) {
        $bd = new database();

        $sql = "SELECT f.quantidade as quantidade,
                a.data as data, 
                date_format(a.data, '%d/%m/%Y') as dataFormatada,
                a.conteudo as conteudo
            FROM Aulas a, Frequencias f, Matriculas m
            WHERE f.aula = a.codigo
            AND f.matricula = m.codigo
            AND m.aluno = :cod
            AND a.atribuicao = :atr
            ORDER BY a.data, a.codigo";

        $params = array(':cod' => $aluno, ':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            $i = 0;
            foreach ($res as $reg) {
                if ($A = $this->getFrequenciaAbono($aluno, $atribuicao, $reg['data'])) {
                    $res[$i]['falta'] = $A['tipo'];
                } else {
                    $res[$i]['falta'] = $reg['quantidade'];
                }
                $i++;
            }
            return $res;
        } else {
            return false;
        }
    }

    // CONTA A QDE DE AULAS DO PROFESSOR
    // USADO POR: VIEW/PROFESSOR/PROFESSOR.PHP
    public function countQdeAulas($atribuicao) {
        $bd = new database();

        $sql = "SELECT SUM(quantidade) as TOTAL
                FROM Aulas a
            WHERE atribuicao = :atr ";

        $params = array(':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]['TOTAL']) {
            return $res[0]['TOTAL'];
        } else {
            return 0;
        }
    }

    // LISTA AS AULAS AULAS DO PROFESSOR
    // USADO POR: VIEW/PROFESSOR/AULA.PHP
    public function listAulasProfessor($codigo) {
        $bd = new database();

        $sql = "SELECT date_format(data, '%d/%m/%Y') data_formatada,
                    a.quantidade, a.codigo, a.conteudo, a.data, 
                    d.nome as disciplina,  t.numero as turma,
                    at.status, DATEDIFF(at.prazo, NOW()) as prazo,
                    (SELECT SUM(quantidade) FROM Aulas WHERE atribuicao = at.codigo) as aulasDadas,
                    (SELECT COUNT(*) FROM Aulas WHERE atribuicao = at.codigo) as dias
                    FROM Disciplinas d, Turmas t, Atribuicoes at
                    LEFT JOIN Aulas a ON a.atribuicao=at.codigo 
                    WHERE at.disciplina=d.codigo 
                    AND at.turma=t.codigo 
                    AND at.codigo= :cod
                    ORDER BY data DESC";

        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: VIEW/PROFESSOR/FREQUENCIA.PHP
    // RETORNA DADOS DE UMA DETERMINADA AULA
    public function getAula($aula) {
        $bd = new database();

        $sql = "SELECT 
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                t.numero as turma, tu.nome as turno, t.ano, t.semestre, 
		date_format(au.data, '%d/%m/%Y') as dataFormatada, au.quantidade,
		DATEDIFF(a.prazo, NOW()) as prazo, a.status, au.data
 		FROM Atribuicoes a, Disciplinas d, Turmas t, Cursos c, Turnos tu, Aulas au
 		WHERE a.disciplina=d.codigo 
 		AND a.turma=t.codigo
 		AND au.atribuicao=a.codigo
 		AND t.curso=c.codigo 
 		AND t.turno=tu.codigo 
 		AND au.codigo=:codigo";

        $params = array(':codigo' => $aula);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]) {
            return $res[0];
        } else {
            return false;
        }
    }

    // LISTA OS ALUNOS DE UMA AULA ESPECIFICA
    // USADO POR: VIEW/PROFESSOR/FREQUENCIA.PHP
    public function listAlunosByAula($atribuicao, $aula) {
        $bd = new database();

        $sql = "SELECT f.quantidade as frequencia, al.codigo as codAluno, 
                al.nome as aluno, m.codigo as matricula, a.turma, a.bimestre, 
                s.listar, s.habilitar, s.nome as situacao, al.prontuario, 
                au.quantidade as aulaQde, f.codigo as freqCodigo
		FROM Atribuicoes a 
		left join Aulas au on au.atribuicao=a.codigo 
		left join Matriculas m on m.atribuicao=a.codigo 
		left join Frequencias f on f.aula=au.codigo and f.matricula=m.codigo 
		left join Pessoas al on m.aluno=al.codigo 
	        left join Situacoes s on s.codigo = m.situacao 
		WHERE a.codigo=:atribuicao 
                AND au.codigo=:aula 
                ORDER BY al.nome";

        $params = array(':atribuicao' => $atribuicao, ':aula' => $aula);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>