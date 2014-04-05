<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

require PATH . LIB . '/PHPMailer/PHPMailerAutoload.php';

class Instituicoes extends Generic {

    public function __construct() {
        
    }

    // MÉTODO PARA CARREGAR VARIÁVEIS DE AMBIENTE
    // USADO POR: INC/VARIAVEIS.PHP
    public function sendEmail($email, $assunto, $mensagem, $headers) {

        $res = $this->listRegistros();

        if ($res[0]['email_smtp'] && $res[0]['email_port'] && $res[0]['email_secure'] && 
                $res[0]['email_account'] && $res[0]['email_password']) {
            
            $mail = new PHPMailer;

            $mail->isSMTP();
            $mail->Host = $res[0]['email_smtp'];
            $mail->SMTPAuth = true;
            $mail->Username = $res[0]['email_account'];
            $mail->Password = $res[0]['email_password'];
            $mail->SMTPSecure = $res[0]['email_secure'];
            $mail->Port = $res[0]['email_port'];

            $mail->From = $email;
            $mail->FromName = $email;
            $mail->addAddress($email, $email);

            $mail->WordWrap = 50;
            $mail->AddEmbeddedImage(PATH.VIEW."/css/images/logo.png", 'IFSP', 'IFSP.jpg');
            $mail->isHTML(true);

            $mail->Subject = $assunto;
            $mail->Body = $mensagem . '<br /><br /><img src="cid:IFSP" />';
            $mail->AltBody = $mensagem;

            if (!$mail->send()) {
                echo 'Problema na configura&ccedil;&atilde;o de E-mail.';
                echo '<br />Mailer Error: ' . $mail->ErrorInfo;
                die;
            } else {
                return true;
            }
        } else {
            mail($email, $assunto, $mensagem, $headers);
            return true;
        }

        return false;
    }

}
