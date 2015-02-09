<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class OcorrenciasInteracoes extends Generic {

    public function __construct() {
        //
    }
    
    public function listInteracoes($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT i.codigo, p.nome as registroPor,
                        date_format(i.data, '%d/%m/%Y') as data,i.descricao
                        FROM Ocorrencias o, OcorrenciasInteracoes i, Pessoas p
                        WHERE i.registroPor = p.codigo
                        AND i.ocorrencia = o.codigo";

        $sql .= " $sqlAdicional ";

        $sql .= "$nav";
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        else
            return false;
    }    
}

?>