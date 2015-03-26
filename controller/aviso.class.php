<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Avisos extends Generic {

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem avisos
    // Pode ser colocado com função no MySQL futuramente
    public function getAvisoGeral($codigo, $ano) {
        $bd = new database();
        $sql = "SELECT date_format(a.data, '%d/%m/%Y %H:%i') as Data, 
                a.conteudo as Conteudo,
                (SELECT d1.nome FROM Disciplinas d1, Atribuicoes a1 WHERE a1.disciplina = d1.codigo AND a1.codigo = a.atribuicao) as disciplina,
                (SELECT CONCAT(codigo, '#', nome) FROM Pessoas WHERE codigo = a.pessoa) as Pessoa
                FROM Avisos a 
                    WHERE pessoa <> :cod
                    AND date_format(a.data, '%Y') = :ano
                    AND ( destinatario = :cod
                        OR (destinatario IS NULL 
                            AND atribuicao IS NULL 
                            AND curso IS NULL 
                            AND turma IS NULL
                            AND tipo IS NULL 
                        )
                        OR (destinatario IS NULL
                            AND atribuicao IS NULL 
                            AND curso IS NOT NULL
                            AND tipo IS NULL 
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
                            AND curso IS NULL 
                            AND turma IS NULL 
                            AND tipo IS NULL 
                            AND atribuicao IN (SELECT a.codigo 
                                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                                WHERE t.codigo = a.turma 
                                AND m.atribuicao = a.codigo 
                                AND m.aluno = p.codigo 
                                AND t.codigo = a.turma
                                AND p.codigo = :cod )
                        )
                    )
                ORDER BY a.data DESC
                LIMIT 50";

        $params = array(':cod' => $codigo, ':ano' => $ano);

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: ALUNO/AVISO.PHP
    // Lista os avisos do usuário
    public function getAvisoAtribuicao($codigo, $atribuicao) {
        $bd = new database();

        $sql = "SELECT Data, Conteudo 
                	FROM Avisos 
			WHERE atribuicao = :atr
			AND (destinatario = 0 OR destinatario = :cod)";
        $params = array(':cod' => $codigo, ':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function insertOrUpdateAvisos($POST) {
        $params['codigo'] = $POST['codigo'];
        $params['conteudo'] = $POST['conteudo'];
        $params['pessoa'] = $POST['pessoa'];
        $params['atribuicao'] = $POST['atribuicao'];
        if (!$POST['atribuicao'])
            $params['atribuicao'] = 'NULL';

        $to = explode(',', $POST['to']);

        $res = 0;
        foreach ($to as $dest) {
            $params['destinatario'] = 'NULL';
            $params['curso'] = 'NULL';
            $params['turma'] = 'NULL';

            if (substr($dest, 0, 2) == 'P:')
                $params['destinatario'] = crip(substr($dest, 2));
            if (substr($dest, 0, 2) == 'C:')
                $params['curso'] = crip(substr($dest, 2));
            if (substr($dest, 0, 2) == 'T:')
                $params['turma'] = crip(substr($dest, 2));
            if (substr($dest, 0, 3) == 'TP:')
                $params['tipo'] = crip(substr($dest, 3));

            if ($this->insertOrUpdate($params))
                $res++;
        }

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // Lista os avisos
    // USADO EM SECRETARIA/AVISO.PHP
    // PROFESSOR/AVISO.PHP
    public function listAvisos($params, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        if ($params['atribuicao'])
            $att = "AND a.atribuicao = :atribuicao";

        $sql = "SELECT a.codigo as codigo, date_format(a.data, '%d/%m/%Y %H:%i') as data, 
    			a.conteudo as conteudo, a.atribuicao as atribuicao,
    			(SELECT p1.nome FROM Pessoas p1 WHERE p1.codigo = a.destinatario) as destinatario,
    			(SELECT CONCAT('[', c.codigo, '] ', c.nome) FROM Cursos c WHERE c.codigo = a.curso) as curso,
    			(SELECT t.numero FROM Turmas t WHERE t.codigo = a.turma) as turma,
                        (SELECT pt.nome FROM Tipos pt WHERE pt.codigo = a.tipo) as tipo
    			FROM Avisos a 
    			WHERE a.pessoa = :pessoa
                        $att
                        ORDER BY data DESC ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>