<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Calendarios extends Generic {
    public function __construct() {
        //
    }

    // LISTA OS ALUNOS COM AUSENCIA ELEVADA
    // USADO POR: VIEW/RELATORIOS/AUSENCIA.PHP
    public function listCalendario($ano) {
        $bd = new database();
        $sql = "SELECT codigo, date_format(data, '%d') as dia, 
                date_format(data, '%m') as mes, ocorrencia, diaLetivo 
                FROM Calendarios WHERE date_format(data, '%Y') = :ano";

        $params = array ('ano' => $ano);
        $res = $bd->selectDB($sql, $params);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
        
    }
}
?>