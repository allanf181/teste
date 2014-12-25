<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Avaliacoes extends Generic {

    public function __construct() {
        //
    }

    // LISTA AVALIACOES DO ALUNO
    // USADO POR: VIEW/ALUNO/AVALIACAO.PHP, BOLETIM.PHP
    public function listAvaliacoesAluno($aluno, $atribuicao) {
        $bd = new database();

        $sql = "SELECT av.nome, av.sigla, ti.nome as tipoAval,
                ti.tipo, DATE_FORMAT(av.data, '%d/%m/%Y') as data, 
                n.nota as nota, UPPER(a.calculo) as calculo,
                IF(av.peso > 0, av.peso, '') as peso
    		FROM Atribuicoes a 
    		left join Avaliacoes av on av.atribuicao=a.codigo 
    		left join Matriculas m on m.atribuicao=a.codigo 
    		left join Notas n on n.avaliacao=av.codigo and n.matricula=m.codigo
		left join TiposAvaliacoes ti on av.tipo=ti.codigo
 		left join Pessoas al on m.aluno=al.codigo 
 		left join Situacoes s on s.codigo = m.situacao 
 		WHERE a.codigo=:atr 
 		AND m.aluno=:aluno
 		ORDER BY al.nome";

        $params = array(':aluno' => $aluno, ':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // LISTA AVALIACOES DO PROFESSOR
    // USADO POR: VIEW/PROFESSOR/AVALIACAO.PHP
    public function listAvaliacoes($atribuicao, $tipo = null) {
        $bd = new database();

        if ($tipo == 'substitutiva')
            $sqlAdicional = " AND ( ( a.codigo NOT IN (SELECT a2.substitutiva FROM Avaliacoes a2 "
                    . "WHERE a2.atribuicao = at.codigo AND a2.substitutiva IS NOT NULL) )"
                    . " AND ( a.substitutiva IS NULL  )"
                    . " AND ( a.tipo IN ( SELECT codigo FROM TiposAvaliacoes WHERE tipo = 'avaliacao') ) )";

        $sql = "SELECT date_format(a.data, '%d/%m/%Y') dataFormatada, a.nome as nome,
		a.peso, a.codigo, d.nome as disciplina, tu.nome as turno, a.data, a.tipo, at.status,
		DATEDIFF(prazo, NOW()) as prazo, at.calculo, ti.tipo,
		(SELECT calculo FROM TiposAvaliacoes WHERE codigo = a.tipo AND tipo='recuperacao') as recuperacao,
		(SELECT SUM(peso) FROM Avaliacoes a1, TiposAvaliacoes t1 
                        WHERE a1.tipo = t1.codigo AND t1.tipo = 'avaliacao' AND a1.atribuicao = at.codigo) as totalPeso,
		(SELECT final FROM TiposAvaliacoes WHERE codigo = a.tipo AND tipo='recuperacao' AND final=1) as final,
		at.bimestre, a.sigla, t.numero,
                (SELECT CONCAT(nome, ' (', sigla,')') FROM Avaliacoes WHERE codigo = a.substitutiva) as substitutiva
                FROM Turnos tu, Turmas t, Disciplinas d, Atribuicoes at
                LEFT JOIN Avaliacoes a ON a.atribuicao=at.codigo $sqlAdicional
            LEFT JOIN TiposAvaliacoes ti ON ti.codigo = a.tipo
            WHERE at.codigo = :atr
            AND at.turma=t.codigo 
            AND at.disciplina=d.codigo 
            AND t.turno=tu.codigo
            ORDER BY a.data DESC";

        $params = array(':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: VIEW/PROFESSOR/PROFESSOR.PHP
    public function getQdeAvaliacoes($atribuicao) {
        $bd = new database();

        $sql = "SELECT (SELECT count(av.codigo) 
                        FROM Avaliacoes av, TiposAvaliacoes t1 
                        WHERE av.tipo = t1.codigo 
                        AND av.atribuicao = a.codigo 
                        AND t1.tipo = 'avaliacao') as avalCadastradas,
    		t.qdeMinima as qdeMinima
		FROM TiposAvaliacoes t, Modalidades m, Turmas tu, Cursos c, Atribuicoes a
		WHERE t.modalidade = m.codigo
		AND c.codigo = tu.curso
		AND c.modalidade = m.codigo
		AND a.turma = tu.codigo
		AND a.codigo = :atr
		AND t.tipo = 'avaliacao'";

        $params = array(':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]) {
            return $res[0];
        } else {
            return false;
        }
    }

}

?>