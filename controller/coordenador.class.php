<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Coordenadores extends Generic {
    
    public function __construct(){
        //
    }
    
    // UTILIZADO POR: SECRETARIA/CIDADE.PHP
    public function listCoordenadores($params=null, $sqlAdicional=null, $item=null, $itensPorPagina=null) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";
        
        $sql = "SELECT c.nome, p.nome as coordenador, co.codigo,
                    IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, 
                        IF(m.codigo < 1000 OR m.codigo > 2000, CONCAT(c.nome,' [',m.nome,']'), c.nome)   ) 
                    as curso,(SELECT nome FROM Areas WHERE codigo = co.area) as area
                    FROM Coordenadores co, Cursos c, Pessoas p, Modalidades m
		    WHERE co.coordenador = p.codigo 
                    AND c.modalidade = m.codigo
		    AND co.curso = c.codigo
                    $sqlAdicional
                    ORDER BY c.nome $nav";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        
        return false;
    }
    
    // UTILIZADO PELAS FUNCOES DE ENVIAR EMAIL
    public function getEmailCoordFromAtribuicao($atribuicao) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";
        
        $sql = "SELECT p.email "
                . "FROM Atribuicoes a, Pessoas p, Turmas t, Coordenadores c "
                . "WHERE a.turma = t.codigo "
                . "AND t.curso = c.curso "
                . "AND p.codigo = c.coordenador "
                . "AND a.codigo = :atribuicao "
                . "GROUP BY c.coordenador";

        $params = array('atribuicao' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            foreach($res as $reg) {
                $email[] = $reg['email'];
            }
            return implode(',', $email);
        
        return false;
    }
    
    // UTILIZADO PELAS FUNCOES DE ENVIAR EMAIL
    public function getEmailCoordFromArea($area) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";
        
        $sql = "SELECT p.email "
                . "FROM Pessoas p, Coordenadores c "
                . "WHERE p.codigo = c.coordenador "
                . "AND c.area = :area "
                . "GROUP BY c.coordenador";

        $params = array('area' => $area);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            foreach($res as $reg) {
                $email[] = $reg['email'];
            }
            return implode(',', $email);
        
        return false;
    }    
}

?>