<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de notas de avaliações pelo professor.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
	$avaliacao = $_POST["campoAvaliacao"];
	$atribuicao = $_POST["campoAtribuicao"];

	$erro=0;
	foreach ($_POST['matricula'] as $matricula => $nota) {
		$resultado = mysql_query("update Notas set nota='$nota' where avaliacao=$avaliacao and matricula=$matricula");
		if ($resultado == 0) {
			$erro++;
			break;
		}
		else if (mysql_matched_rows($resultado)==0){
			$resultado = mysql_query("insert into Notas values (0,'$matricula', '$avaliacao', '$nota')");
			if ($resultado == 0) {
				$erro++;
				break;
			}
		}
	}
	if (!$erro)
		mensagem('OK', 'TRUE_INSERT');
	else
		mensagem('NOK', 'FALSE_INSERT');	
	$_GET["avaliacao"] = crip($avaliacao);
	$_GET["atribuicao"] = crip($atribuicao);
}
?>

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>
<?php

$avaliacao = dcrip($_GET["avaliacao"]);
$atribuicao = dcrip($_GET["atribuicao"]);
$disabled="";

// efetuando a consulta para listagem
$sql = "SELECT c.nome, t.codigo, tu.nome, t.ano, t.semestre, av.codigo, 
		date_format(av.data, '%d/%m/%Y') data, av.peso, av.nome, ti.tipo,
		DATEDIFF(a.prazo, NOW()) as prazo, a.status,
		d.modulo, d.numero, a.bimestre, ti.final, t.numero, ti.notaMaxima, a.calculo
 		FROM Atribuicoes a, Disciplinas d, Turmas t, Cursos c, Turnos tu, Avaliacoes av, TiposAvaliacoes ti
 		WHERE a.disciplina=d.codigo 
 		AND a.turma=t.codigo 
 		AND av.atribuicao=a.codigo 
 		AND t.curso=c.codigo 
 		AND ti.codigo = av.tipo
 		AND t.turno=tu.codigo 
 		AND av.codigo=$avaliacao";
//print $sql; 		
$resultado = mysql_query($sql);
$linha = mysql_fetch_row($resultado);
$tipo = $linha[9];
$status = $linha[11];
$turma = $linha[1];
$numeroDisciplina = $linha[13];
$numeroBimestre = $linha[14];
$final = $linha[15];
$notaMaxima = ($linha[17]) ? $linha[17] : 10;
$calculo = $linha[18];

if ($calculo == 'soma' && $linha[9] != 'recuperacao') $notaMaxima = $linha[7];

if ( $status || ($_SESSION['dataExpirou'] && ($linha[10] < 0 || $linha[10]=='')))
	$disabled="disabled='disabled'";

?>
<div id="etiqueta" align="center">
	<?php echo "Curso: " . $linha[0]; ?><br />
	<?php echo "Turma: " . $linha[16]; ?><br />
	<?php echo "Semestre: " . $linha[4] . "/" . $linha[3]; ?><br />
	<?php echo "Notas para: " . $linha[8] . " de " . $linha[6]; if ($calculo == 'peso') echo " (peso: $linha[7])" ?><br />
	<?php echo "Nota m&aacute;xima permitida: $notaMaxima<br />\n"; ?>
</div>
<br><hr>

<?php
print "<script>\n";
print "    $('#form_padrao').html5form({ \n";
print "        method : 'POST', \n";
print "        action : '$SITE', \n";
print "        responseDiv : '#professor', \n";
print "        colorOn: '#000', \n";
print "        colorOff: '#999', \n";
print "        messages: 'br' \n";
print "    }) \n";
print "</script>\n";

print "<div id=\"html5form\" class=\"main\">\n";
print "<form id=\"form_padrao\" onsubmit=\"return validaForm()\" >\n";
?>

<table id="listagem" border="0" align="center">
	<tr><th align="center" width="80">Prontuário</th><th align="center">Aluno</th><th width="50" align='center'>Nota</th>
<?php
if ($numeroBimestre <> 0) {
	$sql = "SELECT a2.codigo, a2.bimestre FROM Atribuicoes a2 WHERE a2.turma IN (
				SELECT t1.codigo FROM Turmas t1 
				WHERE t1.numero IN ( SELECT t.numero FROM Atribuicoes a, Turmas t WHERE a.turma = t.codigo AND a.codigo = $atribuicao))
			AND a2.disciplina = (SELECT a3.disciplina FROM Atribuicoes a3 WHERE a3.codigo = $atribuicao)
			AND a2.subturma = (SELECT a4.subturma FROM Atribuicoes a4 WHERE a4.codigo = $atribuicao)";
	$result = mysql_query($sql);
	//print $sql;
	while ($lb = mysql_fetch_array($result)){
		if ($numeroBimestre == $lb[1] && !$final) $color = 'blue'; else $color="";
		print "<th width=\"35\" align='center'><font color=\"$color\">&nbsp;$lb[1]&ordm; BIM</font></th>\n";
		$AT_BIM[$lb[1]] = $lb[0];
	}
	if ($final) $color = 'blue'; else $color='';
	print "<th width=\"50\"><font color=\"$color\">M&eacute;dia</font></th>\n";
} else {
	print "<th width=\"50\">M&eacute;dia</th>\n";
}
	print "<th width=\"50\"></th>\n";


$i = 1;
$sql = "SELECT al.codigo, m.codigo, n.nota, al.nome, s.listar, s.habilitar, s.nome, a.turma, a.bimestre, al.prontuario
		FROM Atribuicoes a 
		left join Avaliacoes av on av.atribuicao=a.codigo 
		left join Matriculas m on m.atribuicao=a.codigo 
		left join Notas n on n.avaliacao=av.codigo and n.matricula=m.codigo 
		left join Pessoas al on m.aluno=al.codigo 
        left join Situacoes s on s.codigo = m.situacao 
		WHERE a.codigo=$atribuicao AND av.codigo=$avaliacao ORDER BY al.nome";
		//echo $sql;
$res = mysql_query($sql);

while ($l = mysql_fetch_array($res)){
	$i%2==0 ? $cdif="class='cdif'" : $cdif="";
	echo "<tr $cdif><td align='center'>$l[9]</td>
	<td><li><a href='#' rel='".INC."/file.inc.php?type=pic&id=".crip($l[0])."' class='screenshot nav' title='".mostraTexto($l[3])."'><img style='width: 20px; height: 20px' alt='Embedded Image' src='".INC."/file.inc.php?type=pic&id=".crip($l[0])."' /></a>
	<a class='nav' href=\"javascript:$('#professor').load('".VIEW."/aluno/boletim.php?aluno=".crip($l[0])."&turma=".crip($l[7])."&bimestre=".crip($l[8])."'); void(0);\">".mostraTexto($l[3])."</a></td>
	<td align='center'>";

	if ($l[4]) {
		if (1) {
			echo "<input $disabled tabindex='$i' style='width: 30px' type='text' value='$l[2]' size='4' maxlength='4' name='matricula[" . $l[1] . "]' onchange=\"validaItem(this)\" /></td>";
			if ($numeroBimestre > 0) { 
				foreach($AT_BIM as $nBim => $at) {
					$resultado = mysql_query("SELECT m.codigo FROM Atribuicoes a, Matriculas m WHERE m.atribuicao=a.codigo AND a.codigo=$at AND m.aluno=$l[0] AND bimestre = $nBim");
					$linha = mysql_fetch_row($resultado);
					$dados = resultado($linha[0], $at, 0);
					if ($numeroBimestre == $nBim && $final == 0) {
						$color = 'blue';
						$situacao = $dados['situacao'];
					} else $color = '';
					print "<td align='center'><font color=\"$color\">".$dados['media']."</font></td>";
				}
				$dados1 = resultadoBimestral($l[0], $turma, $numeroDisciplina, $final);
				if ($final) $color = 'blue'; else $color='';
				print "<td align='center'><font color=\"$color\">".$dados1['media']."</font></td>";
				if ($l[8] == 4 && $nBim == 4 && ( ($tipo=='recuperacao' && $final) || (!$situacao ) ) )
					print "<td align='center'>".$dados1['situacao']."</td>";

				//if ($l[8] == 4 && $nBim == 4) {
				print "<td align='center'>$situacao</td>";
				//}
			} else {
				$dados = resultado($l[1], $atribuicao, $final);
				print "<td align='center'>".$dados['media']."</td>";
				print "<td align='center'>".$dados['situacao']."</td>";
			}
		} else {
			print "<td align='center' colspan='6'>".$l[6]."</td>\n";
		}
	}
	print "</tr>\n";
	$i++;
}
mysql_close($conexao);
?>
</table>
	<table align="center">
			<tr><td></td><td>
				<input type="hidden" value="<?php echo $avaliacao; ?>" name="campoAvaliacao" />
				<input type="hidden" value="<?php echo $atribuicao; ?>" name="campoAtribuicao" />
				<input type="hidden" name="opcao" value="InsertOrUpdate" />
				<?php if (!$disabled) print "<input type=\"submit\" value=\"Salvar\" name=\"salvar\" />\n"; ?>
			</td></tr>
		</table>
	</form>
</div>
	<?php

    echo "<br><div style='margin: auto'><a href=\"javascript:$('#professor').load('".VIEW."/professor/avaliacao.php?atribuicao=".crip($atribuicao)."'); void(0);\" class='voltar' title='Voltar' ><img class='botao' src='" . ICONS . "/left.png'/></a></div>";
    $_SESSION['VOLTAR'] = "professor";
    $_SESSION['LINK'] = VIEW."/professor/nota.php?atribuicao=".crip($atribuicao)."&avaliacao=".crip($avaliacao);

    ?>
<script>
	retorno=true;
	function validaForm(){
		return retorno;
	}
	function validaItem(item){
		item.value = item.value.replace(",",".");
    	if (item.value < 0 || item.value > <?php print $notaMaxima; ?>){
			item.value='';
		} else {
			retorno = true;
		}
	}
</script>