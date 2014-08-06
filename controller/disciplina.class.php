<?php

if (!class_exists('Generic')) {
    require_once CONTROLLER . '/generic.class.php';
}

class Disciplinas extends Generic {

    public function __construct() {
        //
    }

    public function listDisciplinas($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT d.codigo as codigo, d.nome as disciplina, d.numero as numero, 
                d.ch as ch, m.nome as modalidade,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso
    		FROM Disciplinas d, Cursos c, Modalidades m
    		WHERE d.curso = c.codigo
    		AND m.codigo = c.modalidade 
    		";

        if ($params["curso"] || $params["numeroDisciplina"] || $params["nomeDisciplina"] || $params["codigo"]) {
            $sql .= " $sqlAdicional ";
        }

        $sql .= "$nav";
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }

}

?>