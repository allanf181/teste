<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class AulasTrocas extends Generic {

    public function __construct() {
        //
    }

    // LISTA AS TROCAS
    // USADOR POR: PROFESSOR/AULATROCA.PHP
    public function listTrocas($professor) {
        $bd = new database();

        $sql = "SELECT *,(SELECT nome FROM Pessoas WHERE codigo = professorSubstituto) as professorSubstituto, "
                . "IF(LENGTH(avalProfessorSubstituto) > 0,avalProfessorSubstituto, 'aguardando...') as avalProfSub "
                . "FROM AulasTrocas "
                . "WHERE professor = :professor";

        $params = array(':professor' => $professor);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // LISTA OS PEDIDOS DE TROCA
    // USADOR POR: HOME.PHP
    public function hasTrocas($professor) {
        $bd = new database();

        $sql = "SELECT *,(SELECT nome FROM Pessoas WHERE codigo = professor) as professor, "
                . "IF(LENGTH(avalProfessorSubstituto) > 0,avalProfessorSubstituto, 'aguardando...') as avalProfSub "
                . "FROM AulasTrocas "
                . "WHERE professorSubstituto = :professor";

        $params = array(':professor' => $professor);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    
}
?>