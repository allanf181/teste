<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Chat extends Generic {

    public function getMessage($codigo, $atribuicao, $para, $first, $origem) {
        $bd = new database();

        if ($first)
            $sqlAdicional = "AND ( (c.prontuario = :para AND para = :cod)
                               OR (c.prontuario = :cod AND para = :para) )";

        if (!$first)
            $sqlAdicional = " AND c.prontuario = :para AND para = :cod
                                AND visualizado = '' ";

        $sql = "SELECT date_format(c.data, '%d/%m/%Y %H:%i:%s') as data,
                p.nome, c.mensagem, c.codigo, c.prontuario
                FROM Chat c, Pessoas p
                WHERE p.prontuario = c.prontuario
                AND (atribuicao = :atr OR origem = :origem)
                $sqlAdicional
                ORDER BY data ASC";
        $params = array(':cod' => $codigo, ':atr' => $atribuicao, ':para' => $para, ':origem' => $origem);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                $mensagem .= '<font size="1"><b>' . $reg['data'] . ' - ' . $reg['nome'] . ' diz...</b></font><br>' . $reg['mensagem'] . '<br><br>';
                if ($codigo != $reg['prontuario']) {
                    $params = array('codigo' => $reg['codigo'], 'visualizado' => '1');
                    $res = $this->insertOrUpdate($params);
                }
            }
            return $mensagem;
        } else {
            return false;
        }
    }

    public function haveMessage($codigo, $atribuicao, $origem) {
        $bd = new database();

        $sql = "SELECT (SELECT COUNT(*) 
                        FROM Chat c1 
                        WHERE (c1.atribuicao = :atr OR c1.origem = :origem)
                        AND c1.para = :cod
                        AND c1.visualizado = ''
                        AND c1.prontuario = c.prontuario) as total, 
                c.prontuario, p.nome
                FROM Chat c, Pessoas p
                WHERE p.prontuario = c.prontuario
                AND (c.atribuicao = :atr OR c.origem = :origem)
                AND c.para = :cod                
                GROUP BY c.prontuario";
        $params = array(':cod' => $codigo, ':atr' => $atribuicao, ':origem' => $origem);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            $i = 0;
            foreach ($res as $reg) {
                if ($reg["total"]) {
                    $new_res[$i]['nome'] = '[' . $reg["prontuario"] . '] ' . $reg["nome"];
                    $new_res[$i]['prontuario'] = $reg["prontuario"];
                    $new_res[$i]['total'] = '(' . $reg["total"] . ')';
                    $i++;
                }
            }
            return $new_res;
        } else {
            return false;
        }
    }

    public function qdeMessage($params, $sqlAdicional = null) {
        $bd = new database();

        $sql = "SELECT COUNT(*) as total 
                    FROM Chat c
                    WHERE c.para = :prontuario
                    AND c.visualizado = '' ";

        $sql .= $sqlAdicional;

        $res = $bd->selectDB($sql, $params);

        if ($res[0]['total']) {
            return $res[0]['total'];
        } else {
            return false;
        }
    }

    public function listMessage($prontuario, $ano) {
        $bd = new database();

        $new_res = null;
        // CHATS DE ATRIBUICOES
        $sql = "SELECT COUNT(*) as total, 
                    t.numero as turma, d.numero as disciplina
                    FROM Chat c, Atribuicoes a, Turmas t, Disciplinas d
                    WHERE a.codigo = c.atribuicao
                    AND a.turma = t.codigo
                    AND a.disciplina = d.codigo
                    AND c.para = :prontuario
                    AND c.visualizado = ''
                    AND str_to_date(:ano, '%Y') = str_to_date(data, '%Y') 
                    GROUP BY a.codigo";

        $params = array(':prontuario' => $prontuario, ':ano' => $ano);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                $new_res .= $reg["total"] . ' mensagem(ns) na disciplina ' . $reg["disciplina"] . ' [' . $reg["turma"] . ']<br>';
            }
        }

        // CHATS DE OUTROS LUGARES
        $sql = "SELECT COUNT(*) as total, c.origem
                    FROM Chat c
                    WHERE c.para = :prontuario
                    AND c.visualizado = '' $sqlAdicional
                    AND c.origem <> ''
                    GROUP BY c.prontuario";
        
        $params = array(':prontuario' => $prontuario);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                $new_res .= $reg["total"] . ' mensagem(ns) ('.$reg["origem"].')<br>';
            }
        }

        if ($new_res) return $new_res;

        return false;
    }

    public function listMessageBolsa($codigo, $origem) {
        $bd = new database();

        $sql = "SELECT p.prontuario, p.nome, p.codigo
            FROM Chat c, Pessoas p 
            WHERE c.prontuario = p.prontuario
            AND ( (c.atribuicao NOT IN (SELECT m1.atribuicao 
                                        FROM Matriculas m1 
                                        WHERE m1.aluno = :codigo)
                    AND c.atribuicao IN (SELECT a1.codigo 
                                    FROM Atribuicoes a1, BolsasAlunos ba1, BolsasDisciplinas bd1 
                                    WHERE bd1.bolsa = ba1.bolsa 
                                    AND bd1.disciplina = a1.disciplina 
                                    AND ba1.aluno = :codigo)
                )
                OR (origem = :origem)
                )
            AND origem <> ''
            GROUP BY p.prontuario 
            ORDER BY p.nome";

        $params = array('codigo' => $codigo, 'origem' => $origem);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>