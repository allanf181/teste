<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class PlanosEnsino extends Generic {

    public function __construct() {
        //
    }

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem correções para Plano
    // Pode ser colocado com função no MySQL futuramente
    public function hasChangePlano($codigo, $atribuicao = null) {
        $bd = new database();

        if ($atribuicao)
            $sqlAtt = " AND a.codigo = :atr";

        $sql = "SELECT (SELECT nome FROM Pessoas "
                . "WHERE codigo = pe.solicitante) as PlanoSolicitante,"
                . " pe.solicitacao as PlanoSolicitacao, d.nome as Disc, "
                . "a.codigo as CodAtribuicao "
                . "FROM PlanosEnsino pe, Atribuicoes a, Pessoas p, "
                . "Professores pr, Disciplinas d "
                . "WHERE pe.atribuicao = a.codigo "
                . "AND pr.atribuicao = a.codigo "
                . "AND pr.professor = p.codigo "
                . "AND d.codigo = a.disciplina "
                . "AND pe.valido = '0000-00-00 00:00:00' "
                . "AND (pe.solicitacao IS NOT NULL AND pe.solicitacao <> \"\") "
                . "AND p.codigo = :cod "
                . " $sqlAtt";

        $params = array(':cod' => $codigo);
        if ($atribuicao)
            $params = array(':cod' => $codigo, ':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: ALUNO/PLANOENSINO.PHP, PROFESSOR/PLANO.PHP, SECRETARIA/PLANO.PHP
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
                as rfTitle,
		pe.bibliografiaBasica as bibliografiaBasica,
                pe.bibliografiaComplementar as bibliografiaComplementar,
                date_format(pe.valido, '%d/%m/%Y %H:%i') as valido,
                (SELECT ps.nome FROM Pessoas ps WHERE ps.codigo = pe.solicitante) as solicitante,
		d.nome as disciplina, d.ch as ch, d.numero as numero, a.codigo as atribuicao,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                m.nome as modalidade, m.codigo as codModalidade, t.numero as turma
		FROM PlanosEnsino pe, Atribuicoes a, Disciplinas d,
		Cursos c, Modalidades m, Turmas t, Professores p
		WHERE pe.atribuicao = a.codigo 
		AND d.codigo = a.disciplina
		AND a.turma = t.codigo
		AND t.curso = c.codigo
		AND c.modalidade = m.codigo
                AND p.atribuicao = a.codigo";
        
        $sql .= " $sqlAdicional ";
        $sql .= ' ORDER BY d.nome ';
        $sql .= "$nav";
        
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: HOME.PHP
    // Mostra para o coordenador os professores com Planos para Alterar.
    // Pode ser colocado com função no MySQL futuramente
    public function listChangePlano($codigo, $ano, $semestre) {
        $bd = new database();

        $sql = "SELECT p.nome as Professor, d.nome as Disciplina, "
                . "c.codigo as codCurso, t.codigo as turma "
                . "FROM PlanosEnsino pe, Atribuicoes a, Pessoas p, "
                . "Professores pr, Cursos c, Turmas t, Disciplinas d "
                . "WHERE pe.atribuicao = a.codigo "
                . "AND pr.atribuicao = a.codigo "
                . "AND pr.professor = p.codigo "
                . "AND d.codigo = a.disciplina "
                . "AND a.turma = t.codigo "
                . "AND t.curso = c.codigo "
                . "AND t.ano = :ano "
                . "AND (t.semestre=:sem OR t.semestre=0)"                
                . "AND pe.finalizado <> '0000-00-00 00:00:00' "
                . "AND pe.valido = '0000-00-00 00:00:00' "
                . "AND c.codigo IN (SELECT curso FROM Coordenadores WHERE coordenador = :cod) ";

        $params = array(':cod' => $codigo, ':ano' => $ano, ':sem' => $semestre);
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