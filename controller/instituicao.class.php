<?php

if(!class_exists('database'))
{
    require_once MYSQL;
}

class Instituicao {
    
    public function __construct(){}
    
    // MÉTODO PARA CARREGAR VARIÁVEIS DE AMBIENTE
    // USADO POR: INC/VARIAVEIS.PHP
    public function dadosInstituicao() {
        $bd = new database();
        $sql = "SELECT * FROM Instituicoes";
        $res = $bd->selectDB($sql);

        if ( $res )
        {
            while ($res) {
                return $res[0];
            }
        }
    }
    
}

