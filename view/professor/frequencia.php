<?php
//Esse arquivo é fixo para o professor.
//Permite o registro de frequências no WebDiário.
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
	$aula = $_POST["campoAula"];
	$atribuicao = $_POST["campoAtribuicao"];
	$qdeAula = $_POST["campoQdeAula"];
	$erro=0;
	foreach ($_POST['matricula'] as $matricula => $qtd) {
		$SIT = '';
		for ($i=0; $i < $qdeAula; $i++) {
			if (array_key_exists($i, $qtd))
				$SIT .= 'F';
			else
				$SIT .= '*';
		}

		$sql = "update Frequencias set quantidade='$SIT' where aula=$aula and matricula=$matricula";
		$resultado = mysql_query($sql);
		if ($resultado == 0)
			$erro++;
		else if (mysql_matched_rows($resultado)==0){
			$resultado = mysql_query("insert into Frequencias values (0,'$matricula', $aula, '$SIT')") ;
			if ($resultado == 0)
				$erro++;
		}
	}
		
	if (!$erro)
		mensagem('OK', 'TRUE_INSERT');
	else
		mensagem('NOK', 'FASLE_INSERT');
	$_GET["aula"] = crip($aula);
	$_GET["atribuicao"] = crip($atribuicao);
}

?>

<h2><?php print $TITLE; ?></h2>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>
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
print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n";
$aula = dcrip($_GET["aula"]);
$atribuicao = dcrip($_GET["atribuicao"]);
$disabled="";

$sql = "SELECT c.nome, t.numero, tu.nome, t.ano, t.semestre, 
		date_format(au.data, '%d/%m/%Y') data, au.quantidade,
		DATEDIFF(a.prazo, NOW()) as prazo, a.status, au.data
 		FROM Atribuicoes a, Disciplinas d, Turmas t, Cursos c, Turnos tu, Aulas au
 		WHERE a.disciplina=d.codigo 
 		AND a.turma=t.codigo
 		AND au.atribuicao=a.codigo
 		AND t.curso=c.codigo 
 		AND t.turno=tu.codigo 
 		AND au.codigo=$aula";
//echo $sql;
$resultado = mysql_query($sql);
$linha = mysql_fetch_row($resultado);
$status = $linha[8];

if ( $status || ($_SESSION['dataExpirou'] && ($linha[7] < 0 || $linha[7]=='')))
	$disabled="disabled='disabled'";	

?>
<div id="etiqueta" align="center">
	<?php echo "<span class='rotulo_professor'>Curso:</span> " . $linha[0]; ?><br />
	<?php echo "<span class='rotulo_professor'>Turma:</span> " . $linha[1]; ?><br />
	<?php echo "<span class='rotulo_professor'>Semestre:</span> " . $linha[4] . "/" . $linha[3]; ?><br />
	<?php echo "<span class='rotulo_professor'>Chamada para:</span> " . $linha[5] . " - " . $linha[6] . " aulas" ?><br />
</div>
<hr><br>

<table id="listagem" border="0" align="center" style="border: 0px solid black">
	<tr class="listagem_tr"><th align="center" style="width: 100px">Prontuário</th><th align="center">Aluno</th><th width="120" align='center'>Faltas</th><th width="50" align='center'>Total</th><th width="85" align='center'>Frequ&ecirc;ncia na Disciplina</th></tr>

		<?php
		$i = 1;
		$sql = "SELECT f.quantidade, al.codigo, al.nome, m.codigo, a.turma, 
						a.bimestre, s.listar, s.habilitar, s.nome, al.prontuario, au.quantidade
			FROM Atribuicoes a 
			left join Aulas au on au.atribuicao=a.codigo 
			left join Matriculas m on m.atribuicao=a.codigo 
			left join Frequencias f on f.aula=au.codigo and f.matricula=m.codigo 
			left join Pessoas al on m.aluno=al.codigo 
	        left join Situacoes s on s.codigo = m.situacao 
			WHERE a.codigo=$atribuicao AND au.codigo=$aula ORDER BY al.nome";
		//echo $sql;
		$resultado = mysql_query($sql);
		while ($l = mysql_fetch_array($resultado)) {
			$qdeAula = $l[10];
			$i%2==0 ? $cdif="class='cdif'" : $cdif="class='cdif2'";
			echo "<tr $cdif ><td align='center'>$l[9]</td>
			<td><li><a href='#' rel='".INC."/file.inc.php?type=pic&id=".crip($l[1])."' class='screenshot' title='".mostraTexto($l[2])."'><img class='foto_lista' alt='Embedded Image' src='".INC."/file.inc.php?type=pic&id=".crip($l[1])."' /></a>
			<a class='nav' href=\"javascript:$('#professor').load('".VIEW."/aluno/boletim.php?aluno=".crip($l[1])."&turma=".crip($l[4])."&bimestre=".crip($l[5])."'); void(0);\">".mostraTexto($l[2])."</a></td>";
	
			$frequencia = resultado($l[3], $atribuicao);

			if ($l[6]) {
				if (1) {
					print "<td align='left'>\n";
					if (!$A = getFrequenciaAbono($l[1], $atribuicao, $linha[9])) {
						echo "<input $disabled type='hidden' checked name='matricula[" . $l[3] . "][".$l[3]."]' />";
						for($n=0; $n < $l[10]; $n++) {
							if (substr($l[0], $n, 1) == 'F') $F = 'checked';
							else $F = '';
							echo "<input id='".$l[3]."' class='".$l[3]."' $disabled tabindex='$i' type='checkbox' $F name='matricula[" . $l[3] . "][".$n."]' />";
						}
					} else {
						print $A['nome'];
					}
					print "</td>\n";
					echo "<td align='center'>".$frequencia['faltas']."</td>";
					echo "<td align='center'>".arredondar($frequencia['frequencia'])."%</td>";
                                    } else {
					print "<td align='center' colspan='3'>".$l[8]."</td>\n";
				}
			}
			echo "<td align='center'>".$valor."</td>";
			$i++;
		}
		mysql_close($conexao);
		?>
	</table>
        <table align="center" style="width: 100%; margin-top: 10px;">
		<tr><td></td><td align="center">
			<input type="hidden" value="<?php echo $qdeAula; ?>" name="campoQdeAula" />
			<input type="hidden" value="<?php echo $aula; ?>" name="campoAula" />
			<input type="hidden" value="<?php echo $atribuicao; ?>" name="campoAtribuicao" />
			<input type="hidden" name="opcao" value="InsertOrUpdate" />
			<input id="professores_botao" <?=$disabled?> type="submit" value="Salvar" />
		</td></tr>
	</table>
</form>
<?php
    echo "<br><div style='margin: auto'><a href=\"javascript:$('#professor').load('".VIEW."/professor/aula.php?atribuicao=".crip($atribuicao)."'); void(0);\" class='voltar' title='Voltar' ><img class='botao' src='" . ICONS . "/left.png'/></a></div>";

    ?>

<script>
$(document).ready(function(){
	$("input:checkbox").click(function(){
		var codigo = $(this).attr('id');
		if ($(this).prop('checked')==true){
 			$('.' + codigo).prop('checked', true);
 		}
	});
});
</script>