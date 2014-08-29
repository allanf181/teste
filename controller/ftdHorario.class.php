<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class FTDHorarios extends Generic {

    public function __construct() {
        //
    }

    // USADO POR: PROFESSOR/FTD.PHP
    public function insertFTDHorario($params) {
        $bd = new database();
        
        $paramsDelete['ano'] = $params['ano'];
        $paramsDelete['semestre'] = $params['semestre'];
        $paramsDelete['professor'] = $params['professor'];
        
        $sql = "DELETE FROM FTDHorarios "
                . "WHERE ftd = (SELECT codigo "
                . "FROM FTDDados WHERE ano = :ano "
                . "AND semestre = :semestre "
                . "AND professor = :professor)";

        $bd->deleteDB($sql, $paramsDelete);
        
        $erro=0;
        if (!$erro) {
            $sql = "INSERT INTO FTDHorarios VALUES (NULL, :ftd, :registro, :horario)";
                    
            $paramsHor['ftd'] = $params['codigo'];
            foreach ($params["dte"] as $reg) {
                list ($r, $horario) = explode('-', $reg);
                $paramsHor['registro'] = $r;
                $paramsHor['horario'] = $horario;
                if (!$bd->insertDB($sql, $paramsHor))
                    $erro=1;
            }
            foreach ($params["dts"] as $reg) {
                list ($r, $horario) = explode('-', $reg);
                $paramsHor['registro'] = $r;
                $paramsHor['horario'] = $horario;
                if (!$bd->insertDB($sql, $paramsHor))
                    $erro=1;
            }
        }

        return $erro;
    }

}

?>