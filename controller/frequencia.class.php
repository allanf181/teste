<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Frequencias extends Generic {

    public function __construct() {
        //
    }

    // LISTA OS ALUNOS COM AUSENCIA ELEVADA
    // USADO POR: VIEW/RELATORIOS/AUSENCIA.PHP
    public function listAusencias($mes) {
        $bd = new database();

        $sql = "SELECT a.data, a.quantidade, p.prontuario, p.nome as aluno,
                f.quantidade, a.atribuicao, d.nome as disciplina,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso
                FROM Aulas a, Frequencias f, Matriculas m, Pessoas p,
                    Disciplinas d, Turmas t, Cursos c, Atribuicoes at
                WHERE f.matricula = m.codigo
                AND a.codigo = f.aula
                AND p.codigo = m.aluno
                AND at.codigo = a.atribuicao
                AND at.turma = t.codigo
                AND t.curso = c.codigo
                AND d.codigo = at.disciplina
                AND DATE_FORMAT(a.data, '%m') = :mes
                ORDER BY p.prontuario";

        $params = array(':mes' => $mes);
        $res = $bd->selectDB($sql, $params);
        
        foreach ($res as $reg) {
            if (strpos($reg['quantidade'], 'F') !== false) {
                $new[$reg['prontuario']][$reg['atribuicao']] += 1;
                $new[$reg['atribuicao']] = $reg['disciplina'].' ('.$reg['curso'].')';
                $new[$reg['prontuario']]['aluno'] = $reg['aluno'];
            }
        }

        foreach ($new as $pront => $reg) {
            foreach ($reg as $atr => $r) {
                if ($r >= 3) {
                    $arr[$pront]['aluno'] = $new[$pront]['aluno'];
                    $arr[$pront]['disciplina'] = $new[$atr];
                }
            }
        }
        
        if ($arr) {
            return $arr;
        } else {
            return false;
        }
    }
}

?>