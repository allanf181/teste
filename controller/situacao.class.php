<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Situacoes extends Generic {
    
    public function __construct(){
        //
    }
    
    // USADO POR: RELATORIOS.PHP
    // Retornas situacoes de uma determinada turma
    public function getSituacoes($params, $sqlAdicional) {
        $bd = new database();
        
        $sql = "SELECT s.codigo, s.nome 
                    FROM Situacoes s, Matriculas m, Atribuicoes a, Turmas t, Cursos c
	            WHERE s.codigo = m.situacao
	            AND m.atribuicao = a.codigo
	            AND t.codigo = a.turma
	            AND c.codigo = t.curso
	            $sqlAdicional
	            GROUP BY s.codigo
	            ORDER BY codigo";
        
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }    
}

?>