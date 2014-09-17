<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class Matriculas extends Generic {
    
    public function __construct(){
        //
    }
    
    // USADO POR: BOLETIM.PHP, RELATORIO.PHP
    // Retorna dados da matricula (Disciplina, Turma, etc..)
    public function getMatriculas($params, $sqlAdicional = null, $item = null, $itensPorPagina = null, $campoExtra = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        $sql = "SELECT d.numero, d.nome as disciplina, p.codigo as codPessoa,
                    IF(a.bimestre > 0, CONCAT(' [', a.bimestre,'º BIM]'), '') as bimestreFormat,
                    p.prontuario, a.codigo as atribuicao, s.sigla, s.listar, s.habilitar,
                    IF(LENGTH(a.subturma) > 0,CONCAT(' [',a.subturma,']'),CONCAT(' [',a.eventod,']')) as subturma, 
                    s.nome as situacao, s.codigo as codSituacao, a.status, d.codigo as codDisciplina,
                    p.nome as pessoa, t.numero as turma, m.codigo as matricula,
                    DATE_FORMAT(m.data, '%d/%m/%Y') as data, p.rg, a.bimestre,
                    IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                    date_format(p.nascimento, '%d/%m/%Y') as nascimento $campoExtra
		FROM Matriculas m, Pessoas p, Turmas t, Turnos tu, Cursos c, 
                    Atribuicoes a, Disciplinas d, Situacoes s
		WHERE m.aluno=p.codigo 
		AND a.turma=t.codigo 
		AND d.codigo=a.disciplina
		AND m.atribuicao=a.codigo
		AND t.turno=tu.codigo 
		AND c.codigo=t.curso
		AND m.situacao=s.codigo";
        
        $sql .= $sqlAdicional;
        
        $sql .= $nav;
                
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }  

    // USADO POR: PROFESSOR/NOTA.PHP
    // Retorna a matricula
    public function getMatricula($aluno, $atribuicao, $bimestre) {
        $bd = new database();
        
        $sql = "SELECT m.codigo "
                . "FROM Atribuicoes a, Matriculas m "
                . "WHERE m.atribuicao=a.codigo "
                . "AND a.codigo=:att "
                . "AND m.aluno=:aluno "
                . "AND a.bimestre=:bim";
        
        $params = array(':att' => $atribuicao, ':aluno' => $aluno, ':bim' => $bimestre);
        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res[0]['codigo'];
        else
            return false;
    }  
    
}

?>