<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela contendo uma lista com todos os códigos e descrição das turmas ativas no semestre do Campus.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from Turmas where codigo=$codigo") ;
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
    $resultado = mysql_query("SELECT COUNT(*) 
	            FROM Turmas, Cursos, Turnos 
	            WHERE Turmas.curso = Cursos.codigo 
	            and (Turmas.semestre=$semestre OR Turmas.semestre=0)
	            and Turmas.ano=$ano
	            and Turmas.turno = Turnos.codigo $anoRestricao ");
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
 
$SITENAV = $SITE."?";
require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
    <tr><th>N&uacute;mero</th><th>Curso</th><th>Modalidade</th><th width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT t.codigo, t.ano, t.semestre, t.numero, c.nome, m.nome, c.codigo
            FROM Turmas t, Cursos c, Modalidades m 
            WHERE t.curso = c.codigo 
            and (t.semestre=$semestre OR t.semestre=0)
            and t.ano=$ano
            and m.codigo = c.modalidade $anoRestricao $restricao 
            ORDER BY c.nome, t.numero limit ". ($item - 1) . ",$itensPorPagina";
    //echo $sql;
    $resultado = mysql_query($sql);
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
        echo "<tr $cdif><td align='left'>$linha[3]</td><td align='left'>".mostraTexto($linha[4])." ($linha[6])</td><td align='left'>".mostraTexto($linha[5])."</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
        $i++;
    }
    mysql_close($conexao);
    ?>

</table>

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
});    
</script>