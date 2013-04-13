<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

Abstract class Generic {
    
    public function __construct(){
        //
    }
    
    // MÉTODO PARA INSERÇÃO DE OBJETO
    public function insertOrUpdate($params) {
        $bd = new database();
        
        foreach(array_keys($params) as $key) {
            if ($key == 'codigo') $INS = 'NULL,';
            else {
                $INS .= ':'.$key.',';
                $UP .= $key.'=:'.$key.',';
            }
        }
        $INS = substr_replace($INS, "", -1);
        $UP = substr_replace($UP, "", -1);
        
        $table = get_called_class();
        if (!$params['codigo']) {
            $sql = "INSERT INTO $table VALUES ($INS)";
            unset($params['codigo']);
            $res = $bd->insertDB($sql, $params);        
        } else {
            $sql = "UPDATE $table SET $UP "
                . "WHERE codigo=:codigo";
            $res = $bd->updateDB($sql, $params);
        }

        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
    
    public function listRegistros($params, $item=null, $itensPorPagina=null) {
        $bd = new database();
        $table = get_called_class();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        if (!$params['codigo']) {
            $sql = "SELECT * FROM $table $nav";
        } else {
            $sql = "SELECT * FROM $table "
                . "WHERE codigo=:codigo $nav";
        }
        
        $res = $bd->selectDB($sql, $params);
        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
    
    public function delete($codigo) {
        $bd = new database();
        $table = get_called_class();

        // PDO NÃO ACEITA VÁRIOS ARGUMENTOS PARA DELETE
        // É NECESSÁRIO PREPARAR A QUERY
        // OBS: NÃO FOI FEITO DIRETO PARA NÃO COMPROMETER
        // A SEGURANÇA FORNECIDA PELO PDO CONTRA SQLInjection
        $codigo = explode(',',$codigo);
        if ($codigo[0] == '0') unset($codigo[0]);
        
        $i=0;
        foreach ($codigo as $value) {
            $indice = 'A'.$i;
            $new_array[$indice] = $value;
            $new_params[] = ':'.$indice;
            $i++;
        }
        $param = implode($new_params, ',');
        $sql = "DELETE FROM $table WHERE codigo IN ($param)";
              
        $params = $new_array;
        $res = $bd->deleteDB($sql, $params);

        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }    
}

?>