<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Troca de Senha no Sistema.
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require '../../inc/config.inc.php';
require FUNCOES;
require MENSAGENS;
require VARIAVEIS;
require CONTROLLER . '/login.class.php';
require SESSAO;

// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}

if ($LINK_RECUPERAR_SENHA) {
    ?>
    <script>
        $(document).ready(function () {
            location.replace('<?= $LINK_RECUPERAR_SENHA ?>');
        });
    </script>
    <?php
    die;
}

if ($LDAP_ATIVADO && !$LDAP_PASS) {
    mensagem('INFO', 'LDAP_ATIVADO');
    die;
}

// Instância a classe login
$login = new login();

// ENVIANDO A CHAVE DE RECUPERAR SENHA
if (isset($_GET["opcao"]) && $_GET["opcao"] == 'recuperar') {
    $prontuario = $_GET["campoLogin"];
    if (!$LDAP_ATIVADO) {
        if ($prontuario) {
            if ($emailRetorno = $login->recuperaSenha($prontuario)) {
                mensagem('OK', 'EMAIL_ENVIADO', $emailRetorno);
            } else {
                mensagem('ERRO', 'EMAIL_NAO_CADASTRADO');
            }
        } else {
            mensagem('INFO', 'PRONTUARIO_VAZIO');
        }
    } else {
        mensagem('INFO', 'LDAP_ATIVADO');
    }
}

if (isset($_POST["opcao"]) && $_POST["opcao"] == 'alterarToBanco') {
    $senha = addslashes($_POST["senhaAtual"]);
    $senhaNova = addslashes($_POST["senhaNova"]);
    $prontuario = addslashes(base64_decode($_POST["prontuario"]));
    $chave = addslashes($_POST["chave"]);

    if ($_SESSION['session_textoCaptcha'] == $_POST["captcha_r"]) {
        $rs = $login->alteraSenha($prontuario, $senha, $senhaNova, $chave, $LDAP_PASS);
        if ($rs == 1) {
            @session_unset($_SESSION_NAME);
            @session_destroy($_SESSION_NAME);
            print "Sua senha foi alterada com sucesso.<br />";
            print "<a href=\"?\">Clique aqui e acesse o sistema com sua nova senha.</a><br/><br/>";
            die;
        } else if ($rs == 2) {
            mensagem('ERRO', 'LDAP_WRONG_PASS');
        } else {
            if ($senha)
                mensagem('ERRO', 'PASS_NOT_MATCH');

            if ($chave)
                mensagem('ERRO', 'KEY_NOT_MATCH');
        }
    } else {
        mensagem('ERRO', 'WRONG_CHARACTER');
    }
    if ($senha)
        $_GET["opcao"] = 'alterar';
    if ($chave)
        $_GET["opcao"] = 'recuperarPorChave';
}

if ($_GET["opcao"] == 'recuperarPorChave') {
    print "<h2>Recupera&ccedil;&atilde;o de Senha</h2>\n";
    $recuperar = addslashes($_GET['key']);
    $prontuario = addslashes($_GET['prt']);
}

if ($_GET["opcao"] == 'alterar') {
    print "<h2>Altera&ccedil;&atilde;o de Senha</h2>\n";
    $prontuario = base64_encode($_SESSION["loginProntuario"]);

    $data = "mais de $diasAlterarSenha dias";
    if ($_SESSION["loginDataSenha"] != "0000-00-00 00:00:00")
        $data = $_SESSION["loginDataSenha"];
    if ($data)
        print "<p>&Uacute;ltima altera&ccedil;&atilde;o da senha: " . formata($data) . "</p><br />\n";
}

if ($_GET["opcao"] == 'recuperarPorChave' || $_GET["opcao"] == 'alterar') {
    ?>
    <script>
        $('#form_padrao').html5form({
            method: 'POST',
            action: '<?= VIEW ?>/system/senha.php',
            responseDiv: '#index',
            colorOn: '#000',
            colorOff: '#999',
            messages: 'br'
        })
    </script>

    <div id="html5form" class="main">
        <form id="form_padrao">

            Digite uma nova senha, em seguida confirme.<Br/>
            <strong>Dicas de segurança:</strong><Br/>
            -Não utilize sequenciais. ex: 123456 ou abcd<Br/>
            -Não coloque data nascimento ou seu próprio prontuário.<Br/>
            -Utilize letras, números e caracteres especiais.<Br/>

            <center><table style="width: 200px; text-align: center" border="0" align="center">
                    <?php
                    // Se for alteração de senha, precisa digitar a senha atual
                    if ($_GET["opcao"] == 'alterar') {
                        ?>
                        <tr><td>Senha Atual: </td></tr>
                        <tr><td><input type='password' id='senhaAtual' name="senhaAtual" value='' /></td></tr>
                        <?php
                    }
                    ?>	
                    <tr>
                        <td>Nova Senha: </td>
                    </tr>
                    <tr>
                        <td>
                            <div>
                                <input type='password' id='nv_senha' name="senhaNova" value='' />
                            </div>
                            <div id='result2'></div>
                        </td>
                    </tr>
                    <tr>
                        <td>Confirmar senha: </td>
                    </tr>
                    <tr>
                        <td>
                            <div>
                                <input type='password' id='cf_senha' name="confSenha" value='' />
                            </div>
                            <div id='result3'></div>
                            <input type='hidden' id='has_chv' name="chave" value='<?= $recuperar ?>' />
                            <input type='hidden' id='prontuario_grv' name="prontuario" value='<?= $prontuario ?>' />
                        </td>
                    </tr>
                    <input type='hidden' id='opcao' name="opcao" value="alterarToBanco" />
                    <tr>
                        <td>
                            Digite o codigo da figura abaixo:
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="captcha_r" id="captcha_r" autocomplete="off" />  
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img src="<?= CAPTCHA ?>/captcha.php" width="133" height="49" style="padding-top: 10px;">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input style="margin-top: 10px;" title="Clique para alterar sua senha" type="submit" id="alterar_snh" value="   Alterar   " disabled="disabled"  />
                        </td>
                    </tr>
                </table>
            </center>
        </form>
    </div>

    <script src="<?= VIEW ?>/js/passwordStrenghtMeter.js" type="text/javascript"></script>

    <script>
        jQuery(document).ready(function () {
            $('#nv_senha').keyup(function () {
                validar()
            });
            $('#cf_senha').keyup(function () {
                validar()
            });

            function validar() {// função adaptada do arquivo senhAlterar.php
                var res = passwordStrength($('#nv_senha').val(), '<?= $prontuario_r; ?>');
                if ($('#nv_senha').val() != "" && $('#nv_senha').val() == $('#cf_senha').val() && res != 'Muito curta' && res != 'Senha fraca') {
                    $('#result1').html('');
                    $('#result2').attr("class", "infoBalloon");
                    $('#result2').html(res);
                    $('#result2').attr("class", "infoBalloon");
                    $('#result3').attr("class", "infoBalloon");
                    $('#result3').html('OK');
                    $('#result3').attr("class", "infoBalloon");
                    $('#2').attr("class", "");
                    $('#3').attr("class", "");
                    $('#alterar_snh').attr('disabled', false);
                }
                else {
                    $('#result1').html('');
                    $('#result2').attr("class", "errorBalloon");
                    if (res != 'Muito curta' && res != 'Senha fraca') {
                        $('#result2').attr("class", "infoBalloon");
                        $('#2').attr("class", "");
                    }
                    else {
                        $('#result2').attr("class", "errorBalloon");
                        $('#result3').attr("class", "errorBalloon");
                    }
                    $('#result2').html(res);
                    $('#result3').attr("class", "errorBalloon");
                    $('#result3').html('Senhas não conferem');
                    if (res == 'Senha fraca') {
                        $('#result3').html('');
                        $('#result3').attr("class", "");
                    }
                    $('#alterar_snh').attr('disabled', true);
                }
            }
        });
    </script>
    <?php
}
?>