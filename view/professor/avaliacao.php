<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$atribuicao = dcrip($_GET["atribuicao"]);
$result = mysql_query("SELECT calculo, formula FROM Atribuicoes WHERE codigo=$atribuicao");
$calculo_avaliacao = @mysql_result($result, 0, "calculo");
$formula = @mysql_result($result, 0, "formula");
if (!$calculo_avaliacao) {
	mysql_query("UPDATE Atribuicoes SET calculo = 'peso' WHERE codigo=$atribuicao");
	$calculo_avaliacao = 'peso';
}

if ($_POST["opcao"] == 'InsertFormula') {
	$formula = $_POST["campoFormula"];
	$atribuicao = $_POST["atribuicao"];
	mysql_query("UPDATE Atribuicoes SET formula = '$formula' WHERE codigo=$atribuicao");
	mensagem('OK', 'TRUE_INSERT');
}
print "<h2>$TITLE</h2>\n";
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<?php

if ($_POST["opcao"] == 'InsertOrUpdate') {
	$data = dataMysql($_POST["campoData"]);
    $codigo = $_POST["campoCodigo"];
    $nome = $_POST["campoNome"];
    $peso = $_POST ["campoPeso"];
    $atribuicao = $_POST["campoAtribuicao"];
    $sigla = $_POST["campoSigla"];
    $tipo = $_POST["campoTipo"];

    if (empty($codigo)){
        $resultado = mysql_query("insert into Avaliacoes values(0, '$data', '$nome', '$sigla', '$peso', '$atribuicao', $tipo)");
		if ($resultado==1)        
			mensagem('OK', 'TRUE_INSERT');
        else
			mensagem('NOK', 'FALSE_INSERT');
    }
    else{
        $resultado = mysql_query("update Avaliacoes set data='$data', nome='$nome', peso='$peso', tipo='$tipo', atribuicao='$atribuicao' where codigo=$codigo"); 
		if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_UPDATE');
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from Avaliacoes where codigo=$codigo");
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('NOK', 'FALSE_DELETE');
    $atribuicao = dcrip($_GET["atribuicao"]);
    $_GET['opcao'] = '';
}

if ($_GET['opcao'] == 'calculo') {
    $calculo = $_GET["campoCalculo"];
    $resultado = mysql_query("update Atribuicoes set calculo='$calculo' WHERE codigo = '$atribuicao'");
    $_GET['opcao'] = '';
}

if ($_GET['opcao'] == 'insert') {

	if ($calculo_avaliacao == 'peso') $PONTO = 1;
	if ($calculo_avaliacao == 'soma') $PONTO = 10;

    // inicializando as variÃ¡veis do formulÃ¡rio
    $codigo="";
    //$data=date("d/m/Y", time());// data atual
    $nome="";
    $peso="";
    $tipo="";
		$peso=null;
    $atribuicao = dcrip($_GET["atribuicao"]);
    $pontos = $_GET["pontos"]; // pontos jÃ¡ atribuidos
    $maxPontos=$PONTO-$pontos;

	print "<script>\n";
	print " valida(); \n";
	print " $(document).ready(function(){ \n";
	if ($calculo_avaliacao == 'peso' || $calculo_avaliacao == 'soma' ) {
		if ($calculo_avaliacao == 'peso') print "    $(\"#3\").mask(\"9.99\");      \n";
		if ($calculo_avaliacao == 'soma' && $maxPontos < 10) print "    $(\"#3\").mask(\"9.99\"); \n";
		if ($calculo_avaliacao == 'soma' && $maxPontos >= 10) print "    $(\"#3\").mask(\"99.99\"); \n";
		print "    $(\"#3\").change(function(){  \n";
		print "        if ($(this).val() > $maxPontos) \n";
		print "             $(this).val(".number_format($maxPontos,2)."); \n";
		print "    });    \n";
		$P = "&& $('#3').val()!=\"\"";
		$P1 = ', #3';
	}
	print "    $('#data1, #campoTipo, #2 $P1, #4').keyup(function(){\n";
	print "			valida(); \n";
	print "    }); \n";
	print " }); \n";
	print " function valida() { \n";
	print "   if ($('#data1').val()!=\"\" && $('#campoTipo').val()!=null && $('#2').val()!=\"\" && $('#4').val()!=\"\" $P) \n";
	print "       $('#salvar').enable(); \n";
	print "   else \n";
	print "       $('#salvar').attr('disabled', 'disabled'); \n";
	print " } \n";
	print "</script>\n";

    if (!empty ($_GET["codigo"])){ // se o parÃ¢metro nÃ£o estiver vazio
        $codigo=dcrip($_GET["codigo"]);
        // consulta no banco
        $resultado = mysql_query("select a.codigo, DATE_FORMAT(data, '%d/%m/%Y'), a.nome, a.peso, a.tipo, at.prazo, a.sigla
            from Avaliacoes a , Atribuicoes at
            where a.atribuicao=at.codigo
            and a.codigo=$codigo");
        $linha = mysql_fetch_row($resultado);

        // armazena os valores nas variÃ¡veis
        $codigo = $linha[0];
        $data = $linha[1];
        $nome = $linha[2];
        $peso = $linha[3];
        $sigla = $linha[6];
        $tipo = $linha[4];
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
	<center>
    <h2>Cadastro de Avalia&ccedil;&atilde;o</h2>
    <table>
        <tr><td align="right">Data: </td><td><input type="text" readonly size="10" id="data1" name="campoData" value="<?php echo $data; ?>" /></td></tr>
        <tr><td align="right">Nome: </td><td><input style="width: 350px" type="text" id="2" maxlength="145" name="campoNome" value="<?php echo $nome; ?>"/></td></tr>
        <tr><td align="right">Sigla: </td><td><input type="text" id="4" size="2" maxlength="2" name="campoSigla" value="<?php echo $sigla; ?>"/></td></tr>
        <?php
        if ($calculo_avaliacao == 'peso' || $calculo_avaliacao == 'soma') {
        	if ($maxPontos <= 0) $enabled = 'disabled';
        ?>
        <tr><td align="right">Valor</td><td><input type="text" id="3" style="width: 50px" <?php echo $enabled; ?> name="campoPeso" value="<?php echo number_format($peso,2); ?>"/> (m&aacute;ximo <?=(number_format ($maxPontos,2))?>)</td></tr>
        <?php } ?>
        <tr><td align="right">Tipo: </td><td>
                <select name="campoTipo" id="campoTipo" value="<?php echo $tipo; ?>">
                    <?php
                        $sql = "SELECT t.codigo, t.nome, t.tipo
												FROM TiposAvaliacoes t, Modalidades m, Cursos c, Atribuicoes a, Turmas tu 
												WHERE t.modalidade = m.codigo 
												AND m.codigo = c.modalidade 
												AND a.turma = tu.codigo 
												AND tu.curso = c.codigo 
												AND a.codigo = $atribuicao
												AND ( (t.final = 0 OR t.final IS NULL AND a.bimestre < 4) OR (a.bimestre = 4))
												AND t.tipo NOT IN (SELECT t1.tipo FROM Avaliacoes a1, TiposAvaliacoes t1 
															WHERE a1.tipo= t1.codigo 
															AND a1.tipo = t.codigo 
															AND a1.atribuicao = $atribuicao 
															AND t1.tipo = 'recuperacao' 
															AND t1.final = 0)
												ORDER BY t.nome";
						$resultado = mysql_query($sql);
                        $selected=""; // controla a alteraÃ§Ã£o no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$tipo)
                               $selected="selected";
                               if ($calculo_avaliacao == 'peso' && $pontos < $PONTO && $linha[2]!='recuperacao')
	                                print "<option $selected value='$linha[0]'>$linha[1]</option>";
                               if ($calculo_avaliacao == 'peso' && $pontos >= $PONTO && $linha[2]=='recuperacao')
                                	print "<option $selected value='$linha[0]'>$linha[1]</option>";
                              
                               if ($calculo_avaliacao == 'soma' && $pontos < $PONTO && $linha[2]!='recuperacao')
	                                print "<option $selected value='$linha[0]'>$linha[1]</option>";
                               if ($calculo_avaliacao == 'soma' && $pontos >= $PONTO && $linha[2]=='recuperacao')
                                	print "<option $selected value='$linha[0]'>$linha[1]</option>";
                              
                               if ($calculo_avaliacao == 'media' || $calculo_avaliacao == 'formula') 
                                   	print "<option $selected value='$linha[0]'>$linha[1]</option>";
                            $selected="";
                        }
                    ?>
                </select>
                <?php //print $sql; ?>
        </td></tr>
        <tr><td></td><td>
            <input type="hidden" name="campoAtribuicao" value="<?php echo $atribuicao; ?>" />
            <input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
			<input type="hidden" name="opcao" value="InsertOrUpdate" />
        <input type="submit" disabled value="Salvar" id="salvar" />
    </td></tr>
    </table>
</form>

<?php
    echo "<br><div style='margin: auto'><a href=\"javascript:$('#professor').load('".$SITE."?atribuicao=".crip($atribuicao)."'); void(0);\" class='voltar' title='Voltar' ><img class='botao' src='" . ICONS . "/left.png'/></a></div>";

}

if ($_GET['opcao'] == '') {
	$result = mysql_query("SELECT calculo, formula FROM Atribuicoes WHERE codigo=$atribuicao");
	$calculo_avaliacao = @mysql_result($result, 0, "calculo");
	$formula = @mysql_result($result, 0, "formula");

    $sql = "select date_format(a.data, '%d/%m/%Y') data_formatada, a.nome,
		a.peso, a.codigo, d.nome, tu.nome, a.data, a.tipo, at.status,
		DATEDIFF(prazo, NOW()) as prazo, at.calculo, ti.tipo,
		(SELECT calculo FROM TiposAvaliacoes WHERE codigo = a.tipo AND tipo='recuperacao') as recuperacao,
		(SELECT SUM(peso) FROM Avaliacoes WHERE atribuicao = at.codigo),
		(SELECT final FROM TiposAvaliacoes WHERE codigo = a.tipo AND tipo='recuperacao' AND final=1) as final,
		at.bimestre, a.sigla, t.numero
        from Avaliacoes a, Atribuicoes at, Turnos tu, Turmas t, Disciplinas d, TiposAvaliacoes ti 
        where atribuicao=$atribuicao
        and at.turma=t.codigo 
        and ti.codigo = a.tipo
        and at.disciplina=d.codigo 
        and t.turno=tu.codigo 
        and a.atribuicao=at.codigo 
        order by a.data,ti.tipo,a.nome";
    //print $sql;
    $resultado = mysql_query($sql);
    $i = 1;
    $pontos=0;
    $linhasTabela="";
    $disciplina="";
    $FP=0;
	
	if ($resultado) {
	    while ($linha = mysql_fetch_array($resultado)) {
	        $status = $linha[8];
	        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
	 		
	 				$final .= $linha[14];
	 		 
	      if ($linha[11]=='avaliacao') {
		    		$peso = $linha[2];
		    		if ($calculo_avaliacao == 'media') {
								$peso='M&eacute;dia';
								$titleAval = 'M&eacute;dia';
		    		} else if ($calculo_avaliacao == 'soma') {
								$titleAval = 'Soma';
								$pontos=$linha[13];
		    		} else if ($calculo_avaliacao == 'formula') {
								$peso='F&oacute;rmula';
								$titleAval = 'F&oacute;rmula';
						} else {
							$pontos=$linha[13];
		    		}
	        }
	        if ($linha[12]!='') {
	            $recuperacao=$linha[12];
	            $peso = $linha[12];
	            $titleAval = strtoupper($peso);
	            $titleAval = $$titleAval;
	        }
	
	        $linhasTabela .= "<tr $cdif><td>$i</td><td><a class='nav' title='Clique aqui para lan&ccedil;ar as notas.' href=\"javascript:$('#professor').load('".VIEW."/professor/nota.php?atribuicao=".crip($atribuicao)."&avaliacao=".crip($linha[3])."'); void(0);\">$linha[0]</a></td><td>$linha[1]</td><td>$linha[16]</td><td><a title='$titleAval' href='#'>$peso</a></td>";
		
		if ( $linha[8] || ($_SESSION['dataExpirou'] && ($linha[9] < 0 || $linha[9]=='')) ) {
	            $linhasTabela.="<td align='center'><a href='#' title='Di&aacute;rio Fechado'>Fechado</a></td>";
	            $FP=1;
	        }       
		else {
		    $codigo = crip($linha[3]);
		    $linhasTabela.="<td align='center' width=\"20\"><a href='#' title='Excluir' class='item-excluir' id=\"$codigo\"><img class='botao' src='".ICONS."/remove.png' /></a><a href=\"javascript:$('#professor').load('$SITE?opcao=insert&codigo=$codigo&pontos=".(round($pontos - $linha[2],2))."&atribuicao=".crip($atribuicao)."'); void(0);\" class='nav' title='Alterar'><img class='botao' src='".ICONS."/config.png' /></a></td>";
		}
	        $linhasTabela.="</tr>";
	        $disciplina = "$linha[4]";
	        $turma = "$linha[17]";
	        $i++;
	    }
	}

   	if ($i==1) {
    	 $sql = "select d.nome, at.status, DATEDIFF(at.prazo, NOW()) as prazo, t.numero
    	 		 FROM Atribuicoes at, Disciplinas d, Turmas t
    			 WHERE at.disciplina=d.codigo
    			 AND t.codigo = at.turma
			    and at.codigo=$atribuicao";
    	//echo $sql;
    	$resultado = mysql_query($sql);
    	while ($linha = mysql_fetch_array($resultado)) {
    		$disciplina = $linha[0];
    		$turma = $linha[3];
    		$aulasDadas = 0;
    		$i=1;
    		if ( $linha[1] || ($_SESSION['dataExpirou'] && ($linha[2] < 0 || $linha[2]==''))) {
	            $linhasTabela.="<td align='center' colspan='6'><a href='#' title='Di&aacute;rio Fechado'>Fechado</a></td>";
		    $FP=1;
		}
    	}
    }
    mysql_close($conexao);
    
    ?>

    <div id="etiqueta" align="center">
    <form  id="form" method="post"> 
    <?php echo "<b>Turma:</b> $turma"; ?><br />
    <?php echo "<b>Disciplina:</b> $disciplina"; ?><br />
    <?php echo "<b>Avalia&ccedil;&otilde;es:</b> ".($i-1); ?><br />
    <?php echo "<b>Recupera&ccedil;&atilde;o:</b> $recuperacao"; ?><br />
    <?php echo "<b>M&eacute;todo de C&aacute;lculo:</b> ";
    if ( ($i-1)!=0 && $calculo_avaliacao != 'formula' ) $disabled = 'disabled';
    print "<select name=\"campoCalculo\" $disabled id=\"campoCalculo\" value=\"$calculo_avaliacao\" onChange=\"$('#professor').load('$SITE?opcao=calculo&atribuicao=".crip($atribuicao). "&campoCalculo=' + this.value);\">\n";
    if ($calculo_avaliacao == 'peso') $selected = 'selected'; else $selected = '';
    echo "<option $selected value='peso'>peso</option>";
    if ($calculo_avaliacao == 'media') $selected = 'selected'; else $selected = '';
    echo "<option $selected value='media'>m&eacute;dia</option>";
    if ($calculo_avaliacao == 'soma') $selected = 'selected'; else $selected = '';
    echo "<option $selected value='soma'>soma</option>";
    if ($calculo_avaliacao == 'formula') $selected = 'selected'; else $selected = '';
    echo "<option $selected value='formula'>f&oacute;rmula</option>";
		?>
    </select>
		<?php if ($calculo_avaliacao == 'peso' || $calculo_avaliacao == 'soma') echo "<br><b>Pontos atribu&iacute;dos:</b> ".round($pontos,2); ?>
    </form>
		<?php if ($calculo_avaliacao == 'formula') {
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
  	  print "<font size=\"2\">M&eacute;dia: </font><input type=\"text\" $disabled size=\"15\" maxlength=\"50\" name=\"campoFormula\" value=\"$formula\"/>\n";
  	  print "<font size=\"1\">".'Exemplo: <b>($A1+$A2)/2</b>  --> onde A1 &eacute; a sigla da avalia&ccedil;&atilde;o precedida de $'."</font>\n";
			print "<input type=\"hidden\" name=\"opcao\" value=\"InsertFormula\" />\n";
			print "<input type=\"hidden\" name=\"atribuicao\" value=\"$atribuicao\" />\n";
      if (!$disabled) print "<input type=\"submit\" value=\"Salvar\" />\n";
			print "</form>\n";
			print "</div>\n";
		}
		?>  
</div>
<hr><br>

<table id="listagem" border="0" align="center">
    <tr><th width="40">#</th><th width="100">Data</th><th>Avalia&ccedil;&atilde;o</th><th>Sigla</th><th width="150">Valor</th><th width="40">A&ccedil;&atilde;o</th></tr>
    <?php echo $linhasTabela; ?>
</table>

<?php if ($status==0 && ($pontos<1 || $recuperacao=="" || $final=="")){ 
    ?>
	<br />
	<center>
    <input type="hidden" id="campoAtribuicao" name="campoAtribuicao" value="<?php echo crip($atribuicao); ?>" />
       <?php if ($FP == 0) print "<a class=\"nav\" href=\"javascript:$('#professor').load('$SITE?opcao=insert&atribuicao=".crip($atribuicao)."&pontos=".round($pontos,2)."'); void(0);\" title=\"Cadastrar Nova\"><img class='botao' src='".ICONS."/add.png' /></a>"; ?>
	</center>
<?php }
    else if ($status==0){
    	echo "<br /><p style='text-align: center; font-weight: bold; color: red'>Não é possível cadastrar mais avaliações, pois a soma dos pontos distribuídos é igual a 1<br />Exclua ou altere o peso de alguma avaliação para adicionar uma nova.</p>";
    }
}

        
// DATA DE INICIO E FIM DA ATRIBUICAO PARA RESTRINGIR O CALENDARIO
$sql = "SELECT 	DATE_FORMAT( a.dataInicio,  '%d/%m/%Y' ) AS dataInicio, 
				DATE_FORMAT( a.dataFim,  '%d/%m/%Y' ) AS dataFim
			FROM Atribuicoes a 
			WHERE a.codigo = $atribuicao";
$result = mysql_query($sql);
$data = mysql_fetch_object($result);
?>
<script>
  $(document).ready(function(){    
	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#professor').load('<?php print $SITE; ?>?opcao=delete&codigo=' + codigo + '&atribuicao=<?php print crip($atribuicao); ?>');
		});
	});
	
	$("#data1").datepicker({
	    dateFormat: 'dd/mm/yy',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
	    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
	    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
	    monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
	    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
	    nextText: 'Próximo',
	    prevText: 'Anterior',
	    minDate:'<?php print $data->dataInicio; ?>',
	    maxDate:'<?php print $data->dataFim; ?>'	    
	});  
	
   });
</script>