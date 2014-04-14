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

if ($_POST["opcao"] == 'InsertOrUpdateObs') {
    $observacoes = ($_POST["campoObservacoes"]);
    $competencias = ($_POST["campoCompetencias"]);
    $atribuicao= dcrip($_POST["campoAtribuicao"]);

    if (empty($codigo)){
        $sql = "update Atribuicoes set observacoes='$observacoes', competencias='$competencias' where codigo=$atribuicao";
        $resultado = mysql_query($sql);
        if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_UPDATE');
    }
    $_GET['atribuicao'] = crip($atribuicao);
}

if ($_GET["opcao"] == 'controleDiario') {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $status = $_GET["status"];
    if (!$erro=fecharDiario($atribuicao)) {
   		$sql = "update Atribuicoes set status=$status where codigo=$atribuicao";
			$resultado = mysql_query($sql); 
    	if ($resultado==1)
				mensagem('OK', 'TRUE_UPDATE');
    	else
				mensagem('NOK', 'FALSE_UPDATE');	
		} else {
			if ($erro == 2)
				mensagem('NOK', 'FALSE_CLOSE_CLASS_REGISTRY');
			else
				mensagem('NOK', 'FALSE_UPDATE');
	}
}

$atribuicao=$_GET["atribuicao"];

?>

<table width="100%" align="center" border="0">
<?php
if ($_GET["atribuicao"]) {
	$atribuicao=dcrip($_GET["atribuicao"]);
    $disciplina="";
    $disciplinaCompartilhada="";
    $turma="";
    $curso="";
    $observacoes="Não ";

    $sql = "select d.nome, t.numero, c.nome, tu.nome, a.observacoes, a.bimestre, a.status, c.fechamento, 
        p.atribuicao, p.solicitacao, t.semestre, t.ano, p2.nome,
        a.grupo, a.competencias, 
        date_format(DATE_ADD(a.prazo, INTERVAL $LIMITE_AULA_PROF DAY), '%H:%i de %d/%m/%Y'), 
        DATEDIFF(DATE_ADD(a.prazo, INTERVAL $LIMITE_AULA_PROF DAY), NOW()),
        date_format( DATE_ADD(a.dataFim, INTERVAL $LIMITE_AULA_PROF DAY), '%d/%m/%Y'), t.codigo, a.subturma, 
        a.eventod,DATEDIFF( DATE_ADD(a.dataFim, INTERVAL $LIMITE_AULA_PROF DAY), NOW()),
        m.codigo, m.nome, c.nomeAlternativo
        from Disciplinas d, Turmas t, Cursos c, Modalidades m, Turnos tu, Atribuicoes a
        left join PlanosEnsino p on p.atribuicao=a.codigo
        left join Pessoas p2 on p.solicitante=p2.codigo
        where a.disciplina=d.codigo
        and d.curso=c.codigo
        and m.codigo = c.modalidade
        and a.turma=t.codigo
        and t.turno=tu.codigo
        and a.codigo=$atribuicao";
	
		//print $sql;
	        
    $resultado = mysql_query($sql);

    while ($linha = mysql_fetch_array($resultado)) {
        $disciplina = $linha[0];
        $turma = $linha[1];
        $turmaCodigo = $linha[18];
        
        $curso=($linha[24]) ? $linha[24] : $linha[2];
        if ( ($linha[22] < 1000 || $linha[22] >= 2000) && !$linha[24]) $curso = "$linha[23] - $linha[2]"; 
            
        $turno = $linha[3];
        $observacoes = $linha[4];
        $competencias = $linha[14];
        $bimestre="SEMESTRAL";
        $status=$linha[6];
        $fechamento=$linha[7];
        $EntregaPlano=$linha[8];
        $solicitacao=$linha[9];
        $_SESSION['semestre']=$linha[10];
        $_SESSION['ano']=$linha[11];
        $solicitante=$linha[12];
        $prazo=$linha[15];
        $diff_prazo = ($linha[16]) ? $linha[16] : $linha[21];
        $dataFim=$linha[17];
        $grupo=$linha[13];
        
        if ($fechamento == 'a') $bimestre="ANUAL";
        
        if ($linha[5]!="" && $linha[5]>0){
        	$numeroBimestre=$linha[5];
            $bimestre=abreviar("$linha[5]º BIMESTRE", 100);
        }
        
   	if (!$subturma = $linha[19]) $subturma = $linha[20];
    }
   
   // verificando se o prazo foi atingido.
   if ($diff_prazo < 0) {
   	mysql_query("UPDATE Atribuicoes SET status=4,prazo='' WHERE codigo = $atribuicao");
   	$status=4;
        $dataExpirou=true;
   } else
     	$dataExpirou=false;
   $_SESSION['dataExpirou'] = $dataExpirou;
   
   $sql = "SELECT (SELECT sum(quantidade) FROM Aulas au WHERE au.atribuicao = a.codigo),
        		d.ch, aulaPrevista
        		FROM Atribuicoes a, Disciplinas d
            	where a.disciplina=d.codigo 
            	and a.codigo=$atribuicao";
    $res = mysql_query($sql);
    $aulas = mysql_fetch_row($res);
	
    $sql = "SELECT (SELECT count(av.codigo) FROM Avaliacoes av, TiposAvaliacoes t1 WHERE av.tipo = t1.codigo AND av.atribuicao = a.codigo AND t1.tipo = 'avaliacao'),
    		d.ch, t.qdeMinima 
			FROM TiposAvaliacoes t, Modalidades m, Turmas tu, Cursos c, Disciplinas d, Atribuicoes a
			WHERE t.modalidade = m.codigo
			AND c.codigo = tu.curso
			AND c.modalidade = m.codigo
			AND a.turma = tu.codigo
			AND d.codigo = a.disciplina
			AND a.codigo = $atribuicao
			AND t.tipo = 'avaliacao'";
    //print $sql;
    $res = mysql_query($sql);
    $avaliacoes = mysql_fetch_row($res);
    
    if ($numeroBimestre <> 0 && $aulas[1])
			$aulas[1] = $aulas[1]/4;
				
    // desabilita edição se o status for igual a 1 e informa se o prazo foi estendido.
    if ( $status > 0 || ($prazo != '00:00 de 00/00/0000' && $prazo != NULL) ){
        if($status > 0) $disabled="disabled='disabled'";
				if ($status==1)
					$info = "Este diário foi fechado pelo Coordenador!";
        if ($status==2)
	        $info = "Você já finalizou este diário!";
        if ($status==3)
	        $info = "Este diário foi fechado pela Secretaria!";
        if ($status==4)
          $info = "Este diário foi fechado pelo Sistema pois o prazo para finalização do diário foi atingido!";
        if ($prazo && !$status)
          $info = "Seu prazo para altera&ccedil;&atilde;o do di&aacute;rio foi estentido at&eacute; &agrave;s $prazo";
				print "<script> jAlert('$info', '$TITLE'); </script>\n";
    }
    else{
        if ($dataExpirou || ($aulas[1] && $aulas[0]>=$aulas[1] && $avaliacoes[0] >= $avaliacoes[2] && $status==0 )){ // está desbloqueado e já tem a quantidade de aulas previstas e pelo menos uma avaliação ou a data final do período foi atingida
            $pergunta="Sr. Professor:<br />"; 
        	
	    	if ($dataExpirou){
        		$pergunta.= "A data final do período letivo ($dataFim) foi atingida. <br /> <br />";                
	    	    $pergunta.="Atenção: O seu diário foi bloqueado e somente o coordenador poderá desfazer esta operação.";
						$pergunta.="<br>Deseja tentar finalizar seu di&aacute;rio, caso j&aacute; tenha conclu&iacute;do a digita&ccedil;&atilde;o de aulas a notas?";
	      }
    	  else{
          	$pergunta.= "O número de aulas do diário está completo e avaliações foram aplicadas. <br /> <br />";
           	$pergunta.="<b>Deseja finalizar a digitação do diário e efetuar a entrega à secretaria?</b> <br /> <br />";
	    	    $pergunte.="Atenção: O seu diário será bloqueado e somente o coordenador poderá desfazer esta operação.";
	      }

			print "<script>jConfirm('$pergunta', '$TITLE', function(r) {
				if ( r ) $('#index').load('$SITE?opcao=controleDiario&status=2&atribuicao=".crip($atribuicao)."');
				}); </script>\n";
      }
   }

   print "<h2>".abreviar("$disciplina [$subturma]: $turma/$curso", 150)."</h2>";
   if($grupo)
   		$grupo = "(Turma $grupo)";
   else
      $grupo="";
    
    print "<h2 id='titulo_disciplina_modalidade'>$bimestre $grupo</h2><br />";
    print "<tr valign=\"top\" align='center'>";
    print "<td valign=\"top\" width=\"90\"><a class='nav professores_item' href=\"javascript:$('#professor').load('".VIEW."/professor/aula.php?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 80px' src='".IMAGES."/aulas.png' /><br />Aulas</a></td>";
    print "<td valign=\"top\" width=\"90\"><a class='nav professores_item' href=\"javascript:$('#professor').load('".VIEW."/professor/avaliacao.php?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 80px' src='".IMAGES."/avaliacoes.png' /><br />Avalia&ccedil;&otilde;es</a></td>";
    print "<td valign=\"top\" width=\"90\"><a class='professores_item' id='diario' target='_blank' href='".VIEW."/secretaria/relatorios/inc/diario.php?atribuicao=".crip($atribuicao)."');  void(0);\"><img style='width: 80px' src='".IMAGES."/diario.png' /><br />Di&aacute;rio de Classe</a></td>";
    print "<td valign=\"top\" width=\"90\"><a class='professores_item' id='listaChamada' target='_blank' href='".VIEW."/secretaria/relatorios/inc/chamada.php?atribuicao=".crip($atribuicao)."';)  void(0);\"><img style='width: 80px' src='".IMAGES."/chamada.png' /><br />Lista de Chamada</a></td>";
    if ($bimestre=="SEMESTRAL" || $bimestre=="1º BIMESTRE" || $bimestre=="ANUAL")
	    print "<td valign=\"top\" width=\"90\"><a class='nav professores_item' href=\"javascript:$('#professor').load('".VIEW."/professor/plano.php?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 80px' src='".IMAGES."/planoEnsino.png' /><br />Plano de Ensino</a></td>";
    print "<td valign=\"top\" width=\"90\"><a class='nav professores_item' href=\"javascript:$('#professor').load('".VIEW."/professor/aviso.php?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 80px' src='".IMAGES."/aviso.png' /><br />Avisos para Turma</a></td>";
    print "<td valign=\"top\" width=\"90\"><a class='nav professores_item' href=\"javascript:$('#professor').load('".VIEW."/professor/ensalamento.php?turma=".crip($turmaCodigo)."&subturma=".crip($subturma)."'); void(0);\"><img style='width: 80px' src='".IMAGES."/horario.png' /><br />Hor&aacute;rio da Turma</a></td>";
    print "</tr>";
    print "<tr><td colspan=\"7\"><hr></td></tr>\n";
    print "</table>";
    
    print "<div id=\"professor\">\n";

    $aulas[0] = (!$aulas[0])? 0 : $aulas[0];
    print "<table border=\"0\">\n";
    print "<tr><td><b><font size=\"1\">Aulas dadas:</b> $aulas[0]<br><b>Carga Hor&aacute;ria:</b> $aulas[1]";
    if ($numeroBimestre == 0) print "<br><b>Aulas previstas:</b> ".$aulas[2]."</font>\n";
		print "</td>\n";
    
		$professores='';
		foreach(getProfessor($atribuicao) as $key => $reg)
			$professores[] = "<a target=\"_blank\" href=".$reg['lattes'].">".$reg['nome']."</a>";
		$professor_disc = implode(" / ", $professores);
				
    print "<td><b><font size=\"1\">N&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es:</b> $avaliacoes[2] <br><b>Avalia&ccedil;&otilde;es aplicadas:</b> $avaliacoes[0]</font></td></tr>\n";
    print "<tr><td colspan=\"2\"><font size=\"1\"><b>Professores da disciplina:</b> $professor_disc</font></td></tr>\n";
    print "<tr><td colspan=\"7\"><hr></td></tr>\n";

    print "<tr><td colspan=\"7\"><font size=\"1\">Para esse di&aacute;rio ser finalizado, &eacute; necess&aacute;rio que a quantidade de aulas dadas seja maior ou igual a carga hor&aacute;ria e atingido o n&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es.\n";
    print "Caso deseje finalizar o di&aacute;rio manualmente, <a href='#' title='Excluir' class='finalizar' id='2'>clique aqui</a></td></tr>\n";

    print "<tr><td colspan=\"2\"><hr></td></tr>\n";
    print "<tr><td colspan=\"2\"><font size=\"1\">Aten&ccedil;&atilde;o: esse di&aacute;rio ser&aacute; bloqueado automaticamente pelo sistema em $dataFim.</font></td></tr>\n";
    print "</table>\n";

    print "<tr><td colspan=\"7\"><hr></td></tr>\n";

    if ($bimestre=="SEMESTRAL" || $bimestre=="1º BIMESTRE"){
		$status=0;
		print "<tr><td colspan=10 align='center'><br />\n";
		print "<div id=\"resposta_erro\"></div>\n";
        print "<div id=\"resposta\">\n";
        if (empty($EntregaPlano) && empty($solicitacao)){
			print "<p style='color: red; font-weight: bold'>Aten&ccedil;&atilde;o: o plano de ensino desta disciplina n&atilde;o foi digitado!</p>\n";
			$status=1;
        }
        else if (!empty($EntregaPlano) && !empty($solicitacao)){
        	print "<p style='color: red; font-weight: bold'>Aten&ccedil;&atilde;o: ".mostraTexto($solicitante)." solicitou a seguinte correção em seu plano de ensino:</p>\n";
            print "<p>".stripslashes($solicitacao)."</p><br>";
            $status=1;
        }

    }
    print "<tr><td colspan=10 align='center'>\n";

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
	print "        
        <h2>Competências Desenvolvidas:</h2>
        <div class='professores_textarea'>
        <textarea $disabled maxlength='500' id='4' name='campoCompetencias'>$competencias</textarea>
        </div>
        <h2>Observações a serem incluídas no diário da disciplina:</h2>
        <div class='professores_textarea'>
        <textarea $disabled maxlength='500' id='3' name='campoObservacoes'>$observacoes</textarea>
        </div>
        <input type='hidden' value='".crip($atribuicao)."' name='campoAtribuicao' id='campoAtribuicao' />
        <input type='hidden' value='$curso' name='campoCurso' id='campoCurso' />
		<input type='hidden' name='opcao' value='InsertOrUpdateObs' />
        <input id='professores_botao' $disabled type='submit' value='Salvar' />
        
        </form>
        </div>";
    print "</td></tr>";
    print "</table>\n";
    print "</div>\n";
} else {
	print "<p>Escolha uma disciplina</p>\n";
}
mysql_close($conexao);
?>

<script>
    $(document).ready(function(){   
            $('#3, #4').maxlength({   
                events: [], // Array of events to be triggerd    
                maxCharacters: 500, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification",	// Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
            });

            $(".finalizar").click(function(){
                    var status = $(this).attr('id');
                    jConfirm('<b>Deseja finalizar a digitação do diário e efetuar a entrega à secretaria?</b> <br /> <br />Atenção: O seu diário será bloqueado e somente o coordenador poderá desfazer esta operação.', '<?php print $TITLE; ?>', function(r) {
                            if ( r )
                                    $('#index').load('<?php print $SITE; ?>?opcao=controleDiario&status='+ status +'&atribuicao=<?php print $atribuicao; ?>');
                    });
            });

    });
</script>