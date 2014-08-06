<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Cursos extends Generic {
    
    public function __construct(){
        //
    }
    
    public function listCursos() {
        $bd = new database();

        $sql = "SELECT c.codigo as codigo, m.nome as modalidade, "
                . "IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso "
                . "FROM Cursos c, Modalidades m "
                . "WHERE c.modalidade = m.codigo "
                . "ORDER BY c.nome";

        $res = $bd->selectDB($sql);

        if ($res)
            return $res;
        else
            return false;
    }    
}

?>