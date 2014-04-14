<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

class Log {

    // USADO POR: INC/HOME.PHP
    // Verifica se o CRON está sendo utilizado.
    public function hasCronActive() {
        $bd = new database();
        $sql = "SELECT DATEDIFF( NOW( ) , data ) as dias "
                . "FROM Logs WHERE origem LIKE 'CRON%' "
                . "ORDER BY data DESC LIMIT 1";
        $res = $bd->selectDB($sql);

        if ($res[0]['dias'] > 10 || $res[0]['dias'] === NULL)
        {
            return true;
        }
        else
        {
            return false;
        }
    }    
}

?>