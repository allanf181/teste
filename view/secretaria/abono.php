<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela de registro de abonos de faltas dos alunos referente ao regime de exercícios domiciliares, matrícula após início do ano letivo ou por outro motivo.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;


if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = dcrip($_POST["campoCodigo"]);

    if ($_POST["campoDataInicio"]) {
    	$dataInicio = dataMysql($_POST["campoDataInicio"]);
	    $dataInicio = new DateTime($dataInicio);
	}

	if ($_POST["campoDataFim"]) {
   		$dataFim = dataMysql($_POST["campoDataFim"]);
    	$dataFim = new DateTime($dataFim);
    }

    $aluno = dcrip($_POST["campoAluno"]);
    $motivo = $_POST["campoMotivo"];
    $atribuicao = dcrip($_POST["campoAtribuicao"]);
    $aula = dcrip($_POST["campoAula"]);
    $tipo = $_POST["campoTipo"];

    if (empty($codigo)){
    	if ( $_POST["campoDataInicio"] && $_POST["campoDataFim"]) {
    		while ($dataInicio <= $dataFim) {
    			$data = $dataInicio->format('Y-m-d');
        		$sql = "insert into FrequenciasAbonos values(0, $aluno, '$data', '$aula', '$atribuicao', '$motivo', '$tipo')";
        		$resultado = mysql_query($sql);
				$dataInicio->add(new DateInterval('P1D'));
			}
		} else {
		    $data = dataMysql($_POST["campoDataInicio"]);
	    	$sql = "insert into FrequenciasAbonos values(0, $aluno, '$data', '$aula', '$atribuicao', '$motivo', '$tipo')";
        	$resultado = mysql_query($sql);
	        $_GET["codigo"] = crip(mysql_insert_id());
		}
        if ($resultado==1)
			mensagem('OK', 'TRUE_INSERT');
    	else
			mensagem('NOK', 'FALSE_INSERT');
    }
    else{
	    $data = dataMysql($_POST["campoDataInicio"]);
        $resultado = mysql_query("update FrequenciasAbonos set aluno=$aluno, data='$data', aula='$aula', atribuicao='$atribuicao', atribuicao='$atribuicao', motivo='$motivo', tipo='$tipo' where codigo=$codigo");
        if ($resultado==1)
			mensagem('OK', 'TRUE_UPDATE');
        else
			mensagem('NOK', 'FALSE_INSERT');
	    $_GET["codigo"] = $_POST["campoCodigo"];
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from FrequenciasAbonos where codigo=$codigo");
    if ($resultado==1)
		mensagem('OK', 'TRUE_DELETE');
    else
		mensagem('NOK', 'FALSE_DELETE');
    $_GET["codigo"] = null;
    $_GET['dataInicio'] = null;
}
?>
<h2><?php print $TITLE; ?></h2>

<?php

    // inicializando as variáveis do formulário
    $codigo="";
    $motivo="";
    $dataInicio="";
    $dataFim="";
    $aluno="";
    $atribuicao="";
    
		if (isset($_GET["aluno"])) {
		    $aluno=dcrip($_GET["aluno"]);
		    $restricao = "AND aluno = $aluno";
		}
		
		if (isset($_GET["atribuicao"]))
		    $atribuicao=dcrip($_GET["atribuicao"]);

    $dataInicio = $_GET['dataInicio'];
    $dataFim = $_GET['dataFim'];

    if ( $dataFim && $dataInicio )  {
        $restricao .= " AND f.data >= '".dataMysql($dataInicio)."' AND f.data <= '".dataMysql($dataFim)."'";
	} else {
        if (!empty($dataInicio))
            $restricao .= " AND f.data='".dataMysql($dataInicio)."'";
        if (!empty($dataFim))
            $restricao .= " AND f.data='".dataMysql($dataFim)."'";
    }

    if (!empty($_GET["codigo"])){ // se o parâmetro não estiver vazio

        // consulta no banco
        $resultado = mysql_query("SELECT codigo, aluno, aula, DATE_FORMAT(data, '%d/%m/%Y'), motivo, tipo
        						 FROM FrequenciasAbonos WHERE codigo=".dcrip($_GET["codigo"]));
        $linha = mysql_fetch_row($resultado);

        // armazena os valores nas variáveis
        $codigo = $linha[0];
        $aluno = $linha[1];
        $aula = $linha[2];
        $dataInicio = $linha[3];
        $motivo = $linha[4];
        $tipo = $linha[5];

   		$restricao = " and f.codigo=".dcrip($_GET["codigo"]);
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
        <input type="hidden" value="<?php echo crip($codigo); ?>" name="campoCodigo" id="campoCodigo" />
		<tr><td  style="width: 100px" align="right">Data: </td><td>
			<input type="text" size="10" value="<?php echo $dataInicio; ?>" name="campoDataInicio" id="campoDataInicio" />
			a <input type="text" size="10" value="<?php echo $dataFim; ?>" name="campoDataFim" id="campoDataFim" />
		</td>
		</tr>
        <tr><td align="right">Aluno: </td>
        	<td><select name="campoAluno" id="campoAluno" style="width: 350px">
            <?php
                $sql = "SELECT p.codigo, p.nome
                			FROM Pessoas p, PessoasTipos pt
                			WHERE p.codigo = pt.pessoa
                			AND pt.tipo = $ALUNO
                			ORDER BY p.nome";
                $resultado = mysql_query($sql);

                while ($linha = mysql_fetch_array($resultado)) {
  	   				$selected="";
                    if ($linha[0]==$aluno)
	                    $selected="selected";
                    echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
                }
            ?>
        </select>
        </td></tr>
        <tr><td align="right">Disciplina: </td>
        	<td><select name="campoAtribuicao" id="campoAtribuicao" style="width: 350px">
        		<option></option>
            <?php
                $sql = "SELECT a.codigo, d.nome, a.bimestre
                			FROM Atribuicoes a, Disciplinas d, Matriculas m, Pessoas p
                			WHERE a.disciplina = d.codigo
                			AND m.atribuicao = a.codigo
                			AND m.aluno = p.codigo
                			AND p.codigo = $aluno
                			ORDER BY p.nome";
                $resultado = mysql_query($sql);

                while ($linha = mysql_fetch_array($resultado)) {
  	   					$selected="";
                	if ($linha[0]==$atribuicao)
	                	$selected="selected";
	                if ($linha[2] > 0) $BIM = '['.$linha[2].'ºBIM]';
                	echo "<option $selected value='".crip($linha[0])."'>$linha[1] $BIM</option>";
                }
            ?>
        </select> ou 
        </td></tr>
        <?php //print $sql; 
        if ($atribuicao) $disabled = 'disabled';
        ?>
        <tr><td align="right">Aula: </td>
        	<td><select name="campoAula" <?php print $disabled; ?> id="campoAula" style="width: 350px">
        		<option></option>
            <?php
                $sql = "SELECT sigla,nome FROM Turnos ORDER BY nome";
                $resultado = mysql_query($sql);

                while ($linha = mysql_fetch_array($resultado)) {
									$selected="";
                    if ($linha[0]==$aula)
	                    $selected="selected";
                    echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
                }

                $sql = "SELECT codigo,nome,date_format(inicio, '%H:%i'),date_format(fim, '%H:%i') FROM Horarios ORDER BY codigo";
                $resultado = mysql_query($sql);

                while ($linha = mysql_fetch_array($resultado)) {
	                $selected="";
                    if ($linha[1]==$aula)
	                    $selected="selected";
                    echo "<option $selected value='".crip($linha[1])."'>$linha[1] [$linha[2] - $linha[3]]</option>";
                }
            ?>
        </select>
        </td></tr>
        <tr><td align="right">Motivo:</td><td><input type="text" size="50" maxlength="255" name="campoMotivo" id="campoMotivo" value="<?php echo $motivo;?>" />
        </td></tr>
				<tr><td align="right">Tipo: </td><td>
				<input type="radio" id="campoTipo" <?php if ($tipo == 'A') print 'checked'; ?> name="campoTipo" value="A" />Abono &nbsp;
				<input type="radio" id="campoTipo" <?php if ($tipo == 'R') print 'checked'; ?> name="campoTipo" value="R" />Regime de Exerc&iacute;cios Domiciliares &nbsp;
				<input type="radio" id="campoTipo" <?php if ($tipo == 'M') print 'checked'; ?> name="campoTipo" value="M" />Matr&iacute;cula ap&oacute;s inicio letivo &nbsp;
        </td></tr>
        <tr><td></td><td>
	    <input type="hidden" name="opcao" value="InsertOrUpdate" />
		<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
			<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
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
    $resultado = mysql_query("SELECT count(*)
    							FROM FrequenciasAbonos f
    							WHERE date_format(f.data, '%Y') = '$ano' $restricao");
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

	$SITENAV = $SITE."?dataInicio=$dataInicio&dataFim=$dataFim";

require PATH . VIEW . '/paginacao.php';
?>
<table id="listagem" border="0" align="center">
    <tr><th width="80">Data</th><th width="80">Prontu&aacute;rio</th><th width="220">Aluno</th><th th width="200">Tipo</th><th>Aula ou Disciplina</th><th width="50">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT f.codigo, date_format(f.data, '%d/%m/%Y'), 
    		f.motivo, f.aula, f.tipo, p.nome, p.prontuario,
    		(SELECT d.nome FROM Atribuicoes a, Disciplinas d WHERE a.disciplina = d.codigo AND a.codigo = f.atribuicao)
    		FROM FrequenciasAbonos f, Pessoas p
    		WHERE f.aluno = p.codigo AND date_format(f.data, '%Y') = '$ano' $restricao
    		ORDER BY f.data DESC limit ". ($item - 1) . ",$itensPorPagina";
    //echo $sql;
    $resultado = mysql_query($sql);
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
      $i%2==0 ? $cdif="class='cdif'" : $cdif="";
      if ($linha[4] == 'A') $tipo = 'Abono';
      if ($linha[4] == 'R') $tipo = 'Regime de Exerc&iacute;cios Domiciliares';
      if ($linha[4] == 'M') $tipo = 'Matr&iacute;cula ap&oacute;s inicio letivo';
			$codigo = crip($linha[0]);
			if (!$linha[3]) $linha[3] = $linha[7];
      echo "<tr $cdif><td>$linha[1]</td><td>$linha[6]</td><td>".mostraTexto($linha[5])."</td><td><a href=\"#\" title=\"".mostraTexto($linha[2])."\">$tipo</a></td><td>$linha[3]</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td></tr>";
      $i++;
    }
    ?>
</table>


<script>
function atualizar(getLink){
  var dataInicio = $('#campoDataInicio').val();
  var dataFim = $('#campoDataFim').val();
  var aluno = $('#campoAluno').val();
  var atribuicao = $('#campoAtribuicao').val();
	var URLS = '<?php print $SITE; ?>?dataInicio='+ dataInicio +'&dataFim='+ dataFim +'&aluno='+ aluno +'&atribuicao='+ atribuicao;
	if (!getLink)
		$('#index').load(URLS + '&item=<?php print $item; ?>');
	else
		return URLS;
}

function valida() {
  if ( $('#campoDataInicio').val() != "" && $('#campoMotivo').val() != "" 
  && ( $('#campoAtribuicao').val() != "" || $('#campoAula').val() != "") ) {
	 	$('#salvar').enable();
  } else {
	  $('#salvar').attr('disabled', 'disabled');
  }
}

$(document).ready(function(){
	valida();
    $('#campoDataInicio, #campoMotivo, #campoAtribuicao, #campoAula').change(function(){
        valida();
    });

	$("#campoDataInicio, #campoDataFim").datepicker({
	    dateFormat: 'dd/mm/yy',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
	    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
	    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
	    monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
	    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
	    nextText: 'Próximo',
	    prevText: 'Anterior'
	});

	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )
				$('#index').load(atualizar(1) + '&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});

	$(".item-alterar").click(function(){
		var codigo = $(this).attr('id');
		$('#index').load(atualizar(1) + '&codigo=' + codigo);
	});

	<?php if (!$_GET["codigo"]) { ?>
 		$('#campoDataInicio, #campoDataFim, #campoAluno, #campoAtribuicao').change(function(){
			atualizar();
  		});
  	<?php } ?>
});
</script>

<?php
mysql_close($conexao);
?>