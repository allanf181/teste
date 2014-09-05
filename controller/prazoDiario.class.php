<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class PrazosDiarios extends Generic {

    public function __construct() {
        //
    }
    
    // USADO POR: PROFESSOR/PROFESSOR.PHP
    // Retorna os prazos já solicitados
    public function listPrazos($atribuicao) {
        $bd = new database();
        
        $sql = "SELECT data, motivo, "
                . "IF(dataConcessao IS NULL, 'Aguardando liberação do coordenador...', dataConcessao) as dataConcessao"
                . " FROM PrazosDiarios "
                . "WHERE atribuicao = :att "
                . "ORDER BY data DESC";
        
        $params = array(':att' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }

    // USADO POR: HOME.PHP
    // Retorna os prazos já solicitados
    public function listPrazosToCoord($coordenador, $ano, $semestre) {
        $bd = new database();
        
        $sql = "SELECT p.data, p.motivo, ps.nome as professor, d.nome as disciplina, t.codigo as turma, "
                . "IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso, c.codigo as codCurso, "
                . "IF(dataConcessao IS NULL, 'Aguardando liberação do coordenador...', dataConcessao) as dataConcessao"
                . " FROM PrazosDiarios p, Atribuicoes a, Turmas t, Cursos c, Professores pr, Pessoas ps, Disciplinas d "
                . "WHERE p.atribuicao = a.codigo "
                . "AND a.turma = t.codigo "
                . "AND t.curso = c.codigo "
                . "AND pr.atribuicao = a.codigo "
                . "AND pr.professor = ps.codigo "
                . "AND d.codigo = a.disciplina "
                . "AND t.ano = :ano "
                . "AND (t.semestre=:sem OR t.semestre=0) "
                . "AND p.dataConcessao IS NULL "
                . "AND c.codigo IN (SELECT curso FROM Coordenadores WHERE coordenador = :cod) "
                . "GROUP BY ps.codigo, a.codigo, t.codigo "
                . "ORDER BY data DESC";

        $params = array(':cod' => $coordenador, ':ano' => $ano, ':sem' => $semestre);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }
}

?>