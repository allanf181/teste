<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

class Ftd {

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem correções para FTD
    // Pode ser colocado com função no MySQL futuramente
    public function hasChangeFtd($codigo, $ano, $semestre) {
        $bd = new database();
        $sql = "SELECT (SELECT nome FROM Pessoas "
                    . "WHERE codigo = fd.solicitante) as ftdSolicitante, "
                    . "fd.solicitacao as ftdSolicitacao "
                    . "FROM FTDDados fd "
                    . "WHERE fd.ano = :ano "
                    . "AND fd.semestre = :sem "
                    . "AND fd.valido = '0000-00-00 00:00:00' "
                    . "AND fd.professor = :cod";
        $params = array(':cod'=> $codigo, ':ano'=> $ano, ':sem'=> $semestre);
        $res = $bd->selectDB($sql, $params);

        if ( $res )
        {
            return $res[0];
        }
        else
        {
            return false;
        }
    }    
}

?>