<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela em que é exibida uma lista com os códigos, nomes dos cursos e modalidades de todos os cursos dados pelo Campus.
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
    $nomeAlternativo = $_POST["campoNomeAlternativo"];

    if (!empty($codigo)){
        $resultado = mysql_query("update Cursos set nomeAlternativo='$nomeAlternativo' where codigo=$codigo");
        if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_UPDATE');
		
		$_GET["codigo"] = crip($_POST["campoCodigo"]); 
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from Cursos where codigo=$codigo");
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('NOK', 'FALSE_DELETE');
    $_GET["codigo"] = null;
}
?> 

<h2><?php print $TITLE; ?></h2>

<?php
    // inicializando as variï¿½veis do formulï¿½rio
    $codigo="";
    $nome="";
    
    if (!empty ($_GET["codigo"])){ // se o parï¿½metro nï¿½o estiver vazio
        
        // consulta no banco
        $sql = "SELECT Cursos.codigo, Cursos.nome, Cursos.nomeAlternativo FROM Cursos 
        							WHERE Cursos.codigo=".dcrip($_GET["codigo"]);
		//print $sql;
        $resultado = mysql_query($sql);
        $linha = mysql_fetch_row($resultado);
        
        // armazena os valores nas variï¿½veis
        $codigo = $linha[0];
        $nome = $linha[1];
        $nomeAlternativo = $linha[2];
        $restricao = " AND Cursos.codigo=".dcrip($_GET["codigo"]);
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
        <tr><td align="right" style="width: 120px">Nome: </td><td><input type="text" style="width: 400px" disabled maxlength="145" name="campoNome" id="campoNome" value="<?php echo $nome; ?>"/></td></tr>
        <tr><td align="right" style="width: 100px">Nome Alternativo: </td><td><input type="text" style="width: 400px" maxlength="145" title="Nome alternativo utilizado no atestado de matr&iacutecula." name="campoNomeAlternativo" id="campoNomeAlternativo" value="<?php echo $nomeAlternativo; ?>"/></td></tr>
        <tr><td></td><td>
		<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
	    <input type="hidden" name="opcao" value="InsertOrUpdate" />    
		<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
			<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
		</tr></table>
		</td></tr>
    </table></center>
</form>

<?php
    // inicializando as variï¿½veis
    $item = 1;
    $itensPorPagina = 50;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a pï¿½gina atual
    if (!empty($_GET["item"])){
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a pï¿½gina anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

    // descobrindo a quantidade total de registros
    $resultado = mysql_query("SELECT COUNT(*) FROM Cursos, Modalidades WHERE Modalidades.codigo = Cursos.modalidade $restricao");
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];
    
    // validando o prï¿½ximo item
    if ($proximo > $ultimo){
        $proximo = $item;
        $ultimo = $item;
    }
    
    // validando o ï¿½ltimo item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    
 
$SITENAV = $SITE.'?';

require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
    <tr><th align="left" width="60">C&oacute;digo</th><th align="left">Curso</th><th align="left">Modalidade</th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("SELECT Cursos.codigo, Cursos.nome, Modalidades.nome FROM Cursos, Modalidades WHERE Modalidades.codigo = Cursos.modalidade $restricao ORDER BY Cursos.nome limit ". ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
		$codigo = crip($linha[0]);
        echo "<tr $cdif><td>".$linha[0]."</td><td>".mostraTexto($linha[1])."</td><td>".mostraTexto($linha[2])."</td><td><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td></tr>";
        $i++;
    }
    mysql_close($conexao);
    ?>
    
<?php require(PATH.VIEW.'/navegacao.php'); ?>

<script>
$(document).ready(function(){
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