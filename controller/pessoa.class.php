<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Pessoas extends Generic {

    public function __construct() {
        //
    }

    // UTILIZADO POR: SECRETARIA/AVISO.PHP
    public function listPessoasToJSON($string) {
        $bd = new database();

        $sql = "SELECT CONCAT('P:', codigo) as id, nome as name "
                . "FROM Pessoas "
                . "WHERE nome LIKE :s "
                . "ORDER BY nome DESC LIMIT 10";

        $params = array(':s' => '%' . $string . '%');
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;

        return false;
    }

    //UTILIZADO POR: SECRETARIA/ABONO.PHP, SECRETARIA/CURSOS/COORDENADOR.PHP
    //RETORNA PESSOAS DE UM DETERMINADO TIPO
    public function listPessoasTipos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

        $sql = "SELECT p.codigo, p.nome as nome,
                p.prontuario, p.email, t.nome as tipo
               	FROM Pessoas p, PessoasTipos pt, Tipos t
               	WHERE p.codigo = pt.pessoa
                AND t.codigo = pt.tipo
                $sqlAdicional
		ORDER BY p.nome $nav";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: HOME.PHP
    public function removeFoto($codigo) {
        $bd = new database();
        $sql = "UPDATE Pessoas SET foto = '' WHERE codigo = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->updateDB($sql, $params);
        if ($res[0]) {
            return true;
        } else {
            return false;
        }
    }

    // USADO POR: HOME.PHP
    // RETORNA DADOS DA SENHA
    public function infoPassword($codigo) {
        $bd = new database();
        $sql = "SELECT DATEDIFF(NOW(), dataSenha) as data,"
                . "(SELECT diasAlterarSenha FROM Instituicoes) as dias, "
                . "dataSenha, senha, PASSWORD(prontuario) as pront "
                . "FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]) {
            return $res[0];
        } else {
            return false;
        }
    }

    // USADO POR: SECRETARIA/PESSOA.PHP
    // RETORNA QUANTIDADE DE FOTOS BLOQUEADAS
    public function countBloqPic() {
        $bd = new database();
        $sql = "SELECT p.codigo, p.nome, bloqueioFoto,
                    (SELECT COUNT(*) FROM Pessoas WHERE bloqueioFoto = 1) as total
		    FROM Pessoas p
		    WHERE bloqueioFoto = 1
		    ORDER BY p.nome";
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: SECRETARIA/PESSOA.PHP
    // DESBLOQUEIA AS FOTOS
    public function desloqueioFoto($codigo) {
        $bd = new database();

        $validos = explode(',', $codigo);
        $i = 0;
        foreach ($validos as $value) {
            if ($value) {
                $indice = 'A' . $i;
                $new_array[$indice] = $value;
                $new_params[] = ':' . $indice;
                $i++;
            }
        }
        $param = implode($new_params, ',');
        $params = $new_array;

        $sql = "UPDATE Pessoas SET bloqueioFoto = '' WHERE codigo IN ($param)";
        $res = $bd->updateDB($sql, $params);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    // USADO POR: SECRETARIA/SOCIOECONOMICO.PHP
    // RETORNA DADOS PARA O SOCIOECONOMICO
    public function dadosSocioEconomico($tabela, $campo, $params, $sqlAdicional = null, $group = null) {
        $bd = new database();

        $tabela = mysql_real_escape_string($tabela);
        $campo = mysql_real_escape_string($campo);

        $sql = "SELECT COUNT(*) as total, 
                (SELECT nome 
                    FROM $tabela 
                    WHERE codigo = p.$campo) as nome,
                        $campo as campo
                FROM Pessoas p 
                WHERE p.codigo IN (SELECT p.codigo FROM
                    Matriculas m, Atribuicoes a, Turmas t, Cursos c, PessoasTipos pt
                    WHERE m.atribuicao = a.codigo
                    AND m.aluno = p.codigo
                    AND a.turma = t.codigo
                    AND t.curso = c.codigo
                    AND pt.pessoa = p.codigo
                    AND pt.tipo = :aluno
                    AND (t.semestre=:semestre OR t.semestre=0)
                    AND t.ano=:ano
                    $sqlAdicional )
                GROUP BY p.$campo";

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            foreach ($res as $reg) {
                if (!$reg['nome'])
                    $reg['nome'] = 'Não preenchido';

                if ($group) {
                    if (!$reg['campo'])
                        $reg['campo'] = 0;

                    foreach ($group as $g) {
                        list($i, $f, $nome) = explode('|', $g);
                        if (
                                (preg_match('/^[\D]*$/', $i) && strcasecmp($i, $reg['campo']) == 0) || (preg_match('/^[\d]*$/', $i) && $reg['campo'] >= $i && $reg['campo'] <= $f )
                        ) {
                            $new_res[$nome]['nome'] = $nome;
                            $new_res[$nome]['total'] += $reg['total'];
                        }
                    }
                } else {
                    $new_res[$reg['nome']]['nome'] = $reg['nome'];
                    $new_res[$reg['nome']]['total'] += $reg['total'];
                }
                $totalGeral += $reg['total'];
            }
            $new_res[array_shift(array_keys($new_res))]['totalGeral'] = $totalGeral;
            return $new_res;
        } else {
            return false;
        }
    }

    // USADO POR: INC/PROCESSUPLOAD.PHP
    // ALTERACAO DE FOTOS
    public function updateFoto($params, $image, $aluno) {
        $bd = new database();

        if ($aluno)
            $sqlAdicional = ",bloqueioFoto=(SELECT i.bloqueioFoto FROM Instituicoes i)";

        $sql = "UPDATE Pessoas SET foto=(\"" . $image . "\") $sqlAdicional ";
        
        if ($params['codigo']) {
            $sql .= " WHERE codigo = :codigo";
        }

        if ($params['prontuario']) {
            $sql .= " WHERE prontuario = :codigo ";
        }

        $res = $bd->updateDB($sql, $params);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

}

?>