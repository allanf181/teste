<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Calendarios extends Generic {

    public function __construct() {
        //
    }

    public function listCalendario($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

        $sql = "SELECT c.codigo, c.ocorrencia, c.diaLetivo,
                  date_format(c.dataInicio, '%d/%m/%Y') as dataInicio,
                  date_format(c.dataFim, '%d/%m/%Y') as dataFim,
                  IF(c.diaLetivo = 0,'NÃO','SIM') as diaLetivoNome,
                  date_format(dataInicio, '%d') as dia, 
                  date_format(dataInicio, '%m') as mes,
                  (SELECT IF(LENGTH(c1.nomeAlternativo) > 0,c1.nomeAlternativo, c1.nome) 
                    FROM Cursos c1
                    WHERE c1.codigo = c.curso)
                  as cursoCal, curso,
                  (SELECT nome FROM Tipos WHERE codigo = c.tipo) as tipoCal, tipo
                  FROM Calendarios c 
                  WHERE date_format(c.dataInicio, '%Y') = :ano
                  $sqlAdicional
                  ORDER BY c.dataInicio ASC $nav";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function getFeriados() {
        $bd = new database();

        $sql = "SELECT dataInicio, dataFim, date_format(dataInicio, '%m/%d/%Y') as data "
                . "FROM Calendarios "
                . "WHERE diaLetivo = 0";

        $res = $bd->selectDB($sql);
        
        if ($res) {
            foreach($res as $reg) {
                if ($reg['dataFim'] && $reg['dataFim']) {
                    $end = $reg['dataFim'];
                    $start = $reg['dataInicio'];
                    $datediff = strtotime($end) - strtotime($start);
                    $datediff = floor($datediff / (60 * 60 * 24));
                    for ($i = 0; $i < $datediff + 1; $i++) {
                        $dias[] = date("m/d/Y", strtotime($start . ' + ' . $i . 'day'));
                    }
                }
                $dias[] = $reg['data'];
            }
            return $dias;
        } else {
            return false;
        }
    }    
}

?>