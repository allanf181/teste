<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Professores extends Generic {

    public function getProfessor($atribuicao, $separador = null, $lattes = null, $foto = null) {
        $bd = new database();
        $sql = "SELECT p.codigo, p.nome, p.lattes "
                . "FROM Professores pr, Pessoas p "
                . "WHERE p.codigo = pr.professor "
                . "AND atribuicao = :cod";

        $params = array(':cod' => $atribuicao);

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            foreach ($res as $reg) {
                $r=null;
                if ($foto)
                    $r .= "<a href='#' rel='".INC."/file.inc.php?type=pic&id=<?=crip(" . $reg['codigo'] . ")?>&timestamp=<?=time()?>' class='screenshot' title=''>
                <img style='width: 20px; height: 20px' alt='Embedded Image' src='".INC."/file.inc.php?type=pic&id=<?=crip(" . $reg['codigo'] . ")?>&timestamp=<?=time()?>' /></a>";
                if ($lattes && $reg['lattes'])
                    $r .= "<a title='Curr&iacute;culo Lattes' target='_blank' href='" . $reg['lattes'] . "'>" . $reg['nome'] . "</a>";
                else
                    $r .= $reg['nome'];
                
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
    
    // USADO POR: ATENDIMENTO.PHP
    // LISTA OS PROFESSORES DA BASE
    public function listProfessores($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT DISTINCT p.codigo, p.nome, p.lattes
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

}
?>