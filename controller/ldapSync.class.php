<?php

require PATH . INC . '/ldap.inc.php';
require CONTROLLER . '/pessoa.class.php';

class ldapSync extends ldap {

    public function __construct() {
        
    }

    public function syncLDAP() {
        $ldap = new ldap();
        $pessoa = new Pessoas();

        $res = $pessoa->listPessoasTipos();

        foreach ($res as $reg) {
            $ldap->addOU($reg['tipo']);
            $rs = $ldap->addUser($reg['tipo'], $reg['nome'], $reg['prontuario']);
            if ($rs == '1') {
                $result .= "LDAP ADD USER: " . $reg['prontuario'] . "\n";
                $res = $ldap->changePassword($reg['prontuario'], $reg['prontuario']);
            }
        }

        return $result;
    }

}

?>