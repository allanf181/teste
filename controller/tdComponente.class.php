<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class TDComponente extends Generic {

    public function __construct() {
        //
    }

    public function listComponentes($codigo) {
        $bd = new database();

        $sql = "SELECT sigla,nome,curso,turno,aulas,prioridade,referencia FROM TDComponente WHERE TD = :codigo ";
        
        $params = array('codigo' => $codigo);
        
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function deleteComponentes($codigo) {
        $bd = new database();

        $sql = "DELETE FROM TDComponente WHERE TD = :codigo ";
        
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