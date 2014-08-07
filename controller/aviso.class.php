<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';


class Avisos extends Generic {

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem avisos
    // Pode ser colocado com função no MySQL futuramente
    public function getAvisoGeral($codigo) {
        $bd = new database();
        $sql = "SELECT date_format(a.data, '%d/%m/%Y %H:%i') as Data, 
                a.conteudo as Conteudo,
                (SELECT CONCAT(codigo, '#', nome) FROM Pessoas WHERE codigo = a.pessoa) as Pessoa
                FROM Avisos a 
                    WHERE pessoa <> :cod
                    AND ( destinatario = :cod
                        OR (destinatario = 0 
                            AND atribuicao = 0 
                            AND curso = 0 
                            AND turma = 0
                        )
                        OR (destinatario = 0
                            AND atribuicao = 0 
                            AND curso <> 0 
                            AND turma IN (SELECT t.codigo 
                                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                                WHERE t.codigo = a.turma 
                                AND m.atribuicao = a.codigo 
                                AND m.aluno = p.codigo 
                                AND t.codigo = a.turma
                                AND p.codigo = :cod )
                            )
                        OR (destinatario = 0
                            AND atribuicao = 0 
                            AND turma = 0 
                            AND curso IN (SELECT t.curso 
                                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                                WHERE t.codigo = a.turma 
                                AND m.atribuicao = a.codigo 
                                AND m.aluno = p.codigo 
                                AND t.codigo = a.turma
                                AND p.codigo = :cod )
                            )
                    )
                ORDER BY a.data DESC
                LIMIT 20";
        
        $params = array(':cod'=> $codigo);

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
    
    // USADO POR: ALUNO/AVISO.PHP
    // Lista os avisos do usuário
    public function getAvisoAtribuicao($codigo, $atribuicao) {
        $bd = new database();
        
        $sql = "SELECT Data, Conteudo 
                	FROM Avisos 
			WHERE atribuicao = :atr
			AND (destinatario = 0 OR destinatario = :cod)";
        $params = array(':cod'=> $codigo, ':atr'=> $atribuicao);
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

        // USADO POR: ALUNO/AVISO.PHP
    // Lista os avisos do usuário
    public function insertOrUpdateAvisos($POST) {
        $params['codigo'] = $POST['codigo'];
        $params['conteudo'] =  $POST['conteudo'];        
        $params['pessoa'] =  $POST['pessoa'];
        
        $params['atribuicao'] = 0;
        
        $to = explode(',', $POST['to']);

        $res=0;
        foreach($to as $dest) {
            $params['destinatario'] = null;
            $params['curso'] = null;
            $params['turma'] = null;
            
            if ( substr($dest, 0, 2) == 'P:' )
                $params['destinatario'] =  crip(substr($dest, 2));
            if ( substr($dest, 0, 2) == 'C:' )
                $params['curso'] =  crip(substr($dest, 2));
            if ( substr($dest, 0, 2) == 'T:' )
                $params['turma'] =  crip(substr($dest, 2));

            if ($this->insertOrUpdate($params))
                $res++;
        }

        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
    
    // Lista os avisos
    // USADO EM SECRETARIA/AVISO.PHP
    // PROFESSOR/AVISO.PHP
    public function listAvisos($params, $item = null, $itensPorPagina = null) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";
        
        $sql = "SELECT a.codigo as codigo, date_format(a.data, '%d/%m/%Y %H:%i') as data, 
    			a.conteudo as conteudo, a.atribuicao as atribuicao,
    			(SELECT p1.nome FROM Pessoas p1 WHERE p1.codigo = a.destinatario) as destinatario,
    			(SELECT CONCAT('[', c.codigo, '] ', c.nome) FROM Cursos c WHERE c.codigo = a.curso) as curso,
    			(SELECT t.numero FROM Turmas t WHERE t.codigo = a.turma) as turma
    			FROM Avisos a 
    			WHERE a.pessoa = :pessoa
                        ORDER BY data DESC ";

        $sql .= "$nav";
        
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