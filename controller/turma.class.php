<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Turmas extends Generic {
    
    public function __construct(){
        //
    }
    
    public function listTurmas($ANO, $SEMESTRE) {
        $bd = new database();

        $sql = "SELECT t.codigo as codTurma, t.numero as numero, c.nome as curso,
                    m.nome as modalidade, m.codigo as codModalidade, c.codigo as codCurso
                    FROM Turmas t, Cursos c, Modalidades m
	            WHERE t.curso = c.codigo 
	            AND m.codigo = c.modalidade
	            AND ano = :ano
	            AND (semestre= :semestre OR semestre=0) 
	            ORDER BY c.nome, t.numero";

        $params = array (':ano' => $ANO, ':semestre' => $SEMESTRE);
        $res = $bd->selectDB($sql, $params);

        foreach ($res as $reg) {
            if ($reg['codModalidade'] < 1000 || $reg['codModalidade'] >= 2000)
                $reg['curso'] = $reg['curso'].' ['. $reg['modalidade'].']';
            $new[$reg['codTurma']]['codigo'] = $reg['codTurma'];
            $new[$reg['codTurma']]['nome'] = '['.$reg['numero'].'] '.$reg['curso'].' ('.$reg['codCurso'].')';
        }

        if ($new)
            return $new;
        else
            return false;
    }    
}

?>