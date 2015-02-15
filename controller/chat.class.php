<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Chat extends Generic {

    public function getMessage($codigo, $atribuicao, $para, $first) {
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
                AND atribuicao = :atr
                $sqlAdicional
                ORDER BY data ASC";
        $params = array(':cod' => $codigo, ':atr' => $atribuicao, ':para' => $para);
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

    public function haveMessage($codigo, $atribuicao) {
        $bd = new database();

        $sql = "SELECT (SELECT COUNT(*) 
                        FROM Chat c1 
                        WHERE c1.atribuicao = :atr
                        AND c1.para = :cod
                        AND c1.visualizado = ''
                        AND c1.prontuario = c.prontuario) as total, 
                c.prontuario, p.nome
                FROM Chat c, Pessoas p
                WHERE p.prontuario = c.prontuario
                AND c.atribuicao = :atr
                AND c.para = :cod                
                GROUP BY c.prontuario";
        $params = array(':cod' => $codigo, ':atr' => $atribuicao);
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

    public function listMessage($params, $sqlAdicional = null) {
        $bd = new database();

        $sql = "SELECT COUNT(*) as total, 
                    t.numero as turma, d.numero as disciplina
                    FROM Chat c, Atribuicoes a, Turmas t, Disciplinas d
                    WHERE a.codigo = c.atribuicao
                    AND a.turma = t.codigo
                    AND a.disciplina = d.codigo
                    AND c.para = :prontuario
                    AND c.visualizado = '' $sqlAdicional
                    GROUP BY a.codigo";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                $new_res .= $reg["total"] . ' mensagem(ns) na disciplina ' . $reg["disciplina"] . ' [' . $reg["turma"] . ']<br>';
            }
            return $new_res;
        } else {
            return 'Voc&ecirc; n&atilde;o tem mensagens novas';
        }
    }

}

?>