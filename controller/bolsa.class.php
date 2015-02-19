<?php

if (!class_exists('Frequencias'))
    require_once CONTROLLER . '/frequencia.class.php';

class Bolsas extends Generic {

    public function __construct() {
        //
    }

    public function listBolsas($params = null, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT b.codigo, b.titulo, p.nome as professor, b.observacao,
                CONCAT(date_format(b.dataInicio, '%d/%m/%Y'), ' - ',
                date_format(b.dataFim, '%d/%m/%Y') ) as duracao
		FROM Bolsas b, Pessoas p
		WHERE b.professor = p.codigo";

        $sql .= " $sqlAdicional ";
        $sql .= " $nav ";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;

        return false;
    }

    public function checkBolsas($codigo, $tipo) {
        $bd = new database();

        if ($tipo == 'professor') {
            $sql = "SELECT COUNT(*) as total
		FROM Bolsas b
		WHERE b.professor = :codigo
                AND CURDATE() between dataInicio and dataFim";
        }

        if ($tipo == 'aluno') {
            $sql = "SELECT COUNT(*) as total
		FROM Bolsas b, BolsasAlunos ba
		WHERE b.codigo = ba.bolsa
                AND ba.aluno = :codigo
                AND CURDATE() between dataInicio and dataFim";
        }

        $params = array('codigo' => $codigo);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]['total']) {
            $t = $res[0]['total'];
            if ($tipo == 'aluno') $resp = "Você está participando de $t bolsa(s) nesse semestre.";
            if ($tipo == 'professor') $resp = "Você está supervisionando $t bolsa(s) nesse semestre.";
            return $resp;
        }

        return false;
    }

}

?>