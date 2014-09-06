<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class PlanosEnsino extends Generic {

    public function __construct() {
        //
    }

    // USADO POR: ALUNO/PLANOENSINO.PHP, PROFESSOR/PLANO.PHP,
    // HOME.PHP, SECRETARIA/PLANO.PHP
    // LISTA O PLANO DE ENSINO E PLANO DE AULA
    public function listPlanoEnsino($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT pe.codigo, pe.numeroAulaSemanal as numeroAulaSemanal,
                pe.totalHoras as totalHoras, pe.totalAulas as totalAulas,
                pe.numeroProfessores as numeroProfessores,
		pe.ementa as ementa, pe.objetivo as objetivo, 
                pe.conteudoProgramatico as conteudoProgramatico,
                pe.metodologia as metodologia,pe.solicitacao,pe.finalizado,
                pe.recursoDidatico as recursoDidatico, pe.avaliacao as avaliacao,
                pe.recuperacaoParalela as recuperacaoParalela, pe.recuperacaoFinal as recuperacaoFinal,
                IF(m.codigo=1001 OR
                   m.codigo=1003 OR 
                   m.codigo < 1001, 'Reavaliação Final', 'Instrumento Final de Avaliação' )
                as rfTitle,IF(LENGTH(a.subturma) > 0,CONCAT(' [',a.subturma,']'),CONCAT(' [',a.eventod,']')) as subturma, 
		pe.bibliografiaBasica as bibliografiaBasica,
                pe.bibliografiaComplementar as bibliografiaComplementar,
                date_format(pe.valido, '%d/%m/%Y %H:%i') as valido,
                (SELECT ps.nome FROM Pessoas ps WHERE ps.codigo = pe.solicitante) as solicitante,
		d.nome as disciplina, d.ch as ch, d.numero as numero, a.codigo as atribuicao,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                m.nome as modalidade, m.codigo as codModalidade, t.numero as turma,
                c.codigo as codCurso, p.professor as codProfessor, t.codigo as codTurma
		FROM PlanosEnsino pe, Atribuicoes a, Disciplinas d,
		Cursos c, Modalidades m, Turmas t, Professores p
		WHERE pe.atribuicao = a.codigo 
		AND d.codigo = a.disciplina
		AND a.turma = t.codigo
		AND t.curso = c.codigo
		AND c.modalidade = m.codigo
                AND p.atribuicao = a.codigo";
        
        $sql .= " $sqlAdicional ";
        $sql .= ' GROUP BY a.codigo ORDER BY d.nome';
        $sql .= "$nav";
 
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: PROFESSOR/PLANO.PHP
    // COPIA O PLANO DE ENSINO DE DISCIPLINAS EQUIVALENTES
    public function copyPlano($codigo, $copia) {
        $bd = new database();
        // DELETANDO OS PLANOS ANTIGOS
        $sql = "DELETE FROM PlanosAula WHERE atribuicao=:cod";
        $params = array(':cod' => $codigo);
        $res = $bd->deleteDB($sql, $params);

        $sql = "DELETE FROM PlanosEnsino WHERE atribuicao=:cod";
        $params = array(':cod' => $codigo);
        $res = $bd->deleteDB($sql, $params);

        $sql = "INSERT INTO PlanosEnsino 
                    SELECT 
		    NULL,:cod,p.numeroAulaSemanal,p.totalHoras,p.totalAulas,p.numeroProfessores,
		    p.ementa,p.objetivo,p.conteudoProgramatico,p.metodologia,p.recursoDidatico,p.avaliacao,
		    p.recuperacaoParalela,p.recuperacaoFinal,p.bibliografiaBasica,p.bibliografiaComplementar
		    ,NULL,NULL,NULL,NULL
                    FROM PlanosEnsino p
                    WHERE p.atribuicao=:copia";

        $params = array(':cod' => $codigo, ':copia' => $copia);
        $res = $bd->insertDB($sql, $params);
       
        $sql = "INSERT INTO PlanosAula 
			SELECT 
			NULL,:cod,p.semana,p.conteudo
			FROM PlanosAula p
			WHERE p.atribuicao=:copia";

        $params = array(':cod' => $codigo, ':copia' => $copia);
        $res = $bd->insertDB($sql, $params);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // USADO POR: PROFESSOR/PLANO.PHP
    public function entregarPlano($codigo) {
        $bd = new database();
        $sql = "UPDATE PlanosEnsino SET finalizado=NOW(), valido='', solicitacao='' WHERE atribuicao = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->updateDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }        

    // USADO POR: PROFESSOR/PLANO.PHP
    // LISTA OS PLANOS EQUIVALENTES
    public function getPlanoEquivalente($codigo) {
        $bd = new database();
        $sql = "SELECT a.codigo, d.nome, t.numero, t.ano, t.semestre, a.eventod, a.subturma 
        		FROM PlanosEnsino pe, PlanosAula pa, Disciplinas d, Atribuicoes a, Turmas t
        		WHERE pe.atribuicao = pa.atribuicao
        		AND pe.atribuicao = a.codigo
        		AND a.disciplina = d.codigo
        		AND a.turma = t.codigo
        		AND d.numero IN (SELECT d1.numero 
        				FROM Disciplinas d1, Atribuicoes a1 
        				WHERE a1.disciplina = d1.codigo 
        				AND d1.numero = d.numero AND a1.codigo = :cod)
        		AND a.codigo <> :cod
        		GROUP BY a.codigo 
        		ORDER BY d.nome";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
}

?>