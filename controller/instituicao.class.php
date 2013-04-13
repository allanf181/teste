<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Instituicoes extends Generic {
    
    public function __construct(){}
    
    // MÉTODO PARA CARREGAR VARIÁVEIS DE AMBIENTE
    // USADO POR: INC/VARIAVEIS.PHP
    public function dadosInstituicao() {
        return $this->listRegistros();
    }
    
}

