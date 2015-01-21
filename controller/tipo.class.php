<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Tipos extends Generic {
    
    public function __construct(){
        //
    }
    
    // UTILIZADO POR: LOGIN.PHP
    // VERIFICA SE O USUÁRIO PODE ALTERAR ANO/SEMESTRE
    function getTipo($tipo) {
        $bd = new database();

        $sql = "SELECT SUM(alteraAnoSem) as reg FROM Tipos WHERE codigo IN (".implode(',', $tipo).")";

        $res = $bd->selectDB($sql);
        
        if ($res) {
            return $res[0]['reg'];
        } else {
            return false;
        }
    }    
}

?>