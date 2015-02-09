<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Ocorrencias extends Generic {

    public function __construct() {
        //
    }
    
    public function listOcorrencias($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT o.codigo, p.nome as aluno, p1.nome as registroPor, p.codigo as codAluno,
                        date_format(o.data, '%d/%m/%Y %H:%i') as data,o.descricao,
                        (SELECT COUNT(*) FROM OcorrenciasInteracoes i WHERE i.ocorrencia = o.codigo) as interacao
                        FROM Ocorrencias o, Pessoas p, Pessoas p1
                        WHERE o.aluno = p.codigo
                        AND o.registroPor = p1.codigo";

        $sql .= " $sqlAdicional ";

        $sql .= "$nav";
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        else
            return false;
    }
    
    public function checkOcorrencias($params, $sqlAdicional = null) {
        $bd = new database();

        $sql = "SELECT date_format(i.data, '%d/%m/%Y %H:%i') as data, i.descricao, 
                    i.registroPor, p.nome as aluno, 
                    p1.nome as registroPor, p.codigo as codAluno 
                FROM Ocorrencias o, OcorrenciasInteracoes i, Pessoas p, Pessoas p1
                WHERE o.aluno = p.codigo
                AND o.registroPor = p1.codigo
                AND i.ocorrencia = o.codigo
            UNION 
                SELECT date_format(o.data, '%d/%m/%Y %H:%i') as data, o.descricao, 
                    o.registroPor,p.nome as aluno, 
                    p1.nome as registroPor, p.codigo as codAluno 
                FROM Ocorrencias o, Pessoas p, Pessoas p1
                WHERE o.aluno = p.codigo
                AND o.registroPor = p1.codigo 
                AND o.codigo NOT IN (SELECT ocorrencia FROM OcorrenciasInteracoes i)";
        $sql .= " $sqlAdicional ";

        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        else
            return false;
    }    
}

?>