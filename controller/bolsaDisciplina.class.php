<?php

if (!class_exists('Frequencias'))
    require_once CONTROLLER . '/frequencia.class.php';

class BolsasDisciplinas extends Generic {

    public function __construct() {
        //
    }
    
    public function listDisciplinas($params = null, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT bd.codigo, CONCAT('[',d.numero,'] ', d.nome) as disciplina,
                c.nome as curso, CONCAT(b.titulo, ' [',p.nome,']')as titulo
		FROM BolsasDisciplinas bd, Bolsas b, Disciplinas d, Cursos c, Pessoas p
		WHERE b.codigo = bd.bolsa
                AND d.codigo = bd.disciplina
                AND c.codigo = d.curso
                AND p.codigo = b.professor";

        $sql .= " $sqlAdicional ";
        $sql .= " $nav ";

        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
    }
}

?>