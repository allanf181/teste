<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

class Turno {
    
    public function __construct(){
        //
    }
    
    // MÉTODO PARA INSERÇÃO DE OBJETO
    // USADO POR: VIEW/SECRETARIA/ABONO.PHP
    public function listTurnos() {
        $bd = new database();
        
        $sql = "SELECT * FROM Turnos";
        $res = $bd->selectDB($sql, $params);
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