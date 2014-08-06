<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita a tela em que é possível a visualização completa de acessos ao sistema, sendo possível identificar qual perfil o acessou e em qual data e hora.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

?>

<h2><?php print $TITLE; ?></h2>

<?php
// inicializando as vari�veis do formul�rio
$data=date("d/m/Y");
$filtro="";
$filtragem = "";
if ($_GET["data"] != '')
    $data = $_GET["data"];
if (($_GET["filtro"]) != ''){
    $filtro = $_GET["filtro"];

    $filtragem = "and ((l.url like '%$filtro%') or (p.nome like '%$filtro%'))";
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
        <tr><td align="right" style="width: 100px">Data: </td><td><input value="<?php echo $data; ?>" type="text" name="data" id="data" onChange="$('#index').load('<?php print $SITE; ?>?filtro=<?php echo $filtro; ?>&data=' + this.value);"></td></tr>
        <tr><td align="right">Filtro: </td><td><input value="<?php echo $filtro; ?>" type="text" value="<?php echo $filtro; ?>" name=filtro" id=filtro" onblur="$('#index').load('<?php print $SITE; ?>?data=<?php echo $data; ?>&filtro=' + encodeURIComponent(this.value));" />
        <a href="#" title="Buscar"><img class="botao" style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' id="atualizaData" /></a>
        &nbsp;&nbsp;<a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
	</tr>
    </table>
</form>

<?php
    // inicializando as vari�veis
    $item = 1;
    $itensPorPagina = 100;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a p�gina atual
    if (!empty($_GET["item"])){
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a p�gina anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

	$SITENAV = $SITE."?data=$data&filtro=$filtro";

	$data = dataMysql($data);
    // descobrindo a quantidade total de registros
    $sql = "SELECT COUNT( * ) 
				FROM Logs l, Pessoas p
				WHERE l.pessoa = p.codigo AND origem NOT LIKE 'CRON%'
				AND STR_TO_DATE( l.data, '%Y-%m-%d' ) = '$data' $filtragem
				ORDER BY l.data, l.codigo DESC";
    $resultado = mysql_query($sql);
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];
    
    // validando o pr�ximo item
    if ($proximo > $ultimo){
        $proximo = $item;
        $ultimo = $item;
    }
    
    // validando o �ltimo item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    
 
	require(PATH.VIEW.'/navegacao.php'); ?>

	<table id="listagem" border="0" align="center">
    <tr><th align="center">URL</th><th style="width: 20%">Data</th><th width="100" style="width: 20%">Origem</th><th width="100" style="width: 20%">Pessoa</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT l.codigo, l.url, date_format(l.data, '%d/%m/%Y %H:%i:%s'), p.codigo, p.nome, l.origem
    			FROM Logs l, Pessoas p
				WHERE l.pessoa = p.codigo AND origem NOT LIKE 'CRON%'
				AND STR_TO_DATE( l.data, '%Y-%m-%d' ) = '$data' $filtragem
		        ORDER BY l.data, l.codigo DESC limit ". ($item - 1) . ",$itensPorPagina";
    //echo $sql;
    $resultado = mysql_query($sql);
    $i=0;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
        echo "<tr $cdif><td>$linha[1]</td><td align='left'>$linha[2]</td><td align='left'>$linha[5]</td><td align='left'>[$linha[3]]$linha[4]</td></tr>";
        $i++;
    }
    mysql_close($conexao);
    ?>
</table>

<?php 	require(PATH.VIEW.'/navegacao.php'); ?>

 
<script>
$(document).ready(function(){
	$("#data").datepicker({
	    dateFormat: 'dd/mm/yy',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
	    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
	    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
	    monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
	    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
	    nextText: 'Próximo',
	    prevText: 'Anterior'
	});
});
</script>