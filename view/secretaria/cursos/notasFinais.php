<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita a visualização das notas finais dos alunos de um determinado curso após o fechamento das notas.
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

<h2><?php print $TITLE; ?></h2>

<script>
	function valida() {
  	curso = $('#campoCurso').val();
  	turma = $('#campoTurma').val();
		$('#index').load('<?php print $SITE; ?>?curso=' + curso + '&turma=' + turma);
  }
    	
	$('#campoCurso, #campoTurma').change(function(){
		valida();
  });

</script>

<?php
$curso="";
$turma="";
$restricao="";

if (isset($_GET["curso"])) {
  $curso = dcrip($_GET["curso"]);
  $restricao .= " AND c.codigo = $curso";
}

if (isset($_GET["turma"]) && $_GET["turma"]!="") {
	$turma = dcrip($_GET["turma"]);
	$restricao = " AND t.codigo = $turma";
}


?>
<table align="center" id="form" width="100%">
<tr><td align="right" style="width: 100px">Curso: </td><td>
<select name="campoCurso" id="campoCurso" value="<?php echo $curso; ?>" style="width: 350px">
<option></option>
<?php
if (in_array($COORD, $_SESSION["loginTipo"]))
	$restricaoCoord = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador=".$_SESSION['loginCodigo'].")";
	$resultado = mysql_query("select distinct c.codigo, c.nome, m.nome, m.codigo
				from Cursos c, Turmas t, Modalidades m
				where t.curso=c.codigo
				and m.codigo = c.modalidade
                		and (t.semestre=$semestre OR t.semestre=0)
				and t.ano=$ano $restricaoCoord order by c.nome");
				$selected=""; // controla a alteração no campo select
while ($linha = mysql_fetch_array($resultado)){
	if ($linha[0]==$curso)
		$selected="selected";
	if ($linha[3] < 1000 || $linha[3] >= 2000) $linha[1] = "$linha[1] [$linha[2]]";
		echo "<option $selected value='".crip($linha[0])."'>[$linha[0]] $linha[1]</option>";
		$selected="";
}
?>
</select>
</td></tr>
<tr><td align="right">Turma: </td>
<td><select name="campoTurma" id="campoTurma" style="width: 350px">
<option></option>
<?php
$resultado = mysql_query("select t.codigo, t.numero, c.nome, tu.nome, t.semestre, t.ano, c.fechamento
			from Turmas t, Cursos c, Turnos tu 
                	where t.curso=c.codigo 
			and t.ano=$ano 
			and t.turno=tu.codigo
			and c.codigo = $curso
			and (t.semestre=$semestre OR t.semestre=0) $restricaoCoord");
$selected = "";
if (mysql_num_rows($resultado) > 0) {
	while ($linha = mysql_fetch_array($resultado)) {
		if ($linha[6] == 'b') $S=1;
		if ($linha[0] == $turma)
			$selected = "selected";
		print "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
		$selected = "";
	}
} else {
	print "<option value=''>Não há turmas cadastrados neste semestre/ano letivo</option>";
}
?>
</select>
</td></tr>
</table>
<br />
<?php
if (!empty($curso) && !empty($turma)){
?>
	<table id="form" border="0" align="center" width="100%">
	<tr><th align="center" width="220">Aluno</th><th align="center" width="200">Disciplina</th><th align="center" width="50">Turma</th><th width="140" align="center">Sincronizado</th><th width="140" align="center">Retorno</th><th width="20" align="center">&nbsp;</th><th width="20" align="center">FLAG</th></tr>
	<?php
	// efetuando a consulta para listagem
	$sql = "SELECT n.codigo, p.nome, n.sincronizado, a.bimestre, n.atribuicao, d.nome, t.numero, n.retorno, n.flag
                    FROM NotasFinais n, Atribuicoes a, Matriculas m, Turmas t, Disciplinas d, Pessoas p
                    WHERE n.atribuicao = a.codigo
                    AND n.matricula = m.codigo
                    AND a.turma = t.codigo
                    AND a.disciplina = d.codigo
                    AND p.codigo = m.aluno
                    $restricao
                    ORDER BY t.numero, d.nome, p.nome";
	//echo $sql;
	$resultado = mysql_query($sql);
	$i = 1;
	if ($resultado){
		while ($linha = mysql_fetch_array($resultado)) {
		  $i%2==0 ? $cdif="class='cdif'" : $cdif="";
	  	if ($linha[3]!=0)
	  		$bimestre="[".$linha[3]."&ordm; Bim]";
	    print "<tr $cdif>";
	    print "<td>".mostraTexto($linha[1])."</td>";
	    print "<td align='left'>$bimestre ".mostraTexto($linha[5])."</td>\n";
	    print "<td align=left>$linha[6]</td>";
	    print "<td align=left>$linha[2]</td>";
	    print "<td align=left><div id=\"S".$linha[0]."\">$linha[7]</div></td>";
	    if ($linha[8] != 5) {
                print "<td align=left><a href='#' title='Sincronizar' class='sync' id='" . $linha[0] . "'>\n";
                if ($linha[7] == '')
        	    	print "<img src=\"".ICONS."/sync.png\" class='botao'>\n";
                if ($linha[7] == '1')
                	print "<img src=\"".ICONS."/true.png\" class='botao'>\n";
                if ($linha[7] == '0')
                    print "<img src=\"".ICONS."/exclamation.png\" class='botao'>\n";
        	print "</a></td>";
            } else {
                print "<td align=left><img src=\"".ICONS."/true.png\" class='botao'></td>"; 
           }
                    
            print "<td align=center>$linha[8]</td>";
	    $i++;
	  }
        }
	mysql_close($conexao);
	?>
	</table>
<?php
}
?>
<script>
$(document).ready(function(){
	$(".sync").click(function(){
		var codigo = $(this).attr('id');
		var div1 = 'S' + codigo;
		$('#'+div1).load('db2/db2DigitaNotas.php?codigo=' + codigo);
		return false;
	});
});  
</script>