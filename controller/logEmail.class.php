<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

if (!class_exists('Instituicoes'))
    require_once CONTROLLER . '/instituicao.class.php';

class LogEmails extends Generic {

    public function __construct() {
        //
    }

    public function sendEmailLogger($nome, $solicitacao, $email) {
        $mensagem .= "Nome: $nome";
        $mensagem .= "<br />Solicita&ccedil;&atilde;o: " . utf8_decode($solicitacao);

        $params = array('para' => $email, 'mensagem' => $mensagem, 'assunto' => 'Solicitacao - WebDiario');
        $this->insertOrUpdate($params);
    }

    public function send() {
        $bd = new database();

        foreach ($this->listRegistros(null, ' WHERE data IS NULL GROUP BY para, mensagem ', null, null) as $reg)
            foreach (explode(',', $reg['para']) as $e)
                if ($e)
                    $lista[$e] .= '<hr>'.$reg['mensagem'];

        $ma = '<b>E-mail das solicitações em que você foi adicionado no WebDiário:<br /></b>';
        $md .= "<br /><br />Atenciosamente,<br/>";
        $md .= "Equipe IFSP - Instituto Federal de Educa&ccedil;&atilde;o, Ci&ecirc;ncia e Tecnologia de S&atilde;o Paulo";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: naoresponda@ifsp.edu.br \n";
        $headers .= "Return-Path: naoresponda@ifsp.edu.br \n";
        $assunto = 'Solicitacao - WebDiario';

        $instituicao = new Instituicoes();
        
        foreach($lista as $e => $m)
            $instituicao->sendEmail(array($e), $assunto, $ma.$m.$md, $headers);
        
        $bd->updateDB("UPDATE LogEmails SET data = NOW()");
    }
}

?>