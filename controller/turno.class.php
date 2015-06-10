<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Turnos extends Generic {
    
    public function __construct(){
        //
    }
    
    public function getTurnos($turma = null) {
        $bd = new database();

        if (!$turma) {
            $sql = "SELECT distinct * FROM Turnos";
        } else {
            $sql = "SELECT distinct * FROM Turnos t, Turmas tu WHERE tu.turno=t.codigo AND tu.codigo=:turma";
        }
        $params = array(':turma' => $turma);

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
}

?>