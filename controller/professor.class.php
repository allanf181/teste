<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Professores extends Generic {

    public function getProfessor($atribuicao) {
        $bd = new database();
        $sql = "SELECT p.codigo, p.nome, p.lattes "
                . "FROM Professores pr, Pessoas p "
                . "WHERE p.codigo = pr.professor "
                . "AND atribuicao = :cod";

        $params = array(':cod'=> $atribuicao);

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