<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class AnoSemestre extends Generic {

    public function __construct() {
        //
    }

    // GRAVA O ANO E SEMESTRE PADRÃO QUANDO FOR ALTERADO PELO USUÁRIO
    public function updateAnoSemestre($codigo, $ano, $semestre) {
        $bd = new database();
        $sql = "UPDATE Pessoas SET anoPadrao=:ano, semPadrao=:semestre where codigo=:codigo ";
        $res = $bd->updateDB($sql, array(':codigo'=>$codigo, ':ano'=>$ano, ':semestre'=>$semestre));        
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
    
    // BUSCA O ANO E SEMESTRE PADRÃO
    public function getAnoSemestre($codigo) {
        $bd = new database();
        $sql = "select anoPadrao,semPadrao from Pessoas where codigo=:codigo ";
        $res = $bd->selectDB($sql, array(':codigo'=>$codigo));        
        if ($res) {
            return $res[0];
        } else {
            return false;
        }
    }
    
}

?>