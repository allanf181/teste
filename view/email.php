<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Envia e-mail de sugestão ou problemas.
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require '../inc/config.inc.php';
require FUNCOES;
require MENSAGENS;
require VARIAVEIS;
require SESSAO;

// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}

if ($_POST['nome'] && $_POST['email'] && $_POST['conteudo']) {
    if (isset($_SESSION['session_textoCaptcha']) && isset($_POST["captcha_r"]) && $_SESSION['session_textoCaptcha'] != $_POST["captcha_r"]) {
        mensagem('ERRO', 'CAPTCHA');
    } else {
        $instituicao = new Instituicoes();

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: ".$_POST['email']." \n";
        $headers .= "Return-Path: ".$_POST['email']." \n";
        
        $mensagem = '<br />NOME: '.utf8_decode($_POST['nome']);
        $mensagem .= '<br />E-MAIL: '.utf8_decode($_POST['email']);
        $mensagem .= '<br />CAMPUS: '.utf8_decode($SITE_CIDADE);
        $mensagem .= '<br /><br />MENSAGEM: <br>'.utf8_decode($_POST['conteudo']);
       
        $email = array( 'webdiario@ifsp.edu.br', 'naylorgarcia@ifsp.edu.br');
        
        if ($instituicao->sendEmail($email, 'CONTATO-WEBDIARIO', $mensagem, $headers))
            mensagem('OK', 'EMAIL_SUGESTAO');
    }
}
?>
<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= VIEW ?>/email.php',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <table width="100%" id="form">
            <tr>
                <td colspan="2">
                    <strong>Ol&aacute;, utilize esse canal para enviar sugest&otilde;es ou reportar problemas.</strong>
                    <br />
                    <strong>Caso necess&aacute;rio, tentaremos responder o mais breve poss&iacute;vel.</strong>
                </td>
            </tr>
            <tr><td>&nbsp;</td><td align="right"><a href="javascript:$('#index').load('home.php'); void(0);">VOLTAR</a></td></tr>
            <tr>
                <td width="20px" align="right">Nome: </td>
                <td><input type='text' id='nome' name="nome" value='' size='60' /></td>
            </tr>
            <tr>
                <td align="right">E-mail: </td>
                <td><input type='text' id='email' name="email" value='' size='60' /></td>
            </tr>
            <tr>
                <td align="right">Mensagem: </td>
                <td><textarea maxlength="500" rows="5" cols="80" id="conteudo" name="conteudo" style="width: 600px; height: 60px"></textarea></td>
            </tr>
            <tr>
                <td>&nbsp;</td><td>Digite o c&oacute;digo da figura abaixo:</td>
            </tr>
            <tr>
                <td>&nbsp;</td><td><input type="text" name="captcha_r" id="captcha_r" autocomplete="off" />  </td>
            </tr>
            <tr>
                <td>&nbsp;</td><td><img src="<?php print CAPTCHA; ?>/captcha.php" width="133" height="49"></td>
            </tr>
            <tr>
                <td>&nbsp;</td><td><input type="submit" id='enviar' value="   Enviar   " disabled="disabled"  /></td>
            </tr>
        </table>
    </form>
</div>

<script>
    $(document).ready(function () {
        $('#nome, #email, #conteudo, #captcha_r').keypress(function () {
            if ($('#nome').val() != "" && $('#email').val() != "" && $('#conteudo').val() != "" && $('#captcha_r').val() != "")
                $('#enviar').enable();
            else
                $('#enviar').attr('disabled', 'disabled');
        });

        $('#conteudo').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 500, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
        });
    });
</script>