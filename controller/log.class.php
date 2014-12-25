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
    public function getLOGINToJSON($pessoa) {
        $bd = new database();

        $sql = "SELECT COUNT(*) as total, url, "
                . "(SELECT date_format(MAX(data), '%d/%m/%Y às %H:%i') FROM Logs WHERE pessoa = :pessoa LIMIT 1,1) as last "
                . "FROM Logs "
                . "WHERE ORIGEM = 'LOGIN' "
                . "AND pessoa = :pessoa "
                . "GROUP BY url LIMIT 20";

        $params = array('pessoa' => $pessoa);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                $item1[] = $reg['url'];
                $item2[] = intval($reg['total']);
            }
        }

        $graph_data = array('item1Name' => 'Data', 'item1' => $item1,
            'item2Name' => 'Acessos', 'item2' => $item2,
            'title' => 'Seus Acessos (por IP)', 'titleY' => 'Quantidade', 'titleX' => 'Último acesso: '.$res[0]['last']);

        return json_encode($graph_data);
    }

}

?>