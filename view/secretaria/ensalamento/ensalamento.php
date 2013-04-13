<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela em que é possível a visualização da alocação das salas dos respectivos professores e das disciplinas dadas nesta sala em determinado horário.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if (in_array($ADM, $_SESSION["loginTipo"]) 
	|| in_array($SEC, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
	 	
	if ($_GET["opcao"] == 'delete') {
	    $codigo = dcrip($_GET["codigo"]);
		$resultado = mysql_query("delete from Ensalamentos where codigo=$codigo");
	    if ($resultado==1)
			mensagem('OK', 'TRUE_DELETE');
	    else
			mensagem('NOK', 'FALSE_DELETE');
	    $_GET["codigo"] = null;
	}
	?>
	
	<h2><?php print $TITLE; ?></h2>
	<?php

	    if (isset($_GET["turma"])){
	        $turma = dcrip($_GET['turma']);
	        if (!empty($turma))
	            $restricao = " and t.codigo=$turma";
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
	        <tr><td align="right" style="width: 100px">Turma: </td><td>
	                <select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>">
	                	<option></option>
	                    <?php
	                    	$bimestre = ($semestre == 2)? 3 : 1;
	                        $resultado = mysql_query("SELECT t.codigo, t.numero, c.nome, m.nome, m.codigo, c.codigo
	                        							FROM Turmas t, Cursos c, Modalidades m
	                        							WHERE t.curso = c.codigo 
	                        							AND m.codigo = c.modalidade
	                        							AND ano = $ano 
	                        							AND (semestre=$semestre OR semestre=0) 
	                        							ORDER BY c.nome, t.numero");
	                        $selected=""; // controla a alteração no campo select
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
	            <tr><td></td><td>   
	         <input type="hidden" name="opcao" value="InsertOrUpdate" />
			<table width="100%"><tr>
				<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
			</tr></table>
			</td></tr>
	    </table>
	</form>
	<?php
		
	    // inicializando as vari?veis
	    $item = 1;
	    $itensPorPagina = 50;
	    $primeiro = 1;
	    $anterior = $item - $itensPorPagina;
	    $proximo = $item + $itensPorPagina;
	    $ultimo = 1;
	
	    // validando a p?gina atual
	    if (!empty($_GET["item"])){
	        $item = $_GET["item"];
	        $anterior = $item - $itensPorPagina;
	        $proximo = $item + $itensPorPagina;
	    }
	
	    // validando a p?gina anterior
	    if ($item - $itensPorPagina < 1)
	        $anterior = 1;
	
	    // descobrindo a quantidade total de registros
	    $resultado = mysql_query("SELECT COUNT(*)
	            					FROM Atribuicoes a, Pessoas p, Turmas t, Disciplinas d, Ensalamentos e, Horarios h, Salas s, Professores pr
					                WHERE pr.atribuicao = a.codigo
                					AND pr.professor = p.codigo
					                AND a.turma = t.codigo 
					                AND e.atribuicao = a.codigo 
					                AND h.codigo = e.horario
					                AND a.disciplina = d.codigo 
					                AND s.codigo = e.sala
					                AND t.ano = $ano
					                AND (t.semestre=$semestre OR t.semestre=0)					                
	                				$restricao
	                				ORDER BY p.nome, d.nome, t.numero");
	    $linha = mysql_fetch_row($resultado);
	    $ultimo = $linha[0];
	    
	    // validando o pr?ximo item
	    if ($proximo > $ultimo){
	        $proximo = $item;
	        $ultimo = $item;
	    }
	    
	    // validando o ?ltimo item
	    if ($ultimo % $itensPorPagina > 0)
	        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    
	 
		$SITENAV = $SITE."?turma=".crip($turma);
    require PATH . VIEW . '/paginacao.php';
?>	
		<table id="listagem" border="0" align="center">
	    <tr><th align="center" width="40">#</th><th>Atribui&ccedil;&atilde;o</th><th>Sala</th><th width="180">Hor&aacute;rio</th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
	    <?php
       	$bimestre = ($semestre == 2)? 3 : 1;
	    // efetuando a consulta para listagem
	    $sql = "SELECT e.codigo, p.nome, d.numero, t.numero, e.diaSemana, s.nome, h.nome, date_format(h.inicio, '%H:%i'), date_format(h.fim, '%H:%i')
	            	FROM Atribuicoes a, Pessoas p, Turmas t, Disciplinas d, Ensalamentos e, Horarios h, Salas s, Professores pr
	                WHERE pr.atribuicao = a.codigo
   					AND pr.professor = p.codigo
	                AND a.turma = t.codigo 
	                AND e.atribuicao = a.codigo 
	                AND h.codigo = e.horario
	                AND a.disciplina = d.codigo 
	                AND s.codigo = e.sala
	                AND t.ano = $ano
	                AND (t.semestre=$semestre OR t.semestre=0)
	                $restricao
	                ORDER BY p.nome, d.nome, t.numero limit ". ($item - 1) . ",$itensPorPagina";
	    //print $sql;
	    $resultado = mysql_query($sql);
	    $i = $item;
	    $dias = diasDaSemana();
	    while ($linha = mysql_fetch_array($resultado)) {
	        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
		    $codigo = crip($linha[0]);
		    $linha[4] = $dias[$linha[4]];
			echo "<tr $cdif><td align='left'>$i</td><td>$linha[1] [$linha[2]][$linha[3]]</a></td><td>$linha[5]</td><td>$linha[6] [$linha[7] - $linha[8]]</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
	        $i++;
	    }

}
?>

<style>
.calendario {
     border-collapse: collapse;
     border: 1px solid #333;
     background-color: #FBFBFB;
     text-align: center;
}

caption {
     padding: 5px 0 5px 0;
     font: small-caps bold 11px verdana, arial, tahoma;
     background-color: #999;
     border: 1px solid #333;
}
</style>

<?php
$NOT=0;
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<?php
if (isset($_SESSION["loginCodigo"]) && in_array($PROFESSOR, $_SESSION["loginTipo"]) && !isset($_GET["turma"])) {
	$professor = "AND pr.professor = ".$_SESSION['loginCodigo'];
	$NOT=1;
}

if (isset($_SESSION["loginCodigo"]) && in_array($ALUNO, $_SESSION["loginTipo"]) && !isset($_GET["turma"])) {
	$aluno = "AND m.aluno = ".$_SESSION['loginCodigo'];
	$sql1 = ", Matriculas m";
	$sql2 = "AND m.atribuicao = a.codigo";
	$NOT=1;
}

if (isset($_GET["atribuicao"]) && !isset($_GET["turma"])) {
	$atribuicao = "AND a.codigo = ".dcrip($_GET['atribuicao']);
}

if (isset($_GET["turma"])) {
	$turma = "AND t.codigo IN (SELECT t1.codigo FROM Turmas t1 
				WHERE t1.numero IN (SELECT t2.numero FROM Turmas t2 
				WHERE t2.codigo = ".dcrip($_GET['turma'])."))";
}

if (isset($_GET["subturma"])){
	if (!is_numeric(dcrip($_GET['subturma'])))
		$sub = " AND (a.subturma = '".dcrip($_GET['subturma'])."' OR a.subturma = 'ABCD')";

	$subturma = dcrip($_GET['subturma']);
}

if ($turma || $professor || $aluno) {	
	$sql = "SELECT diaSemana, date_format(h.inicio, '%H:%i'), date_format(h.fim, '%H:%i'),
				d.numero, s.nome, d.nome, p.nome, a.codigo, t.numero, s.localizacao, h.nome, h.codigo
				FROM Ensalamentos e, Disciplinas d, Salas s, Horarios h, Pessoas p, Atribuicoes a, Turmas t, Professores pr $sql1
    			WHERE a.codigo = e.atribuicao
    			AND a.disciplina = d.codigo
    			AND e.sala = s.codigo
    			AND e.horario = h.codigo
    			AND a.turma = t.codigo
    			AND pr.professor = e.professor
    			AND pr.professor = p.codigo
			AND pr.atribuicao = a.codigo
                        AND t.semestre = $semestre
                        AND t.ano = $ano
                        $sql2
    			$aluno 
    			$atribuicao
   			$turma $sub $professor
    			GROUP BY diaSemana, h.inicio, h.fim, d.numero, s.nome
				ORDER BY diaSemana, inicio, disciplina";
	//print $sql;
    $resultado = mysql_query($sql);
    while ($linha = mysql_fetch_array($resultado)) {
		preg_match('#\[(.*?)\]#', $linha[10], $match);
		$turno[$linha[11]] = $match[1];
		$linha[10] = str_ireplace("[$match[1]]", "", $linha[10]);
		$horas[$linha[0]][$linha[1]][$linha[7]][$linha[11]] = $linha[1]." - ".$linha[2];
		$disciplinas[$linha[7]] = 'SALA: '.$linha[4].' |LOCAL: '.$linha[9] . " |DISC: ". $linha[5]. " |PROF: ".$linha[6];
		$siglas[$linha[7]] = $linha[3];
		$aulas[$linha[11]] = $linha[10];
		$turmaNome = $linha[8];
    }

    $sql = "SELECT nome, sigla FROM Turnos";
    $result = mysql_query($sql);
    while ($l = mysql_fetch_array($result))
		$turnos[$l[1]] = $l[0];
	
	$domingo  = "style=color:#C30;";
   
   if ($NOT) $MOSTRA = 'HOR&Aacute;RIO INDIVIDUAL';
   else $MOSTRA = "Turma $turmaNome [$subturma]";
	print "<h2><font color=\"white\">$MOSTRA</h2>\n";

	print "<center><table width=\"80%\" border=\"0\" summary=\"Calendário\" id=\"tabela_boletim\">\n";
	print "<thead>\n";
	print "<tr>\n";
	foreach (diasDaSemana() as $dCodigo => $dNome) {
		print "<th abbr=\"Domingo\" title=\"$dNome\"><span style='font-weight: bold; color: white'>$dNome</span></th>\n";
	}
	print "</tr>\n";
	print "</thead>\n";
	print "<tr align=\"center\">\n";
    for ($i=1; $i<=7; $i++) {
    	$turnoAnterior='';
	  	print "<td style='width: 10%' valign=\"top\">";
	  	if ($horas[$i])
			foreach ($horas[$i] as $disc) {
				foreach ($disc as $dNum => $dHor) {
					foreach ($dHor as $cHor => $dSala) {
	   		        	if ($turno[$cHor] != $turnoAnterior) print "<br>".strtoupper($turnos[$turno[$cHor]])."<hr><br>\n";
			        	print "<a href='#' title='$disciplinas[$dNum]'>$siglas[$dNum] - $aulas[$cHor]</a><br>$dSala<br>";
			        	if (count($horas[$i]) > 1) print "<hr>";
			        	$turnoAnterior = $turno[$cHor];
		        	}
	    	    }
	      	}
   		print "</td>";
  	}
	print "</tr>\n";
	print "</table></center>\n";
}
mysql_close($conexao);
?>
<script>
function atualizar(getLink){
    var turma = $('#campoTurma').val();
	var URLS = '<?php print $SITE; ?>?turma=' + turma;
	if (!getLink)
		$('#index').load(URLS + '&item=<?php print $item; ?>');
	else
		return URLS;
}

function valida() {
    if ( $('#campoAtribuicao').val() == "" ||
    	 $('#campoSala').val() == "" || $('#campoHorario').val() == "" || $('#campoDia').val() == "" ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}


$(document).ready(function(){
    valida();
    $('#campoAtribuicao, #campoSala, #campoHorario, #campoDia').change(function(){
        valida();
    });
    
	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load(atualizar(1) + '&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});

	
   	$('#campoTurma').change(function(){
    	atualizar();
	});
});    
</script>