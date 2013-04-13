<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Estados extends Generic {
    
    public function __construct(){
        //
    }
    
    // MÉTODO PARA INSERÇÃO DE OBJETO
    public function insertOrUpdateEstado($params) {
        return $this->insertOrUpdate($params);
    }
    
    public function listEstados($params, $item=null, $itensPorPagina=null) {
        return $this->listRegistros($params, $item, $itensPorPagina);
    }
    
    public function deleteEstado($codigo) {
        return $this->delete($codigo);
    }
}

?>