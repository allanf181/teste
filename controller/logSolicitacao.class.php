<?php

if (!class_exists('Generic')) {
    require_once CONTROLLER . '/generic.class.php';
}

class LogSolicitacoes extends Generic {

    public function __construct() {
        //
    }

    public function updateSolicitacao($params) {
        $bd = new database();

        $sql = "UPDATE LogSolicitacoes SET dataConcessao = :data "
                . "WHERE nomeTabela = :nome AND codigoTabela = :codigo "
                . "AND ( dataConcessao = '0000-00-00 00:00:00' OR dataConcessao IS NULL)";

        $res = $bd->updateDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function listSolicitacoes($params, $sqlAdicional = null) {
        $bd = new database();

        $sql = "SELECT l.solicitacao,l.solicitante,"
                . "date_format(l.dataSolicitacao, '%d/%m/%Y %H:%i') as dataSolicitacao,"
                . "date_format(l.dataConcessao, '%d/%m/%Y %H:%i') as dataConcessao, "
                . "p.nome as solicitante "
                . "FROM LogSolicitacoes l, Pessoas p "
                . "WHERE l.solicitante = p.codigo "
                . "AND codigoTabela = :codigoTabela "
                . "AND nomeTabela = :nomeTabela ";

        $sql .= $sqlAdicional;

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>