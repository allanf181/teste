<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita a tela em que é possível a visualização da atribuição das disciplinas aos seus respectivos docentes e o código das turmas dessas disciplinas.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_GET["opcao"] == 'delete') {
	$codigo = dcrip($_GET["codigo"]);
	$resultado = mysql_query("DELETE FROM Professores WHERE codigo=$codigo");
	if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
	else
		mensagem('NOK', 'FALSE_DELETE');
	
	$_GET["codigo"] = null;
}
?>

<h2><?php print $TITLE; ?></h2>

<?php
// inicializando as variÃ¡veis do formulÃ¡rio
$codigo="";
$disciplina="";
$professor="";
$turma="";
$ementa="";
$restricao="";
$bimestre="";
$grupo="";

$ordem="d.nome";

if (isset($_GET["turma"])){
    $turma=dcrip($_GET["turma"]);
    if (!empty($turma))
    $restricao.=" and t.codigo=$turma";
}

if (isset($_GET["ordem"])){
    $ordem=$_GET["ordem"];
    if ($ordem=="d")
    $ordem="d.nome";
    else if ($ordem=="t")
    $ordem="t.numero";
    else if ($ordem=="p")
    $ordem="p.nome";    
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
<table align="center" align="left" id="form" width="100%" >
	<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
	<tr><td align="right" style="width: 100px">Turma: </td><td>
		<select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>" style="width: 650px">
			<option></option>
			<?php

			$resultado = mysql_query("select distinct t.codigo, t.numero, c.nome, m.nome, m.codigo 
                        							from Cursos c, Turmas t, Modalidades m
                        							where t.curso=c.codigo 
                        							and m.codigo = c.modalidade
													and (t.semestre=$semestre OR t.semestre=0) 
													order by c.nome, t.numero");
			$selected=""; // controla a alteraÃ§Ã£o no campo select
			while ($linha = mysql_fetch_array($resultado)){
				if ($linha[0]==$turma)
				$selected="selected";
                if ($linha[4] < 1000 || $linha[4] >= 2000) $linha[2] = "$linha[2] [$linha[3]]";				
				echo "<option $selected value='".crip($linha[0])."'>[$linha[1]] $linha[2]</option>";
				$selected="";
			}

			?>
		</select>
	</td></tr>
<tr><td>
	<table width="100%"><tr><td>&nbsp;</td>
	<td align="right"><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
</tr></table>
</td></tr>
</table>

</form>

<?php
// inicializando as variÃ¡veis
$item = 1;
$itensPorPagina = 25;
$primeiro = 1;
$anterior = $item - $itensPorPagina;
$proximo = $item + $itensPorPagina;
$ultimo = 1;

// validando a pÃ¡gina atual
if (!empty($_GET["item"])){
	$item = $_GET["item"];
	$anterior = $item - $itensPorPagina;
	$proximo = $item + $itensPorPagina;
}

// validando a pÃ¡gina anterior
if ($item - $itensPorPagina < 1)
$anterior = 1;

// descobrindo a quantidade total de registros
$sql = "SELECT COUNT(*)
				FROM Atribuicoes a,Disciplinas d, Turmas t,Cursos c,Turnos tu, Professores pr, Pessoas p
				WHERE a.disciplina = d.codigo 
                and a.turma = t.codigo
				and t.curso = c.codigo
				and t.turno = tu.codigo
				and pr.atribuicao = a.codigo
				and pr.professor = p.codigo
				and t.ano=$ano
				and (t.semestre=$semestre OR t.semestre=0) $restricao
				ORDER BY a.bimestre, $ordem";
//print $sql;
$resultado = mysql_query($sql);
$linha = mysql_fetch_row($resultado);
$ultimo = $linha[0];

// validando o prÃ³ximo item
if ($proximo > $ultimo){
	$proximo = $item;
	$ultimo = $item;
}

// validando o Ãºltimo item
if ($ultimo % $itensPorPagina > 0)
$ultimo=$ultimo-($ultimo % $itensPorPagina)+1;

$SITENAV = $SITE."?turma=".crip($turma)."&disciplina=".crip($disciplina)."&professor=".crip($professor);

require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
	<tr><th align="left" width="300"><a href="#Ordenar" class="ordenacao" id="p">Professor</a></th><th align="left"><a href="#Ordenar" class="ordenacao" id="d">Disciplina</a></th><th><a href="#Ordenar" class="ordenacao" id="t">Turma</a></th><th width="40">A&ccedil;&atilde;o</th></tr>
	<?php
	// efetuando a consulta para listagem
	$sql = "SELECT pr.codigo, p.nome, d.nome,t.numero, c.nome, d.numero,
				a.bimestre, a.grupo, a.subturma, a.eventod
				FROM Atribuicoes a,Disciplinas d, Turmas t,Cursos c,Turnos tu, Professores pr, Pessoas p
				WHERE a.disciplina = d.codigo 
                and a.turma = t.codigo
				and t.curso = c.codigo
				and t.turno = tu.codigo
				and pr.atribuicao = a.codigo
				and pr.professor = p.codigo
				and t.ano=$ano
				and (t.semestre=$semestre OR t.semestre=0) $restricao
				ORDER BY a.bimestre, $ordem limit ". ($item - 1) . ",$itensPorPagina";
	//echo $sql;
	$resultado = mysql_query($sql);
	$i = $item;
	if ($resultado){
		while ($linha = mysql_fetch_array($resultado)) {
			$i%2==0 ? $cdif="class='cdif'" : $cdif="class='cdif2'";
			($linha[6]>0) ? $bimestre="$linha[6]ºBIM:":$bimestre="";
			($linha[7]!="0") ? $grupo=" Grupo $linha[7]":$grupo="";
			$codigo = crip($linha[0]);
			if (!$linha[8]) $linha[8] = $linha[9];
			echo "<tr $cdif><td align='left'>$linha[1]</td><td>$linha[2] [$linha[5]]</td><td align=left>$linha[3] $grupo [$linha[8]]</td><td align='left'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
			$i++;
		}
	}
	mysql_close($conexao);
	?>

	<?php require(PATH.VIEW.'/navegacao.php'); ?>
	
<script>
function atualizar(getLink){
    var turma = $('#campoTurma').val();
	var URLS = '<?php print $SITE; ?>?turma=' + turma;
	if (!getLink)
		$('#index').load(URLS + '&item=<?php print $item; ?>');
	else
		return URLS;
}

$(document).ready(function(){
	$(".ordenacao").click(function(){ 
		$('#index').load(atualizar(1) +'&ordem='+ $(this).attr('id'));
	});

	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Confirma excluir o professor dessa atribuição?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load(atualizar(1) + '&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
	
   	$('#campoTurma').change(function(){
    	atualizar();
	});
});    
</script>