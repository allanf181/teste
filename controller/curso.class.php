<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Cursos extends Generic {
    
    public function __construct(){
        //
    }

    // UTILIZADO POR: SECRETARIA/AVISO.PHP
    public function listCursosToJSON($string, $ano, $semestre) {
        $bd = new database();

        $sql = "SELECT CONCAT('C:', c.codigo) as id,
                        CONCAT(IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome), '[', m.nome, ']') as name 
               		FROM Cursos c, Modalidades m, Turmas t 
               		WHERE c.modalidade = m.codigo 
                        AND c.codigo = t.curso
                        AND t.ano=:ano 
           		AND (t.semestre=:sem OR t.semestre=0)
                        AND c.nome LIKE :s 
                        ORDER BY c.nome DESC LIMIT 10";

        $params = array(':s' => '%'.$string.'%',':ano' => $ano,':sem' => $semestre);
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
    }
    
    public function listCursos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT c.codigo, m.nome as modalidade,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, 
                    IF(m.codigo < 1000 OR m.codigo > 2000, CONCAT(c.nome,' [',m.nome,']'), c.nome)   ) 
                as curso
    		FROM Cursos c, Modalidades m
    		WHERE m.codigo = c.modalidade";

        $sql .= " $sqlAdicional ";
        $sql .= " ORDER BY c.nome ";
        $sql .= " $nav ";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }
    
}
?>