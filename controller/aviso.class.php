<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';


class Avisos extends Generic {

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem avisos
    // Pode ser colocado com função no MySQL futuramente
    public function hasAviso($codigo) {
        $bd = new database();
        $sql = "SELECT date_format(a.data, '%d/%m/%Y %H:%i') as Data, 
                a.conteudo as Conteudo,
                (SELECT CONCAT(codigo, '#', nome) FROM Pessoas WHERE codigo = a.pessoa) as Pessoa
                FROM Avisos a 
                WHERE pessoa <> :cod
                AND destinatario = :cod
                OR ( (destinatario = 0 
                AND atribuicao = 0 
                AND curso = 0 AND 
                turma = 0)
                OR (destinatario = 0
                AND atribuicao = 0 
                AND curso <> 0 
                AND turma IN (SELECT t.codigo 
                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                WHERE t.codigo = a.turma 
                AND m.atribuicao = a.codigo 
                AND m.aluno = p.codigo 
                AND t.codigo = a.turma
                AND p.codigo = :cod ))
                OR (destinatario = 0
                AND atribuicao = 0 
                AND turma = 0 
                AND curso IN (SELECT t.curso 
                FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
                WHERE t.codigo = a.turma 
                AND m.atribuicao = a.codigo 
                AND m.aluno = p.codigo 
                AND t.codigo = a.turma
                AND p.codigo = :cod ))
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
    public function listAvisos($codigo, $atribuicao) {
        $params = array(':cod'=> $codigo, ':atr'=> $atribuicao);
        $res = $this->listRegistros($params);
        
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