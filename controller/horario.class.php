<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Horarios extends Generic {
    
    public function __construct(){
        //
    }

        public function listHorarios() {
        $bd = new database();
        $sql = "SELECT codigo,nome,date_format(inicio, '%H:%i') as inicio, "
                . "date_format(fim, '%H:%i') as fim "
                . "FROM Horarios "
                . "ORDER BY codigo";
        $res = $bd->selectDB($sql);

        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
}

?>