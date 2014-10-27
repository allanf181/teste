<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class AulasTrocas extends Generic {

    public function __construct() {
        //
    }

    // LISTA AS TROCAS
    // USADOR POR: PROFESSOR/AULATROCA.PHP
    public function listTrocas($params, $sqlAdicional) {
        $bd = new database();

        $sql = "SELECT *,date_format(dataPedido, '%d/%m/%Y %H:%i') as dataPedido,"
                . "(SELECT nome FROM Pessoas WHERE codigo = professorSubstituto) as professorSubstituto, "
                . "(SELECT nome FROM Pessoas WHERE codigo = professor) as professor, "
                . "(SELECT nome FROM Pessoas WHERE codigo = coordenador) as coordenador, "
                . "IF(LENGTH(professorSubstitutoParecer) > 0,professorSubstitutoParecer, 'aguardando...') as avalProfSub, "
                . "(SELECT nome FROM Disciplinas d, Atribuicoes a WHERE a.disciplina = d.codigo AND a.codigo = atribuicao) as disciplina, "
                . "IF(LENGTH(coordenadorParecer) > 0,coordenadorParecer, 'aguardando...') as avalCoord,"
                . "date_format(dataTroca, '%d/%m/%Y') as dataTrocaFormatada "                
                . "FROM AulasTrocas ";

        $sql .= $sqlAdicional;
        $res = $bd->selectDB($sql, $params);
       
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // LISTA OS PEDIDOS DE TROCA
    // USADOR POR: HOME.PHP
    public function hasTrocas($params, $sqlAdicional = null) {
        $bd = new database();

        $sql = "SELECT date_format(at.dataPedido, '%d/%m/%Y %H:%i') as dataPedido, "
                . "date_format(at.dataTroca, '%d/%m/%Y') as dataTrocaFormatada, at.aula, "
                . "at.dataTroca, at.atribuicao, at.motivo, at.codigo, at.professor,"
                . "(SELECT nome FROM Disciplinas d, Atribuicoes a WHERE a.disciplina = d.codigo AND a.codigo = at.atribuicao) as disciplina, "
                . "date_format(at.professorSubstitutoData, '%d/%m/%Y') as professorSubstitutoData, "
                . "(SELECT nome FROM Pessoas WHERE codigo = at.professor) as professorNome, "
                . "(SELECT nome FROM Pessoas WHERE codigo = at.professorSubstituto) as professorSubNome, "
                . "at.professorSubstitutoParecer as professorParecer, at.coordenadorParecer "
                . "FROM AulasTrocas at ";
        
        $sql .= $sqlAdicional;
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    
}
?>