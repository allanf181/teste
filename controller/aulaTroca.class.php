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
                . "(SELECT nome FROM Pessoas WHERE codigo = professorSub) as professorSub, "
                . "(SELECT nome FROM Pessoas WHERE codigo = professor) as professor, "
                . "(SELECT nome FROM Pessoas WHERE codigo = coordenador) as coordenador, "
                . "IF(professorSubAceite = '0', 'aguardando...', IF(professorSubAceite = 'S', 'SIM', 'NÃO')) as avalProfSub, "
                . "(SELECT nome FROM Disciplinas d, Atribuicoes a WHERE a.disciplina = d.codigo AND a.codigo = atribuicao) as disciplina, "
                . "IF(coordenadorAceite = '0','aguardando...', IF(coordenadorAceite = 'S', 'SIM', 'NÃO')) as avalCoord,"
                . "IF(tipo = 'reposicao','Reposição', 'Troca') as tipo,"
                . "date_format(dataTroca, '%d/%m/%Y') as dataTrocaFormatada,d.numero as discNumero,"
                . "t.numero as turma, professorSubAceite, professorSubParecer, coordenadorParecer, "
                . "IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso, "
                . "t.numero as turma "
                . "FROM AulasTrocas at, Atribuicoes a, Turmas t, Cursos c, Disciplinas d "
                . "WHERE at.atribuicao = a.codigo "
                . "AND a.disciplina = d.codigo "
                . "AND a.turma = t.codigo "
                . "AND t.curso = c.codigo ";

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

        $sql = "SELECT date_format(at.dataPedido, '%d/%m/%Y %H:%i') as dataPedido, professorSubAceite, "
                . "date_format(at.dataTroca, '%d/%m/%Y') as dataTrocaFormatada, at.aula, "
                . "at.dataTroca, at.atribuicao, at.motivo, at.codigo, at.professor,"
                . "(SELECT nome FROM Disciplinas d, Atribuicoes a WHERE a.disciplina = d.codigo AND a.codigo = at.atribuicao) as disciplina, "
                . "date_format(at.professorSubData, '%d/%m/%Y') as professorSubData, "
                . "(SELECT nome FROM Pessoas WHERE codigo = at.professor) as professorNome, "
                . "(SELECT nome FROM Pessoas WHERE codigo = at.professorSub) as professorSubNome, "
                . "IF(tipo = 'reposicao','Reposição', 'Troca') as tipo,"                
                . "at.professorSubParecer as professorParecer, at.coordenadorParecer "
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