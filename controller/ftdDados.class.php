<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class FTDDados extends Generic {

    // USADO POR: ATENDIMENTO.PHP
    // Retorna os horário de atendimento ao aluno
    // que o professor cadastrou na FTD
    public function getAtendimentoAluno($codigo, $ano, $semestre) {
        $bd = new database();
        
        $sql = "SELECT horario, 
		SUBSTR( registro, 3, 1 ) AS diaSemana, 
		SUBSTR( registro, 4, 1 ) AS ES
		FROM FTDHorarios fh, FTDDados fd
		WHERE fd.codigo = fh.ftd
		AND SUBSTR( registro, 2, 1 ) = 4
		AND fd.ano = :ano 
		AND fd.semestre = :sem
		AND fd.professor = :cod";
        $params = array(':cod'=> $codigo, ':ano'=> $ano, ':sem'=> $semestre);
        $res = $bd->selectDB($sql, $params);

        if ( $res )
        {
            foreach($res as $reg) {
                $new_res[$reg['diaSemana']][$reg['ES']] = $reg['horario'];
            }
            return $new_res;
        }
        else
        {
            return false;
        }
    } 
    
    // USADO POR: HOME.PHP
    // Verifica se o usuário tem correções para FTD
    // Pode ser colocado com função no MySQL futuramente
    public function hasChangeFtd($codigo, $ano, $semestre) {
        $bd = new database();
        $sql = "SELECT (SELECT nome FROM Pessoas "
                    . "WHERE codigo = fd.solicitante) as ftdSolicitante, "
                    . "fd.solicitacao as ftdSolicitacao "
                    . "FROM FTDDados fd "
                    . "WHERE fd.ano = :ano "
                    . "AND fd.semestre = :sem "
                    . "AND fd.valido = '0000-00-00 00:00:00' "
                    . "AND fd.professor = :cod";
        $params = array(':cod'=> $codigo, ':ano'=> $ano, ':sem'=> $semestre);
        $res = $bd->selectDB($sql, $params);

        if ( $res )
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