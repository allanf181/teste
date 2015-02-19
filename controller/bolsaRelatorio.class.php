<?php

if (!class_exists('Frequencias'))
    require_once CONTROLLER . '/frequencia.class.php';

class BolsasRelatorios extends Generic {

    public function __construct() {
        //
    }

    public function listRelatorios($params = null, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT br.codigo, p.nome as aluno, br.assunto,
                br.descricao, CONCAT(b.titulo, ' [',p2.nome,']') as titulo,
                date_format(br.data, '%d/%m/%Y') as data
		FROM BolsasRelatorios br, Pessoas p, Bolsas b, Pessoas p2
		WHERE br.aluno = p.codigo
                AND b.codigo = br.bolsa
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