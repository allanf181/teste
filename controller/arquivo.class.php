<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Arquivos extends Generic {

    public function __construct() {
        //
    }

    // Sincroniza pasta do usuário com o banco
    public function syncFolder($dir, $atribuicao) {
        $bd = new database();

        // VERIFICANDO SE HÁ ARQUIVOS NO DIR, MAS NÃO NO BANCO
        $dir = ARQUIVOS . '/' . $dir;
        if (is_dir($dir)) {
            $files = dirToArray($dir);
            foreach ($files as $f) {
                $parts = explode('/', $f[0]);
                $sql = "SELECT arquivo FROM Arquivos "
                        . "WHERE atribuicao = :atribuicao "
                        . "AND pessoa = :pessoa "
                        . "AND arquivo = :arquivo";
                $params = array('atribuicao' => $parts[2],
                    'pessoa' => $parts[1],
                    'arquivo' => $parts[3]);
                $res = $bd->selectDB($sql, $params);
                if (!$res) {
                    $params['descricao'] = 'SEM DESCRIÇÃO';
                    $res = $this->insertOrUpdate($params);
                }
            }
        }

        //VERIFICANDO SE HÁ NO BANCO, MAS NÃO NO DIR
        $sql = "SELECT codigo, pessoa, arquivo FROM Arquivos WHERE atribuicao = :att AND (arquivo <> NULL OR arquivo <> '')";
        $params = array(':att' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            foreach ($res as $reg) {
                $file = ARQUIVOS . '/' . $reg['pessoa'] . '/' . $atribuicao . '/' . $reg['arquivo'];
                if (!is_file($file))
                    $this->delete(crip($reg['codigo']));
            }
        }
    }

    // MÉTODO PARA INSERÇÃO DE OBJETO
    public function insertOrUpdateArquivo($params, $dir) {

        $dir_final = ARQUIVOS . '/' . $dir;

        $rs['TIPO'] = 'ARQUIVO';
        $rs['STATUS'] = 'ERRO';

        if ($_FILES['arquivo']['tmp_name']) {
            // CRIANDO O DIR DE ACORDO COM AS SUBPASTAS
            if ($rs['RESULTADO'] = $this->makeDirStructure($dir))
                return $rs;

            if (is_file($dir_final . '/' . $_FILES['arquivo']['name'])) {
                $rs['RESULTADO'] = "Esse arquivo j&aacute; existe em seu diret&oacute;rio: " . $dir_final . '/' . $_FILES['arquivo']['name'];
                return $rs;
            }

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $dir_final . '/' . $_FILES['arquivo']['name'])) {
                $rs['RESULTADO'] = "Erro ao gravar arquivo em: " . $dir_final . '/' . $_FILES['arquivo']['name'];
                return $rs;
            }
        }

        unset($params['data']);
        if ($_FILES['arquivo']['name'])
            $params['arquivo'] = $_FILES['arquivo']['name'];
        return $this->insertOrUpdate($params);
    }

    // MÉTODO PARA INSERÇÃO DE OBJETO
    public function deleteArquivo($codigo, $dir) {
        $bd = new database();

        $dir_final = ARQUIVOS . '/' . $dir;
        $codigos = explode(',', $codigo);
        foreach ($codigos as $value) {

            $sql = "SELECT arquivo FROM Arquivos WHERE codigo = :cod";
            $params = array(':cod' => dcrip($value));
            $res = $bd->selectDB($sql, $params);

            if ($res[0]['arquivo'] != null) {

                unlink($dir_final . '/' . $res[0]['arquivo']);
                if (!is_file($dir_final . '/' . $res[0]['arquivo'])) {
                    $ERRO = 0;
                } else {
                    $rs['TIPO'] = 'ARQUIVO';
                    $rs['STATUS'] = 'ERRO';
                    $rs['RESULTADO'] = 'N&atilde;o foi poss&iacute;vel apagar o arquivo.';
                    $ERRO = 1;
                }
            }
        }
        if (!$ERRO)
            return $this->delete($codigo);
        else
            return $rs;
    }

    // USADO POR: PROFESSOR/ARQUIVO.PHP
    // LISTA OS ARQUIVOS EQUIVALENTES
    public function getArquivoEquivalente($codigo) {
        $bd = new database();
        $sql = "SELECT a.codigo, d.nome, t.numero, t.ano, t.semestre, a.eventod, a.subturma 
        		FROM Arquivos ar, Disciplinas d, Atribuicoes a, Turmas t
        		WHERE ar.atribuicao = a.codigo
        		AND a.disciplina = d.codigo
        		AND a.turma = t.codigo
        		AND d.numero IN (SELECT d1.numero 
        				FROM Disciplinas d1, Atribuicoes a1 
        				WHERE a1.disciplina = d1.codigo 
        				AND d1.numero = d.numero AND a1.codigo = :cod)
        		AND a.codigo <> :cod
        		GROUP BY a.codigo 
        		ORDER BY d.nome";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function listArquivos($params, $item = null, $itensPorPagina = null) {
        $bd = new database();
        $params = dcripArray($params);

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT * FROM Arquivos WHERE atribuicao = :codigo";

        $sql .= "$nav";
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }

    // USADO POR: PROFESSOR/ARQUIVO.PHP
    // COPIA ARQUIVO DE DISCIPLINAS EQUIVALENTES
    public function copyArquivo($pessoa, $atribuicao, $copia) {
        $bd = new database();

        $dir_final = ARQUIVOS . '/' . $pessoa . '/' . $atribuicao;

        // TENTA CRIAR A ESTRUTURA DE DIRETÓRIOS
        if ($rs['RESULTADO'] = $this->makeDirStructure($pessoa . '/' . $atribuicao))
            return $rs;

        $sql = "SELECT * FROM Arquivos WHERE atribuicao = :cod";
        $params = array(':cod' => $copia);
        $res = $bd->selectDB($sql, $params);

        $erros = 0;
        $copias = 0;
        $exite = 0;
        if ($res) {
            foreach ($res as $reg) {
                $inserir = 1;
                if ($reg['arquivo']) {
                    if (is_file($dir_final . '/' . $reg['arquivo'])) {
                        $exite++;
                        $inserir = 0;
                    } else {
                        $origem = ARQUIVOS . '/' . $reg['pessoa'] . '/' . $reg['atribuicao'] . '/' . $reg['arquivo'];
                        if (!copy($origem, $dir_final . '/' . $reg['arquivo'])) {
                            $erros++;
                            $inserir = 0;
                        }
                    }
                }
                if ($inserir) {
                    $params = array('arquivo' => $reg['arquivo'],
                        'atribuicao' => $atribuicao,
                        'pessoa' => $pessoa,
                        'link' => $reg['link'],
                        'descricao' => $reg['descricao']);
                    $res = $this->insertOrUpdate($params);

                    if ($res)
                        $copias++;
                }
            }
        }

        $rs['TIPO'] = 'ARQUIVO';
        $rs['STATUS'] = 'INFO';
        $rs['RESULTADO'] = "ERROS: $erros<br />ARQUIVOS COPIADOS: $copias <br />ARQUIVOS N&Atilde;O COPIADOS, J&Aacute; EXISTE COM O MESMO NOME: $exite";

        return $rs;
    }

    public function makeDirStructure($dir) {
        if (is_writable(ARQUIVOS)) {
            $dirs = explode('/', $dir);
            foreach ($dirs as $dir) {
                $cdir .= '/' . $dir;
                $ddir = ARQUIVOS . $cdir;
                if (!is_dir($ddir) && !mkdir($ddir, 0700)) {
                    $rs = "Erro ao criar diret&oacute;rio: $ddir";
                    return $rs;
                }

                if (!is_writable($ddir)) {
                    $rs = "Erro ao acessar diret&oacute;rio: $ddir";
                    return $rs;
                }
            }
            return '';
        } else {
            $rs = "A pasta " . ARQUIVOS . " n&atilde;o exite ou n&atilde;o tem permiss&atilde;o de escrita por: " . get_current_user();
            return $rs;
        }
    }

}

?>