<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class PlanosEnsino extends Generic {

    public function __construct() {
        //
    }

    // USADO POR: PROFESSOR/PLANO.PHP, RELATORIOS/PLANO.PHP
    public function getTipoRecuperacao($atribuicao) {
        $bd = new database();
        $sql = "SELECT IF(m.codigo=1001 OR
                        m.codigo=1003 OR 
                        m.codigo < 1001, '7.2 - Reavaliação Final', 
                        '7.2 - Instrumento Final de Avaliação' )
                    as rfTitle
                FROM Atribuicoes a, Modalidades m, Turmas t, Cursos c
                WHERE a.turma = t.codigo
                AND t.curso = c.codigo
                AND c.modalidade = m.codigo
                AND a.codigo = :cod";

        $params = array(':cod' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]['rfTitle']) {
            return $res[0]['rfTitle'];
        } else {
            return false;
        }
    }

    // USADO POR: ALUNO/PLANOENSINO.PHP, PROFESSOR/PLANO.PHP,
    // HOME.PHP, SECRETARIA/PLANO.PHP
    // LISTA O PLANO DE ENSINO E PLANO DE AULA
    public function listPlanoEnsino($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        $nav = null;        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT pe.codigo, pe.numeroAulaSemanal as numeroAulaSemanal,
                pe.totalHoras as totalHoras, pe.totalAulas as totalAulas,
                pe.numeroProfessores as numeroProfessores,
		pe.ementa as ementa, pe.objetivoGeral as objetivoGeral,
                pe.objetivoEspecifico as objetivoEspecifico, 
                pe.conteudoProgramatico as conteudoProgramatico,
                pe.metodologia as metodologia,
                pe.recursoDidatico as recursoDidatico, pe.avaliacao as avaliacao,
                pe.recuperacaoParalela as recuperacaoParalela, pe.recuperacaoFinal as recuperacaoFinal,
                IF(LENGTH(a.subturma) > 0,CONCAT(' [',a.subturma,']'),CONCAT(' [',a.eventod,']')) as subturma, 
		pe.bibliografiaBasica as bibliografiaBasica,
                pe.bibliografiaComplementar as bibliografiaComplementar,
                date_format(pe.valido, '%d/%m/%Y %H:%i') as valido,
                date_format(pe.finalizado, '%d/%m/%Y %H:%i') as finalizado,
		d.nome as disciplina, d.ch as ch, d.numero as numero, a.codigo as atribuicao,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                m.nome as modalidade, m.codigo as codModalidade, t.numero as turma,
                c.codigo as codCurso, p.professor as codProfessor, t.codigo as codTurma,
                t.ano, t.semestre, (SELECT nome FROM Turnos WHERE codigo = a.periodo) as turno,
                (SELECT l.solicitacao FROM LogSolicitacoes l
                         WHERE l.codigoTabela = a.codigo
                         AND l.nomeTabela = 'PlanoEnsino'
                         AND l.dataConcessao IS NULL) as solicitacao,
                (SELECT p.nome FROM LogSolicitacoes l, Pessoas p
                         WHERE p.codigo = l.solicitante
                         AND l.codigoTabela = a.codigo
                         AND l.nomeTabela = 'PlanoEnsino'
                         AND l.dataConcessao IS NULL) as solicitante
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
		    p.ementa,p.objetivoGeral,p.objetivoEspecifico,p.conteudoProgramatico,p.metodologia,
                    p.recursoDidatico,p.avaliacao,
		    p.recuperacaoParalela,p.recuperacaoFinal,p.bibliografiaBasica,p.bibliografiaComplementar
		    ,NULL,NULL
                    FROM PlanosEnsino p
                    WHERE p.atribuicao=:copia";

        $params = array(':cod' => $codigo, ':copia' => $copia);
        $res = $bd->insertDB($sql, $params);

        $sql = "INSERT INTO PlanosAula 
			SELECT 
			NULL,:cod,p.semana,p.conteudo,p.criterio,p.metodologia
			FROM PlanosAula p
			WHERE p.atribuicao=:copia";

        $params = array(':cod' => $codigo, ':copia' => $copia);
        $res = $bd->insertDB($sql, $params);

        $res['TIPO'].="_PLANO";
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: PROFESSOR/PLANO.PHP
    public function entregarPlano($codigo) {
        $bd = new database();
        $sql = "UPDATE PlanosEnsino SET finalizado=NOW(), valido='' WHERE atribuicao = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->updateDB($sql, $params);
        if ($res) {
            //REGISTRANDO NO LOG DE SOLICITACOES
            $l['codigo'] = $codigo;
            $l['nome'] = 'PlanoEnsino';
            $l['data'] = date('Y:m:d H:i:s');
            $log = new LogSolicitacoes();
            $log->updateSolicitacao($l);
            
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: PROFESSOR/PLANO.PHP
    // LISTA OS PLANOS EQUIVALENTES
    public function getPlanoEquivalente($codigo) {
        $bd = new database();
        $sql = "SELECT a.codigo, d.nome, t.numero, t.ano, t.semestre, a.eventod, a.subturma, c.nome as curso
        		FROM PlanosEnsino pe, PlanosAula pa, Disciplinas d, Atribuicoes a, Turmas t, Cursos c
        		WHERE pe.atribuicao = pa.atribuicao
        		AND pe.atribuicao = a.codigo
        		AND a.disciplina = d.codigo
        		AND a.turma = t.codigo
                        AND t.curso = c.codigo
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
    
    // USADO POR: HOME.PHP
    // Verifica se o usuário tem correções no Plano de Ensino
    public function hasChangePE($params, $sqlAdicional=null) {
        $bd = new database();
        $sql = "SELECT (SELECT nome FROM Pessoas "
                . "WHERE codigo = l.solicitante) as solicitante, "
                . "l.solicitacao, d.nome as disciplina, p.atribuicao "
                . "FROM PlanosEnsino p, LogSolicitacoes l, Professores pr, Atribuicoes a, Disciplinas d "
                . "WHERE l.nometabela = 'PlanoEnsino' "
                . "AND l.codigoTabela = p.atribuicao "
                . "AND pr.atribuicao = p.atribuicao "
                . "AND p.atribuicao = a.codigo "
                . "AND a.disciplina = d.codigo "
                . "AND p.valido = '0000-00-00 00:00:00' "
                . "AND p.finalizado = '0000-00-00 00:00:00' "
                . "AND l.dataConcessao = '0000-00-00 00:00:00' ";

        $sql .= $sqlAdicional;
        
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    

}

?>