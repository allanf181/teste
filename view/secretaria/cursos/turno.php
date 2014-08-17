<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Tela contendo os dados dos 3 turnos dos cursos ministrados no Campus.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
 
    // inicializando as vari�veis
    $item = 1;
    $itensPorPagina = 50;
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

    // descobrindo a quantidade total de registros
    $resultado = mysql_query("select count(*) from Turnos $restricao");
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
 
	$SITENAV = $SITE."?";
	require(PATH.VIEW.'/navegacao.php'); ?>

	<table id="listagem" border="0" align="center">
    <tr><th align="left" width="40">#</th><th>Turno</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("SELECT * FROM Turnos $restricao ORDER BY nome limit ". ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
		echo "<tr $cdif><td align='left'>$i</td><td>".mostraTexto($linha[1])."</td></td></tr>";
        $i++;
    }
    ?>

<?php require(PATH.VIEW.'/navegacao.php');

mysql_close($conexao);
?>
