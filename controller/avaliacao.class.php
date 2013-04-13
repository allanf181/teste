<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';


class Avaliacoes extends Generic {
    
    public function __construct(){
        //
    }
    
    // LISTA AVALIACOES DO ALUNO
    // USADO POR: VIEW/ALUNO/AVALIACAO.PHP
    public function listAvaliacoes($aluno, $atribuicao) {
        $bd = new database();
        
        $sql = "SELECT date_format(a.data, '%d/%m/%Y') as data, a.nome as conteudo,
    			(SELECT nota FROM Notas n, Matriculas m 
    				WHERE n.matricula = m.codigo 
    				AND m.aluno = :aluno
    				AND m.atribuicao = :atr
    				AND n.avaliacao = a.codigo) as falta 
    			FROM Avaliacoes a WHERE a.atribuicao = :atr";

        $params = array(':aluno'=> $aluno,':atr'=> $atribuicao);
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
    
    // USADO POR: VIEW/PROFESSOR/PROFESSOR.PHP
    public function getQdeAvaliacoes($atribuicao) {
        $bd = new database();
        
        $sql = "SELECT (SELECT count(av.codigo) 
                        FROM Avaliacoes av, TiposAvaliacoes t1 
                        WHERE av.tipo = t1.codigo 
                        AND av.atribuicao = a.codigo 
                        AND t1.tipo = 'avaliacao') as avalCadastradas,
    		t.qdeMinima as qdeMinima
		FROM TiposAvaliacoes t, Modalidades m, Turmas tu, Cursos c, Atribuicoes a
		WHERE t.modalidade = m.codigo
		AND c.codigo = tu.curso
		AND c.modalidade = m.codigo
		AND a.turma = tu.codigo
		AND a.codigo = :atr
		AND t.tipo = 'avaliacao'";

        $params = array(':atr'=> $atribuicao);
        $res = $bd->selectDB($sql, $params);
        if ( $res[0] )
        {
            return $res[0];
        }
        else
        {
            return false;
        }
    }
    
}

?>