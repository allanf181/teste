<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Cursos extends Generic {
    
    public function __construct(){
        //
    }
    
    public function listCursos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT c.codigo, m.nome as modalidade,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso
    		FROM Cursos c, Modalidades m
    		WHERE m.codigo = c.modalidade 
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