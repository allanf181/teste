<?php
//A descri��o abaixo � utilizada em Permiss�es para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela referente ao envio de avisos a um discente ou a todos os discentes da disciplina dada pelo professor.
//O n�mero abaixo indica se o arquivo deve entrar nas permiss�es (respeitar a ordem da linha)
//1

require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $aviso = $_POST["campoAviso"];
		$atribuicao = dcrip($_POST["campoAtribuicao"]);
		$destinatario = (dcrip($_POST["campoDestinatario"]) == 'Todos') ? '' : dcrip($_POST["campoPessoa"]);
		$pessoa = $_SESSION['loginCodigo'];
		$turma = (dcrip($_SESSION['campoTurma']) == 'Todos') ? '' : dcrip($_POST["campoTurma"]);
		$curso = (dcrip($_SESSION['campoCurso']) == 'Todos') ? '' : dcrip($_POST["campoCurso"]);
	
    $resultado = mysql_query("insert into Avisos values(NULL, '$pessoa', '$atribuicao', '$turma', '$curso', '$destinatario', now(), '$aviso')"); 
    if ($resultado==1)
			mensagem('OK', 'TRUE_INSERT');
        else
			mensagem('NOK', 'FALSE_INSERT');
    $_GET['atribuicao'] = $_POST["campoAtribuicao"];
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
		$resultado = mysql_query("delete from Avisos where codigo=$codigo");
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('NOK', 'FALSE_DELETE');
    $_GET["codigo"] = null;
}
?>

<h2><?php print $TITLE; ?></h2>

<?php
		$atribuicao = dcrip($_GET["atribuicao"]);

    if (isset($_GET["turma"]) && $_GET["turma"]!="")
        $turma = dcrip($_GET["turma"]);
    if (isset($_GET["curso"]))
        $curso = dcrip($_GET["curso"]);
            
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

		if (in_array($ADM, $_SESSION["loginTipo"]) 
				|| in_array($SEC, $_SESSION["loginTipo"])
				|| in_array($GED, $_SESSION["loginTipo"])
				|| in_array($COORD, $_SESSION["loginTipo"])) { $C=1; $T=1; }

    print "<div id=\"html5form\" class=\"main\">\n"; 
    print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n"; 
    ?> 
    <table align="center" width="100%" id="form"> <input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" /> 
		<?php 
		if ($C) { ?>
	    <tr><td align="right">Curso: </td><td><select name="campoCurso" id="campoCurso" style="width: 350px">
	    <?php
	    	if (!$restricaoCoordenador && !$restricaoCoordenadorAnd ) {
    			echo "<option value='".crip("Todos")."'>Todos os Cursos</option>"; 
	    	}
	    	$sql = "select c.codigo, c.nome, m.nome, m.codigo 
	               		from Cursos c, Modalidades m 
	               		where c.modalidade = m.codigo $restricaoCoordenadorAnd
	               		order by c.nome";
	      $resultado = mysql_query($sql);
	      $selected = "";
	      while ($linha = mysql_fetch_array($resultado)) {
	      	if ($linha[0]==$curso)
	        	$selected="selected";
					if ($linha[3] < 1000 || $linha[3] >= 2000) $linha[1] = "$linha[1] [$linha[2]]";                            
	        echo "<option $selected value='".crip($linha[0])."'>[$linha[0]] $linha[1]</option>";
	        $selected = "";
	      }
	  	?>
	    </select>
	    </td></tr>
    <?php
    } 
		if ($T) { ?>
 			<tr><td align="right">Turma: </td>
    	<td><select name="campoTurma" id="campoTurma" style="width: 350px">
    	<?php
     	$resultado = mysql_query("select t.codigo, t.numero, c.nome, tu.nome, t.semestre, t.ano, c.fechamento
          							from Turmas t, Cursos c, Turnos tu 
           							where t.curso=c.codigo 
           							and t.ano=$ano 
           							and t.turno=tu.codigo
           							and c.codigo = $curso
           							and (t.semestre=$semestre OR t.semestre=0) $restricaoCoordenadorAnd");
    	$selected = "";
      if (mysql_num_rows($resultado) > 0) {
	   		if ($turma == 'Todos') $selected = 'selected';
  	  		echo "<option $selected  value='".crip("Todos")."'>Todos as Turmas</option>"; 
      	while ($linha = mysql_fetch_array($resultado)) {
        	if ($linha[6] == 'b' && $relatorio != 'matriculas') $S=1;
          if ($linha[0] == $turma)
          $selected = "selected";
          echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
          $selected = "";
        }
      }
      ?>
      </select>
      </td></tr>
      <?php 
    } ?>
   	<tr><td align="right">Aluno: </td><td><select name="campoPessoa" id="campoPessoa" style="width: 350px"> 
    <?php 
    	if (in_array($PROFESSOR, $_SESSION["loginTipo"])) 
    		$sqlADD = " AND a.codigo = $atribuicao";
    	else
    		$sqlADD = "AND t.codigo = $turma";
    		
    	$sql = "SELECT p.codigo, p.nome 
    							FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t 
    							WHERE t.codigo = a.turma AND m.atribuicao = a.codigo 
    							AND m.aluno = p.codigo 
    							AND t.codigo = a.turma 
     							$sqlADD
    							GROUP BY p.codigo ORDER BY p.nome"; 
    $resultado = mysql_query($sql); $selected = ""; 
    if (mysql_num_rows($resultado) > 0) { 
    	echo "<option value='".crip("Todos")."'>Todos da Turma</option>"; 

    	while ($linha = mysql_fetch_array($resultado)) { 
    		if ($linha[0] == $turma) 
	    		$selected = "selected"; 
  	  	echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>"; 
    		$selected = "";
    	} 
    }
    ?>
    </select>
    </td></tr>
    <tr><td align="right" style="width: 120px">Aviso: </td> 
    <td><textarea rows="5" cols="60" maxlength='500' id='campoAviso' name='campoAviso'><?php print $aviso; ?></textarea></tr>
    <tr><td></td><td>
    <input type="hidden" name="campoAtribuicao" value="<?php echo $_GET['atribuicao']; ?>" /> 
    <input type="hidden" name="opcao" value="InsertOrUpdate" />
    	<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
    	<td><a href="javascript:$('#index').load('<?php print $SITE."?atribuicao=".$_GET['atribuicao']; ?>'); void(0);">Novo/Limpar</a></td> 
    	</tr></table> 
    </td></tr> 
    </table>
    </form>
	</div>
<?php
    // inicializando as vari?veis
    $item = 1;
    $itensPorPagina = 10;
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
    $resultado = mysql_query("select count(*) from Avisos a 
    														WHERE pessoa = ".$_SESSION['loginCodigo']."");
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
 
	$SITENAV = $SITE."?atribuicao=".crip($atribuicao);
	require(PATH.VIEW.'/navegacao.php'); ?>

	<table id="listagem" border="0" align="center">
    <tr><th align="left" width="40">#</th><th>Data</th><th>Aviso</th><th>Para</th><th width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
		$sql = "SELECT a.codigo, date_format(a.data, '%d/%m/%Y %H:%i'), 
    													a.conteudo, a.atribuicao,
    													(SELECT p1.nome FROM Pessoas p1 WHERE p1.codigo = a.destinatario),
    													(SELECT CONCAT('[', c.codigo, '] ', c.nome) FROM Cursos c WHERE c.codigo = a.curso),
    													(SELECT t.numero FROM Turmas t WHERE t.codigo = a.turma)
    													FROM Avisos a 
    													WHERE pessoa = ".$_SESSION['loginCodigo']."
    													ORDER BY a.data DESC limit ". ($item - 1) . ",$itensPorPagina";
		//print $sql;
    $resultado = mysql_query($sql);
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
        $para='';
        if ($linha[5]) $para = $linha[5];
        if ($linha[6]) $para = $linha[6];
        if ($linha[4]) $para = $linha[4];

        if (!$para) $para = 'Todos';
				echo "<tr $cdif><td align='left'>$i</td><td>".$linha[1]."</td><td>".mostraTexto($linha[2])."</td><td>".mostraTexto($para)."</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a></td></tr>";
        $i++;
    }
    ?>
<?php require(PATH.VIEW.'/navegacao.php'); 

echo "<div style='margin: auto'><a href=\"javascript:$('#index').load('$VOLTAR'); void(0);\" class='voltar' title='Voltar' ><img class='botao' src='".ICONS."/left.png'/></a></div>";

mysql_close($conexao);

$atribuicao = $_GET["atribuicao"];
?>
<script>
function valida() {
   	turma = $('#campoTurma').val();
   	curso = $('#campoCurso').val();
		$('#index').load('<?php print $SITE; ?>?turma='+ turma +'&curso=' + $('#campoCurso').val());
}

function valida2() {
	if ( $('#campoAviso').val() == "" ) {
  	$('#salvar').attr('disabled', 'disabled');
  } else {
  	$('#salvar').enable();
  }
}

$(document).ready(function(){
    valida2();

    $('#campoAviso').keyup(function(){
        valida2();
    });
    
    $('#campoTurma, #campoCurso').change(function(){
        valida();
    });

	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load('<?php print $SITE."?atribuicao=$atribuicao"; ?>&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
});    
</script>