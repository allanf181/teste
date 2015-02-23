<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class QuestionariosRespostas extends Generic {

    public function listRespostas($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT p.nome as pessoa, p.codigo as codPessoa,
                qq.nome as questao, qr.resposta, qr.codigo
                FROM QuestionariosRespostas qr, Questionarios q, 
                    QuestionariosQuestoes qq, Pessoas p 
                WHERE q.codigo = qq.questionario 
                AND qr.questao = qq.codigo
                AND qr.pessoa = p.codigo ";

        $sql .= $sqlAdicional;
        
        $sql .= $nav;

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function dadosTabela($params, $sqlAdicional = null) {
        $bd = new database();

        $tabela = mysql_real_escape_string($tabela);
        $campo = mysql_real_escape_string($campo);

        $sql = "SELECT COUNT(*) as total, qr.resposta,
                (SELECT COUNT(*)
                    FROM QuestionariosRespostas qr, QuestionariosQuestoes qq
                        WHERE qq.codigo = qr.questao
                        AND qq.questionario = :questionario
                        AND qr.questao = :questao) as geral
                FROM QuestionariosRespostas qr, QuestionariosQuestoes qq, Pessoas p
                WHERE qq.codigo = qr.questao 
                AND p.codigo = qr.pessoa
                AND qq.questionario = :questionario
                AND qr.questao = :questao
                $sqlAdicional
                GROUP BY qq.codigo,qr.resposta";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
}

?>
