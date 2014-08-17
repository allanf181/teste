<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita associar um ou mais coordenadores a um ou mais cursos.
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

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $coordenador = $_POST["campoCoordenador"];
    $curso = $_POST["campoCurso"];

    if (empty($codigo)){
        $resultado = mysql_query("insert into Coordenadores values(NULL, $coordenador, $curso)");
        if ($resultado==1)
			mensagem('OK', 'TRUE_INSERT');
        else
			mensagem('NOK', 'FALSE_INSERT');
		
		$_GET["codigo"] = crip(mysql_insert_id());
    }else{
        $resultado = mysql_query("update Coordenadores set coordenador=$coordenador,curso=$curso where codigo=$codigo");
        if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_UPDATE');
		
		$_GET["codigo"] = crip($_POST["campoCodigo"]); 
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from Coordenadores where codigo=$codigo");
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('NOK', 'FALSE_DELETE');
    $_GET["codigo"] = null;
}

?> 

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
    // inicializando as variï¿½veis do formulï¿½rio
    $codigo="";
    $coordenador="";
    $curso="";
    
    if (!empty ($_GET["codigo"])){ // se o parï¿½metro nï¿½o estiver vazio
        
        // consulta no banco
        $sql = "SELECT codigo, coordenador, curso FROM Coordenadores WHERE codigo = ".dcrip($_GET["codigo"]);
		//print $sql;
        $resultado = mysql_query($sql);
        $linha = mysql_fetch_row($resultado);
        
        // armazena os valores nas variï¿½veis
        $codigo = $linha[0];
        $coordenador = $linha[1];
        $curso = $linha[2];
        $restricao = " AND c.codigo=".dcrip($_GET["codigo"]);
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
        <tr><td align="right">Curso: </td><td><select name="campoCurso" id="campoCurso" value="<?php echo $curso; ?>"><option></option>
                    <?php
                        $resultado = mysql_query("select c.codigo, c.nome, m.nome, m.codigo 
                        							from Cursos c, Modalidades m
                        							where m.codigo = c.modalidade
													order by c.nome");
                        $selected=""; // controla a alteraÃ§Ã£o no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$curso)
                               $selected="selected";
							if ($linha[3] < 1000 || $linha[3] >= 2000) $linha[1] = "$linha[1] [$linha[2]]";                              
                            echo "<option $selected value='$linha[0]'>[$linha[0]] $linha[1]</option>";
                            $selected="";
                        }
                        
                    ?>
                </select>
        </td></tr>
        <tr><td align="right">Coordenador: </td><td>
        	<select name="campoCoordenador" id="campoCoordenador" value="<?php echo $coordenador; ?>">
        		<option></option>
                    <?php
                        $resultado = mysql_query("SELECT * FROM Pessoas p, PessoasTipos pt
                        							WHERE pt.pessoa = p.codigo AND pt.tipo = $COORD ORDER BY p.nome");
                        $selected=""; // controla a alteracao no campo select
                        $i=0;
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$coordenador)
                               $selected="selected";
                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
                            $selected="";
                            $i++;
                        }
                        if (!$i)
                        	echo "<option selected>Nenhuma pessoa cont&eacute;m o tipo Coordenador</option>";
                    ?>
                </select>
        </td></tr>
        <tr><td></td><td>
		<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
	    <input type="hidden" name="opcao" value="InsertOrUpdate" />    
		<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
			<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
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
    $resultado = mysql_query("SELECT COUNT(*) 
							    FROM Coordenadores c, Cursos cu, Pessoas p 
							    WHERE c.coordenador = p.codigo 
							    AND c.curso = cu.codigo $restricao");
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
    <tr><th align="left" width="60">C&oacute;digo</th><th align="left">Curso</th><th align="left">Coordenador</th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $resultado = mysql_query("SELECT c.codigo, cu.nome, p.nome, cu.codigo FROM Coordenadores c, Cursos cu, Pessoas p 
							    WHERE c.coordenador = p.codigo 
							    AND c.curso = cu.codigo $restricao ORDER BY cu.nome limit ". ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
		$codigo = crip($linha[0]);
        echo "<tr $cdif><td>".$linha[3]."</td><td>".mostraTexto($linha[1])."</td><td>".mostraTexto($linha[2])."</td><td><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td></tr>";
        $i++;
    }
    mysql_close($conexao);
    ?>
    
<?php require(PATH.VIEW.'/navegacao.php'); ?>

<script>
function valida() {
    if ( $('#campoCurso').val() == "" || $('#campoCoordenador').val() == "" ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}
$(document).ready(function(){
    valida();
    $('#campoCurso, #campoCoordenador').change(function(){
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