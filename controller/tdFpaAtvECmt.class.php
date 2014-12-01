<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class TDFPAAtvECmt extends Generic {

    public function __construct() {
        //
    }
    
    public function listAtvECmt($codigo, $tipo) {
        $bd = new database();

        $sql = "SELECT descricao,aulas FROM TDFPAAtvECmt WHERE TD = :codigo AND tipo = :tipo ";
        
        $params = array('codigo' => $codigo, 'tipo' => $tipo);
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function deleteAtvECmt($codigo) {
        $bd = new database();

        $sql = "DELETE FROM TDFPAAtvECmt WHERE TD = :codigo ";
        
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