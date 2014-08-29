<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class PessoasTipos extends Generic {

    public function __construct() {
        //
    }
   
    public function getTipoPessoa($pessoa) {
        $bd = new database();

        $sql = "SELECT tipo FROM PessoasTipos WHERE pessoa = :cod";

        $params = array(':cod'=> $pessoa);            
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach($res as $reg)
                $new_res[] = $reg['tipo'];
            return $new_res;
        } else {
            return false;
        }
    }
}

?>