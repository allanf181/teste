<?php

if (!class_exists('Frequencias'))
    require_once CONTROLLER . '/frequencia.class.php';

class BolsasAlunos extends Generic {

    public function __construct() {
        //
    }
    
    public function listAlunos($params = null, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT ba.codigo, p.nome as aluno, p.prontuario,
                CONCAT(b.titulo, ' [',p2.nome,']')as titulo, p.codigo as codAluno
		FROM BolsasAlunos ba, Pessoas p, Bolsas b, Pessoas p2
		WHERE ba.aluno = p.codigo
                AND b.codigo = ba.bolsa
                AND p2.codigo = b.professor";

        $sql .= " $sqlAdicional ";
        $sql .= " $nav ";

        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
    }
}

?>