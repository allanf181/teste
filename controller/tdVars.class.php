<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class TDVars extends Generic {

    public function __construct() {
        //
    }

    public function listVars($codigo, $modelo) {
        $bd = new database();

        $sql = "SELECT finalizado,valido FROM TDVars WHERE TD = :codigo AND modelo = :modelo ";
        
        $params = array('codigo' => $codigo, 'modelo' => $modelo);
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function deleteVars($codigo) {
        $bd = new database();

        $sql = "DELETE FROM TDVars WHERE TD = :codigo ";
        
        $params = array('codigo' => $codigo);
        
        $res = $bd->deleteDB($sql, $params);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    
}

?>