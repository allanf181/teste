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
                date_format(b.dataFim, '%d/%m/%Y') ) as duracao,
                b.dataInicio, b.dataFim
		FROM Bolsas b, Pessoas p
		WHERE b.professor = p.codigo";

        $sql .= " $sqlAdicional ";
        $sql .= " $nav ";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;

        return false;
    }

    public function checkBolsas($codigo, $tipo, $ano) {
        $bd = new database();

        if ($tipo == 'professor') {
            $sql = "SELECT COUNT(*) as total
		FROM Bolsas b
		WHERE b.professor = :codigo
                AND str_to_date(:ano, '%Y') 
                    between str_to_date(dataInicio, '%Y') 
                    AND str_to_date(dataFim, '%Y')";
        }

        if ($tipo == 'aluno') {
            $sql = "SELECT COUNT(*) as total
		FROM Bolsas b, BolsasAlunos ba
		WHERE b.codigo = ba.bolsa
                AND ba.aluno = :codigo
                AND str_to_date(:ano, '%Y') 
                    between str_to_date(dataInicio, '%Y') 
                    AND str_to_date(dataFim, '%Y')";
        }

        $params = array('codigo' => $codigo, 'ano' => $ano);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]['total']) {
            $t = $res[0]['total'];
            if ($tipo == 'aluno')
                $resp = "Você está participando de $t bolsa(s).";
            if ($tipo == 'professor')
                $resp = "Você está supervisionando $t bolsa(s).";
            return $resp;
        }

        return false;
    }

    public function checkBolsista($disciplina, $foto) {
        $bd = new database();

        $sql = "SELECT p.codigo, p.nome, p.prontuario
		FROM Bolsas b, BolsasAlunos ba, BolsasDisciplinas bd, Pessoas p
		WHERE b.codigo = ba.bolsa
                AND b.codigo = bd.bolsa
                AND ba.aluno = p.codigo
                AND bd.disciplina = :disciplina
                AND CURDATE() between dataInicio and dataFim";

        $params = array('disciplina' => $disciplina);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                $new_res .= "<a href='#' rel='" . INC . "/file.inc.php?type=pic&id=" . crip($reg['codigo']) . "&timestamp=" . time() . "' class='screenshot' title='" . $reg['nome'] . "'>
                                <img style='width: 25px; height: 25px' alt='Embedded Image' src='" . INC . "/file.inc.php?type=pic&id=" . crip($reg['codigo']) . "&timestamp=" . time() . "' />
                           </a>" . $reg['nome'] . "<br>";
            }
            if ($foto)
                return $new_res;

            return $res;
        }

        return false;
    }

}

?>