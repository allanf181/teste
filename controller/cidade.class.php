<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Cidades extends Generic {
    
    public function __construct(){
        //
    }
    
    // UTILIZADO POR: SECRETARIA/CIDADE.PHP
    public function listCidadesToJSON($estado) {
        $bd = new database();

        $sql = "SELECT codigo, nome
		FROM Cidades
		WHERE estado=:estado
		ORDER BY nome";

        $params = array(':estado' => $estado);
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
    }

    // UTILIZADO POR: SECRETARIA/CIDADE.PHP
    public function listCidades($params=null, $item=null, $itensPorPagina=null, $sqlAdicional=null) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";
        
        $sql = "SELECT c.codigo, c.nome as cidade, 
                e.nome as estado, e.codigo as codEstado
		FROM Cidades c, Estados e
		WHERE c.estado = e.codigo
                $sqlAdicional
		ORDER BY c.nome $nav";

        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
    }    
}

?>