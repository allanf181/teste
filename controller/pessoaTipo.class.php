<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class PessoasTipos extends Generic {

    public function __construct() {
        //
    }

    public function insertOrUpdateTipo($codigo, $tipo) {
        $bd = new database();

        $sql = "SELECT tipo FROM PessoasTipos WHERE pessoa = :cod";

        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);

        foreach ($res as $reg) {
            if (!in_array($reg['tipo'], $tipo)) {
                $sql = "DELETE FROM PessoasTipos WHERE pessoa = :cod and tipo = :tipo";
                $params = array(':cod' => $codigo, ':tipo' => $reg['tipo']);
                $ret = $bd->deleteDB($sql, $params);
            } else {
                $tipo_existe[] = $reg['tipo'];
            }
        }

        foreach ($tipo as $reg) {
            if (!in_array($reg, $tipo_existe)) {
                $params = array('pessoa' => $codigo, 'tipo' => $reg);
                $ret = $this->insertOrUpdate($params);
            }
        }
        return $ret;
    }

    // UTILIZADO POR: LOGIN.PHP
    // RETORNA OS TIPOS DE UM DETERMINADO USUARIO
    function getTipoPessoa($pessoa) {
        $bd = new database();

        $sql = "SELECT tipo FROM PessoasTipos WHERE pessoa = :cod";

        $params = array(':cod' => $pessoa);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg)
                $new_res[] = $reg['tipo'];
            return $new_res;
        } else {
            return false;
        }
    }

}

?>