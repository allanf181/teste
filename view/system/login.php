<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Login do Sistema
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require '../../inc/config.inc.php';
require FUNCOES;
require MENSAGENS;
require VARIAVEIS;
require CONTROLLER . '/login.class.php';

// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}

// Quando o usuário utiliza o link de recuperar senha.
if ($_GET['key'] && $_GET['prt']) {
    $key = $_GET['key'];
    $prt = $_GET['prt'];
    ?>
    <script>
        $(document).ready(function() {
            $('#index').load('<?= VIEW ?>/system/senha.php?opcao=recuperarPorChave&key=<?= $key ?>&prt=<?= $prt ?>');
        });
    </script>
    <?php
}

// instância a classe login
$login = new login();

if (isset($_POST["try"]))
    $try = $_POST["try"];
else
    $try = 3;

// Recuperando o Prontuario e Senha
$prontuario = (isset($_POST["campoLogin"])) ? addslashes($_POST["campoLogin"]) : null;
$senha = (isset($_POST["campoSenha"])) ? addslashes($_POST["campoSenha"]) : null;

if ($prontuario && $senha) {
    if ($try < 1 && isset($_SESSION['session_textoCaptcha']) && isset($_POST["cap"]) && $_SESSION['session_textoCaptcha'] != $_POST["cap"]) {
        $ERRO = 'CAPTCHA';
        $erro_cap = 1;
    }

    if (!isset($erro_cap)) {
        if ($login->autentica($prontuario, $senha, $LDAP_ATIVADO, $LDAP_DROP_LEFT, $LDAP_CACHE)) {
            
            // Guardando em sessão a chave randomica gerada para a função de cripto/decriptografia.
            $_SESSION["cripto"] = genRandomString();            
            
            print "Aguarde... redirecionando...";
            print "<script type=\"text/javascript\">\n";
            print "  location.reload(); \n";
            print "</script>\n";
            die;
        } else {
            $try -= 1;
            $ERRO = 'USER_OR_PASS_INVALID';
        }
    }
}
?>
<div id='fundo_login'></div>
<div id='form_login_logo'></div>
<div id="" class="">
    <form method="post" id="form_login">
        <div id='div_login_logo'>
            <img id='login_logo' src='<?= IMAGES ?>/logotipo_new.png' />
        </div>
        <table>
            <tr>
                <td align="right">
                    <div id="form_login_label">Prontu&aacute;rio: </div>
                </td>
                <td align='left'>
                    <input type="text" name="campoLogin" id="campoLogin" />
                </td>
            </tr>
            <tr>
                <td align="right">
                    <div id="form_login_label">Senha: </div>
                </td>
                <td align='left'>
                    <input type="password" name="campoSenha" id="campoSenha" />
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp</td>
            </tr>
            <?php
            if ($try < 1) {
                ?>
                <div id='senhaTip2'>
                    <img src='<?= CAPTCHA ?>/captcha.php' style='margin-left: 0px; width:106px; height: 32px' />
                    <input type="text" name="cap" id="cap" placeholder='Digite o código da figura' autocomplete='off' />
                    <input type="hidden" name="try" value="<?= $try ?>" />
                </div>
                <tr>
                    <td colspan="2">&nbsp</td>
                </tr>
                <style>#entrar {margin-top: 15px;}</style>
                <?php
            } else {
                ?>
                <input type="hidden" name="try" value="<?= $try ?>" />
                <?php
            }
            ?>
            <tr>
                <td>&nbsp;</td>
                <td style='text-align: left'>
                    <input type="button"  id="entrar" value=" ENTRAR " />
                    &nbsp;
                    <a style='color: black' href="#" onclick="return recuperarSenha();">Recuperar a Senha</a>
                </td>
            </tr>
        </table>
    </form>
</div>
<div id='recuperarSenha'></div>


<script>
<?php
if ($try < 0) {
    ?>
        document.getElementById("cap").focus();
    <?php
} else {
    ?>
        document.getElementById("campoLogin").focus();
    <?php
}
?>
</script>
<?php
if ($prontuario && $senha) { // MOSTRA OS ERROS DO LOGIN, APÓS O FORM.
    if ($try < 3 && $try > 0) {
        mensagem('ERRO', 'MANY_TRY', $try);
    }
    if (!isset($erro_cap))
        mensagem('ERRO', $ERRO);
}
?>
<script>
    $('#campoSenha').keypress(function (e) {
        if (e.which == 13) {
            $('#entrar').click();
        }
    });

    $('#form_login').html5form({
        method: 'POST',
        action: '<?= VIEW ?>/system/login.php',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })

    function recuperarSenha() {
        var campoLogin = $('#campoLogin').val();
        $('#recuperarSenha').load('<?= VIEW ?>/system/senha.php?opcao=recuperar&campoLogin=' + campoLogin);
    }
</script>