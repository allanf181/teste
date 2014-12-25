<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Matriculas extends Generic {
    
    public function __construct(){
        //
    }
    
    // USADO POR: BOLETIM.PHP
    // Retorna dados da matricula (Disciplina, Turma, etc..)
    public function getDadosMatricula($aluno, $turma, $bimestre = null) {
        $bd = new database();
        
        if ($bimestre) {
            $params['bimestre'] = $bimestre;
            $bimestre = " AND at.bimestre = :bimestre";
        }
        
        $sql = "SELECT d.numero, d.nome as disciplina,
                    a.prontuario, at.bimestre, at.codigo as atribuicao, 
                    s.nome as situacao, at.status, d.codigo as codDisciplina,
                    a.nome as pessoa, t.numero as turma, m.codigo as matricula,
                    IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso
		FROM Matriculas m, Pessoas a, Turmas t, Turnos tu, Cursos c, 
                    Atribuicoes at, Disciplinas d, Situacoes s
		WHERE m.aluno=a.codigo 
		AND at.turma=t.codigo 
		AND d.codigo=at.disciplina
		AND m.atribuicao=at.codigo
		AND t.turno=tu.codigo 
		AND c.codigo=t.curso
		AND m.situacao=s.codigo
		AND a.codigo=:aluno
		AND t.codigo=:turma
		ORDER BY at.bimestre, d.nome";
        
        $sql .= $bimestre;
        
        $params['aluno'] = $aluno;
        $params['turma'] = $turma;
        
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }    
}

?>