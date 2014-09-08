<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class PrazosDiarios extends Generic {

    public function __construct() {
        //
    }
    
    // USADO POR: PROFESSOR/PROFESSOR.PHP
    // Retorna os prazos já solicitados
    public function listPrazos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";
        
        $sql = "SELECT date_format(pd.data, '%d/%m/%Y às %H:%m:%s') as data,
                IF(pd.dataConcessao IS NULL,
                    'Aguardando liberação do coordenador...',
                    date_format(pd.dataConcessao, '%d/%m/%Y às %H:%m:%s')) as dataConcessao,
                    d.nome as disciplina, a.codigo as atribuicao,
                    c.codigo as codCurso, t.codigo as turma,
                    p.professor as codProfessor,pd.motivo, dataConcessao as dConcessao
                FROM PrazosDiarios pd, Atribuicoes a, Turmas t, 
                    Disciplinas d, Cursos c, Professores p
                WHERE pd.atribuicao = a.codigo
                AND a.turma = t.codigo
                AND t.curso = c.codigo
                AND p.atribuicao = a.codigo
                AND a.disciplina = d.codigo
                AND t.ano=:ano
                AND (t.semestre=:semestre OR t.semestre=0)";

        $sql .= " $sqlAdicional ";

        $sql .= " GROUP BY pd.codigo ORDER BY pd.data, pd.dataConcessao DESC ";
        
        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }
}

?>