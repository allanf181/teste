<?php

require_once ('criptografia.php');

class ConsultaDisciplinasWS {

    public function consultaDisciplinas($user, $pass, $campus, $prontProfessor) {
        try {
            $pass = Criptografia::codificar($pass);

            $servicoConsultarDisciplinaMinistrada = 'http://aurorateste.ifsp.edu.br:8080/IFSPWebServices-0.0.1-SNAPSHOT/services/servicoConsultarDisciplinaMinistrada';
            $cliente = new SoapClient($servicoConsultarDisciplinaMinistrada . "?wsdl", array(
                "trace" => 1,
                "exception" => 1,
                'encoding' => 'UTF-8',
                'login' => $user,
                'password' => $pass
            ));
                        
            $cliente->__getFunctions();

            $cliente->__setLocation($servicoConsultarDisciplinaMinistrada);

            $professorObj = $cliente->consultarDisciplinasMinistradas($campus, $prontProfessor);

            return (object) $professorObj;

        } catch (Exception $e) {
            $erro = "Erro ConsultaNotas (WS): " . $e->getMessage();
            if ($DEBUG) {
                echo "$erro \n";
            }
            mysql_query("insert into Logs values(0, '" . addslashes($erro) . "', now(), 'CRON_ERRO', 1)");
        }
        return false;
    }

}
