<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar as atribuições dos professores/disciplinas de todos os cursos.
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

if ($_GET["opcao"] == 'delete') {
	$codigo = dcrip($_GET["codigo"]);
	$resultado = mysql_query("DELETE FROM Atribuicoes WHERE codigo=$codigo");
	if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
	else
		mensagem('INFO', 'DELETE');
	
	$_GET["codigo"] = null;
}
?>

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
// inicializando as variÃ¡veis do formulÃ¡rio
$codigo="";
$professor="";
$turma="";
$restricao="";
$restricao2="";
$bimestre="";
$grupo="";

$ordem="d.numero,t.numero";

if (isset($_GET["turma"])){
    $turma=dcrip($_GET["turma"]);
    if (!empty($turma))
    	$restricao = " AND t.codigo=$turma";
}

if (isset($_GET["professor"])) {
  $professor = dcrip($_GET["professor"]);
  if (!empty($professor))
 		$restricao2 = " AND p.professor = $professor";
}

if (isset($_GET["ordem"])){
    $ordem=$_GET["ordem"];
    if ($ordem=="d")
    $ordem="d.nome";
    else if ($ordem=="t")
    $ordem="t.numero";
    else if ($ordem=="n")
    $ordem="d.numero";    
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

			$resultado = mysql_query("select distinct t.codigo, t.numero, c.nome, m.nome, m.codigo, c.codigo
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
				echo "<option $selected value='".crip($linha[0])."'>[$linha[1]] $linha[2] ($linha[5])</option>";
				$selected="";
			}

			?>
		</select>
	</td></tr>
  <tr><td align="right">Professor: </td><td><select name="campoProfessor" id="campoProfessor" style="width: 350px">
        <?php
          if ($turma) $profSQL = "AND pr.atribuicao IN (SELECT a1.codigo FROM Atribuicoes a1 WHERE a1.turma = $turma)";
          $sql = "SELECT DISTINCT p.codigo, p.nome 
          				FROM Pessoas p, PessoasTipos pt, Professores pr
          				WHERE p.codigo = pt.pessoa
          				AND pt.tipo = $PROFESSOR
          				AND pr.professor = p.codigo
          				$profSQL
           				ORDER BY p.nome";
          $resultado = mysql_query($sql);
          $selected = "";
          if (mysql_num_rows($resultado) > 0) {
              echo "<option value=''>Todos</option>";
              $prof_ant = 0;
              while ($linha = mysql_fetch_array($resultado)) {
              	$selected = "";
                if ($linha[0] == $professor) {
                	$selected = "selected";
                	$prof_ant = 1;
                }
                echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
                $selected = "";
              }
              if (!$prof_ant)
              	$restricao2 = '';
          }
          else {
              echo "<option value=''>Não há professores cadastrados.</option>";
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
$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*)
        FROM Atribuicoes a,Disciplinas d, Turmas t,Cursos c,Turnos tu, Professores p
				WHERE a.disciplina = d.codigo 
        and a.turma = t.codigo
				and t.curso = c.codigo
				and t.turno = tu.codigo
        and p.atribuicao = a.codigo
				and t.ano=$ano
				and (t.semestre=$semestre OR t.semestre=0) $restricao $restricao2
				GROUP BY a.codigo
				ORDER BY a.bimestre, $ordem) contar";
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

$SITENAV = $SITE."?turma=".crip($turma);

    require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
	<tr><th align="left" width="60"><a href="#Ordenar" class="ordenacao" id="n">N&uacute;mero</a></th><th align="left"><a href="#Ordenar" class="ordenacao" id="d">Disciplina</a></th><th>Professor</th><th><a href="#Ordenar" class="ordenacao" id="t">Turma</a></th><th width="40">A&ccedil;&atilde;o</th></tr>
	<?php
	// efetuando a consulta para listagem
	$sql = "SELECT a.codigo, d.nome,d.codigo,t.numero, c.nome, tu.nome, d.numero,
				a.bimestre, a.grupo, a.subturma, a.eventod
				FROM Atribuicoes a,Disciplinas d, Turmas t,Cursos c,Turnos tu, Professores p
				WHERE a.disciplina = d.codigo 
        and a.turma = t.codigo
				and t.curso = c.codigo
				and t.turno = tu.codigo
        and p.atribuicao = a.codigo
				and t.ano=$ano
				and (t.semestre=$semestre OR t.semestre=0) $restricao $restricao2
				GROUP BY a.codigo
				ORDER BY a.bimestre, $ordem limit ". ($item - 1) . ",$itensPorPagina";
	//echo $sql;
	$resultado = mysql_query($sql);
	$i = $item;
	if ($resultado){
            require CONTROLLER . "/professor.class.php";
            $professor = new Professores();
            
		while ($linha = mysql_fetch_array($resultado)) {
			$i%2==0 ? $cdif="class='cdif'" : $cdif="class='cdif2'";
			($linha[7]>0) ? $bimestre="$linha[7]ºBIM:":$bimestre="";
			($linha[8]!="0") ? $grupo=" Grupo $linha[8]":$grupo="";
			$codigo = crip($linha[0]);
			if (!$linha[9]) $linha[9] = $linha[10];

                        echo "<tr $cdif><td align='left'>$linha[6]</td><td><a target=\"_blank\" href='".VIEW."/secretaria/relatorios/inc/diario.php?atribuicao=".crip($linha[0])."'>$bimestre ".mostraTexto($linha[1])."</a></td><td align='left'>".$professor->getProfessor($linha[0],'<br>', 1, 1)."</td><td align=left>$linha[3] $grupo [$linha[9]]</td><td align='left'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
			$i++;
		}
	}
	mysql_close($conexao);
	?>

	<?php     require(PATH.VIEW.'/navegacao.php'); ?>

	
<script>
function atualizar(getLink){
  var turma = $('#campoTurma').val();
  var professor = $('#campoProfessor').val();
	var URLS = '<?php print $SITE; ?>?turma=' + turma + '&professor=' + professor;
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
		jConfirm('Aten&ccedil;&atilde;o, ser&atilde;o exclu&iacute;das as avalia&ccedil;&otilde;es, notas e ensalamentos gerados para essa atribui&ccedil;&atilde;o. Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load(atualizar(1) + '&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
	
   	$('#campoTurma, #campoProfessor').change(function(){
    	atualizar();
	});
});    
</script>