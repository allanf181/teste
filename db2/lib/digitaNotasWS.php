<?php

require_once ('criptografia.php');

class DigitaNotasWS {

    public function digitarNotasAlunos($user, $pass, $campus, $lista) {
        try {
            $pass = Criptografia::codificar($pass);

            $servicoDigitaNotas = 'http://ws.ifsp.edu.br/servicoDigitaNotas';
            $cliente = new SoapClient($servicoDigitaNotas . "?wsdl", array(
                "trace" => 1,
                "exception" => 1,
                'encoding' => 'UTF-8',
                'login' => $user,
                'password' => $pass
            ));

            $cliente->__getFunctions();

            $cliente->__setLocation($servicoDigitaNotas);

            $notaAlunoObj = $cliente->digitarNotasAlunos($campus, $lista);
            return (object) $notaAlunoObj;

        } catch (Exception $e) {
            $erro = "Erro DigitaNotas (WS): " . $e->getMessage();
            if ($DEBUG) {
                echo "$erro \n";
            }
            mysql_query("insert into Logs values(0, '" . addslashes($erro) . "', now(), 'CRON_ERRO', 1)");
        }
        return false;
    }

}
