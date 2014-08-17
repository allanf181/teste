<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite o cadastro da identificação do Campus que está utilizando o sistema, as atribuições dos perfis de usuários que terão acesso ao sistema, as datas limites do docente para alteração do diário e inserção de registros de aulas após a data real da mesma.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    if (!$_POST['bloqueioFoto']) $_POST['bloqueioFoto'] = 0;
    if (!$_POST['envioFoto']) $_POST['envioFoto'] = 0;
    if (!$_POST['ldap_ativado']) $_POST['ldap_ativado'] = 0;
    
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    unset($_POST['opcao']);

    $ret = $instituicao->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if ($_POST['codigo']) $_GET["codigo"] = $_POST['codigo'];
    else $_GET["codigo"] = crip($ret['RESULTADO']);
}
?> 

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
// LISTAGEM
$res = $instituicao->listRegistros();
extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
?>

<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?php print $SITE; ?>',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>
<link rel="stylesheet" type="text/css" href="view/css/aba.css" media="screen" />
<script src="view/js/aba.js"></script>


<div id="html5form" class="main">
<form id="form_padrao">
    
<ul class="tabs">
	<li><a href="#Dados">Local</a></li>
	<li><a href="#Dados2">Pap&eacute;is</a></li>
	<li><a href="#Dados3">Limites</a></li>
	<li><a href="#Dados4">Configura&ccedil;&otilde;es</a></li>
        <li><a href="#Dados5">LDAP</a></li>
</ul>
<div class="tab_container">
<input type="hidden" name="opcao" value="InsertOrUpdate" />
<input type="hidden" name="codigo" value="<?php echo crip($codigo); ?>" />
<div class="cont_tab professores_textarea" id="Dados">
    <table align="center" width="60%">
      <tr><td align="left">Nome: </td><td><input type="text" name="nome" id="nome" maxlength="200" value="<?php echo $nome; ?>"/></td></tr>
      <tr><td align="left">Cidade: </td><td><input type="text" name="cidade" id="cidade" maxlength="200" value="<?php echo $cidade; ?>"/></td></tr>
    </table>
<br><input type="submit" value="Salvar" id="salvar" />
    
    </div>

<div class="cont_tab professores_textarea" id="Dados2">
    <table align="center" width="60%">
 	  <tr><td colspan="2"><p align="center">Tipos (Pap&eacute;is)</p></td></tr>
        <tr><td align="left">Administrador: </td><td><select name="adm" value="<?php echo $adm; ?>">
        	<option></option>
                        <?php
                        require CONTROLLER . '/tipo.class.php';
                        $tipo = new Tipos();
                        $res = $tipo->listRegistros();
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $adm)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                </select>
        </td></tr>
        <tr><td align="left">Ger&ecirc;ncia Educacional: </td><td><select name="ged" value="<?php echo $ged; ?>">
        	<option></option>
                        <?php
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $ged)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                </select>
        </td></tr>
        <tr><td align="left">Coordena&ccedil;&atilde;o: </td><td><select name="coord" value="<?php echo $coord; ?>">
        	<option></option>
                        <?php
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $coord)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                </select>
        </td></tr>
        <tr><td align="left">Doc&ecirc;ncia: </td><td><select name="prof" value="<?php echo $prof; ?>">
        	<option></option>
                        <?php
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $prof)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                </select>
        </td></tr>
        <tr><td align="left">Secretaria: </td><td><select name="sec" value="<?php echo $sec; ?>">
        	<option></option>
                        <?php
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $sec)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                </select>
        </td></tr>
        <tr><td align="left">Disc&ecirc;ncia : </td><td><select name="aluno" value="<?php echo $aluno; ?>">
        	<option></option>
                        <?php
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $aluno)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                </select>
        </td></tr>
        <tr><td></td><td>
	</td></tr>
    </table>
    <br><input type="submit" value="Salvar" id="salvar" />

    </div>

<div class="cont_tab professores_textarea" id="Dados3">
    <table align="left" width="100%">
	<tr><td colspan="2"><b>Papel Professor</b></td></tr>
   	<tr><td colspan="2"><hr></td></tr>
     <tr><td valign="top">Altera&ccedil;&atilde;o do Di&aacute;rio: </td><td><input style="width: 50px" type="text" name="limiteAltDiarioProf" id="limiteAltDiarioProf" value="<?php echo $limiteAltDiarioProf; ?>" maxlength="3" />
     	<br>Limite de dias para altera&ccedil;&atilde;o do di&aacute;rio (Ap&oacute;s data fim da atribui&ccedil;&atilde;o). <br>(Deixar 0 para desabilitar)</td></tr>
   	<tr><td colspan="2"><hr></td></tr>
   	<tr><td colspan="2">Obs.: Definir a quantidade de dias n&atilde;o implica na liberac&atilde;o autom&aacute;tica ap&oacute;s vencimento do prazo. &Eacute; necess&aacute;rio liberar o di&aacute;rio para altera&ccedil;&atilde;o no menu "Prazos" e informar o motivo da libera&ccedil;&atilde;o.</td></tr>
   	<tr><td colspan="2"><hr></td></tr>
   	<tr><td colspan="2">&nbsp;</td></tr>
     <tr><td valign="top">Inser&ccedil;&atilde;o de Aula: </td><td><input style="width: 50px" type="text" name="limiteInsAulaProf" id="limiteInsAulaProf" value="<?php echo $limiteInsAulaProf; ?>" maxlength="3" />
     	<br>Limite de dias para inser&ccedil;&atilde;o de aula ap&oacute;s a data real da aula. <br>(Deixar 0 para desabilitar)</td></tr>
   	<tr><td colspan="2"><hr></td></tr>
    </table>
    <br><input type="submit" value="Salvar" id="salvar" />

    </div>

<div class="cont_tab professores_textarea" id="Dados4">
    <table align="left" width="100%">
		<tr><td colspan="2"><h3><b>Campus - Digita Notas</b></h3></td></tr>
     <tr><td>Campus: </td><td><input style="width: 50px" type="text" name="campiDigitaNotas" id="campiDigitaNotas" value="<?php echo $campiDigitaNotas; ?>" maxlength="2" /></td></tr>
		<tr><td colspan="2"><hr></td></tr>
		
		<tr><td colspan="2"><h3><b>Senha</b></h3></td></tr>
     <tr><td>Altera&ccedil;&atilde;o de senha: </td><td><input style="width: 50px" type="text" name="diasAlterarSenha" id="diasAlterarSenha" value="<?php echo $diasAlterarSenha; ?>" maxlength="3" />
     	Limite de dias para altera&ccedil;&atilde;o de senha. <br>(Deixar 0 para desabilitar)</td></tr>
		<tr><td colspan="2"><hr></td></tr>

		<tr><td colspan="2"><h3><b>Atualiza&ccedil;&atilde;o do Sistema</b></h3></td></tr>
    <tr><td>IP: </td><td><input type="text" name="ipServidorAtualizacao" id="ipServidorAtualizacao" value="<?php echo $ipServidorAtualizacao; ?>" /></td></tr>
     <tr><td>Usu&aacute;rio: </td><td><input type="text" name="usuarioServidorAtualizacao" id="usuarioServidorAtualizacao" value="<?php echo $usuarioServidorAtualizacao; ?>" /></td></tr>
     <tr><td>Senha: </td><td><input type="text" name="senhaServidorAtualizacao" id="senhaServidorAtualizacao" value="<?php echo $senhaServidorAtualizacao; ?>" /></td></tr>
		<tr><td colspan="2"><hr></td></tr>

		<tr><td colspan="2"><h3><b>Envio e Valida&ccedil;&atilde;o de Fotos</b></h3></td></tr>
                <tr><td>Permitir envio de fotos por alunos: </td><td><input type='checkbox' <?php if ($envioFoto != '') print "checked"; ?> id='envioFoto' name='envioFoto' value='1' /></td></tr>
		<tr><td>Validar fotos de alunos: </td><td><input type='checkbox' <?php if ($bloqueioFoto != '') print "checked"; ?> id='bloqueioFoto' name='bloqueioFoto' value='1' /></td></tr>
    </table>
		<tr><td colspan="2">&nbsp;</td></tr>
<br><input type="submit" value="Salvar" id="salvar" />
</div>

<div class="cont_tab professores_textarea" id="Dados5">
    <table align="left" width="100%">
		<tr><td colspan="2"><h3><b>Autentica&ccedil;&atilde;o LDAP</b></h3></td></tr>
		<tr><td colspan="2"><hr></td></tr>
                <tr><td width="400"><b>Ativar LDAP para autentica&ccedil;&atilde;o:</b> </td><td><input type='checkbox' <?php if ($ldap_ativado != '') print "checked"; ?> id='ldap_ativado' name='ldap_ativado' value='1' /></td></tr>
                <tr><td><b>Usu&aacute;rio:</b> </td><td><input type="text" name="ldap_user" id="ldap_user" maxlength="50" value="<?php echo $ldap_user; ?>" /></td></tr>
                <tr><td><b>Senha:</b> </td><td><input type="text" name="ldap_password" id="ldap_password" maxlength="50" value="<?php echo $ldap_password; ?>" /></td></tr>
		<tr><td colspan="2"><hr></td></tr>
                <tr><td><b>BASE DN</b> <br>(Ex. DC=ifsp,DC=local): </td><td><input type="text" name="ldap_basedn" id="ldap_basedn" maxlength="200" value="<?php echo $ldap_basedn; ?>" /></td></tr>
                <tr><td><b>Dom&iacute;nio</b> <br>(Ex. ldap://ifsp.local e ldaps://ifsp.local para SSL): </td><td><input type="text" name="ldap_dominio" id="ldap_dominio" maxlength="200" value="<?php echo $ldap_dominio; ?>" /></td></tr>
                <tr><td><b>Porta</b> <br>(Ex. 389 -> NO SSL e 636 -> SSL): </td><td><input type="text" name="ldap_porta" id="ldap_porta" maxlength="5" value="<?php echo $ldap_porta; ?>" /></td></tr>
		<tr><td colspan="2"><hr></td></tr>
                <tr><td><b>Cache</b> <br>(Quantidade de dias que o sistema armazena em cache a autenti&ccedil;&atilde;o do usu&aacute;rio, evitando m&uacute;ltiplos acessos ao LDAP) <br> Deixe 0 para desabilitar: </td><td><input type="text" maxlength="2" name="ldap_cache" id="ldap_cache" value="<?php echo $ldap_cache; ?>" /></td></tr>
		<tr><td colspan="2"><hr></td></tr>

    </table>
		<tr><td colspan="2">&nbsp;</td></tr>
<br><input type="submit" value="Salvar" id="salvar" />
</div>

</div>
</form>

<script>
function valida() {
    if ( $('#limiteInsAulaProf').val() > 5 ) {
    	$('#limiteInsAulaProf').val('5');
  	}

    if ( $('#nome').val() == "" ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}

$(document).ready(function(){
	valida();
    $('#nome,#limiteInsAulaProf').change(function(){
        valida();
    });
});

</script>
</script>