<?php

require_once ('criptografia.php');

class DigitaNotasWS {
    public function digitarNotaAluno($user, $pass, //usuario e senha do WS
                                    $campus, //sigla do campus
                                    $prontuario, //prontuario do professor
                                    $prontuarioAluno, //prontuario do aluno
                                    $codigoDisciplina, //codigo da disciplina
                                    $eventod,
                                    $bimestre,
                                    $ano,
                                    $semestre,
                                    $faltas,
                                    $nota, //nota de 0 a 10
                                    $turma,
                                    $dataGravacao,
                                    $flagDigitacaoNota //flag = 5 => nota digitada e fechada
                                    ){
        try {
            
            $pass = Criptografia::codificar($pass);
			
            $servicoDigitaNotas = 'http://ws.ifsp.edu.br/teste_servicoDigitaNotas';
            $cliente = new SoapClient($servicoDigitaNotas . "?wsdl", array(
                "trace" => 1,
                "exception" => 1,
                'encoding' => 'UTF-8',
                'login' => $user,
                'password' => $pass
                ));
            
            $cliente->__getFunctions();
            
            $cliente->__setLocation($servicoDigitaNotas);

            $notaAlunoObj = $cliente->digitarNotasAlunos($ano, 
                                                        $turma,
                                                        $eventod,
                                                        $bimestre,
                                                        $codigoDisciplina,
                                                        $prontuario,
                                                        $prontuarioAluno,
                                                        $semestre,
                                                        $flagDigitacaoNota,                    
                                                        $nota,
                                                        $faltas,
                                                        $campus,
                                                        $dataGravacao);
            if (isset($notaAlunoObj) && $notaAlunoObj->sucesso) {
                return true;
            }
        } catch (Exception $e) {
	        	$erro = "Erro DigitaNotas: ".$e->getMessage();
	        	if ($DEBUG) echo "$erro \n";
	       		mysql_query("insert into Logs values(0, '".addslashes($erro)."', now(), 'CRON_ERRO', 1)");
	   	  
            error_log("DigitaNotasWS::digitarNotaAluno service general error: " . $e->getMessage());
            throw $e;
        }
        return false;
    }

}
