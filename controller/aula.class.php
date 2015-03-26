<?php

if (!class_exists('Frequencias'))
    require_once CONTROLLER . '/frequencia.class.php';

class Aulas extends Frequencias {

    public function __construct() {
        //
    }

    // LISTA OS CONTEUDOS DAS AULAS DO ALUNO
    // USADO POR: VIEW/ALUNO/AULA.PHP, VIEW/SECRETARIA/RELATORIOS/DIARIO.PHP
    public function listAulasAluno($aula, $aluno, $sigla = null, $data = null) {
        $bd = new database();

        if ($data) $sqlAdicional = ' AND a.data = :data ';
        
        $sql = "SELECT (
                    SELECT quantidade
			FROM Frequencias f, Matriculas m
			WHERE f.matricula = m.codigo
                        AND f.aula = a.codigo
			AND m.aluno = :aluno
                        AND m.atribuicao = a.atribuicao
                    LIMIT 1) as quantidade, 
                    quantidade as auladada,a.data, a.atribuicao,
                    date_format(a.data, '%d/%m/%Y') as dataFormatada,
                    a.conteudo as conteudo
		FROM Aulas a
		WHERE a.codigo = :aula
                $sqlAdicional";
        
        $params = array('aluno' => $aluno, ':aula' => $aula);
        if ($data) $params['data'] = $data;
        
        $res = $bd->selectDB($sql, $params);

        if ($res) {

            if (!class_exists('MatriculasAlteracoes'))
                require_once CONTROLLER . '/matriculaAlteracao.class.php';
            $ma = new MatriculasAlteracoes();

            $i = 0;
            foreach ($res as $reg) {
                $M = $ma->getAlteracaoMatricula($aluno, $reg['atribuicao'], $reg['data']);
                if ($M['habilitar'] || !$M) {
                    if ($A = $this->getFrequenciaAbono($aluno, $reg['atribuicao'], $reg['data'])) {
                        $res[$i]['falta'] = (!$sigla) ? $A['tipo'] : $A['sigla'];
                    } else {
                        if ($reg['quantidade'])
                            $res[$i]['falta'] = $reg['quantidade'];
                        else
                            $res[$i]['falta'] = str_repeat('*', $reg['auladada']);
                    }
                } else {
                    $res[$i]['falta'] = (!$sigla) ? $M['tipo'] : $M['sigla'];
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
    public function listAulasProfessor($atribuicao, $order = null) {
        $bd = new database();

        $sql = "SELECT date_format(data, '%d/%m/%Y') data_formatada,
                    a.quantidade, a.codigo, a.conteudo, a.data, 
                    d.nome as disciplina,  t.numero as turma,
                    at.status, DATEDIFF(at.prazo, NOW()) as prazo,
                    DATE_FORMAT(data, '%d') as dia, at.competencias, at.observacoes,
                    DATE_FORMAT(data, '%m') as mes, a.atividade,
                    (SELECT SUM(quantidade) FROM Aulas WHERE atribuicao = at.codigo) as aulasDadas,
                    (SELECT COUNT(*) FROM Aulas WHERE atribuicao = at.codigo) as dias
                    FROM Disciplinas d, Turmas t, Atribuicoes at
                    LEFT JOIN Aulas a ON a.atribuicao=at.codigo 
                    WHERE at.disciplina=d.codigo 
                    AND at.turma=t.codigo 
                    AND at.codigo= :cod
                    $order";

        $params = array(':cod' => $atribuicao);
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
    // USADO POR: VIEW/PROFESSOR/FREQUENCIA.PHP, VIEW/COMMON/CHAT.PHP
    public function listAlunosByAula($params, $sqlAdicional) {
        $bd = new database();

        $sql = "SELECT f.quantidade as frequencia, al.codigo as codAluno, 
                al.nome as aluno, m.codigo as matricula, a.turma, a.bimestre, 
                au.quantidade as aulaQde, f.codigo as freqCodigo, al.prontuario,
                (SELECT s.nome FROM Situacoes s, MatriculasAlteracoes m1 
                    WHERE m1.matricula = m.codigo 
                    AND s.codigo = m1.situacao 
                    ORDER BY m1.data DESC LIMIT 1) as situacao               
		FROM Atribuicoes a 
		left join Aulas au on au.atribuicao=a.codigo 
		left join Matriculas m on m.atribuicao=a.codigo 
		left join Frequencias f on f.aula=au.codigo and f.matricula=m.codigo 
		left join Pessoas al on m.aluno=al.codigo 
		$sqlAdicional";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // LISTA OS PROFESSORES X LANÇAMENTO DE AULAS
    // USADO POR: VIEW/SECRETARIA/RELATORIOS/LANCAMENTOS.PHP
    public function listLancamentoAula($params, $sqlAdicional) {
        $bd = new database();

        $sql = "SELECT SUBSTRING(p.nome, 1, 37) as professor, p.prontuario,
                CONCAT(d.nome,' [',IFNULL(a.subturma, a.eventod),']', 
                IF(a.bimestre>0, CONCAT(' [',a.bimestre,' BIM]'), '') ) as disciplina, d.ch,
                (SELECT SUM(quantidade) FROM Aulas au WHERE au.atribuicao = a.codigo) as aulas,
                t.numero, a.aulaPrevista,
                SUBSTRING(c.nome, 1, 27) as curso
                FROM Disciplinas d, Cursos c, Atribuicoes a, 
                    Pessoas p, Turmas t, Professores pr
                WHERE d.curso = c.codigo
                AND p.codigo = pr.professor
                AND pr.atribuicao = a.codigo
                AND t.codigo = a.turma
                AND a.disciplina = d.codigo
                $sqlAdicional";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // LISTA OS PROFESSORES X LANÇAMENTO DE AULAS
    // USADO POR: VIEW/SECRETARIA/RELATORIOS/LISTAGEM.PHP
    public function listLAToJSON($params, $sqlAdicional) {
        $sqlAdicional .= ' AND t.ano=:ano AND (t.semestre=:semestre OR t.semestre=0) GROUP BY a.codigo ORDER BY p.nome ';
        $res = $this->listLancamentoAula($params, $sqlAdicional);

        foreach ($res as $reg) {
            $item1[] = $reg['prontuario'];
            $item2[] = intval(($reg['aulaPrevista']) ? $reg['aulaPrevista'] : $reg['ch']);
            $item3[] = intval( ($reg['aulas']) ? $reg['aulas'] : 0 );
        }

        $graph_data = array('item1Name' => 'Prontuário', 'item1' => $item1,
            'item2Name' => 'Aulas Previstas/CH', 'item2' => $item2,
            'item3Name' => 'Aulas Dadas', 'item3' => $item3,
            'title' => 'Lançamento de Aulas', 'titleY' => 'Quantidade', 'titleX' => 'Prontuário');

        return json_encode($graph_data);
    }

}

?>