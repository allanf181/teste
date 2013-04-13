<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela onde é possível visualizar a codificação utilizada pelo Nambei para identificação dos níveis dos cursos ministrados nos Campi do instituto, do ensino médio ao nível de pós-graduação.
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
    $resultado = mysql_query("delete from Modalidades where codigo=$codigo"); 
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('INFO', 'DELETE');
    $_GET["codigo"] = null;
}
?>

<h2><?php print $TITLE; ?></h2>

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
    $resultado = mysql_query("select count(*) from Modalidades $restricao");
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
    <tr><th align="center" width="40">#</th><th align="left">Modalidade</th><th width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("select * from Modalidades $restricao order by nome limit " . ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
    	$codigo = crip($linha[0]);
		echo "<tr $cdif><td align='left'>$i</td><td>".mostraTexto($linha[1])."</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
        $i++;
    }
    ?>

 <?php require(PATH.VIEW.'/navegacao.php');
 
  $resultado = mysql_query("SELECT fechamento FROM Cursos WHERE modalidade=".dcrip($_GET["codigo"]));
  $f = mysql_fetch_array($resultado);
  if ($f[0] == '') $f[0] = 's';
  mysql_close($conexao);

 ?>

</table>

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