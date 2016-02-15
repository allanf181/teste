<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Calendarios extends Generic {

    public function __construct() {
        //
    }

    public function listCalendario($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

        $sql = "SELECT c.codigo, c.ocorrencia, c.diaLetivo,
                  date_format(c.dataInicio, '%d/%m/%Y') as dataInicio,
                  date_format(c.dataFim, '%d/%m/%Y') as dataFim,
                  IF(c.diaLetivo = 0,'NÃO','SIM') as diaLetivoNome,
                  date_format(dataInicio, '%d') as dia, 
                  date_format(dataInicio, '%m') as mes,
                  (SELECT IF(LENGTH(c1.nomeAlternativo) > 0,c1.nomeAlternativo, c1.nome) 
                    FROM Cursos c1
                    WHERE c1.codigo = c.curso)
                  as cursoCal, curso,
                  (SELECT nome FROM Tipos WHERE codigo = c.tipo) as tipoCal, tipo
                  FROM Calendarios c 
                  WHERE date_format(c.dataInicio, '%Y') = :ano
                  $sqlAdicional
                  ORDER BY c.dataInicio ASC $nav";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function getFeriados() {
        $bd = new database();

        $sql = "SELECT dataInicio, dataFim, date_format(dataInicio, '%m/%d/%Y') as data "
                . "FROM Calendarios "
                . "WHERE diaLetivo = 0";

        $res = $bd->selectDB($sql);

        if ($res) {
            foreach ($res as $reg) {
                if ($reg['dataFim'] && $reg['dataFim']) {
                    $end = $reg['dataFim'];
                    $start = $reg['dataInicio'];
                    $datediff = strtotime($end) - strtotime($start);
                    $datediff = floor($datediff / (60 * 60 * 24));
                    for ($i = 0; $i < $datediff + 1; $i++) {
                        $dias[] = date("m/d/Y", strtotime($start . ' + ' . $i . 'day'));
                    }
                }
                $dias[] = $reg['data'];
            }
            return $dias;
        } else {
            return false;
        }
    }

    // USADO POR: HOME.PHP
    public function getEventos($pessoa, $ano) {
        $bd = new database();
        $sql = "SELECT c.ocorrencia,
                  date_format(c.dataInicio, '%d/%m/%Y') as dataInicio,
                  date_format(c.dataFim, '%d/%m/%Y') as dataFim
                FROM Calendarios c 
                    WHERE str_to_date(dataInicio, '%Y') >= :ano 
                        AND (date_format(dataInicio, '%m') = date_format(CURDATE(), '%m')
                            OR
                            date_format(dataFim, '%m') = date_format(CURDATE(), '%m')
                            )
                        AND (( curso IS NULL 
                          AND tipo IS NULL 
                        )
                        OR ( tipo IS NULL 
                            AND curso IN (SELECT t.curso 
                                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                                WHERE t.codigo = a.turma 
                                AND m.atribuicao = a.codigo 
                                AND m.aluno = p.codigo 
                                AND t.codigo = a.turma
                                AND p.codigo = :cod )
                            )
                        OR ( curso IS NULL 
                            AND tipo IN (SELECT pt.tipo 
                                FROM Pessoas p, PessoasTipos pt 
                                WHERE pt.pessoa = p.codigo
                                AND p.codigo = :cod)
                            )
                    )
                ORDER BY dataInicio DESC
                LIMIT 5";

        $params = array(':cod' => $pessoa, ':ano' => $ano);

        $res = $bd->selectDB($sql, $params);
        
        if ($res) {
            foreach ($res as $reg) {
                $data = ($reg['dataFim'] && $reg['dataFim']!='00/00/0000')? $reg['dataInicio'].' a '.$reg['dataFim']:$reg['dataInicio'];
                $new_res .= $data . ' - '. $reg['ocorrencia'] .'<br>';
            }
            return $new_res;
        } else {
            return false;
        }
    }
    
    public function isDiaLetivo($data) {
        $bd = new database();

        $sql = "SELECT diaLetivo 
                    FROM Calendarios c
                    WHERE c.dataInicio = :dia";

        $params = array(':dia' => $data);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return false;
        } else {
            return true;
        }
    } 

}

?>