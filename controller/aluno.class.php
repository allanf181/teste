<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

class Aluno {

    // USADO POR: INC/HOME.PHP
    // Verifica se o aluno completou o SocioEconomico
    // Pode ser colocado com função no MySQL futuramente
    public function hasSocioEconomico($codigo) {
        $bd = new database();
        $sql = "SELECT nome FROM Pessoas "
                . "WHERE "
                . "(estadoCivil IS NULL "
                . "OR estadoCivil = ''"
                . "OR numeroPessoasNaResidencia IS NULL "
                . "OR numeroPessoasNaResidencia = '' "
                . "OR numeroPessoasNaResidencia = 0 "
                . "OR situacaoTrabalho IS NULL "
                . "OR situacaoTrabalho = '' "
                . "OR meioTransporte IS NULL "
                . "OR meioTransporte = '' "
                . "OR transporteGratuito IS NULL "
                . "OR transporteGratuito = '' "
                . "OR necessidadesEspeciais IS NULL "
                . "OR necessidadesEspeciais = '' "
                . "OR escolaPublica IS NULL "
                . "OR escolaPublica = '') "
                . "AND codigo= :cod";
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);
        if ( $res )
        {
            return $res[0]['nome'];
        }
        else
        {
            return false;
        }
    }    
}

?>