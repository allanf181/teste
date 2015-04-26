<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class PlanosAula extends Generic {
    
    public function __construct(){
        //
    }
    
    // USADO POR: PROFESSOR/AULA.PHP
    // LISTA AS SEMANAS DE AULA DO PROFESSOR
    public function getConteudosAulas($codigo, $ano, $semestre) {
        $bd = new database();

        $sql = "SELECT pa.conteudo, pa.semana, pe.numeroAulaSemanal 
		FROM PlanosAula pa, PlanosEnsino pe, Atribuicoes a, 
                    Disciplinas d, Turmas t, Cursos c
		WHERE pa.atribuicao = pe.atribuicao 
		AND pe.atribuicao = a.codigo
		AND a.disciplina = d.codigo
                AND t.codigo = a.turma
                AND c.codigo = t.curso
		AND (
                        (pa.atribuicao = :cod 
                            AND t.ano = :ano 
                            AND (t.semestre = 0 OR t.semestre = :semestre)
                        )
                    OR (d.numero IN (SELECT d1.numero 
                            FROM Disciplinas d1, Atribuicoes a1 
                            WHERE a1.disciplina = d1.codigo 
                            AND d1.numero = d.numero 
                            AND a1.codigo = :cod
                            AND t.ano = :ano)
                        AND 
                        t.numero IN (SELECT t1.numero 
                            FROM Atribuicoes a1, Turmas t1
                            WHERE t1.codigo = a1.turma
                            AND a1.codigo = :cod
                            AND t.ano = :ano)
                        )
                    )
                GROUP BY pa.semana
                ORDER BY pa.semana";

        $params = array(':cod'=> $codigo, ':ano'=> $ano, ':semestre'=> $semestre);
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