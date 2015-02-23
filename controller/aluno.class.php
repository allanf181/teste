<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Alunos extends Generic {
    public function __construct() {
        //
    }
    
    // USADO POR: PROFESSOR/AVISO.PHP
    public function listAlunosToJSON($atribuicao, $string) {
        $bd = new database();
    	$sql = "SELECT CONCAT('P:', p.codigo) as id, p.nome as name
                    FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                    WHERE t.codigo = a.turma AND m.atribuicao = a.codigo 
                    AND m.aluno = p.codigo 
                    AND t.codigo = a.turma
                    AND p.nome LIKE :s
                    AND a.codigo = :cod
                    GROUP BY p.codigo ORDER BY p.nome LIMIT 20"; 
        $params = array(':cod'=> $atribuicao, ':s' => '%' . $string . '%');
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
    
    // USADO POR: RELATORIOS.PHP
    public function listAlunos($params, $sqlAdicional, $camposExtra) {
        $bd = new database();
        
    	$sql = "select a.prontuario, upper(a.nome) as nome
        $camposExtra
        from Tipos ti, PessoasTipos pt, Pessoas a, Cidades c, 
            Matriculas m, Turmas t, Cursos c2, Atribuicoes at
        where pt.tipo = ti.codigo
        and a.codigo = pt.pessoa
        and a.cidade=c.codigo 
        and m.aluno=a.codigo 
        and m.atribuicao=at.codigo
        and at.turma=t.codigo 
        and t.curso=c2.codigo 
        $sqlAdicional
        group by a.codigo
        order by a.nome"; 

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