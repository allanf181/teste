<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class PlanosAulas extends Generic {
    
    public function __construct(){
        //
    }
    
    // USADO POR: PROFESSOR/AULA.PHP
    // LISTA AS SEMANAS DE AULA DO PROFESSOR
    public function listPlanoAulas($codigo) {
        $bd = new database();
        
        $sql = "SELECT pa.conteudo, pa.semana, pe.numeroAulaSemanal 
                FROM PlanosAula pa, PlanosEnsino pe, Atribuicoes a, Disciplinas d, Turmas t
                WHERE pa.atribuicao = pe.atribuicao
                AND pe.atribuicao = a.codigo 
                AND a.disciplina = d.codigo 
                AND t.codigo = a.turma
                AND d.numero IN (SELECT d1.numero FROM Atribuicoes a1, Disciplinas d1 
                                    WHERE a1.disciplina = d1.codigo AND a1.codigo = :cod)
                AND t.numero IN (SELECT t2.numero FROM Atribuicoes a2, Turmas t2 
                                    WHERE a2.turma = t2.codigo AND a2.codigo = :cod )";
        $params = array(':cod'=> $codigo);
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
}

?>