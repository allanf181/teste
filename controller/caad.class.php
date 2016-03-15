<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Caads extends Generic {
    
    public function __construct(){
        //
    }

    public function listagem() {
        $bd = new database();

        $sql = "SELECT c.codigo codigo, t.nome responsavel, a.nome area FROM Tipos t, Areas a, Caads c WHERE t.codigo = c.tipo 
            AND a.codigo=c.area ORDER BY a.nome";
        
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function getAreas($tipos) {
        $bd = new database();
        $areas = array();
        foreach ($tipos as $t){
            $sql = "SELECT c.area area FROM Tipos t, Areas a, Caads c WHERE t.codigo = c.tipo AND a.codigo=c.area AND c.tipo = :tipo";
            $params = array(':tipo' => $t);
            $res = $bd->selectDB($sql, $params);
            if ($res){
                foreach ($res as $reg)
                    array_push($areas, $reg['area']);
            }
        }
        if ($areas) {
            return $areas;
        } else {
            return false;
        }
    }

}

?>