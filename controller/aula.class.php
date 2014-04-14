<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

class Aula {
    
    public function __construct(){
        //
    }
    
    // LISTA OS CONTEUDOS DAS AULAS DO ALUNO
    // USADO POR: VIEW/ALUNO/AULA.PHP
    public function listAulas($aluno, $atribuicao) {
        $bd = new database();
        
    $sql = "SELECT (
		SELECT CONCAT_WS(',',f.quantidade,m.codigo)
		FROM Frequencias f, Matriculas m
		WHERE f.aula = a.codigo
		AND f.matricula = m.codigo
		AND m.aluno = :aluno
		) as freqMat, a.quantidade as quantidade,
                a.data as data, 
                date_format(a.data, '%d/%m/%Y') as dataFormatada,
                a.conteudo as conteudo
            FROM Aulas a
            WHERE a.atribuicao = :atr
            ORDER BY a.data, a.codigo";

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