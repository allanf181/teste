<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Aulas extends Generic {

    public function __construct() {
        //
    }

    // LISTA OS CONTEUDOS DAS AULAS DO ALUNO
    // USADO POR: VIEW/ALUNO/AULA.PHP
    public function listAulasAluno($codigo, $atribuicao) {
        $bd = new database();

        $sql = "SELECT (
		SELECT CONCAT_WS(',',f.quantidade,m.codigo)
		FROM Frequencias f, Matriculas m
		WHERE f.aula = a.codigo
		AND f.matricula = m.codigo
		AND m.aluno = :aluno
		) as freqMat, a.quantidade as quantidade,
                a.data as data, 
                date_format(a.data, '%d/%m/%Y') as dataFormatada,
                a.conteudo as conteudo
            FROM Aulas a
            WHERE a.atribuicao = :atr
            ORDER BY a.data, a.codigo";

        $params = array(':aluno' => $aluno, ':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // CONTA A QDE DE AULAS DO PROFESSOR
    // USADO POR: VIEW/PROFESSOR/PROFESSOR.PHP
    public function countQdeAulas($atribuicao) {
        $bd = new database();

        $sql = "SELECT SUM(quantidade) as TOTAL
                FROM Aulas a
            WHERE atribuicao = :atr ";

        $params = array(':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]['TOTAL']) {
            return $res[0]['TOTAL'];
        } else {
            return 0;
        }
    }    

}

?>