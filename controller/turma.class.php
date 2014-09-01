<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Turmas extends Generic {

    public function __construct() {
        //
    }

    // UTILIZADO POR: SECRETARIA/AVISO.PHP
    public function listTurmasToJSON($string, $ano, $semestre) {
        $bd = new database();

        $sql = "SELECT CONCAT('T:', t.codigo) as id, t.numero as name
          		from Turmas t, Cursos c
           		where t.curso=c.codigo
           		and t.ano=:ano 
           		and (t.semestre=:sem OR t.semestre=0)
                        and t.numero LIKE :s
                        ORDER BY t.numero DESC LIMIT 10";

        $params = array(':s' => '%'.$string.'%',':ano' => $ano,':sem' => $semestre);
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
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

        $params = array(':ano' => $ANO, ':semestre' => $SEMESTRE);
        $res = $bd->selectDB($sql, $params);

        foreach ($res as $reg) {
            if ($reg['codModalidade'] < 1000 || $reg['codModalidade'] >= 2000)
                $reg['curso'] = $reg['curso'] . ' [' . $reg['modalidade'] . ']';
            $new[$reg['codTurma']]['codigo'] = $reg['codTurma'];
            $new[$reg['codTurma']]['nome'] = '[' . $reg['numero'] . '] ' . $reg['curso'] . ' (' . $reg['codCurso'] . ')';
        }

        if ($new)
            return $new;
        else
            return false;
    }

}

?>