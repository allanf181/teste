<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class FrequenciasAbonos extends Generic {

    // MÉTODO PARA INSERÇÃO DE OBJETO
    public function insertOrUpdateAbono($params) {
        $bd = new database();

        if ($params["dataInicio"]) {
            $dataInicio = dataMysql($params["dataInicio"]);
            $dataInicio = new DateTime($dataInicio);
        }

        if ($params["dataFim"]) {
            $dataFim = dataMysql($params["dataFim"]);
            $dataFim = new DateTime($dataFim);
        }

        if ($params["dataInicio"] && $params["dataFim"]) {
            $i = 0;
            while ($dataInicio <= $dataFim) {
                $data = $dataInicio->format('Y-m-d');
                unset($params['dataInicio']);
                unset($params['dataFim']);
                $params['data'] = $data;
                $res = $this->insertOrUpdate($params);
                $dataInicio->add(new DateInterval('P1D'));
            }
            return $res;
        } else {
            $data = dataMysql($params["dataInicio"]);
            unset($params['dataInicio']);
            unset($params['dataFim']);
            $params['data'] = $data;
            return $this->insertOrUpdate($params);
        }
    }

    // VERIFICANDO OS ABONOS DO ALUNO
    public function getFrequenciaAbono($aluno, $atribuicao, $data) {
        $bd = new database();

        $sql = "SELECT f.tipo as sigla,
                CASE f.tipo WHEN 'A' THEN 'Abono'
                WHEN 'R' THEN 'Regime de Exerc&iacute;cios Domiciliares'
                WHEN 'M' THEN 'Matr&iacute;cula ap&oacute;s inicio letivo'
                WHEN 'D' THEN 'Dispensa' END as tipo
                FROM FrequenciasAbonos f
                WHERE f.aluno = :aluno
                AND ( (:data BETWEEN f.dataInicio AND f.dataFim)
                        OR (f.dataInicio = :data)
                    )
                AND
                (   (f.atribuicao = :atr)
                    OR 
                    (
                        (f.aula = (SELECT h1.nome
                            FROM Ensalamentos e1, Horarios h1
                            WHERE e1.horario = h1.codigo
                            AND e1.atribuicao=:atr LIMIT 1)
                        )
                        OR 
                        ( f.aula =  (SELECT tu2.sigla
                            FROM Atribuicoes a2, Turmas t2, Turnos tu2
                            WHERE a2.turma = t2.codigo
                            AND t2.turno = tu2.codigo
                            AND a2.codigo=:atr LIMIT 1)
                        ) 
                    )
                )";

        $params = array('aluno' => $aluno, 'atr' => $atribuicao, 'data' => $data);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }
    
    public function listAbonos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

            $sql = "SELECT f.codigo as codigo, date_format(f.dataInicio, '%d/%m/%Y') as dataInicio,
                    date_format(f.dataFim, '%d/%m/%Y') as dataFim,
                    f.motivo as motivo, f.aula as aula, p.nome as nome, p.codigo as aluno,
                    p.prontuario as prontuario, f.tipo as sigla, f.atribuicao as atribuicao,
                    CASE f.tipo WHEN 'A' THEN 'Abono'
                                WHEN 'R' THEN 'Regime de Exerc&iacute;cios Domiciliares'
                                WHEN 'M' THEN 'Matr&iacute;cula ap&oacute;s inicio letivo'
                                WHEN 'D' THEN 'Dispensa' END as tipo,
                    (SELECT IF(a.bimestre>0, CONCAT(d.nome, ' [',a.bimestre,'ºBIM]'), d.nome) FROM Atribuicoes a, Disciplinas d 
                        WHERE a.disciplina = d.codigo AND a.codigo = f.atribuicao) as disciplina
                    FROM FrequenciasAbonos f, Pessoas p
                    WHERE f.aluno = p.codigo 
                    $sqlAdicional
                    ORDER BY f.dataInicio DESC $nav";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
}

?>