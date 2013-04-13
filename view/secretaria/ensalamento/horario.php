<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela em que é possível a visualização dos intervalos das aulas e seus horários do período matutino, vespertino e noturno.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
	$resultado = mysql_query("delete from Horarios where codigo=$codigo");
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('INFO', 'FALSE_DELETE_DEP');
    $_GET["codigo"] = null;
}
?>

<h2><?php print $TITLE; ?></h2>

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
    $resultado = mysql_query("select count(*) from Horarios $restricao");
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
	    require PATH . VIEW . '/paginacao.php';
 ?>

	<table id="listagem" border="0" align="center">
    <tr><th align="center" width="40">#</th><th>Nome</th><th>Hor&aacute;rio</th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("SELECT codigo, nome, DATE_FORMAT(inicio, '%H:%i'), DATE_FORMAT(fim, '%H:%i') FROM Horarios $restricao ORDER BY inicio limit ". ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
		echo "<tr $cdif><td align='left'>$i</td><td>$linha[1]</td><td>$linha[2] a $linha[3]</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
        $i++;
    }
    ?>

<?php 


mysql_close($conexao);
?>
<script>
$(document).ready(function(){
	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load('<?php print $SITE; ?>?opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
});    
</script>