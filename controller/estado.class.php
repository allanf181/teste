<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

class Estado {
    
    public function __construct(){
        //
    }
    
    // MÉTODO PARA INSERÇÃO DE OBJETO
    // USADO POR: VIEW/SECRETARIA/ESTADO.PHP
    public function insertOrUpdate($params) {
        $bd = new database();

        if (!$params['codigo']) {
            $sql = "INSERT INTO Estados VALUES (NULL, :nome, :sigla)";
            unset($params['codigo']);
            $res = $bd->insertDB($sql, $params);        
        } else {
            $sql = "UPDATE Estados SET nome=:nome, sigla=:sigla "
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
    
    public function listEstados($params, $item=null, $itensPorPagina=null) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        if (!$params['codigo']) {
            $sql = "SELECT * FROM Estados $nav";
        } else {
            $sql = "SELECT * FROM Estados "
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
        $sql = "DELETE FROM Estados WHERE codigo IN ($param)";
              
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