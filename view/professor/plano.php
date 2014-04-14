<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_POST['pagina']) $_GET['pagina'] = $_POST['pagina'];
if (!$_GET['pagina']) $_GET['pagina'] = "entregarPlano";

if ($_GET['pagina'] == "planoEnsino") {
	if ($_POST["opcao"] == 'InsertOrUpdate') {
	    $table = "PlanosEnsino";
	    $result = mysql_query("SHOW COLUMNS FROM $table");
	    if (mysql_num_rows($result) > 0) {
	        while ($row = mysql_fetch_assoc($result)) {
	            $campo = str_replace('_', ' ', $row["Field"]);
	            $campo = ucwords($campo);
	            $campo = str_replace(' ', '', $campo);
	            $$row["Field"] = $_POST["campo$campo"];
	        }
	    }
	
	    $result = mysql_query("SHOW COLUMNS FROM $table WHERE field <> 'valido' AND field <> 'finalizado'");
	    if (mysql_num_rows($result) > 0) {
	        $SQLins = "INSERT INTO $table VALUES (";
	        $SQLup = "UPDATE $table SET ";
	        while ($row = mysql_fetch_assoc($result)) {
	            if ( substr($row["Type"], 0, 3) == "var" || substr($row["Type"], 0, 3) == "tex") $ap = "'"; else $ap = '';
	            if (!$$row["Field"] && (substr($row["Type"], 0, 3) == "int" || substr($row["Type"], 0, 5) == "float") ) $value = 'NULL'; else $value = $$row["Field"];
	            $SQLins .= $ap . $value . $ap;
	            $SQLins .= ",";
	
	            $SQLup .= $row["Field"] . "=" . $ap . $value . $ap;
	            $SQLup .= ",";
	        }
	        $SQLins .= "valido = '', finalizado = '' ";
	        $SQLins = substr($SQLins, 0, (strlen($SQLins) - 1));
	        $SQLup = substr($SQLup, 0, (strlen($SQLup) - 1));
	        $SQLins .= ")";
	        $SQLup .= " WHERE codigo=$codigo";
	    }

	    if (empty($codigo)){
	        $resultado = mysql_query("$SQLins"); 
	        if ($resultado==1)
				mensagem('OK', 'TRUE_INSERT');
	        else
				mensagem('NOK', 'FALSE_INSERT_NULL_FIELD');
	        $_GET["codigo"] = crip(mysql_insert_id());
	    }
	    else{
	        $resultado = mysql_query("$SQLup");
	        if ($resultado==1)
				mensagem('OK', 'TRUE_UPDATE');
	        else
				mensagem('NOK', 'FALSE_UPDATE_NULL_FIELD');
			$_GET["codigo"] = crip($_POST["campoCodigo"]);
	    

	    }
	    $_GET['atribuicao'] = crip($_POST['campoAtribuicao']);
	}
}

if ($_POST['campoAtribuicao']) $_GET['atribuicao'] = crip($_POST['campoAtribuicao']);
$atribuicao = $_GET["atribuicao"];
?>
<h2><?php print $TITLE; ?></h2>
<link rel="stylesheet" type="text/css" href="<?php print VIEW; ?>/css/aba.css" media="screen" />

<ul class="tabs">
	<li><a href="javascript:$('#professor').load('<?php print $SITE."?atribuicao=$atribuicao"; ?>&pagina=entregarPlano'); void(0);">Entregar Plano</a></li>	
	<li><a href="javascript:$('#professor').load('<?php print $SITE."?atribuicao=$atribuicao"; ?>&pagina=planoEnsino'); void(0);">Plano de Ensino</a></li>
	<li><a href="javascript:$('#professor').load('<?php print $SITE."?atribuicao=$atribuicao"; ?>&pagina=planoAula'); void(0);">Plano de Aula</a></li>
	<li><a href="javascript:$('#professor').load('<?php print $SITE."?atribuicao=$atribuicao"; ?>&pagina=planoCopiar'); void(0);">Copiar Plano</a></li>
	<li><a target="_blank" href="<?php print VIEW; ?>/secretaria/relatorios/inc/planoEnsino.php?atribuicao=<?php print $atribuicao; ?>"><img src="<?php print ICONS; ?>/icon-printer.gif" width="30"></a></li>
</ul>
<div class="tab_container" id="form">
<?php

if ($_GET['pagina'] == "entregarPlano") {
	print "<p>Caso tenha terminado de digitar o Plano de Ensino e as Aulas, clique em ENTREGAR para submeter ao seu coordenador.</p>";

	if ($_GET["entregar"]) {
		$resultado = mysql_query("UPDATE PlanosEnsino SET finalizado=NOW(), valido='', solicitacao='' WHERE atribuicao = ".dcrip($atribuicao));
		if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
		else
			mensagem('NOK', 'FALSE_UPDATE');
		print "<br>";
	}

  $resultado = mysql_query("SELECT p.finalizado, p.solicitacao,
					    		date_format(p.valido, '%d/%m/%Y %H:%i'),
									(SELECT nome FROM Pessoas WHERE codigo = p.solicitante)
										 FROM PlanosEnsino p WHERE p.atribuicao = ".dcrip($atribuicao));
	$BLOQ = 0;
	$VALIDO = 0;
	while ($l = mysql_fetch_array($resultado)) {
			if ($l[0] && $l[0] != '0000-00-00 00:00:00')
				$BLOQ = 1;
			if ($l[2] && $l[2] != '00/00/0000 00:00')
				$VALIDO=1;
			
			$solicitacao = $l[1];
			$solicitante = $l[3];
		}
	  
	  if ($solicitacao) {
	  	$MSG = "$solicitante, solicitou corre&ccedil;&atilde;o em seu Plano: <br>$solicitacao";
			mensagem('C_NOK', $MSG);
			$BLOQ = 0;
		}
	  if ($VALIDO) {
	  	$MSG = "$solicitante validou seu plano!";
			mensagem('C_OK', $MSG);
		}

	  $resultado = mysql_query("SELECT codigo,(SELECT COUNT(*) FROM PlanosAula a WHERE a.atribuicao = ".dcrip($atribuicao).") as total FROM PlanosEnsino p WHERE p.atribuicao = ".dcrip($atribuicao));
		while ($l = mysql_fetch_array($resultado)) {
			$pd = ($l[0])? 'sim' : 'n&atilde;o';
			print "<br>Plano de Ensino digitado: $pd<br>";
			if ($l[1] <= 0) $disabled = 'disabled';
			print "Quantidade de aulas cadastradas no Plano de Aula: $l[1]<br>";
		}
		
	 	if (!$BLOQ) print "<br><input type=\"submit\" $disabled value=\"Entregar\" id=\"Entregar\"></th>";
	 	else print "<br><b>Esse plano foi finalizado e entregue</b><br>";

	?>
	<script>
	$(document).ready(function(){
	    $("#Entregar").click(function(){
				jConfirm('Deseja enviar seu Plano para seu coordenador? \n O Plano ser&aacute; bloqueado, podendo ser desbloqueado somente pelo coordenador.', '<?php print $TITLE; ?>', function(r) {
		    if ( r ) {
					$('#professor').load('<?php print $SITE; ?>?pagina=entregarPlano&atribuicao=<?php print $atribuicao; ?>&entregar=1');
				}
			});
		});
	});    
	</script>
	<?php
}

if ($_GET['pagina'] == "planoCopiar") {
	print "<p>Permite copiar planos de ensino cadastrados para a mesma disciplina no semestre atual ou outros semestres.</p>";

	if ($codigoCopy = $_GET["codigoCopy"]) {
		$atribuicao = dcrip($_GET["atribuicao"]);
		$resultado = mysql_query("DELETE FROM PlanosAula WHERE atribuicao=$atribuicao");
		$resultado = mysql_query("DELETE FROM PlanosEnsino WHERE atribuicao=$atribuicao");
	
		$resultado = mysql_query("INSERT INTO PlanosEnsino 
						SELECT 
						    NULL,$atribuicao,p.numeroAulaSemanal,p.totalHoras,p.totalAulas,p.numeroProfessores,
						    p.ementa,p.objetivo,p.conteudoProgramatico,p.metodologia,p.recursoDidatico,p.avaliacao,
						    p.recuperacaoParalela,p.recuperacaoFinal,p.bibliografiaBasica,p.bibliografiaComplementar
						    ,NULL,NULL,NULL,NULL
						FROM PlanosEnsino p
						WHERE p.atribuicao=$codigoCopy");
		if ($resultado==1)
			mensagem('OK', 'TRUE_COPY_PLANO_ENSINO');
		else
			mensagem('NOK', 'FALSE_COPY_PLANO_ENSINO');
		print "<br>";
	
		$resultado = mysql_query("INSERT INTO PlanosAula 
						SELECT 
						NULL,$atribuicao,p.semana,p.conteudo
						FROM PlanosAula p
						WHERE p.atribuicao=$codigoCopy");
		if ($resultado==1)
			mensagem('OK', 'TRUE_COPY_PLANO_AULA');
		else
			mensagem('NOK', 'FALSE_COPY_PLANO_AULA');
		print "<br>";
	}
	
	print "<br /><br />";
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
	?>
	<table border="0" width="100%">
	<tr><td align="left" style="width: 150px">Disciplina equivalentes: </td><td>
	<select name="campoDisciplina" id="campoDisciplina" value="<?php echo $disciplina; ?>" >
	<option></option>
	<?php
        $resultado = mysql_query("SELECT a.codigo, d.nome, t.numero, t.ano, t.semestre, a.eventod, a.subturma 
        						FROM PlanosEnsino pe, PlanosAula pa, Disciplinas d, Atribuicoes a, Turmas t
        						WHERE pe.atribuicao = pa.atribuicao
        						AND pe.atribuicao = a.codigo
        						AND a.disciplina = d.codigo
        						AND a.turma = t.codigo
        						AND d.numero IN (SELECT d1.numero 
        											FROM Disciplinas d1, Atribuicoes a1 
        											WHERE a1.disciplina = d1.codigo 
        											AND d1.numero = d.numero AND a1.codigo = ".dcrip($atribuicao).")
        						AND a.codigo <> ".dcrip($atribuicao)."
        						GROUP BY a.codigo 
        						ORDER BY d.nome");
        while ($linha = mysql_fetch_array($resultado)){
        	if (!$linha[6]) $linha[6] = $linha[5];
            echo "<option value='$linha[0]'>$linha[1] [$linha[4]/$linha[3]] [$linha[2]] [$linha[6]]</option>";
        }
	?>
	</select>
	</td></tr>
	</table>
	<br />
	<table width="100%"><tr><td>
		<?php print "<a class=\"nav\" id='item-copiar' href=\"#\" void(0);\" title=\"Copiar Plano de Ensino\"><img src='".ICONS."/copiar.gif' width=\"30\" /></a>"; ?>
	</td></tr></table> 
	</form>
	</div>
	</div>
<?php

}

if ($_GET['pagina'] == "planoEnsino") {
	// inicializando as variÃ¡veis do formulÃ¡rio
    $table = "PlanosEnsino";
    $result = mysql_query("SHOW COLUMNS FROM $table");
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            $$row["Field"] = ''; 
        }
    }

    if (!empty ($_GET["atribuicao"])){ // se o parÃ¢metro nÃ£o estiver vazio
        // consulta no banco
        $resultado = mysql_query("SELECT * FROM PlanosEnsino WHERE atribuicao=".dcrip($_GET["atribuicao"]));
        $linha = mysql_fetch_row($resultado);

	    $table = "PlanosEnsino";
			$i=0;
	    $result = mysql_query("SHOW COLUMNS FROM $table");
	    if (mysql_num_rows($result) > 0) {
    	    while ($row = mysql_fetch_assoc($result)) {
    	        $$row["Field"] = $linha[$i];
				$i++;
        	}
	    }
    }
	
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
	
	$disabled = '';
	if ($finalizado && $finalizado != '0000-00-00 00:00:00')
		$disabled = 'disabled';
	?>
	<input type="hidden" name="opcao" value="InsertOrUpdate" />
	<input type="hidden" name="pagina" value="planoEnsino" />
	<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
	<input type="hidden" name="campoAtribuicao" value="<?php echo dcrip($_GET['atribuicao']); ?>" />
	<table id="form" border="0" width="100%">
	  <tr>
	    <td align="left">N&uacute;m. Aulas Semanais: </td>
	    <td><input type="text" size="2" <?php print $disabled; ?> id="campoNumeroAulaSemanal" onchange="validaItem(this)" name="campoNumeroAulaSemanal" maxlength="2" value="<?php echo $numeroAulaSemanal; ?>"/>
		<td>&nbsp;</td>
	    <td align="left">Total de Horas: </td>
	    <td><input type="text" size="5" <?php print $disabled; ?> id="campoTotalHoras" onchange="validaItem(this)" name="campoTotalHoras" maxlength="5" value="<?php echo $totalHoras; ?>"/>
		<td>&nbsp;</td>
	    <td align="left">Total de Aulas: </td>
	    <td><input type="text" size="5" <?php print $disabled; ?> id="campoTotalAulas" onchange="validaItem(this)" name="campoTotalAulas" maxlength="5" value="<?php echo $totalAulas; ?>"/>
		<td>&nbsp;</td>
	    <td align="left">N&uacute;m. Professores: </td>
	    <td><input type="text" size="1" <?php print $disabled; ?> id="campoNumeroProfessores" name="campoNumeroProfessores" maxlength="1" value="<?php echo $numeroProfessores; ?>"/>
	  </tr>
	  <tr>
	    <td align="left">Ementa: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoEmenta'><?php print $ementa; ?></textarea>
	  </tr>
	  <tr>
	    <td align="left">Objetivo: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoObjetivo'><?php print $objetivo; ?></textarea>
	  </tr>
	  <tr>
	    <td align="left">Conte&uacute;do Program&aacute;tico: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoConteudoProgramatico'><?php print $conteudoProgramatico; ?></textarea>
	  </tr>
	  <tr>
	    <td align="left">Metodologia: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoMetodologia'><?php print $metodologia; ?></textarea>
	  </tr>
	  <tr>
	    <td align="left">Recurso Did&aacute;tico: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoRecursoDidatico'><?php print $recursoDidatico; ?></textarea>
	  </tr>
	  <tr>
	    <td align="left">Avalia&ccedil;&atilde;o: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoAvaliacao'><?php print $avaliacao; ?></textarea>
	  </tr>  
	  <tr>
	    <td align="left">Recupera&ccedil;&atilde;o Paralela: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoRecuperacaoParalela'><?php print $recuperacaoParalela; ?></textarea>
	  </tr>  
	  <tr>
	    <td align="left">Recupera&ccedil;&atilde;o Final: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoRecuperacaoFinal'><?php print $recuperacaoFinal; ?></textarea>
	  </tr>
	  <tr>
	    <td align="left">Bibliografia B&aacute;sica: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoBibliografiaBasica'><?php print $bibliografiaBasica; ?></textarea>
	  </tr>
	  <tr>
	    <td align="left">Bibliografia Complementar: </td>
	    <td colspan="10"><textarea rows="10" <?php print $disabled; ?> cols="70" maxlength='2000' id='3' name='campoBibliografiaComplementar'><?php print $bibliografiaComplementar; ?></textarea>
	  </tr>
	</table>
	<table width="100%"><tr><td><input type="submit" <?php print $disabled; ?> value="Salvar" /></td></tr></table>
	</form>
	</div>
	</div>
	<?php
}

if ($_GET['pagina'] == "planoAula") {
	if ($_POST["opcao"] == 'InsertOrUpdate') {
		$codigo = $_POST["campoCodigo"];
		$atrib = $_POST["campoAtribuicao"];
		$semana = $_POST["campoSemana"];
		$conteudo = $_POST["campoConteudo"];
		$_GET['atribuicao'] = crip($atrib);

	    if (empty($codigo)){
	       	$resultado = mysql_query("INSERT INTO PlanosAula VALUES (0, '$atrib','$semana','$conteudo')"); 
	       	if ($resultado==1)
				mensagem('OK', 'TRUE_INSERT');
	       	else
				mensagem('NOK', 'FALSE_INSERT');
	    }
	    else {
	        $resultado = mysql_query("UPDATE PlanosAula SET semana='$semana', conteudo='$conteudo' WHERE codigo=$codigo"); 
	        if ($resultado==1)
				mensagem('OK', 'TRUE_UPDATE');
	        else
				mensagem('NOK', 'FALSE_UPDATE');
	    }
	}
	if ($_GET["opcao"] == 'delete') {
	    $codigo = dcrip($_GET["codigo"]);
		$resultado = mysql_query("delete from PlanosAula where codigo=$codigo");
	    if ($resultado==1)
			mensagem('OK', 'TRUE_DELETE');
	    else
			mensagem('NOK', 'FALSE_DELETE');
	    $_GET["codigo"] = '';
	    $codigo = '';
	}

	// VERIFICANDO SE O PLANO FOI FINALIZADO
  $disabled = '';
  $resultado = mysql_query("SELECT pe.finalizado
	     												 FROM PlanosEnsino pe WHERE
	    												 pe.atribuicao=".dcrip($_GET["atribuicao"]));
  $linha = mysql_fetch_row($resultado);
	if ($linha[0] && $linha[0] != '0000-00-00 00:00:00')
		$disabled = 'disabled';
		
	if (!empty ($_GET["codigo"])){ // se o par?metro n?o estiver vazio
	       
		// consulta no banco
	    $resultado = mysql_query("SELECT pa.codigo, pa.semana, pa.conteudo
	     												 FROM PlanosAula pa WHERE
	    												 pa.codigo=".dcrip($_GET["codigo"]));
	    $linha = mysql_fetch_row($resultado);
	        
	    // armazena os valores nas vari?veis
	    $codigo = $linha[0];
	    $semana = $linha[1];
	    $conteudo = $linha[2];
	}  

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
	?>
	<input type="hidden" name="pagina" value="planoAula" />
	<input type="hidden" name="opcao" value="InsertOrUpdate" />
	<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
	<input type="hidden" name="campoAtribuicao" value="<?php echo dcrip($_GET['atribuicao']); ?>" />
	<table id="form" border="0" width="100%">
	  <tr>
	    <td align="left">Semana: </td>
	    <td><input type="text" size="2" <?php print $disabled; ?> id="campoSemana" name="campoSemana" maxlength="2" value="<?php echo $semana; ?>"/>
	  </tr>
	  <tr>
	    <td align="left">Conte&uacute;do: </td>
	    <td colspan="10"><textarea rows="5" <?php print $disabled; ?> cols="80" maxlength='2000' id='3' name='campoConteudo'><?php print $conteudo; ?></textarea>
	  </tr>
	</table>
			<table width="100%"><tr><td><input type="submit" <?php print $disabled; ?> value="Salvar" /></td>
				<td><a href="javascript:$('#professor').load('<?php print "$SITE?pagina=planoAula&atribuicao=".$_GET['atribuicao']; ?>'); void(0);">Novo/Limpar</a></td>
			</tr></table>	
	<br>
	<table id="listagem" border="0" align="center">
	<tr><th align="center" width="80">Semana</th><th align="left">Conte&uacute;do</th><th width="40">A&ccedil;&atilde;o</th></tr>
	<?php
	// efetuando a consulta para listagem
	$resultado = mysql_query("SELECT * FROM PlanosAula WHERE atribuicao = ".dcrip($_GET['atribuicao'])." ORDER BY semana");
	$i = $item;
	while ($linha = mysql_fetch_array($resultado)) {
		$i%2==0 ? $cdif="class='cdif'" : $cdif="";
		$codigo = crip($linha[0]);
		echo "<tr $cdif><td align='left'>$linha[2]</td><td>".nl2br(mostraTexto($linha[3]))."</td>\n";
		if (!$$disabled) print "<td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td>\n";
		else print "<td>&nbsp;</td>\n";
		print "</tr>";
	  $i++;
	}
	?>
	</table>
	</form>
	</div>
	</div>	
<?php
}

$atribuicao = $_GET['atribuicao'];
?>
<script>
function validaItem(item) {
	item.value = item.value.replace(",",".");
}

$(document).ready(function(){
	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#professor').load('<?php print "$SITE?atribuicao=$atribuicao"; ?>&pagina=planoAula&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
	$(".item-alterar").click(function(){
		var codigo = $(this).attr('id');
		$('#professor').load('<?php print "$SITE?atribuicao=$atribuicao"; ?>&pagina=planoAula&codigo=' + codigo);
	});
	
	$("#item-copiar").click(function(){
		var codigo = $('#campoDisciplina').val();
		jConfirm('Aten&ccedil;&atilde;o, seu plano de ensino ser&aacute; exclu&iacute;do e substitu&iacute;do pelo escolhido. Deseja continuar?', '<?php print $TITLE; ?>', function(r) {
			if ( r )
				$('#professor').load('<?php print "$SITE?atribuicao=$atribuicao"; ?>&pagina=planoCopiar&codigoCopy=' + codigo);
		});
	});
});    
</script>