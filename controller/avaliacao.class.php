<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Avaliacoes extends Generic {

    public function __construct() {
        //
    }

    // LISTA AVALIACOES DO ALUNO
    // USADO POR: VIEW/ALUNO/AVALIACAO.PHP, BOLETIM.PHP
    public function listAvaliacoesAluno($params, $sqlAdicional=null) {
        $bd = new database();

        $sql = "SELECT av.nome, av.sigla, ti.nome as tipoAval,
                ti.tipo, DATE_FORMAT(av.data, '%d/%m/%Y') as data, 
                n.nota as nota, UPPER(a.calculo) as calculo,
                IF(av.peso > 0, av.peso, '') as peso, a.formula,
                ti.tipo as avaliacao, UPPER(ti.calculo) as avalCalculo
    		FROM Atribuicoes a 
    		left join Avaliacoes av on av.atribuicao=a.codigo 
    		left join Matriculas m on m.atribuicao=a.codigo 
    		left join Notas n on n.avaliacao=av.codigo and n.matricula=m.codigo
		left join TiposAvaliacoes ti on av.tipo=ti.codigo
 		left join Pessoas al on m.aluno=al.codigo 
 		WHERE a.codigo=:atribuicao 
 		AND m.aluno=:aluno
                $sqlAdicional";

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
		DATEDIFF(prazo, NOW()) as prazo, at.calculo, ti.tipo, c.modalidade,
		(SELECT calculo FROM TiposAvaliacoes WHERE codigo = a.tipo AND tipo='recuperacao') as recuperacao,
		(SELECT SUM(peso) FROM Avaliacoes a1, TiposAvaliacoes t1 
                        WHERE a1.tipo = t1.codigo AND t1.tipo = 'avaliacao' AND a1.atribuicao = at.codigo) as totalPeso,
		(SELECT SUM(peso) FROM Avaliacoes a1, TiposAvaliacoes t1 
                        WHERE a1.tipo = t1.codigo AND t1.tipo = 'pontoExtra' AND a1.atribuicao = at.codigo) as totalPonto,                        
		(SELECT final FROM TiposAvaliacoes WHERE codigo = a.tipo AND tipo='recuperacao' AND final=1) as final,
		at.bimestre, a.sigla, t.numero,
                (SELECT CONCAT(nome, ' (', sigla,')') FROM Avaliacoes WHERE codigo = a.substitutiva) as substitutiva
            FROM Turnos tu, Cursos c, Turmas t, Disciplinas d, Atribuicoes at
            LEFT JOIN Avaliacoes a ON a.atribuicao=at.codigo $sqlAdicional
            LEFT JOIN TiposAvaliacoes ti ON ti.codigo = a.tipo
            WHERE at.codigo = :atr
            AND at.turma=t.codigo 
            AND t.curso = c.codigo
            AND at.disciplina=d.codigo 
            AND t.turno=tu.codigo
            ORDER BY a.data desc, a.nome";

        $params = array(':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: VIEW/PROFESSOR/PROFESSOR.PHP
    public function getQdeAvaliacoes($params, $sqlAdicional) {
        $bd = new database();

        $sql = "SELECT (SELECT count(av.codigo) 
                        FROM Avaliacoes av, TiposAvaliacoes t1 
                        WHERE av.tipo = t1.codigo 
                        AND av.atribuicao = a.codigo 
                        AND t1.tipo <> 'recuperacao') as avalCadastradas,
    		t.qdeMinima as qdeMinima
		FROM TiposAvaliacoes t, Modalidades m, Turmas tu, Cursos c, Atribuicoes a
		WHERE t.modalidade = m.codigo
		AND c.codigo = tu.curso
		AND c.modalidade = m.codigo
		AND a.turma = tu.codigo
		AND a.codigo = :atribuicao
		$sqlAdicional";

        $res = $bd->selectDB($sql, $params);
        if ($res[0]) {
            return $res[0];
        } else {
            return false;
        }
    }

    // USADO POR: VIEW/PROFESSOR/NOTA.PHP
    public function getAvaliacao($avaliacao) {
        $bd = new database();

        $sql = "SELECT IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                t.codigo as turmaCodigo, t.ano as ano, t.semestre as semestre,
                av.codigo as avalCodigo, date_format(av.data, '%d/%m/%Y') dataFormat,
                av.peso as peso, av.nome as nome, ti.tipo as tipo, av.data,
		DATEDIFF(a.prazo, NOW()) as prazo, a.status as status,
		d.numero as discNumero, a.bimestre as bimestre, ti.final,
                t.numero as turma, a.calculo as calculo, ti.sigla,
                IF(ti.tipo NOT LIKE 'recuperacao' 
                    AND (a.calculo LIKE 'soma' OR ti.tipo LIKE 'pontoExtra'), av.peso, ti.notaMaxima) as notaMaxima
 		FROM Atribuicoes a, Disciplinas d, Turmas t, Cursos c, Turnos tu,
                    Avaliacoes av, TiposAvaliacoes ti
 		WHERE a.disciplina=d.codigo 
 		AND a.turma=t.codigo 
 		AND av.atribuicao=a.codigo 
 		AND t.curso=c.codigo 
 		AND ti.codigo = av.tipo
 		AND t.turno=tu.codigo 
 		AND av.codigo=:codigo";

        $params = array(':codigo' => $avaliacao);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]) {
            return $res[0];
        } else {
            return false;
        }
    }

    // USADO POR: VIEW/PROFESSOR/NOTA.PHP
    public function getNotasAlunosOfAvaliacao($atribuicao, $avaliacao) {
        $bd = new database();
        $sql = "SELECT al.codigo as codAluno, m.codigo as matricula,
                n.nota, al.nome as aluno, a.turma, a.bimestre, al.prontuario, 
                n.codigo as codNota
		FROM Atribuicoes a 
		left join Avaliacoes av on av.atribuicao=a.codigo 
		left join Matriculas m on m.atribuicao=a.codigo 
		left join Notas n on n.avaliacao=av.codigo and n.matricula=m.codigo 
		left join Pessoas al on m.aluno=al.codigo 
		WHERE a.codigo=:atribuicao 
                AND av.codigo=:avaliacao 
                GROUP BY m.aluno
                ORDER BY al.nome";
        $params = array(':atribuicao' => $atribuicao, ':avaliacao' => $avaliacao);

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: VIEW/SECRETARIA/RELATORIOS/DIARIO.PHP
    public function getAvaliacoes($atribuicao) {
        $bd = new database();

        $sql = "SELECT a.codigo,a.sigla,a.nome,
                    date_format(a.data, '%d/%m/%Y') as data
                    FROM Avaliacoes a, TiposAvaliacoes t 
                    WHERE a.tipo = t.codigo 
                    AND atribuicao = :atribuicao 
                    AND t.tipo <> 'recuperacao'";

        $params = array('atribuicao' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    
}

?>