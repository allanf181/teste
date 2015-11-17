<?php

if (!class_exists('Notas'))
    require_once CONTROLLER . '/nota.class.php';

class Arredondamento{
    
    public function getMedias($atribuicao){
        $bd = new database();
        $notas = new Notas();
        // SELECIONA OS DADOS DA ATRIBUICAO, DISCIPLINA, MATRICULA...
        $sql = "SELECT m.codigo as matricula, a.bimestre as bimestre,
                m.aluno as aluno, t.codigo as turma, d.numero, p.nome as nome, p.prontuario as prontuario, n.ncc as ncc
		FROM Disciplinas d, Turmas t, Pessoas p, Atribuicoes a
                JOIN Matriculas m
                LEFT JOIN NotasFinais n on n.atribuicao=a.codigo and n.matricula=m.codigo
		WHERE a.codigo = m.atribuicao
		AND t.codigo = a.turma
		AND d.codigo = a.disciplina
                AND m.aluno = p.codigo
		AND a.codigo = :cod
                ORDER BY p.nome ASC";

        $params = array(':cod' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
       
        if ($res) {
            if (!class_exists('MatriculasAlteracoes'))
                require CONTROLLER . "/matriculaAlteracao.class.php";
            $ma = new MatriculasAlteracoes();
            
            }
        $medias = array(); 
            //verificar nos códigos quando é null o valor da media final etc
        foreach ($res as $indice=>$reg){
            $situacaoAlunos = $ma->getAlteracaoMatricula($reg['aluno'], $atribuicao, date('Y-m-d'));
            if ($situacaoAlunos['listar'] && $situacaoAlunos['habilitar']) {
                $dados = $notas->resultado($reg['matricula'], $atribuicao, 0, 0);
                $medias[$indice]['atribuicao'] = $atribuicao;
                $medias[$indice]['matricula'] = $reg['matricula'];
                $medias[$indice]['nome'] = $reg['nome'];
                $medias[$indice]['prontuario']=$reg['prontuario'];
                $medias[$indice]['aluno']=$reg['aluno'];
                $medias[$indice]['ncc']=$reg['ncc'];
                    
                if ($reg['bimestre'] == 0) {
                    $medias[$indice]['bimestre'] = '1';
                    $reg['bimestre'] = '1';
                } else 
                    $medias[$indice]['bimestre'] = $reg['bimestre'];
                
                // VERIFICAÇÃO NECESSÁRIA PARA CASOS DE RECUPERAÇÃO   
                $mediasIguais = ($dados['mediaAvaliacao'] == $dados['media']);
                if (!$mediasIguais){
                    $medias[$indice]['media'] = $dados['media'];
                } else
                    $medias[$indice]['media'] = $dados['mediaAvaliacao'];
                
                
            }

        }
        return $medias;
    }
        
}