<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class PlanosAula extends Generic {
    
    public function __construct(){
        //
    }
    
    // USADO POR: PROFESSOR/AULA.PHP
    // LISTA AS SEMANAS DE AULA DO PROFESSOR
    public function getConteudosAulas($codigo) {
        $bd = new database();

        $sql = "SELECT pa.conteudo, pa.semana, pe.numeroAulaSemanal 
		FROM PlanosAula pa, PlanosEnsino pe, Atribuicoes a, Disciplinas d, Turmas t
		WHERE pa.atribuicao = pe.atribuicao 
		AND pe.atribuicao = a.codigo
		AND a.disciplina = d.codigo
                AND t.codigo = a.turma
		AND d.numero IN (SELECT d1.numero 
				FROM Atribuicoes a1, Disciplinas d1, Turmas t1
				WHERE a1.disciplina = d1.codigo 
                                AND t1.codigo = a1.turma
                                AND t.numero = t1.numero
				AND a1.codigo = :cod)
                ORDER BY pa.semana";

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
    
    // USADO POR: PROFESSOR/PLANO.PHP
    // LISTA AS SEMANAS DE AULA DO PROFESSOR
    public function listPlanoAulas($codigo) {
        $bd = new database();

        $sql = "SELECT pa.codigo, pa.conteudo, pa.semana, pe.numeroAulaSemanal 
                FROM PlanosAula pa, PlanosEnsino pe, Atribuicoes a, Disciplinas d, Turmas t
                WHERE pa.atribuicao = pe.atribuicao
                AND pe.atribuicao = a.codigo 
                AND a.disciplina = d.codigo 
                AND t.codigo = a.turma
                AND a.codigo = :cod
                ORDER BY pa.semana";

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