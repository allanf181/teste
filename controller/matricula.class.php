<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';


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
                    p.prontuario, a.codigo as atribuicao, a.status, d.codigo as codDisciplina,
                    IF(LENGTH(a.subturma) > 0,CONCAT(' [',a.subturma,']'),CONCAT(' [',a.eventod,']')) as subturma, 
                    p.nome as pessoa, t.numero as turma, m.codigo as matricula,
                    s.sigla, s.listar, s.habilitar, s.nome as situacao, s.codigo as codSituacao,
                    p.rg, a.bimestre, c.modalidade, ma.data,
                    IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                    date_format(p.nascimento, '%d/%m/%Y') as nascimento $campoExtra
		FROM Pessoas p, Turmas t, Turnos tu, Cursos c, 
                    Atribuicoes a, Disciplinas d, Matriculas m,
                    MatriculasAlteracoes ma, Situacoes s
		WHERE m.aluno=p.codigo 
		AND a.turma=t.codigo 
		AND d.codigo=a.disciplina
		AND m.atribuicao=a.codigo
		AND t.turno=tu.codigo 
		AND c.codigo=t.curso
                AND ma.matricula = m.codigo
                AND ma.situacao = s.codigo
                AND ma.data = (SELECT MAX(data) FROM MatriculasAlteracoes WHERE matricula = m.codigo)";
        
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
    
    // RETORNA OS DADOS DA LISTAGEM DE FREQUENCIAS
    // USADO POR: SECRETARIA/RELATORIOS/LISTAGEM.PHP,
    public function getMToJSON($params, $sqlAdicional) {
        $sqlAdicional .= ' GROUP BY t.numero,s.nome ORDER BY c.nome, t.numero, a.bimestre ';
        $campoExtra = ',COUNT(DISTINCT m.aluno) as quantidadeMatricula';
        $res = $this->getMatriculas($params, $sqlAdicional, null, null, $campoExtra);

        $item = array();
        foreach ($res as $reg) {
            $item[$reg['codSituacao']]['nome'] = $reg['situacao'];
            $item[$reg['codSituacao']]['quantidade'] += intval($reg['quantidadeMatricula']);
        }

        foreach ($item as $k => $i) {
            $item1[] = $i['nome'];
            $item2[] = $i['quantidade'];
        }
        
        $graph_data = array('item1Name' => 'Situação', 'item1' => $item1,
            'item2Name' => 'Quantidade', 'item2' => $item2,
            'item3Name' => ' ', 'item3' => $item3,
            'title' => 'Totalização de Matrículas', 
            'titleY' => 'Quantidade',
            'titleX' => 'Situação');

        return json_encode($graph_data);
    }
    
    public function isMatriculado($aluno, $aula) {
        $bd = new database();
        $sql = "SELECT count(m.codigo) as n "
                . "FROM Atribuicoes a, Matriculas m, Aulas au "
                . "WHERE m.atribuicao=a.codigo "
                . "AND au.atribuicao=a.codigo "
                . "AND m.aluno=:aluno "
                . "AND au.codigo=:aula";
        $params = array(':aluno' => $aluno, ':aula' => $aula);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]['n']>0)
            return true;
        else
            return false;
    }
    
}

?>