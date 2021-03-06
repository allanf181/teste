<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Professores extends Generic {

    public function getProfessor($atribuicao, $nome = null, $separador = null, $lattes = null, $foto = null, $abreviar = null) {
        $bd = new database();
        $sql = "SELECT p.codigo, p.nome, p.lattes "
                . "FROM Professores pr, Pessoas p "
                . "WHERE p.codigo = pr.professor "
                . "AND atribuicao = :cod";

        $params = array(':cod' => $atribuicao);

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            foreach ($res as $reg) {
                $r = null;
                if ($abreviar && $nome)
                    $r .=  abreviar ($reg['nome'], $abreviar);
                
                if (!$abreviar && $nome)
                    $r .= $reg['nome'];

                if ($foto)
                    $r .= "<a href='#' rel='" . INC . "/file.inc.php?type=pic&id=".crip($reg['codigo'])."&timestamp=".time()."' class='screenshot' title='".$reg['nome']."'>
                              <img style='width: 10px' alt='Embedded Image' src='" . INC . "/file.inc.php?type=pic&id=".crip($reg['codigo'])."&timestamp=".time()."' />  </a>";
                
                if ($lattes && $reg['lattes'])
                    $r .= "<a title='Curr&iacute;culo Lattes' data-placement='bottom' data-content='Clique para visualizar' target='_blank' href='" . $reg['lattes'] . "'>&nbsp;<img style='width: 10px' src='".ICONS."/lattes.jpg' /></a>";

                $professores[] = $r;
            }

            if (!$separador)
                $separador = ' / ';

            $professor = implode($separador, $professores);

            return $professor;
        }
        else {
            return false;
        }
    }

    // USADO POR: AULATROCA.PHP, 
    // ATRIBUICAO.PHP, PLANO.PHP, DIARIO.PHP
    // LISTA OS PROFESSORES DA BASE
    public function listProfessores($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT DISTINCT p.codigo, p.nome, p.lattes, p.prontuario
                    FROM Pessoas p, PessoasTipos pt, Professores pr
                    WHERE p.codigo = pt.pessoa
                    AND pt.tipo = :tipo
                    AND pr.professor = p.codigo";

        $sql .= " $sqlAdicional ";

        $sql .= ' ORDER BY p.nome ';

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }

    // USADO POR: SECRETARIA/PROFESSORATRIBUICAO.PHP
    // LISTA OS PROFESSORES DE UMA DETERMINADA TURMA
    public function getProfessoresByTurma($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT pr.codigo, p.nome as professor, d.nome as disciplina,
                t.numero as turma, d.numero, p.codigo as codProfessor,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                IF(a.bimestre > 0, CONCAT(' [', a.bimestre,'º BIM]'), '') as bimestre,
                IF(LENGTH(a.subturma) > 0,CONCAT(' [',a.subturma,']'),CONCAT(' [',a.eventod,']')) as subturma
		FROM Atribuicoes a,Disciplinas d, Turmas t,Cursos c,Turnos tu, Professores pr, Pessoas p
		WHERE a.disciplina = d.codigo 
                and a.turma = t.codigo
		and t.curso = c.codigo
		and t.turno = tu.codigo
		and pr.atribuicao = a.codigo
		and pr.professor = p.codigo
		and t.ano=:ano
		and (t.semestre=:semestre OR t.semestre=0)";

        $sql .= " $sqlAdicional ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;
        else
            return false;
    }

}

?>