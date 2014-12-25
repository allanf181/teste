<?php

if (!class_exists('FrequenciasAbonos'))
    require_once CONTROLLER . '/frequenciaAbono.class.php';

class Frequencias extends FrequenciasAbonos {

    public function __construct() {
        //
    }

    // LISTA OS ALUNOS COM AUSENCIA ELEVADA
    // USADO POR: VIEW/RELATORIOS/AUSENCIA.PHP
    public function listAusencias($mes, $ano) {
        $bd = new database();

        $sql = "SELECT a.data, a.quantidade, p.prontuario, p.nome as aluno,
                f.quantidade, a.atribuicao, d.nome as disciplina, p.codigo as codigo,
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
                AND DATE_FORMAT(a.data, '%Y') = :ano
                ORDER BY p.nome";

        $params = array('ano' => $ano, 'mes' => str_pad($mes, 2, "0", STR_PAD_LEFT));
        $res = $bd->selectDB($sql, $params);
        foreach ($res as $reg) {
            if (strpos($reg['quantidade'], 'F') !== false) {
                $new[$reg['prontuario']][$reg['atribuicao']] += 1;
                $new[$reg['atribuicao']] = $reg['disciplina'] . ' (' . $reg['curso'] . ')';
                $new[$reg['prontuario']]['aluno'] = $reg['aluno'];
                $new[$reg['prontuario']]['codigo'] = $reg['codigo'];
            }
        }
        foreach ($new as $pront => $reg) {
            foreach ($reg as $atr => $r) {
                if ($r >= 3 && $new[$atr]) {
                    $arr[$pront]['aluno'] = $new[$pront]['aluno'];
                    $arr[$pront]['disciplina'][] = $new[$atr];
                    $arr[$pront]['codigo'] = $new[$pront]['codigo'];
                }
            }
        }

        if ($arr) {
            return $arr;
        } else {
            return false;
        }
    }

    // CALCULANDO A FREQUENCIA
    public function getFrequencia($matricula, $atribuicao) {
        $bd = new database();

        $sql = "SELECT f.quantidade, 
			(SELECT SUM(au1.quantidade) 
					FROM Aulas au1 
					WHERE au1.atribuicao = au.atribuicao) as aulas,
			(SELECT IF( AulaPrevista, AulaPrevista, ch ) 
					FROM Atribuicoes at1, Disciplinas d 
					WHERE at1.disciplina = d.codigo
					AND at1.codigo = au.atribuicao) as CH,
			au.data, m.aluno
			FROM Pessoas p, Aulas au, Frequencias f, Matriculas m
			WHERE p.codigo = m.aluno
			AND f.matricula = m.codigo
			AND au.codigo = f.aula
			AND f.matricula = :matricula 
			AND au.atribuicao= :atr";
        $params = array('matricula' => $matricula, 'atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            $faltas = 0;
            foreach ($res as $reg) {
                if (!$this->getFrequenciaAbono($reg['aluno'], $atribuicao, $reg['data']))
                    $faltas += substr_count($reg['quantidade'], 'F');
            }

            if (!$auladada = $res[0]['aulas'])
                $auladada = $res[0]['CH'];

            if ($faltas)
                $frequencia = 100 - (($faltas * 100) / $auladada);
            else {
                $frequencia = 100;
                $faltas = 0;
            }

            $dados['CH'] = $res[0]['CH'];
            $dados['frequencia'] = round($frequencia, 1);
            $dados['faltas'] = $faltas;
            $dados['auladada'] = $auladada;

            return $dados;
        } else {
            return false;
        }
    }
}
?>