<?php 
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela que permite a adição, alteração ou exclusão dos tipos de perfis possíveis no acesso ao sistema Web Diário.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $nome = $_POST["campoNome"];

    if (empty($codigo)){
        $resultado = mysql_query("insert into Tipos values(0,'$nome')"); 
        if ($resultado==1)
			mensagem('OK', 'TRUE_INSERT');
        else
			mensagem('NOK', 'FALSE_INSERT');
		$_GET["codigo"] = crip(mysql_insert_id());
    }
    else{
        $resultado = mysql_query("update Tipos set nome='$nome' where codigo=$codigo"); 
        if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_UPDATE');
		$_GET["codigo"] = crip($_POST["campoCodigo"]);
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from Tipos where codigo=$codigo");
	if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
	else
		mensagem('OK', 'FALSE_DELETE_DEP');
    $_GET["codigo"] = null;
}

?>

<h2><? print $TITLE; ?></h2>
<?php
    // inicializando as variáveis do formulário
    $codigo="";
    $nome="";
    $sigla="";
    if (!empty ($_GET["codigo"])){ // se o parâmetro não estiver vazio
        
        // consulta no banco
        $resultado = mysql_query("select * from Tipos where codigo=".dcrip($_GET["codigo"]));
        $linha = mysql_fetch_row($resultado);
        
        // armazena os valores nas variáveis
        $codigo = $linha[0];
        $nome = $linha[1];
        $restricao = " WHERE Tipos.codigo=".dcrip($_GET["codigo"]);
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
        <input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
        <tr><td align="right">Nome: </td><td><input type="text" id="campoNome" maxlength="45" name="campoNome" value="<?php echo $nome; ?>" /></td></tr>
        <tr><td></td><td>
	<input type="hidden" name="opcao" value="InsertOrUpdate" />
	<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
		<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
	</tr></table>
	</td></tr>
    </table>
	</form>
	</div>

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
    $resultado = mysql_query("select count(*) from Tipos $restricao");
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
    <tr><th align="center" width="40">#</th><th align="left">Tipo</th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("select * from Tipos $restricao order by nome limit " . ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
        echo "<tr $cdif><td align='left'>$i</td><td>".mostraTexto($linha[1])."</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td></tr>";
        $i++;
    }
    ?>
<?php 	require(PATH.VIEW.'/navegacao.php');

 mysql_close($conexao);
 ?>
</table>
<script>
function valida() {
    if ( $('#campoNome').val() == "" ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}
$(document).ready(function(){
	valida();
    $('#campoNome').keyup(function(){
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
