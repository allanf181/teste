<?php
if(!class_exists('database'))
{
    require_once MYSQL;
}

class Pessoa {
    
    public function __construct(){
        //
    }
    
    // USADO POR: HOME.PHP
    public function removeFoto($codigo) {
        $bd = new database();
        $sql = "UPDATE Pessoas SET foto = '' WHERE codigo = :cod";
        $params = array(':cod'=> $codigo);
        $res = $bd->updateDB($sql, $params);
        if ( $res[0] )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // USADO POR: INC/FILE.PHP
    public function getFoto($codigo) {
        $bd = new database();
        $sql = "SELECT foto, bloqueioFoto FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);
        if ( $res[0] )
        {
            return $res[0];
        }
        else
        {
            return $sql;
        }
    }
    
    // USADO POR: HOME.PHP
    // Verifica se o usuário tem foto
    public function hasPicture($codigo) {
        $bd = new database();
        $sql = "SELECT foto FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);
        if ( $res[0]['foto'] )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    // USADO POR: HOME.PHP
    // RETORNA DADOS DA SENHA
    public function infoPassword($codigo) {
        $bd = new database();
        $sql = "SELECT DATEDIFF(NOW(), dataSenha) as data,"
                . "(SELECT diasAlterarSenha FROM Instituicoes) as dias, "
                . "dataSenha, senha, PASSWORD(prontuario) as pront "
                . "FROM Pessoas WHERE codigo = :cod";     
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);
        if ( $res[0] )
        {
            return $res[0];
        }
        else
        {
            return false;
        }
    }
    
    // USADO POR: HOME.PHP
    // Atualiza o Lattes do Usuário
    public function updateLattes($codigo, $lattes) {
        $bd = new database();
        $sql = "UPDATE Pessoas SET lattes = :lattes WHERE codigo = :cod";
        $params = array(':cod'=> $codigo, ':lattes'=> $lattes);
        $res = $bd->selectDB($sql, $params);
        if ( $res )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    // USADO POR: HOME.PHP
    // Retorna o Lattes do Usuário
    public function showLattes($codigo) {
        $bd = new database();
        $sql = "SELECT lattes FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);
        if ( $res[0]['lattes'] )
        {
            if (strpos($res[0]['lattes'], 'http://') === FALSE) {
                return "http://" . $res[0]['lattes'];
            } else {
                return $res[0]['lattes'];
            }
        }
        else
        {
            return false;
        }
    }
    
    // USADO POR: HOME.PHP
    // INFOMRAR AO COORDENADOR PROFESSORES QUE NÃO CADASTRAM 
    // DISCIPLINAS DE ACORDO COM O LIMITE IMPOSTO EM INSTITUIÇÕES
    // --> Enviar essa query para o Banco no futuro.
    public function listProfOutOfLimitAddAula($codigo, $ano, $semestre) {
        $bd = new database();
        $sql = "SELECT p.nome as Professor, date_format(data, '%d/%m/%Y') as Data 
			FROM Pessoas p, Atribuicoes a, Professores pr, Aulas au, Turmas t, Cursos c
			WHERE p.codigo = pr.professor
			AND a.codigo = pr.atribuicao
			AND au.atribuicao = a.codigo
			AND t.codigo = a.turma
			AND t.curso = c.codigo
			AND t.semestre = :sem
			AND t.ano = :ano
			AND DATEDIFF(NOW(), au.data) > 7
			AND c.codigo IN (SELECT curso 
                        FROM Coordenadores co 
                        WHERE co.coordenador=:cod)
			GROUP BY p.codigo
			ORDER BY data ASC";
        $params = array(':cod'=> $codigo, ':sem'=> $semestre, ':ano'=> $ano);
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