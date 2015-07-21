<?php

if (!class_exists('Generic')) {
    require_once CONTROLLER . '/generic.class.php';
}

class Disciplinas extends Generic {

    public function __construct() {
        //
    }
    
    public function listToJSON($codigo) {
        $bd = new database();

        $sql = "SELECT codigo, nome, numero as sigla
		FROM Disciplinas
		WHERE curso=:codigo
		ORDER BY nome";

        $params = array(':codigo' => $codigo);
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
    }    

    //USADO POR: SECREATARIA/DISCIPLINA.PHP, RELATORIOS/DISCIPLINAS.PHP
    public function listDisciplinas($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT d.numero as numero, d.nome as disciplina,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                d.ch as ch, m.nome as modalidade, d.codigo as codigo
    		FROM Disciplinas d, Cursos c, Modalidades m
    		WHERE d.curso = c.codigo
    		AND m.codigo = c.modalidade";

        $sql .= " $sqlAdicional ";

        $sql .= "$nav";
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }

}

?>