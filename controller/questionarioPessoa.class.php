<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class QuestionariosPessoas extends Generic {
    
    public function listQuestionariosPessoas($POST) {
        $bd = new database();
        $table = get_called_class();

        $sql = "SELECT qp.codigo,
	 	 (SELECT p.nome FROM Pessoas p WHERE p.codigo = qp.destinatario) AS pessoa, 
	 	 (SELECT c.nome FROM Cursos c WHERE c.codigo = qp.curso) AS curso,
	 	 (SELECT c.codigo FROM Cursos c WHERE c.codigo = qp.curso) AS codCurso,
	 	 (SELECT d.numero FROM Disciplinas d WHERE d.codigo = qp.atribuicao) AS disciplina,
	 	 (SELECT t.nome FROM Tipos t WHERE t.codigo = qp.tipo) AS tipo,
	 	 (SELECT tr.numero FROM Turmas tr WHERE tr.codigo = qp.turma) AS turma
     	 	 FROM $table qp WHERE qp.questionario = :questionario";

        $res = $bd->selectDB($sql, $POST);

        if ($res)
            return $res;
        else
            return false;
    }

}

?>
