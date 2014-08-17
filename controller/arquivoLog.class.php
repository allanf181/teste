<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class ArquivosLogs extends Generic {

    public function __construct() {
        //
    }
    
    public function listArquivosLogs($codigo) {
        $bd = new database();

        $sql = "SELECT p.nome FROM ArquivosLogs a, Pessoas p "
                . "WHERE p.codigo = a.pessoa "
                . "AND a.arquivo = :codigo";

        $params = array ('codigo' => $codigo);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }    
}

?>