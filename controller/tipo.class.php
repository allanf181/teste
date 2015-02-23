<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Tipos extends Generic {
    
    public function __construct(){
        //
    }
    
    // UTILIZADO POR: SECRETARIA/AVISO.PHP
    public function listTiposToJSON($string) {
        $bd = new database();

        $sql = "SELECT CONCAT('TP:', codigo) as id, nome as name "
                . "FROM Tipos "
                . "WHERE nome LIKE :s "
                . "ORDER BY nome DESC LIMIT 10";

        $params = array(':s' => '%' . $string . '%');
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;

        return false;
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