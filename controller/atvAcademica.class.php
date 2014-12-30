<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class AtvAcademicas extends Generic {

    public function __construct() {
        //
    }
    
    // LISTA AS ATIVIDADES ACADÊMICAS CADASTRADAS
    // USADO POR: CURSOS/ATIVIDADES_ACADEMICAS/ATIVIDADES.PHP
    public function listAtividades($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT aa.nome, aa.codigo, aa.CHmaxSem, aa.CHminSem, aa.CHTotal, c.codigo as codCurso,"
                . "aa.CHminCientifica, aa.CHminCultural, aa.CHminAcademica, "
                . "IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso "
                . "FROM Cursos c, AtvAcademicas aa "
                . "WHERE aa.curso = c.codigo ";

        $sql .= " $sqlAdicional ";

        $sql .= " ORDER BY c.nome, aa.codigo ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
}

?>