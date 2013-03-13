<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela em que é possível o cadastro da identificação do Campus que está utilizando o sistema, as atribuições dos perfis de usuários que terão acesso ao sistema, as datas limites do docente para alteração do diário e inserção de registros de aulas após a data real da mesma.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $nome = $_POST["campoNome"];
    $cidade = $_POST["campoCidade"];
    $ged = $_POST["campoGed"];
    $adm = $_POST["campoAdm"];
    $sec = $_POST["campoSec"];
    $prof = $_POST["campoProf"];
    $aluno = $_POST["campoAluno"];
    $coord = $_POST["campoCoord"];
    $diasAlterarSenha = $_POST["campoSenha"];
    $limiteAltDiarioProf = $_POST["campoLimiteAltDiarioProf"];
    $limiteInsAulaProf = $_POST["campoLimiteInsAulaProf"];
    $ipServidorAtualizacao = $_POST["campoIpServidorAtualizacao"];
    $usuarioServidorAtualizacao = $_POST["campoUsuarioServidorAtualizacao"];
    $senhaServidorAtualizacao = $_POST["campoSenhaServidorAtualizacao"];
    $bloqueioFoto = $_POST["bloqueioFoto"];
    $campiDigitaNotas = $_POST["campoCampiDigitaNotas"];

	$result = mysql_query("SELECT * FROM Instituicoes");
	if (mysql_num_rows($result) == '') {
  	$resultado = mysql_query("INSERT INTO Instituicoes VALUES(0,'$nome','$cidade', $ged, '$adm', '$sec', '$prof', '$aluno', '$coord', '$diasAlterarSenha', '$limiteAltDiarioProf', '$limiteInsAulaProf', '$ipServidorAtualizacao', '$usuarioServidorAtualizacao', '$senhaServidorAtualizacao', 'bloqueioFoto', '$campiDigitaNotas')");
   	if ($resultado==1)
			mensagem('OK', 'TRUE_INSERT');
    else
			mensagem('NOK', 'FALSE_INSERT');

	}	else {
    	$resultado = mysql_query("UPDATE Instituicoes SET nome='$nome',cidade='$cidade', ged=$ged, adm='$adm', sec='$sec', prof='$prof', aluno='$aluno', coord='$coord', diasAlterarSenha='$diasAlterarSenha', limiteAltDiarioProf='$limiteAltDiarioProf', limiteInsAulaProf='$limiteInsAulaProf', ipServidorAtualizacao='$ipServidorAtualizacao', usuarioServidorAtualizacao='$usuarioServidorAtualizacao', senhaServidorAtualizacao='$senhaServidorAtualizacao', bloqueioFoto='$bloqueioFoto', campiDigitaNotas='$campiDigitaNotas'");
      if ($resultado==1)
				mensagem('OK', 'TRUE_UPDATE');
      else
				mensagem('NOK', 'FALSE_UPDATE');
	}
}
?> 

<h2><?php print $TITLE; ?></h2>

<?php
    // inicializando as vari?veis do formul?rio
    $codigo="";
    $nome="";
    $cidade="";
    $cidade="";
    $ged="";
    $adm="";
    $sec="";
    $aluno="";
    $coord="";
    $bloqueioFoto="";
    $campiDigitaNotas="";
    
    // consulta no banco
    $resultado = mysql_query("SELECT i.nome, i.ged, i.adm, i.sec, i.prof, i.aluno, i.coord, i.cidade,
    							i.diasAlterarSenha, i.limiteAltDiarioProf, i.limiteInsAulaProf, i.bloqueioFoto,
    							i.ipServidorAtualizacao, i.usuarioServidorAtualizacao, i.senhaServidorAtualizacao,
    							i.campiDigitaNotas
    							FROM Instituicoes i");
    $linha = mysql_fetch_row($resultado);
	if ($linha) { 
    	$nome = $linha[0];
    	$ged = $linha[1];
    	$adm = $linha[2];
    	$sec = $linha[3];
    	$prof = $linha[4];
    	$aluno = $linha[5];
    	$coord = $linha[6];
    	$cidade = $linha[7];
    	$diasAlterarSenha = $linha[8];
    	$limiteAltDiarioProf = $linha[9];
    	$limiteInsAulaProf = $linha[10];
    	$bloqueioFoto = $linha[11];
    	$ipServidorAtualizacao = $linha[12];
    	$usuarioServidorAtualizacao = $linha[13];
    	$senhaServidorAtualizacao = $linha[14];
    	$campiDigitaNotas = $linha[15];
    }
 	?>
	<link rel="stylesheet" type="text/css" href="view/css/aba.css" media="screen" />
	<script src="view/js/aba.js"></script>
	<?php
    print "<script>\n";
    print "    $('#form_padrao').html5form({ \n";
    print "        method : 'POST', \n";
    print "        action : '$SITE', \n";
    print "        responseDiv : '#index', \n";
    print "        colorOn: '#000', \n";
    print "        colorOff: '#999', \n";
    print "        messages: 'br' \n";
    print "    }) \n";
    print "</script>\n";

    print "<div id=\"html5form\" class=\"main\">\n";
    print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n";
?>
<ul class="tabs">
	<li><a href="#Dados">Local</a></li>
	<li><a href="#Dados2">Pap&eacute;is</a></li>
	<li><a href="#Dados3">Limites</a></li>
	<li><a href="#Dados4">Configura&ccedil;&otilde;es</a></li>
</ul>
<div class="tab_container">
<input type="hidden" name="opcao" value="InsertOrUpdate" />
<div class="cont_tab" id="Dados">
    <table align="center" width="60%">
      <tr><td align="left">Nome: </td><td><input type="text" name="campoNome" id="campoNome" maxlength="200" value="<?php echo $nome; ?>"/></td></tr>
      <tr><td align="left">Cidade: </td><td><input type="text" name="campoCidade" id="campoCidade" maxlength="200" value="<?php echo $cidade; ?>"/></td></tr>
    </table>
<br><input type="submit" value="Salvar" id="salvar" />
    
    </div>

<div class="cont_tab" id="Dados2">
    <table align="center" width="60%">
 	  <tr><td colspan="2"><hr><p align="center">Tipos (Pap&eacute;is)</p></td></tr>
        <tr><td align="left">Administrador: </td><td><select name="campoAdm" value="<?php echo $adm; ?>">
        	<option></option>
                    <?php
                        $resultado = mysql_query("SELECT * FROM Tipos ORDER BY nome");
                        $selected=""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$adm)
                               $selected="selected";
                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
                            $selected="";
                        }
                    ?>
                </select>
        </td></tr>
        <tr><td align="left">Ger&ecirc;ncia Educacional: </td><td><select name="campoGed" value="<?php echo $ged; ?>">
        	<option></option>
                    <?php
                        $resultado = mysql_query("SELECT * FROM Tipos ORDER BY nome");
                        $selected=""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$ged)
                               $selected="selected";
                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
                            $selected="";
                        }
                    ?>
                </select>
        </td></tr>
        <tr><td align="left">Coordena&ccedil;&atilde;o: </td><td><select name="campoCoord" value="<?php echo $coord; ?>">
        	<option></option>
                    <?php
                        $resultado = mysql_query("SELECT * FROM Tipos ORDER BY nome");
                        $selected=""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$coord)
                               $selected="selected";
                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
                            $selected="";
                        }
                    ?>
                </select>
        </td></tr>
        <tr><td align="left">Doc&ecirc;ncia: </td><td><select name="campoProf" value="<?php echo $prof; ?>">
        	<option></option>
                    <?php
                        $resultado = mysql_query("SELECT * FROM Tipos ORDER BY nome");
                        $selected=""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$prof)
                               $selected="selected";
                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
                            $selected="";
                        }
                    ?>
                </select>
        </td></tr>
        <tr><td align="left">Secretaria: </td><td><select name="campoSec" value="<?php echo $sec; ?>">
        	<option></option>
                    <?php
                        $resultado = mysql_query("SELECT * FROM Tipos ORDER BY nome");
                        $selected=""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$sec)
                               $selected="selected";
                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
                            $selected="";
                        }
                    ?>
                </select>
        </td></tr>
        <tr><td align="left">Disc&ecirc;ncia : </td><td><select name="campoAluno" value="<?php echo $aluno; ?>">
        	<option></option>
                    <?php
                        $resultado = mysql_query("SELECT * FROM Tipos ORDER BY nome");
                        $selected=""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$aluno)
                               $selected="selected";
                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
                            $selected="";
                        }
                    ?>
                </select>
        </td></tr>
        <tr><td></td><td>
	</td></tr>
    </table>
    <br><input type="submit" value="Salvar" id="salvar" />

    </div>

<div class="cont_tab" id="Dados3">
    <table align="left" width="100%">
	<tr><td colspan="2"><b>Papel Professor</b></td></tr>
   	<tr><td colspan="2"><hr></td></tr>
     <tr><td valign="top">Altera&ccedil;&atilde;o do Di&aacute;rio: </td><td><input style="width: 50px" type="text" name="campoLimiteAltDiarioProf" id="campoLimiteAltDiarioProf" value="<?php echo $limiteAltDiarioProf; ?>" maxlength="3" />
     	<br>Limite de dias para altera&ccedil;&atilde;o do di&aacute;rio (Ap&oacute;s data fim da atribui&ccedil;&atilde;o). <br>(Deixar 0 para desabilitar)</td></tr>
   	<tr><td colspan="2"><hr></td></tr>
   	<tr><td colspan="2">Obs.: Definir a quantidade de dias n&atilde;o implica na liberac&atilde;o autom&aacute;tica ap&oacute;s vencimento do prazo. &Eacute; necess&aacute;rio liberar o di&aacute;rio para altera&ccedil;&atilde;o no menu "Prazos" e informar o motivo da libera&ccedil;&atilde;o.</td></tr>
   	<tr><td colspan="2"><hr></td></tr>
   	<tr><td colspan="2">&nbsp;</td></tr>
     <tr><td valign="top">Inser&ccedil;&atilde;o de Aula: </td><td><input style="width: 50px" type="text" name="campoLimiteInsAulaProf" id="campoLimiteInsAulaProf" value="<?php echo $limiteInsAulaProf; ?>" maxlength="3" />
     	<br>Limite de dias para inser&ccedil;&atilde;o de aula ap&oacute;s a data real da aula. <br>(Deixar 0 para desabilitar)</td></tr>
   	<tr><td colspan="2"><hr></td></tr>
    </table>
    <br><input type="submit" value="Salvar" id="salvar" />

    </div>

<div class="cont_tab" id="Dados4">
    <table align="left" width="100%">
		<tr><td colspan="2"><h3><b>Campus - Digita Notas</b></h3></td></tr>
     <tr><td>Campus: </td><td><input style="width: 50px" type="text" name="campoCampiDigitaNotas" id="campoCampiDigitaNotas" value="<?php echo $campiDigitaNotas; ?>" maxlength="2" /></td></tr>
		<tr><td colspan="2"><hr></td></tr>
		
		<tr><td colspan="2"><h3><b>Senha</b></h3></td></tr>
     <tr><td>Altera&ccedil;&atilde;o de senha: </td><td><input style="width: 50px" type="text" name="campoSenha" id="campoSenha" value="<?php echo $diasAlterarSenha; ?>" maxlength="3" />
     	Limite de dias para altera&ccedil;&atilde;o de senha. <br>(Deixar 0 para desabilitar)</td></tr>
		<tr><td colspan="2"><hr></td></tr>

		<tr><td colspan="2"><h3><b>Atualiza&ccedil;&atilde;o do Sistema</b></h3></td></tr>
    <tr><td>IP: </td><td><input type="text" name="campoIpServidorAtualizacao" id="campoIpServidorAtualizacao" value="<?php echo $ipServidorAtualizacao; ?>" /></td></tr>
     <tr><td>Usu&aacute;rio: </td><td><input type="text" name="campoUsuarioServidorAtualizacao" id="campoUsuarioServidorAtualizacao" value="<?php echo $usuarioServidorAtualizacao; ?>" /></td></tr>
     <tr><td>Senha: </td><td><input type="text" name="campoSenhaServidorAtualizacao" id="campoSenhaServidorAtualizacao" value="<?php echo $senhaServidorAtualizacao; ?>" /></td></tr>
		<tr><td colspan="2"><hr></td></tr>

		<tr><td colspan="2"><h3><b>Valida&ccedil;&atilde;o de Fotos</b></h3></td></tr>
		<tr><td>Validar fotos de alunos: </td><td><input type='checkbox' <?php if ($bloqueioFoto != '') print "checked"; ?> id='bloqueioFoto' name='bloqueioFoto' value='1' /></td></tr>
    </table>
		<tr><td colspan="2">&nbsp;</td></tr>
<br><input type="submit" value="Salvar" id="salvar" />
</div>
</div>
</form>

<script>
function valida() {
    if ( $('#campoLimiteInsAulaProf').val() > 5 ) {
    	$('#campoLimiteInsAulaProf').val('5');
  	}

    if ( $('#campoNome').val() == "" ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}

$(document).ready(function(){
	valida();
    $('#campoNome,#campoLimiteInsAulaProf').change(function(){
        valida();
    });
});

</script>
</script>