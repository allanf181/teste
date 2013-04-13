<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Turnos extends Generic {
    
    public function __construct(){
        //
    }
    
    // MÉTODO PARA INSERÇÃO DE OBJETO
    // USADO POR: VIEW/SECRETARIA/ABONO.PHP
    public function listTurnos() {
        return $this->listRegistros();
    }
}

?>