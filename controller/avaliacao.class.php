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
}

?>