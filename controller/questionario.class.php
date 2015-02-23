<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

require_once CONTROLLER . '/questionarioPessoa.class.php';

class Questionarios extends Generic {

    public function insertOrUpdateQuestionarios($POST) {
        $params['codigo'] = $POST['codigo'];
        $params['dataCriacao'] = $POST['dataCriacao'];
        $params['nome'] = $POST['nome'];
        $params['descricao'] = $POST['descricao'];
        $params['dataFechamento'] = $POST['dataFechamento'];
        $params['criador'] = $POST['criador'];
        $params['valorTotal'] = $POST['valorTotal'];
        $res = $this->insertOrUpdate($params);


        $to = explode(',', $POST['to']);

        $params = null;

        if ($res) {
            if (!empty($POST['codigo']))
                $params['questionario'] = dcrip($POST['codigo']);
            else
                $params['questionario'] = $res['RESULTADO'];

            $questionarioPessoa = new QuestionariosPessoas();

            $params['atribuicao'] = 'NULL';

            $to = explode(',', $POST['to']);

            $resultado = 0;

            if (empty($POST['alteraPessoa']) && $POST['codigo'])
                unset($to);

            foreach ($to as $dest) {
                $params['destinatario'] = 'NULL';
                $params['curso'] = 'NULL';
                $params['turma'] = 'NULL';
                $params['tipo'] = 'NULL';

                if (substr($dest, 0, 2) == 'P:')
                    $params['destinatario'] = crip(substr($dest, 2));
                if (substr($dest, 0, 2) == 'C:')
                    $params['curso'] = crip(substr($dest, 2));
                if (substr($dest, 0, 2) == 'T:')
                    $params['turma'] = crip(substr($dest, 2));
                if (substr($dest, 0, 2) == 'D:')
                    $params['atribuicao'] = crip(substr($dest, 2));
                if (substr($dest, 0, 3) == 'TP:')
                    $params['tipo'] = crip(substr($dest, 3));

                if ($questionarioPessoa->insertOrUpdate($params))
                    $resultado++;
            }

            if ($res['STATUS'] == 'INFO' && $resultado >= 1) {
                $res['STATUS'] = 'OK';
                $res['RESULTADO'] = $resultado;
                return $res;
            }

            return $res;
        } else
            return false;
    }

    // Lista os questionarios
    public function listQuestionarios($params = null, $item = null, $itensPorPagina = null) {
        $table = get_called_class();
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT * FROM $table q";

        if ($params['criador']) {
            $sql .= " WHERE q.criador = :criador";
        } elseif ($params['codigo']) {
            $sql .= " WHERE q.codigo = :codigo";
        }

        $sql .= " ORDER BY dataCriacao DESC ";

        $sql .= "$nav";

        if ($params)
            $res = $bd->selectDB($sql, $params);
        else
            $res = $bd->selectDB($sql);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: HOME.PHP
    // Verifica se o usuÃ¡rio tem questionarios para responder
    public function getAvisoQuestionarios($codigo, $sqlAdicional=null) {
        $bd = new database();

        $sql = "SELECT date_format(q.dataCriacao, '%d/%m/%Y') as dataCriacao,
                date_format(q.dataFechamento, '%d/%m/%Y') as dataFechamento,
                q.valorTotal, q.descricao, q.situacao, q.codigo,
                q.Nome as nome, DATEDIFF(q.dataFechamento, NOW()) as prazoDiff
                FROM QuestionariosPessoas qp INNER JOIN Questionarios q 
		ON(q.codigo = qp.questionario) 
                    WHERE ( destinatario = :cod
                        OR (destinatario IS NULL 
                            AND atribuicao IS NULL 
                            AND curso IS NULL 
                            AND turma IS NULL
 	 	 	    AND tipo IS NULL
                        )
                        OR (destinatario IS NULL
                            AND atribuicao IS NULL 
	 	 	    AND tipo IS NULL 
                            AND curso IS NOT NULL 
                            AND turma IN (SELECT t.codigo 
                                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                                WHERE t.codigo = a.turma 
                                AND m.atribuicao = a.codigo 
                                AND m.aluno = p.codigo 
                                AND t.codigo = a.turma
                                AND p.codigo = :cod )
                            )
                        OR (destinatario IS NULL
                            AND atribuicao IS NULL 
                            AND turma IS NULL
	 	 	    AND tipo IS NULL 
                            AND curso IN (SELECT t.curso 
                                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                                WHERE t.codigo = a.turma 
                                AND m.atribuicao = a.codigo 
                                AND m.aluno = p.codigo 
                                AND t.codigo = a.turma
                                AND p.codigo = :cod )
                            )
                        OR (destinatario IS NULL 
                            AND curso IS NULL 
                            AND turma IS NULL 
 	 	 	    AND atribuicao IS NULL
                            AND tipo IN (SELECT pt.tipo 
                                FROM Pessoas p, PessoasTipos pt 
                                WHERE pt.pessoa = p.codigo
                                AND p.codigo = :cod)
                            )
                        OR (destinatario IS NULL
                            AND atribuicao IS NULL 
                            AND turma IS NULL 
                            AND curso IS NULL
 	 	 	    AND tipo IS NULL
			)	 
			
                    )
                    $sqlAdicional
                GROUP BY q.codigo
                ORDER BY q.dataCriacao DESC
                LIMIT 20";

        $params[':cod'] = $codigo;
        $params[':dataAtual'] = dataMysql(date('d/m/Y'));

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>
