<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Logs extends Generic {

    // USADO POR: ADMIN/LOGS.PHP
    public function listLogs($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";

        $sql = "SELECT l.codigo, l.url, 
                    date_format(l.data, '%d/%m/%Y %H:%i:%s') as data, 
                    p.codigo, p.nome as pessoa, l.origem
                    FROM Logs l, Pessoas p
                    WHERE l.pessoa = p.codigo ";

        $sql .= $sqlAdicional;

        $sql .= $nav;

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // USADO POR: INC/HOME.PHP
    // Verifica se o CRON está sendo utilizado.
    public function hasCronActive() {
        $bd = new database();
        $sql = "SELECT DATEDIFF( NOW( ) , data ) as dias "
                . "FROM Logs WHERE origem LIKE 'CRON%' "
                . "ORDER BY data DESC LIMIT 1";
        $res = $bd->selectDB($sql);

        if ($res[0]['dias'] > 10 || $res[0]['dias'] === NULL) {
            return true;
        } else {
            return false;
        }
    }

    // LISTA OS LOGS ANTERIORES
    // USADO POR: HOME.PHP
    public function getLastAccess($pessoa) {
        $bd = new database();

        $sql = "SELECT date_format(data, '%d/%m/%Y às %H:%i') as data
                FROM Logs l
                WHERE l.ORIGEM =  'LOGIN'
                AND l.pessoa = :pessoa
                ORDER BY  l.data DESC LIMIT 1";

        $params = array('pessoa' => $pessoa);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return '&Uacute;ltimo acesso: <br> ' . $res[0]['data'];
        else
            return 'Primeiro acesso.';
    }

}

?>