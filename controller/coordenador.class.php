<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Coordenadores extends Generic {
    
    public function __construct(){
        //
    }
    
    // UTILIZADO POR: SECRETARIA/CIDADE.PHP
    public function listCoordenadores($params=null, $sqlAdicional=null, $item=null, $itensPorPagina=null) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";
        
        $sql = "SELECT c.nome, p.nome as coordenador, co.codigo,
                    IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, 
                        IF(m.codigo < 1000 OR m.codigo > 2000, CONCAT(c.nome,' [',m.nome,']'), c.nome)   ) 
                    as curso
                    FROM Coordenadores co, Cursos c, Pessoas p, Modalidades m
		    WHERE co.coordenador = p.codigo 
                    AND c.modalidade = m.codigo
		    AND co.curso = c.codigo
                    $sqlAdicional
                    ORDER BY c.nome $nav";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        
        return false;
    }    
}

?>