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

        $params = array(':s' => '%'.$string.'%');
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        
        return false;
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
}

?>