<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela em que é possível a visualização de todas as disciplinas de todos os cursos dados pelo Campus bem como seus respectivos códigos.
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
    $resultado = mysql_query("delete from Disciplinas where codigo=$codigo");
    if ($resultado==1) {
		mensagem('OK', 'TRUE_DELETE');
    }
    else
		mensagem('INFO', 'DELETE');
	$_GET["codigo"] = null;
    $_GET["curso"] = null;
}
?>
<h2><?php print $TITLE; ?></h2>
<?php
    // inicializando as variÃ¡veis do formulÃ¡rio
    $codigo="";
    $numero="";
    $nome="";
    $ch="";
    $curso="";
    $modulo="";
    $ordem="Cursos.nome";
    $letraOrdem="d";
    
    $disciplina="";
    $nomeDisciplina="";
    
    if (isset($_GET["curso"])){
        $curso = dcrip($_GET['curso']);
        if (!empty($curso))
            $restricao = " and Cursos.codigo=$curso";
    }
    if (isset($_GET["ordem"])){
        $ordem=$_GET["ordem"];
        if ($ordem=="d")
            $ordem="Disciplinas.nome";
        else if ($ordem=="c")
            $ordem="Cursos.nome";
        else if ($ordem=="co")
            $ordem="Disciplinas.numero";
        else if ($ordem=="m")
            $ordem="Disciplinas.modulo";
    }

	if ($_GET["pesquisa"] == 1) {
		$_GET["numeroDisciplina"] = crip($_GET["numeroDisciplina"]);
		$_GET["nomeDisciplina"] = crip($_GET["nomeDisciplina"]);
	}
    
    if (dcrip($_GET["numeroDisciplina"])){
    	$numero=dcrip($_GET["numeroDisciplina"]);
        $restricao .= " and Disciplinas.numero like '%".$numero."%'";
    }
    if (dcrip($_GET["nomeDisciplina"])){
        $nome=dcrip($_GET["nomeDisciplina"]);
        $restricao .= " and Disciplinas.nome like '%".$nome."%'";
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
        <input type="hidden" value="<?php echo $codigo; ?>" name="campoCodigo" id="campoCodigo" />
        <tr><td align="right" style="width: 100px">Curso: </td><td>
                <select name="campoCurso" id="campoCurso" value="<?php echo $curso; ?>">
                	<option></option>
                    <?php
                    
                        $resultado = mysql_query("SELECT c.codigo, c.nome, m.nome FROM Cursos c, Modalidades m WHERE c.modalidade = m.codigo ORDER BY c.nome");
                        $selected=""; // controla a alteraÃ§Ã£o no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$curso)
                               $selected="selected";
                            echo "<option $selected value='".crip($linha[0])."'>$linha[1] [$linha[2]]</option>";
                            $selected="";
                        }
                        
                    ?>
                </select>
            </td></tr>
        <tr><td align="right">C&oacute;digo: </td><td><input type="text" name="campoNumero" maxlength="45" id="campoNumero" value="<?php echo $numero;?>" />
            <a href="#" id="setNumero" title="Buscar"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' /></a>
            </td></tr>
        <tr><td align="right">Nome: </td><td><input type="text" name="campoNome" maxlength="45" id="campoNome" value="<?php echo $nome;?>" />
            <a href="#" id="setNome"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png'/></a>
            </td></tr>
		<tr><td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td></tr>
    </table>
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
    $resultado = mysql_query("SELECT COUNT(*)
    						FROM Disciplinas, Cursos, Modalidades 
    						WHERE Disciplinas.curso = Cursos.codigo
    						AND Modalidades.codigo = Cursos.modalidade $restricao");
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

	if (!isset($_GET["codigo"])){
		$SITENAV = $SITE."?curso=".crip($curso)."&numeroDisciplina=".crip($numero)."&nomeDisciplina=".crip($nome);
	} else $SITENAV = $SITE."?";

require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
    <tr><th align="center" width="60"><a href="#Ordenar" class="ordenacao" id="co">N&uacute;mero</a></th><th align="left"><a href="#Ordenar" class="ordenacao" id="d">Disciplina</a></th><th width="60"><a href="#Ordenar" class="ordenacao" id="m">CH</a></th><th><a href="#Ordenar" class="ordenacao" id="c">Curso</a></th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT Disciplinas.codigo, Disciplinas.nome, Cursos.nome, Disciplinas.numero, Disciplinas.ch, Modalidades.nome 
    		FROM Disciplinas, Cursos, Modalidades 
    		WHERE Disciplinas.curso = Cursos.codigo
    		AND Modalidades.codigo = Cursos.modalidade $restricao 
    		ORDER BY $ordem limit ". ($item - 1) . ",$itensPorPagina";
	//echo $sql;
    $resultado = mysql_query($sql);
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
		$codigo = crip($linha[0]);
        echo "<tr $cdif><td align='left'>$linha[3]</td><td>".(mostraTexto($linha[1]))."</td><td>$linha[4]</td><td><a href='#' class='tooltip' title='$linha[2] [$linha[5]]'>".abreviar(mostraTexto($linha[2]), 32)."</a></td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
        $i++;
    }
    mysql_close($conexao);
    ?>
</table>

<?php require(PATH.VIEW.'/navegacao.php'); ?>

<script>
function atualizar(getLink){
	var curso = encodeURIComponent($('#campoCurso').val());
    var nome = encodeURIComponent($('#campoNome').val());
    var numero = encodeURIComponent($('#campoNumero').val());
    var URLS = '<?php print $SITE; ?>?nomeDisciplina=' + nome + '&numeroDisciplina=' + numero + '&curso=' + curso;
	if (!getLink)
		$('#index').load(URLS + '&item=<?php print $item; ?>');
	else
		return URLS;
}

function valida() {
    if ( $('#campoCurso').val() == "" || $('#campoNumero').val() == "" 
    || $('#campoNome').val() == "" || $('#campoCH').val() == "" ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}

$(document).ready(function(){
	$(".ordenacao").click(function(){ 
		$('#index').load(atualizar(1) +'&ordem='+ $(this).attr('id'));
	});

	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load(atualizar(1) + '&pesquisa=1&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
	
   	$('#campoCurso').change(function(){
    	atualizar();
	});
	
   	$('#setNome, #setNumero').click(function(){
    	$('#index').load(atualizar(1) +'&pesquisa=1');
	});
});    
</script>