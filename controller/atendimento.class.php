<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Atendimento extends Generic {
    
    public function __construct() {
        //
    }
    
    // USADO POR: ATENDIMENTO.PHP, 
    // LISTA OS PROFESSORES QUE CADASTRARAM ATENDIMENTO AO ALUNO
    public function listAtendimento($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT DISTINCT p.codigo, p.nome, p.lattes, a.horario
                    FROM Atendimento a, Pessoas p, PessoasTipos pt, Professores pr
                    WHERE p.codigo = pt.pessoa
                    AND pr.professor = p.codigo
                    AND a.pessoa = p.codigo
                    AND pt.tipo = :tipo
                    AND a.ano = :ano
                    AND a.semestre = :semestre ";

        $sql .= " $sqlAdicional ";

        $sql .= ' ORDER BY p.nome ';

        $sql .= "$nav";
        
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }    
}

?>