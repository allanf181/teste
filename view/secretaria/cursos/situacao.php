<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita a tela que exibe uma lista contendo todos os possíveis status dos discentes relativos à sua situação na disciplina, módulo ou curso que ele frequenta.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $sigla = $_POST["campoSigla"];
	$listar = ($_POST["campoListar"])? 1 : 0;
	$habilitar = ($_POST["campoHabilitar"])? 1 : 0;

    if (!empty($codigo)){
        $resultado = mysql_query("update Situacoes set sigla='$sigla', listar='$listar', habilitar='$habilitar' where codigo=$codigo");
        if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_UPDATE');
        $_GET["codigo"] = crip($_POST["campoCodigo"]);
    }
}

?>

<h2><?php print $TITLE; ?></h2>

<?php
    // inicializando as variáveis do formulário
    $codigo="";
    $nome="";
	$sigla="";
	$listar="";
	$habilitar="";
    
    if (!empty ($_GET["codigo"])){ // se o parâmetro não estiver vazio
        
        // consulta no banco
        $resultado = mysql_query("select * from Situacoes where codigo=".dcrip($_GET["codigo"]));
        $linha = mysql_fetch_row($resultado);
        
        // armazena os valores nas variáveis
        $codigo = $linha[0];
        $nome = $linha[1];
        $sigla = $linha[2];
        $listar = $linha[3];
        $habilitar = $linha[4];
        $restricao = " WHERE Situacoes.codigo=".dcrip($_GET["codigo"]);
    } 

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
    <table align="center" id="form" width="100%">
        <input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
        <tr><td align="right" style="width: 100px">Nome: </td><td><input type="text" disabled id="campoNome" name="campoNome" maxlength="45" value="<?php print $nome; ?>"/></td></tr>
        <tr><td align="right">Sigla: </td><td><input type="text" id="campoSigla" maxlength="2" name="campoSigla" value="<?php print $sigla; ?>"/></td></tr>
            </td></tr>
        <?php $checked=''; if ($listar) $checked="checked='checked'"; ?>
        <tr><td align="right">Listar: </td><td><input type='checkbox' <?php print $checked; ?> id="campoListar" name='campoListar' value='1' /> (listar registros com essa situa&ccedil;&atilde;o em di&aacute;rios e relat&oacute;rios.)</td></tr>
        <?php $checked=''; if ($habilitar) $checked="checked='checked'"; ?>
        <tr><td align="right">Habilitar: </td><td><input type='checkbox' <?php print $checked; ?> id="campoHabilitar" name='campoHabilitar' value='1' /> (habilitar para digita&ccedil;&atilde;o e outras a&ccedil;&otilde;es os registros com essa situa&ccedil;&atilde;o.)</td></tr>
        <tr><td></td><td>
		<input type="hidden" name="opcao" value="InsertOrUpdate" />    
		<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
		<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
		</tr></table>
	</td></tr>
    </table>
</form>

<?php
    // inicializando as variáveis
    $item = 1;
    $itensPorPagina = 50;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a página atual
    if (!empty($_GET["item"])){
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a página anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

    // descobrindo a quantidade total de registros
    $resultado = mysql_query("select count(*) from Situacoes $restricao");
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];
    
    // validando o próximo item
    if ($proximo > $ultimo){
        $proximo = $item;
        $ultimo = $item;
    }
    
    // validando o último item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    
 
$SITENAV = $SITE.'?';

require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
    <tr><th align="left" width="40">#</th><th align="left">Situação</th><th>Sigla</th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("SELECT * FROM Situacoes $restricao ORDER BY nome limit ". ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
        echo "<tr $cdif><td align='left'>$i</td><td>".mostraTexto($linha[1])."</td><td>$linha[2]</td><td align='center'><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td></tr>";
        $i++;
    }
    mysql_close($conexao);
    ?>

 <?php require(PATH.VIEW.'/navegacao.php'); ?>
</table>
<script>
function valida() {
    if ( ( $('#campoNome').val() == "" || $('#campoSigla').val() == "" ) 
		|| ( ($("#campoListar").is(":checked")==false) && ($("#campoHabilitar").is(":checked")==false)) ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}
$(document).ready(function(){
    valida();
    $('#campoNome, #campoSigla, #campoHabilitar, #campoListar').change(function(){
        valida();
    });

	$(".item-alterar").click(function(){
		var codigo = $(this).attr('id');
		$('#index').load('<?php print $SITE; ?>?codigo=' + codigo);
	});
});    
</script>
