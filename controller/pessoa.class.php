<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Pessoas extends Generic {

    public function __construct() {
        //
    }
    
    public function insertOrUpdatePessoa($params) {
        return $this->insertOrUpdate($params);
    }
    
    public function listPessoas($params, $item=null, $itensPorPagina=null) {
        return $this->listRegistros($params, $item, $itensPorPagina);
    }
    
    public function listPessoasTipos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

        $sql = "SELECT p.codigo as codigo, p.nome as nome,
                p.prontuario as prontuario
               	FROM Pessoas p, PessoasTipos pt
               	WHERE p.codigo = pt.pessoa
                $sqlAdicional
		ORDER BY p.nome";
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

    // USADO POR: INC/FILE.PHP
    public function getFoto($codigo) {
        $bd = new database();
        $sql = "SELECT foto, bloqueioFoto FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]) {
            return $res[0];
        } else {
            return $sql;
        }
    }

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem foto
    public function hasPicture($codigo) {
        $bd = new database();
        $sql = "SELECT foto FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]['foto']) {
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

    // USADO POR: HOME.PHP
    // Atualiza o Lattes do Usuário
    public function updateLattes($codigo, $lattes) {
        $bd = new database();
        $sql = "UPDATE Pessoas SET lattes = :lattes WHERE codigo = :cod";
        $params = array(':cod' => $codigo, ':lattes' => $lattes);
        $res = $bd->updateDB($sql, $params);

        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    // USADO POR: HOME.PHP
    // Retorna o Lattes do Usuário
    public function showLattes($codigo) {
        $bd = new database();
        $sql = "SELECT lattes FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]['lattes']) {
            if (strpos($res[0]['lattes'], 'http://') === FALSE) {
                return "http://" . $res[0]['lattes'];
            } else {
                return $res[0]['lattes'];
            }
        } else {
            return false;
        }
    }
}

?>