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

    //UTILIZADO POR: SECRETARIA/ABONO.PHP
    //RETORNA PESSOAS DE UM DETERMINADO TIPO
    public function listPessoasTipos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

        $sql = "SELECT p.codigo, p.nome as nome,
                p.prontuario, p.email
               	FROM Pessoas p, PessoasTipos pt
               	WHERE p.codigo = pt.pessoa
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
        $sql = "SELECT p.codigo, p.nome, bloqueioFoto, COUNT(*) as total
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

}

?>