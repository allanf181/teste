<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela contendo uma lista com o número de todas as salas registradas trazidas da base de dados do Nambei.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $local = $_POST["campoLocal"];

    if (!empty($codigo)){
        $resultado = mysql_query("update Salas set localizacao='$local' where codigo=$codigo"); 
        if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_UPDATE');
		$_GET["codigo"] = crip($_POST["campoCodigo"]);
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
	$resultado = mysql_query("delete from Salas where codigo=$codigo");
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('INFO', 'FALSE_DELETE_DEP');
    $_GET["codigo"] = null;
}
?>

<h2><?php print $TITLE; ?></h2>

<?php
    // inicializando as vari?veis do formul?rio
    $codigo="";
    $nome="";
    $local="";
    
    if (!empty ($_GET["codigo"])){ // se o par?metro n?o estiver vazio
        
        // consulta no banco
        $resultado = mysql_query("select * from Salas where codigo=".dcrip($_GET["codigo"]));
        $linha = mysql_fetch_row($resultado);
        
        // armazena os valores nas vari?veis
        $codigo = $linha[0];
        $nome = $linha[1];
        $local = $linha[2];
        $restricao = " WHERE Salas.codigo=".dcrip($_GET["codigo"]);
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
    <table align="center" width="100%" id="form">
        <input type="hidden" id="campoCodigo" name="campoCodigo" value="<?php echo $codigo; ?>" />
        <tr><td align="right" style="width: 100px">Sala: </td><td><input type="text" disabled name="campoNome" id="campoNome" maxlength="50" value="<?php echo $nome; ?>"/></td></tr>
        <tr><td align="right">Localiza&ccedil;&atilde;o: </td><td><input type="text" id="campoLocal" maxlength="100" name="campoLocal" value="<?php echo $local; ?>"/></td></tr>
        <tr><td></td><td>   
         <input type="hidden" name="opcao" value="InsertOrUpdate" />
		<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
			<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
		</tr></table>
		</td></tr>
    </table>
</form>

<?php
    // inicializando as vari?veis
    $item = 1;
    $itensPorPagina = 50;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a p?gina atual
    if (!empty($_GET["item"])){
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a p?gina anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

    // descobrindo a quantidade total de registros
    $resultado = mysql_query("select count(*) from Salas $restricao");
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];
    
    // validando o pr?ximo item
    if ($proximo > $ultimo){
        $proximo = $item;
        $ultimo = $item;
    }
    
    // validando o ?ltimo item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    
 
	$SITENAV = $SITE."?";
	require PATH . VIEW . '/paginacao.php'; ?>

	<table id="listagem" border="0" align="center">
    <tr><th align="left" width="40">#</th><th>Sala</th><th>Localiza&ccedil;&atilde;o</th><th width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("SELECT * FROM Salas $restricao ORDER BY nome limit ". ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
		echo "<tr $cdif><td align='left'>$i</td><td>".mostraTexto($linha[1])."</td><td>".mostraTexto($linha[2])."</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td></tr>";
        $i++;
    }

mysql_close($conexao);
?>
<script>
function valida() {
    if ( $('#campoNome').val() == "" || $('#campoLocal').val() == "" || $('#campoCodigo').val() == '' ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}
$(document).ready(function(){
    valida();
    $('#campoNome, #campoLocal').change(function(){
        valida();
    });

	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load('<?php print $SITE; ?>?opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});

	$(".item-alterar").click(function(){
		var codigo = $(this).attr('id');
		$('#index').load('<?php print $SITE; ?>?codigo=' + codigo);
	});
});    
</script>
