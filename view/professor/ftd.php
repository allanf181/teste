<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita a tela de visualização da Folha de Trabalho dos docentes. Os campos relativos aos horários das aulas dadas pelos docentes são editáveis.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
?>
<h2><font color="white"><?php print $TITLE; ?></font></h2>
<?php

if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
		
	if ($_GET["dte"] && $_GET["dts"]) {
			$telefone = $_GET["telefone"];			
			$celular = $_GET["celular"];			
			$email = $_GET["email"];			
			$area = $_GET["area"];			
			$regime = $_GET["regime"];			
			$obs = $_GET["obs"];
			
			$DTE = explode(',', $_GET["dte"]);
			$DTS = explode(',', $_GET["dts"]);
			
			// GUARDANDO MEMORIAS DE CALCULO PARA PDF
			$TP = $_GET["TP"];
			$TPT = $_GET["TPT"];
			$TD = $_GET["TD"];
			$TDT = $_GET["TDT"];
			$ITE = $_GET["ITE"];
			$ITS = $_GET["ITS"];
			$A = $_GET["A"];
			$AT = $_GET["AT"];
			$AtvDocente = $_GET["AtvDocente"];
			$Projetos = $_GET["Projetos"];
			$Intervalos = $_GET["Intervalos"];
			$Total = $_GET["Total"];
			
			$NOW=($tipo = $_GET["tipo"])? 'NOW()' : "''";

			$erro=0;
			$sql = "SELECT * FROM FTDDados WHERE ano = '$ano' AND semestre = '$semestre' AND professor = ".$_SESSION['loginCodigo'];
			$resultado = mysql_query($sql);
			$dados = mysql_fetch_object($resultado);
		
			if (!$dados) {
				$r = mysql_query("INSERT INTO FTDDados VALUES (NULL, '".$_SESSION['loginCodigo']."', '$ano', '$semestre', '$telefone', '$celular', '$email', '$area', '$regime', '$obs', '$TP','$TPT','$TD','$TDT','$ITE','$ITS','$A','$AT','$AtvDocente','$Projetos','$Intervalos','$Total', '', '', '', $NOW)");
			  if ($r!=1)
	    		$erro = 1;
				$COD = mysql_insert_id();
			} else {
				$r = mysql_query("UPDATE FTDDados SET telefone='$telefone', celular='$celular', email='$email', area='$area', regime='$regime', solicitacao='', valido='', observacao='$obs', TP='$TP', TPT='$TPT', TD='$TD', TDT='$TDT', ITE='$ITE', ITS='$ITS', A='$A', AT='$AT', AtvDocente='$AtvDocente', Projetos='$Projetos', Intervalos='$Intervalos', Total='$Total', finalizado=$NOW WHERE ano = '$ano' AND semestre = '$semestre' AND professor = ".$_SESSION['loginCodigo']);
				$COD = $dados->codigo;
			  if ($r!=1)
	    		$erro = 1;
	    }
	    
	    // APROVEITA E ATUALIZA OS DADOS DO PROFESSOR NA TABELA PESSOAS
			mysql_query("UPDATE Pessoas SET telefone='$telefone', celular='$celular', email='$email' WHERE codigo = ".$_SESSION['loginCodigo']);

			if ($COD) {
				$r = mysql_query("DELETE FROM FTDHorarios WHERE ftd = (SELECT codigo FROM FTDDados WHERE ano = '$ano' AND semestre = '$semestre' AND professor = ".$_SESSION['loginCodigo'].")");
			  if ($r!=1)
	    		$erro = 1;
	    	if (!$erro) {
					foreach($DTE as $reg) {
						list ($r, $horario) = split ('-', $reg);
						$resultado=mysql_query("INSERT INTO FTDHorarios VALUES (NULL, '$COD', '$r', '$horario')");
		      	if ($resultado!=1)
		      		$erro = 1;
					}
					foreach($DTS as $reg) {
						list ($r, $horario) = split ('-', $reg);
						$resultado=mysql_query("INSERT INTO FTDHorarios VALUES (NULL, '$COD', '$r', '$horario')");
		      	if ($resultado!=1)
		      		$erro = 1;
					}
				}
			}
					
			if (!$erro)
				mensagem('OK', 'TRUE_INSERT');
	  	else
				mensagem('NOK', 'FALSE_INSERT');		
	}
	
	
	// BUSCANDO NO BANCO, CASO O PROFESSOR TENHA JA FEITO O FTD
	$sql = "SELECT fh.registro, fh.horario, fd.observacao,
					fd.finalizado, fd.solicitacao, fd.telefone, fd.celular,
					fd.email, fd.area, fd.regime,
	    		date_format(fd.valido, '%d/%m/%Y %H:%i'),
					(SELECT nome FROM Pessoas WHERE codigo = fd.solicitante),
					fd.observacao
		    	FROM FTDDados fd, FTDHorarios fh
		    	WHERE fd.codigo = fh.ftd
		    	AND fd.ano = '$ano' 
		    	AND fd.semestre = '$semestre' 
		    	AND fd.professor = ".$_SESSION['loginCodigo'];
	//print $sql;
	$resultado = mysql_query($sql);
	$disabled = '';
	$VALIDO = 0;
	if (mysql_num_rows($resultado) != '') {
		print "<script>\n";
		while ($l = mysql_fetch_array($resultado)) {
			$telefone = $l[5];
			$celular = $l[6];
			$email = $l[7];
			$area = $l[8];
			$regime = $l[9];
			$obs = $l[12];
			
	  	print " $('#".$l[0]."').text('".$l[1]."'); \n";
		
			if ($l[3] && $l[3] != '0000-00-00 00:00:00')
				$disabled = 'disabled';
			if ($l[10] && $l[10] != '00/00/0000 00:00')
				$VALIDO=1;
			
			$solicitacao = $l[4];
			$solicitante = $l[11];
		}
	  print "</script>\n";
	  
	  if ($solicitacao) {
                $OPT[0] = $solicitante;
                $OPT[1] = $solicitacao;
                mensagem('INFO', 'INVALID_FTD', $OPT);
		$disabled = '';
          }
	  if ($VALIDO) {
		mensagem('OK', 'VALID_FTD', $solicitante);
            }		
	} else {
		mensagem('OK', 'NOT_SAVE_FTD');		
	
		// CASO O PROFESSOR NAO TENHO FEITO O FTD, O SISTEMA IMPORTA DE ENSALAMENTOS.
		$sql = "SELECT e.diaSemana, h.nome, date_format(h.inicio, '%H:%i') as ini, 
											date_format(h.fim, '%H:%i') as fim, p.telefone, p.celular, p.email
			            		FROM Ensalamentos e, Horarios h, Pessoas p, Atribuicoes a, Turmas t, Professores pr
			                WHERE h.codigo = e.horario
			                AND a.turma = t.codigo
			                AND pr.atribuicao = a.codigo
			                AND pr.professor = e.professor
			                AND p.codigo = e.professor
			                AND t.ano = '$ano' 
		    							AND (t.semestre = '$semestre' OR t.semestre = 0)
		    							AND e.professor = ". $_SESSION['loginCodigo'] ." 
			                GROUP BY h.inicio, h.fim, e.diaSemana
			                ORDER BY e.diaSemana, h.inicio ASC";
		
		//print $sql;
		$resultado = mysql_query($sql);
		if ($resultado) {
			print "<script>\n";
			$P=0;
			$PT=1;
			$CT=0;
			for($i=0; $i < mysql_num_rows($resultado); $i++) {
				$dia = mysql_result($resultado, $i, "e.diaSemana");
				$ini = mysql_result($resultado, $i, "ini");
				$fim = mysql_result($resultado, $i, "fim");
				$nome = mysql_result($resultado, $i, "h.nome");
				
				$email = mysql_result($resultado, $i, "p.email");
				$celular = mysql_result($resultado, $i, "p.celular");
				$telefone = mysql_result($resultado, $i, "p.telefone");
				
				preg_match('#\[(.*?)\]#', $nome, $match);
				if ($match[1] != $PT) {
					$P++;
					$L=0;
				}
				$PT = $match[1];
				
				$C = $dia - 1; //COLUNA
		
				if ($C != $CT) {
					$L=0; $P=1;
				}
				$CT = $C;
					
				if ($fim == @mysql_result($resultado, $i+1, "ini")) {
					//print "$ini - $fim <br>";
					if (!$INI) $INI = $ini;
				} else {
					$L++;
					if (!$INI) $INI = $ini;
					$R = $P.''.$L.''.$C.'1';
					//print "$R : $INI <br>";
					print " $('#".$R."').text('".$INI."'); \n";
			
					$R = $P.''.$L.''.$C.'2';
					//print "$R : $fim <br>";
					print " $('#".$R."').text('".$fim."'); \n";
					$INI = '';
				}
					
			}
		  print "</script>\n";
		}
	}
	print "<font size=\"1\" color=\"red\">* Clique sobre o quadro que deseja inserir a hora e tecle ENTER para confirmar.</font>\n";
	print "<br><br><center><font size=\"3\"><b>FTD - FOLHA DE TRABALHO DOCENTE <br> $semestre&ordm; semestre $ano </b></font>\n";
	print "<table width=\"80%\" border=\"0\" summary=\"FTD\" id=\"tabela_boletim\">\n";
	print "<thead>\n";
	print "<tr>\n";
	print "<th>Professor: </th><th><input type=\"text\" disabled style=\"width: 227pt\" id=\"campoNome\" name=\"campoNome\" maxlength=\"45\" value=\"".utf8_encode($_SESSION["loginNome"])."\"/></th>\n";
	print "<th>&Aacute;rea: </th><th><input type=\"text\" $disabled style=\"width: 227pt\" id=\"campoArea\" name=\"campoArea\" maxlength=\"45\" value=\"$area\"/></th>\n";
	print "</tr>\n";
	print "<tr>\n";	
	print "<th>Prontu&aacute;rio: </th><th><input type=\"text\" disabled style=\"width: 227pt\" id=\"campoProntuario\" name=\"campoProntuario\" maxlength=\"45\" value=\"".$_SESSION["loginProntuario"]."\"/></th>\n";
	print "<th>Email: </th><th><input type=\"text\" $disabled style=\"width: 227pt\" id=\"campoEmail\" name=\"campoEmail\" maxlength=\"100\" value=\"$email\"/></th>\n";
	print "</tr>\n";
	print "<tr>\n";
	print "<th>Telefone: </th><th><input type=\"text\" $disabled style=\"width: 227pt\" id=\"campoTelefone\" name=\"campoTelefone\" maxlength=\"45\" value=\"$telefone\"/></th>\n";
	print "<th>Celular: </th><th><input type=\"text\" $disabled style=\"width: 227pt\" id=\"campoCelular\" name=\"campoCelular\" maxlength=\"45\" value=\"$celular\"/></th>\n";
	print "</tr>\n";
	print "<tr>\n";
	print "<th colspan=\"2\">Regime de Trabalho: </th><th colspan=\"2\">\n";
	$regimes = Array('20H', '40H', 'RDE', 'Substituto', 'Temporario');
	foreach ($regimes as $r) {
		$checked='';
		if ($regime == $r) $checked = 'checked';
			print "<input type=\"radio\" $disabled id=\"campoRegime\" $checked name=\"campoRegime\" value=\"$r\"/>$r &nbsp;\n";
	}
	print "</th>\n";
	print "</tr>\n";

	print "</table>\n";
	print "<center><table width=\"80%\" border=\"0\" summary=\"FTD\" id=\"tabela_boletim\">\n";
	print "<thead>\n";
	print "<tr align=\"right\">\n";
	print "<th colspan=\"8\">\n";
	print "<img style=\"width: 30px\" src=\"".ICONS."/icon-printer.gif\" title=\"Imprimir em PDF\" />\n";
	print "<th colspan=\"3\" align=\"left\">\n";
	print "<a href=\"".VIEW."/secretaria/relatorios/inc/ftd.php?professor=".crip($_SESSION['loginCodigo'])."\" target=\"_blank\"><span style='font-weight: bold; color: white'>Resumida</span></a>\n";
	print "<br><a href=\"".VIEW."/secretaria/relatorios/inc/ftd.php?detalhada=1&professor=".crip($_SESSION['loginCodigo'])."\" target=\"_blank\"><span style='font-weight: bold; color: white'>Detalhada</span></a>\n";
	print "</th><th colspan=\"4\" align=\"center\">\n";
	print "<input type=\"submit\" $disabled style=\"width: 50px;\" value=\"Salvar\" id=\"salvar\">&nbsp;&nbsp;&nbsp;<input type=\"submit\" style=\"width: 50px\" $disabled value=\"Enviar\" id=\"enviar\"></th></tr>";
	
	$dias = diasDaSemana();
	$dias[0]='&ordm; PER&Iacute;ODO';
	$dias[8]='TOTAL';
	unset($dias[1]);
	
	$atividade[7] = 'Aula';
	$atividade[13] = 'Aula';
	$atividade[19] = 'Atendimento';
	$atividade[25] = 'Dedução - 270';
	$atividade[31] = 'Reuniões';
	$atividade[37] = 'Projeto Interno';
	$atividade[43] = 'Projeto Externo';
	
	ksort($dias);
	
	print "<tr>\n";
	foreach ($dias as $dCodigo => $dNome) {
		$col = (!$dCodigo)? 1 : 2;
		if (!$dCodigo) $dNome = '1'.$dNome;
	  print "<th colspan=\"$col\"><span style='font-weight: bold; color: white'>$dNome</span></th>\n";
	}
	print "</tr>\n";
	
	print "<tr align=\"center\">";
	foreach ($dias as $dCodigo => $dNome) {
	  if ($dCodigo == 0)
		print "<th><span style='font-weight: bold; color: white'>ATIVIDADES</span></th>";
	  elseif ($dCodigo == 8)
		print "<th colspan=\"2\"><span style='font-weight: bold; color: white'>PER&Iacute;ODO</span></th>";
	  else
		print "<th>E</th><th>S</th>";
	}
	print "</tr>\n";
	print "</thead>\n";
	
	for ($p=1; $p <= 2; $p++) {
		print "<tr align=\"center\">\n";
	  print "<th>Aula</th>";
	  $c=1;
	  $l=1;
	  for ($i=1; $i <= 48; $i++) {
			if ($c >= 7) {
				print "<tr align=\"center\">";
			  print "<th>".$atividade[$i]."</th>";
				$c=1;
		  }
			$IE = $p.$l.$c.'1';
			$IS = $p.$l.$c.'2';
			print "<td id=\"$IE\"></td><td id=\"$IS\"></td>";
		        
			if ($c==6) { //TOTAL PERÍODO
				print "<th colspan=\"2\" id=\"$p".'T'.$l."\"></th>";
			  print "</tr>";
			  $l++;
			}
	  	$c++;
		}
	  print "</tr>\n";

		//TOTAL PERIODO NA HORIZONTAL
		print "<tr>";
		print "<th>&nbsp;</th>";
		for ($c=1; $c <= 6; $c++) {
			$TDP = $p.'TDP'.$c;
			print "<th id=\"$TDP\" colspan=\"2\"></th>";
		}
		print "<th colspan=\"2\" id=\"".$p."TP\"></th>"; // RESULTADO TOTAL PERIODO
		print "</tr>\n";
	    
	  if ($p == 1) {
	  	print "<tr><th colspan=\"15\">&nbsp;</th></tr>\n";
	  	print "<tr>\n";
			foreach ($dias as $dCodigo => $dNome) {
				$col = (!$dCodigo)? 1 : 2;
				if (!$dCodigo) $dNome = '2'.$dNome;
	  		print "<th colspan=\"$col\"><span style='font-weight: bold; color: white'>$dNome</span></th>\n";
			}
			print "</tr>\n";
			foreach ($dias as $dCodigo => $dNome) {
	  		if ($dCodigo == 0)
					print "<th><span style='font-weight: bold; color: white'>ATIVIDADES</span></th>";
	  		elseif ($dCodigo == 8)
					print "<th colspan=\"2\"><span style='font-weight: bold; color: white'>PER&Iacute;ODO</span></th>";
	  		else
					print "<th>E</th><th>S</th>";
			}
			print "</tr>\n";
		}
	}
	
	//TOTAL DIARIO
	print "<tr><th colspan=\"15\">&nbsp;</th></tr>\n";
	print "<tr>";
	print "<th>TOTAL DI&Aacute;RIO</th>";
	for ($i=1; $i <= 6; $i++) {
		$TD = 'TD'.$i;
		print "<th id=\"$TD\" colspan=\"2\"></th>";
	}
	print "<th colspan=\"2\" id=\"T\"></th>";
	print "</tr>\n";
	    
	//INTERVALO
	print "<tr><th colspan=\"15\">&nbsp;</th></tr>\n";
	print "<tr>\n";
	foreach ($dias as $dCodigo => $dNome) {
		$col = (!$dCodigo)? 1 : 2;
		if (!$dCodigo) $dNome = 'INTERVALO';
		if ($dCodigo < 8)
	  	print "<th colspan=\"$col\"><span style='font-weight: bold; color: white'>$dNome</span></th>\n";
	  elseif ($dCodigo == 8)
			print "<th colspan=\"2\">&nbsp;</th>";
	}
	
	print "</tr>\n";
	print "<tr><th rowspan=\"2\">&nbsp;</th>";
	foreach ($dias as $dCodigo => $dNome) {
		if ($dCodigo < 7)
			print "<th>E</th><th>S</th>";
	  elseif ($dCodigo == 8)
			print "<th colspan=\"2\">&nbsp;</th>";		
	}
	print "</tr>\n";
	print "<tr>";
	for ($i=1; $i <= 6; $i++) {
		$TE = 'IE'.$i;
		$TS = 'IS'.$i;
		print "<th id=\"$TE\" width=\"40\"></th><th id=\"$TS\" width=\"40\"></th>";
	}
	print "<th colspan=\"2\"></th>";
	print "</tr>\n";
	    
	print "</table>\n";
	print "</center>\n";
	
	$atividade[1] = 'Aula';
	$atividade[2] = 'Atendimento';
	$atividade[3] = 'Dedução - 270';
	$atividade[4] = 'Reunião de Área';
	$atividade[5] = 'Projeto Interno';
	$atividade[6] = 'Projeto Externo';
	
	print "<br><table width=\"100%\" border=\"0\" summary=\"FTD\" id=\"tabela_boletim\">\n";
	print "<tr><th>\n";
	print "<table width=\"70%\" border=\"0\">\n";
	print "<tr align=\"center\">\n";
	print "<th><span style='font-weight: bold; color: white'>Atividade</span></th>\n";
	print "<th><span style='font-weight: bold; color: white'>Horas/Semana</span></th>";
	print "</tr>\n";
	for ($a=1; $a <= 6; $a++) {
		print "<tr align=\"left\">\n";
	  print "<th>".$atividade[$a]."</th><th align=\"center\" id=\"A".$a."\"></th>";
	  print "</tr>\n";
	}
	print "<th><span style='font-weight: bold; color: white'>Carga Hor&aacute;ria Total</span></th><th align=\"center\" id=\"AT\"></th>";
	print "</table>\n";
	print "</th><th>\n";
	print "<table width=\"100%\" border=\"0\">\n";
	print "<tr align=\"left\">\n";
	print "<th>Atividade Docente</th><th align=\"center\" id=\"AtvDocente\"></th>";
	print "</tr>\n";
	print "<tr align=\"left\">\n";
	print "<th>Projetos</th><th align=\"center\" id=\"Projetos\"></th>";
	print "</tr>\n";
	print "<tr align=\"left\">\n";
	print "<th>Dedu&ccedil;&atilde;o Intervalos</th><th align=\"center\" id=\"Intervalos\"></th>";
	print "</tr>\n";
	print "<tr align=\"left\">\n";
	print "<th>Total</th><th align=\"center\" id=\"Total\"></th>";
	print "</tr>\n";
	print "</table>\n";
	print "</tr>\n";
	print "<tr><th colspan=\"2\">&nbsp</th></tr>\n";
	print "<tr>\n";
	print "<th colspan=\"2\" valign=\"top\">Incluir Observa&ccedil;&atilde;o: <textarea rows=\"2\" cols=\"73\" maxlength='200' id='obs' name='obs'>$obs</textarea>\n";
	print "</th></tr>\n";
	print "</table>\n";
	
	
	
	mysql_close($conexao);
	?>
	<script>
	<?php if (!$disabled) { ?>
	$(function () {
	  $("td").click(function () {
			var conteudoOriginal = $(this).text();
			$(this).addClass("celulaEmEdicao");
			$(this).html("<input type='text' id='celulaEmEdicao' size='3' value='" + conteudoOriginal + "' />");
			$("#celulaEmEdicao").mask("99:99");
			$(this).children().first().focus();
		
			$(this).children().first().keypress(function (e) {
			  if (e.which == 13) {
					var novoConteudo = $(this).val();
					var test = novoConteudo.split(":");
					if (test != '__,__') {
					  if (test[0] < 24 && test[1] < 60) {
							$(this).parent().text(novoConteudo);
							$(this).parent().removeClass("celulaEmEdicao");
							clean();
							calcDiario();
							calcPeriodo();
							checkRegras();
						}
					} else {
				    $(this).parent().text('');
				    $(this).parent().removeClass("celulaEmEdicao");
						clean();
						calcDiario();
						calcPeriodo();
						checkRegras();
					}
				}
			});
			$(this).children().first().blur(function(){
				$(this).parent().text(conteudoOriginal);
		  	$(this).parent().removeClass("celulaEmEdicao");
			});
		});
	});
	<?php } ?>
	
	function clean() {
		//PERIODOS
		for (p=1; p <= 2; p++) {
	  	for (l=1; l <= 8; l++) {
	    	var TL = p +'T'+ l;
				$("#"+TL).text('');
			}
		}
		
		//INTERVALOS E TOTAL DIARIO
		for (i=1; i <= 6; i++) {
			TE = 'IE'+i;
			TS = 'IS'+i;
			$("#"+TE).text('');
			$("#"+TS).text('');
			
			$("#TD"+i).text('');
			$("#"+i+"TDP1").text('');
			$("#"+i+"TDP2").text('');
		}
	}
	
	function checkRegras() {
		// Intervalos menores que 1 horas
		for (i=1; i <= 6; i++) {
			var IE = $("#IE"+i).text();
			var IS = $("#IS"+i).text();
		
			if (IE && IS) {
				var difI = subtime(IE, IS).split(":");
				if ( difI[0] < 01 ) {
					alert('INTERVALO MENOR QUE 1 HORA');
					$("#IE"+i+",#IS"+i).css({color: '#FF0000'});
				}	else
					$("#IE"+i+",#IS"+i).css({color: '#000'});
			}
		}
		// Se o total é menor que 32 horas.
		var difT = subtime($("#Total").text(), '32:00').split(":");
		if ( difT[0].match(/-/) || ( difT[0] > 00 || (difT[0] == 00 && difT[1] > 00)) ) {
			$("#Total").css({color: '#FF0000'});
		}	else
			$("#Total").css({color: '#000'});
		
		for (p=1; p <= 2; p++) {
		 	for (c=1; c <= 6; c++) {
				var TDP = $("#"+c+"TDP"+p).text();
				var difTDP = TDP.split(":");
				var erro=0;
			
				if (difTDP[0] > 06 || (difTDP[0] == 06 && difTDP[1] > 00)) {
					alert('MÁXIMO DE 6 HORAS NO PERÍODO');
					erro=1;
				}

				if (erro)
					$("#"+p+"TDP"+c).css({color: '#FF0000'});
				else
					$("#"+p+"TDP"+c).css({color: '#000'});
			}
		}	 		
		 		
		// Verificando se há dias com mais de 8 horas, com excessão da quarta-feira.
		for (i=1; i <= 6; i++) {
			var TD = $("#TD"+i).text();
			var difTD = TD.split(":");
			var erro=0;
			
			if (i != 3 && (difTD[0] > 08 || (difTD[0] == 08 && difTD[1] > 00))) {
				alert('MÁXIMO DE 8 HORAS NO DIA');
				erro=1;
			}
				
			if (i == 3 && (difTD[0] > 10 || (difTD[0] == 10 && difTD[1] > 00))) {
				alert('MÁXIMO DE 10 HORAS NA QUARTA-FEIRA');
				erro=1;
			}
			if (erro)
				$("#TD"+i).css({color: '#FF0000'});
			else
				$("#TD"+i).css({color: '#000'});
		}
	}
	
	function calcIntervalo() {
		var TI = 0;
	  for (p=1; p <= 2; p++) {
		 	for (c=1; c <= 6; c++) {
			 	var S1 = 0;
				var E2 = 0;
				var S2 = 0;
				var E3 = 0;
				var difer = 0;
		 		for (l=1; l <= 3; l++) {
					var IE = p+''+l+''+c+'1';
					var IS = p+''+l+''+c+'2';
	    		if ($("#"+IE).text() && $("#"+IS).text()) {
	    			if (l == 1) 
	    				S1 = $("#"+IS).text();
	    			if (l == 2) {
	    				E2 = $("#"+IE).text();
					    difer = subtime(S1, E2);
	    				if (TI != 0)
								TI = addtime(TI, difer);
			    		else
								TI = difer;
	    				S2 = $("#"+IS).text();
	    			}
						if (l == 3) {
	    				E3 = $("#"+IE).text();
					    difer = subtime(S2, E3);
	    				if (TI != 0)
								TI = addtime(TI, difer);
			    		else
								TI = difer;
						}   				
					}
				}
			}
		}
		return TI;
	}
	
	function calcPeriodo() {
		var aula = 0;
		var atendimento = 0;
		var deducao = 0;
		var rArea = 0;
		var pInterno = 0;
		var pExterno = 0;
		var CH = 0;
		
		var T=0; // total dos 2 periodos
		
  for (p=1; p <= 2; p++) {
	  	var TP=0; // var para --> Total Período
	    for (l=1; l <= 8; l++) {
		  	var diff_time = 0;
		    var difer = 0;
		   	for (c=1; c <= 6; c++) {
					var l_temp = l;
					var IE = p+''+l+''+c+'1';
					var IS= p+''+l+''+c+'2';
	    		if ($("#"+IE).text() && $("#"+IS).text()) {
			    	difer = subtime($("#"+IE).text(), $("#"+IS).text());
			    	var TL = p +'T'+ l;
			    	if (l == l_temp) {
							if (diff_time != 0)
				    		diff_time = addtime(diff_time, difer);
							else
				    		diff_time = difer;
			    	} else {
							l_temp = l;
							diff_time = 0;
			    	}
	
			    	if (TP != 0)
							TP = addtime(TP, difer);
			    	else
							TP = difer;
						$("#"+TL).text(diff_time);
						
						// Pegando valores para Resumo
			    	if (l==1 || l==2 || l==3) {
			    		if (aula != 0)
			    			aula = addtime(aula, difer);
			    		else
			    			aula = difer;
			    	}
			    	if (l==4) {
			    		if (atendimento != 0)
			    			atendimento = addtime(atendimento, difer);
			    		else
			    			atendimento = difer;
			    	}
			    	if (l==5) {
			    		if (deducao != 0)
			    			deducao = addtime(deducao, difer);
			    		else
			    			deducao = difer;
			    	}
			    	if (l==6) {
			    		if (rArea != 0)
			    			rArea = addtime(rArea, difer);
			    		else
			    			rArea = difer;
			    	}
			    	if (l==7) {
			    		if (pInterno != 0)
			    			pInterno = addtime(pInterno, difer);
			    		else
			    			pInterno = difer;
			    	}
			    	if (l==8) {
			    		if (pExterno != 0)
			    			pExterno = addtime(pExterno, difer);
			    		else
			    			pExterno = difer;
			    	}
					}
				}
			}
			var IDTP = p +'TP';
			$("#"+IDTP).text(TP);
	
			if (T != 0) // Total dos dois periodos
		    if (TP != 0)
					T = addtime(T, TP);
		    else
					T = T;
			else
		    T = TP;	
			$("#T").text(T);
		}
		
		// TABELA ATIVIDADES ABAIXO DA FTD
		$("#A1").text(aula);
		$("#A2").text(atendimento);
		$("#A3").text(deducao);
		$("#A4").text(rArea);
		$("#A5").text(pInterno);
		$("#A6").text(pExterno);
		
		if (CH != 0 && aula != 0) {
			CH = addtime(CH, aula);
		} else if (aula != 0)
			CH = aula;
		
		if (CH != 0 && atendimento != 0)
			CH = addtime(CH, atendimento);
		else if (atendimento != 0)
			CH = atendimento;	
	
		if (CH != 0 && deducao != 0)
			CH = addtime(CH, deducao);
		else if (deducao != 0)
			CH = deducao;
	
		if (CH != 0 && rArea != 0)
			CH = addtime(CH, rArea);
		else if (rArea != 0)
			CH = rArea;			
	
		if (CH != 0 && pInterno != 0)
			CH = addtime(CH, pInterno);
		else if (pInterno != 0)
			CH = pInterno;
	
		if (CH != 0 && pExterno != 0)
			CH = addtime(CH, pExterno);
		else if (pExterno != 0)
			CH = pExterno;
		
		$("#AT").text(CH);
		
		// TABELA LATERAL A ATIVIDADES, ABAIXO DA FTD
		var AtvDocente = 0;
		var Projetos = 0;
	
		var Intervalos = calcIntervalo();
		$("#Intervalos").text(Intervalos);
		
		var Total = 0;
		
		if (AtvDocente != 0 && aula != 0) {
			AtvDocente = addtime(AtvDocente, aula);
		} else if (aula != 0)
			AtvDocente = aula;
		
		if (AtvDocente != 0 && atendimento != 0)
			AtvDocente = addtime(AtvDocente, atendimento);
		else if (atendimento != 0)
			AtvDocente = atendimento;	
	
		if (AtvDocente != 0 && Intervalos != 0)
			AtvDocente = addtime(AtvDocente, Intervalos);
		else if (Intervalos != 0)
			AtvDocente = Intervalos;
	
		if (AtvDocente != 0 && rArea != 0)
			AtvDocente = addtime(AtvDocente, rArea);
		else if (rArea != 0)
			AtvDocente = rArea;			
	
		if (Projetos != 0 && pInterno != 0)
			Projetos = addtime(Projetos, pInterno);
		else if (pInterno != 0)
			Projetos = pInterno;
	
		if (Projetos != 0 && pExterno != 0)
			Projetos = addtime(Projetos, pExterno);
		else if (pExterno != 0)
			Projetos = pExterno;	
	
		$("#AtvDocente").text(AtvDocente);
		$("#Projetos").text(Projetos);
		
		//GERAR TOTAL
		if (Total != 0 && Intervalos != 0)
			Total = addtime(Total, Intervalos);
		else if (Intervalos != 0)
			Total = Intervalos;
	
		if (Total != 0 && AtvDocente != 0)
			Total = addtime(Total, AtvDocente);
		else if (AtvDocente != 0)
			Total = AtvDocente;			
	
		if (Total != 0 && Projetos != 0)
			Total = addtime(Total, Projetos);
		else if (Projetos != 0)
			Total = Projetos;	
		
		$("#Total").text(Total);
	}
	
	function calcDiario() {
		for (c=1; c <= 6; c++) {
			var first_time=0; // gerar intervalo
			var last_time=0; // gerar intervalo
	  	var diff_time = 0;
	  	var P1 = 0;
	  	var P2 = 0;
	  	for (l=1; l <= 8; l++) {
				for (p=1; p <= 2; p++) {
					var c_temp = c;
					var IE = p+''+l+''+c+'1';
					var IS = p+''+l+''+c+'2';
	    		if ($("#"+IE).text() && $("#"+IS).text()) {
			  		
			  		var d = subtime($("#"+IE).text(), $("#"+IS).text()); // Calcular o total de cada dia
			  																												// em cada periodo
			  		if (p == 1) { //calculo o intervalo da entrada
							if (first_time != 0) {
				    		var diff_first = subtime(first_time, $("#"+IS).text());
				    		if ( !diff_first.match(/-/) )
									first_time = $("#"+IS).text();
							} else {
				    		first_time = $("#"+IS).text();
							}
							
							// calculo total diario de cada periodo
							if (P1 != 0)
								P1 = addtime(P1, d);
							else
				    		P1 = d;
							$("#1TDP"+c).text(P1);
			    	}
			    	if (p == 2) { //calculo o intervalo da saida
							if (last_time != 0) {
				    		var diff_last = subtime(last_time, $("#"+IE).text());
					    	if ( diff_last.match(/-/) )
									last_time = $("#"+IE).text();
							} else {
				    		last_time = $("#"+IE).text();
							}

							// calculo total diario de cada periodo
							if (P2 != 0)
								P2 = addtime(P2, d);
							else
				    		P2 = d;
							$("#2TDP"+c).text(P2);

			  		}
						// mostra os intervalos se tiver entrada e saida.
						if (last_time && first_time) {
							$("#IS"+c).text(last_time);
							$("#IE"+c).text(first_time);
						}
	
			  		var difer = subtime($("#"+IE).text(), $("#"+IS).text());
	
			  		if ( difer.match(/-/) ) { //verificando se a data esta invertida
							$("#"+IE).text('');
							$("#"+IS).text('');
			 			}

			  		var TD = 'TD'+ c;
			  		if (c == c_temp) {
							if (diff_time != 0)
				    		diff_time = addtime(diff_time, difer);
							else
				    		diff_time = difer;
			  		} else {
							c_temp = c;
							diff_time = 0;
						}
			  		$("#"+TD).text(diff_time);
					}
				}
			}
		}
	}
	
	function subtime(start, end) {
	    start = start.split(":");
	    end = end.split(":");
	    var startDate = new Date(0, 0, 0, start[0], start[1], 0);
	    var endDate = new Date(0, 0, 0, end[0], end[1], 0);
	    var diff = endDate.getTime() - startDate.getTime();
	    var hours = Math.floor(diff / 1000 / 60 / 60);
	    diff -= hours * 1000 * 60 * 60;
	    var minutes = Math.floor(diff / 1000 / 60);
	    return (hours <= 9 ? "0" : "") + hours + ":" + (minutes <= 9 ? "0" : "") + minutes;
	}
	
	function addtime(start, end){
		start = start.split(":");
	  end = end.split(":");
	    
	  var hours = parseInt(start[0]) + parseInt(end[0]);
	  var minutes = parseInt(start[1]) + parseInt(end[1]);
	      
	  if ( minutes >= 60 ) {
			hours++;
	    minutes -= 60;
		}
  	return (hours <= 9 ? "0" : "") + hours + ":" + (minutes <= 9 ? "0" : "") + minutes;
	}
	
	$(document).ready(function() {
		calcDiario();
		calcPeriodo();
		checkRegras();
			
		$("#campoCelular").mask("(99) 99999-9999");
		$("#campoTelefone").mask("(99) 9999-9999");
	
	  $("#enviar").click(function(){
			jConfirm('Deseja salvar sua FTD e enviar para seu coordenador? \n A FTD ser&aacute; bloqueada, podendo ser desbloqueada somente pelo coordenador.', '<?php print $TITLE; ?>', function(r) {
		  if ( r )
				salvar(1);
			});
		});
			
	  $("#salvar").click(function(){
			salvar(0);
		});
	});
	
	function salvar(tipo) {
    var DTE = [];
		var DTS = [];
		var TP = [];
		var TPT = [];
		var TD = [];
		var TDT = [];
		var ITE = [];
		var ITS = [];
		var A = [];
		var i=0;
		var j=0;
		var n=0;
		for (p=1; p <= 2; p++) {
	    for (l=1; l <= 8; l++) {
		   	for (c=1; c <= 6; c++) {
	    		var IE = p +''+ l + ''+ c +''+ 1;
	    		var IS = p +''+ l + ''+ c +''+ 2;
	    		if ($("#"+IE).text() != '')
	    			DTE[i] = IE + '-' + $("#"+IE).text();
	    		if ($("#"+IS).text() != '')
	    			DTS[i] = IS + '-' + $("#"+IS).text();
	    		if ($("#"+IE).text() != '' && $("#"+IS).text() != '')
	    			i++;
	    		
	    		if ($("#"+p+"TDP"+c).text() != '')
						TD[p+c] = p+'TDP'+c + '-' + $("#"+p+"TDP"+c).text();
						
					if ($("#TD"+c).text() != '')
						TDT[c] = 'TD'+ c + '-' + $("#TD"+c).text();
						
					if ($("#IE"+c).text() != '')
						ITE[c] = 'IE'+ c + '-' + $("#IE"+c).text();
					if ($("#IS"+c).text() != '')
						ITS[c] = 'IS'+ c + '-' + $("#IS"+c).text();

					if ($("#A"+c).text() != '')
						A[c] = 'A'+ c + '-' + $("#A"+c).text();							
				}
				if ($("#"+p+"T"+l).text() != '') {
					TP[j] = p+'T'+l + '-' + $("#"+p+"T"+l).text();
					j++;
				}
    	}
    	if ($("#"+p+"TP").text() != '') {
				TPT[n] = p+'TP' + '-' + $("#"+p+"TP").text();
				n++;
			}
		}

		var AT = encodeURIComponent($("#AT").text());
		var AtvDocente = encodeURIComponent($("#AtvDocente").text());
		var Projetos = encodeURIComponent($("#Projetos").text());
		var Intervalos = encodeURIComponent($("#Intervalos").text());
		var Total = encodeURIComponent($("#Total").text());

		var telefone = encodeURIComponent($("#campoTelefone").val());
		var celular = encodeURIComponent($("#campoCelular").val());
		var email = encodeURIComponent($("#campoEmail").val());
		var area = encodeURIComponent($("#campoArea").val());
		var regime = encodeURIComponent($("input[type='radio']:checked").val());
		var obs = encodeURIComponent($("#obs").val());
		$('#index').load('<?php print $SITE; ?>?AT='+AT+'&AtvDocente='+AtvDocente+'&Projetos='+Projetos+'&Intervalos='+Intervalos+'&Total='+Total+'&ITS='+ITS+'&ITE='+ITE+'&TDT='+TDT+'&A='+A+'&TP='+TP+'&TPT='+TPT+'&TD='+TD+'&dte='+DTE+'&dts='+DTS+'&obs='+obs+'&telefone='+telefone+'&celular='+celular+'&email='+email+'&area='+area+'&regime='+regime+'&tipo='+tipo);
	}
</script>
<?php
// FIM DA FTD DO PROFESSOR
}
?>