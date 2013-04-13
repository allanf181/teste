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

    public function listAbonos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

            $sql = "SELECT f.codigo as codigo, date_format(f.data, '%d/%m/%Y') as dataInicio, 
                    f.motivo as motivo, f.aula as aula, p.nome as nome, p.codigo as aluno,
                    p.prontuario as prontuario, f.tipo as sigla, f.atribuicao as atribuicao,
                    CASE f.tipo WHEN 'A' THEN 'Abono'
                                WHEN 'R' THEN 'Regime de Exerc&iacute;cios Domiciliares'
                                WHEN 'M' THEN 'Matr&iacute;cula ap&oacute;s inicio letivo'
                                WHEN 'D' THEN 'Dispensa' END as tipo,
                    (SELECT d.nome FROM Atribuicoes a, Disciplinas d 
                        WHERE a.disciplina = d.codigo AND a.codigo = f.atribuicao) as disciplina
                    FROM FrequenciasAbonos f, Pessoas p
                    WHERE f.aluno = p.codigo 
                    $sqlAdicional
                    ORDER BY f.data DESC $nav";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
}

?>