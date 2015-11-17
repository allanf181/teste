<?php
if(!class_exists('Generic'))
{
    require_once CONTROLLER.'/generic.class.php';
}

class MatriculasAlteracoes extends Generic {
    
    public function __construct(){
        //
    }
    
    // RETORNA AS ALTERACOES DE MATRICULAS
    // USO: SECRETARIA/CURSOS/MATRICULA.PHP
    public function listAlteracaoMatricula($params, $sqlAdicional) {
        $bd = new database();
        
        $sql = "SELECT ma.codigo, s.listar, s.habilitar, s.sigla, s.nome,
                        DATE_FORMAT(ma.data, '%d/%m/%Y') as data
			FROM MatriculasAlteracoes ma, Situacoes s
			WHERE ma.situacao = s.codigo ";

        $sql .= $sqlAdicional;
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // RETORNA A MATRICULA DO ALUNO
    // USADO POR: CONTROLLER/AULA.CLASS.PHP, NOTAFINAL.CLASS.PHP,
    // RELATORIOS/FREQUENCIA.PHP, NOTA.PHP, BOLETIMTURMA.PHP
    public function getAlteracaoMatricula($aluno, $atribuicao, $data) {
        $bd = new database();
        
        $sql = "SELECT s.listar, s.habilitar, s.sigla, s.nome, s.codigo, nf.situacao sit
			FROM MatriculasAlteracoes ma, Situacoes s, Matriculas m
                        LEFT JOIN NotasFinais nf ON nf.matricula=m.codigo AND nf.atribuicao=:atr
			WHERE m.codigo = ma.matricula
                        AND ma.situacao = s.codigo
			AND m.aluno = :aluno
                        AND m.atribuicao = :atr
                        ORDER BY ma.data DESC";
        $params = array(':aluno' => $aluno, ':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
//        debugSQL($sql, $params);

        if ($res) {
            $rs['habilitar'] = $res[0]['habilitar'];
            $rs['listar'] = $res[0]['listar'];
            $rs['sigla'] = $res[0]['sigla'];
            $rs['tipo'] = $res[0]['nome'];
            $rs['codSituacao'] = $res[0]['codigo'];
            $rs['situacao'] = $res[0]['sit'];
            if (!$res[0]['sit'])
                $rs['situacao'] = $res[0]['nome'];
                
        } 
        // DESABILITADO ATÉ QUE SEJA INCLUÍDO UM CAMPO DE DATA DE MATRÍCULA NO NAMBEI
//        else {
//            $rs['habilitar'] = '1';
//            $rs['listar'] = 1;
//            $rs['tipo'] = 'IN_AFTER';
//        }
        return $rs;
    }
    
}

?>