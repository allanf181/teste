<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela onde é possível a visualização dos dados pessoais dos docentes e discentes com prontuário ativo do Campus.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $nome = $_POST["campoNome"];
    $prontuario = $_POST["campoProntuario"];
    $senha = $_POST["campoSenha"];
    $cpf = $_POST["campoCPF"];
    $rg = $_POST["campoRG"];
    $nascimento = $_POST["campoNascimento"];
    $naturalidade = $_POST["campoNaturalidade"];
    $endereco = $_POST["campoEndereco"];
    $bairro = $_POST["campoBairro"];
    $cidade = $_POST["campoCidade"];
    $cep = $_POST["campoCEP"];
    $telefone = $_POST["campoTelefone"];
    $celular = $_POST["campoCelular"];
    $email = $_POST["campoEmail"];
    $observacoes = $_POST["campoObservacoes"];
    $tipo = $_POST["campoTipo"];
    $sexo=$_POST["campoSexo"];
    $raca=$_POST["campoRaca"];
    $estadoCivil=$_POST["campoEstadoCivil"];
    $numeroPessoasNaResidencia=$_POST["campoNumeroPessoasNaResidencia"];
    $renda=$_POST["campoRenda"];
    $situacaoTrabalho=$_POST["campoTrabalho"];
    $tipoTrabalho=$_POST["campoTipoTrabalho"];
    $empresaTrabalha=$_POST["campoEmpresaTrabalha"];
    $cargoEmpresa=$_POST["campoCargoEmpresa"];
    $tempo=$_POST["campoTempo"];
    $meioTransporte=$_POST["campoMeioTransporte"];
    $transporteGratuito=$_POST["campoTransporteGratuito"];
    $necessidadesEspeciais=$_POST["campoNecessidadesEspeciais"];
    $descricaoNecessidadesEspeciais=$_POST["campoDescricaoNecessidadesEspeciais"];
    $ano1g=$_POST["campoAno1g"];
    $escola1g=$_POST["campoEscola1g"];
    $escolaPublica=$_POST["campoEscolaPublica"];
	
    if (empty($codigo)){
    	$res = mysql_query("SELECT nome FROM Pessoas WHERE prontuario = '$prontuario'");
    	if (mysql_num_rows($res) == '') {
    		$sql = "insert into Pessoas values(0,'$nome','$prontuario',password('$senha'),'$cpf','$rg','$naturalidade', STR_TO_DATE('$nascimento','%d/%m/%Y'),'$endereco','$bairro','$cidade','$cep', '$telefone','$celular','$email', '$observacoes', '$foto', '$sexo', '$raca', '$estadoCivil', '$numeroPessoasNaResidencia', '$renda', '$situacaoTrabalho', '$tipoTrabalho', '$empresaTrabalha', '$cargoEmpresa', '$tempo', '$meioTransporte', '$transporteGratuito', '$necessidadesEspeciais', '$descricaoNecessidadesEspeciais','', '', '$ano1g', '$escola1g', '$escolaPublica', '', '', '')";
    		//echo $sql;
			$resultado = mysql_query($sql) ;
			$codigo = crip(mysql_insert_id());
	        if ($resultado==1) {
				mensagem('OK', 'TRUE_INSERT');
   				$COD = mysql_insert_id();
    	        $_GET["codigo"] = crip(mysql_insert_id());
	        } else {
				mensagem('NOK', 'FALSE_INSERT');
        	}
        } else {
			mensagem('NOK', 'PRONTUARIO_EXISTE');
        }
	} else {
    	if (!empty($senha)){
            $senha = ", senha=password('$senha'), dataSenha=now()";
      	}
      	
      	if (in_array($ALUNO, $tipo) || in_array($PROFESSOR, $tipo)) {
        	$sql = "update Pessoas set estadoCivil='$estadoCivil' $senha, numeroPessoasNaResidencia='$numeroPessoasNaResidencia', renda='$renda', situacaoTrabalho='$situacaoTrabalho', tipoTrabalho='$tipoTrabalho', empresaTrabalha='$empresaTrabalha', cargoEmpresa='$cargoEmpresa', tempo='$tempo', meioTransporte='$meioTransporte', transporteGratuito='$transporteGratuito', necessidadesEspeciais='$necessidadesEspeciais', descricaoNecessidadesEspeciais='$descricaoNecessidadesEspeciais', escolaPublica='$escolaPublica' where codigo=$codigo";
		} else {
	        $sql = "update Pessoas set nome='$nome' $senha, cpf='$cpf', rg='$rg', nascimento= STR_TO_DATE('$nascimento','%d/%m/%Y'), endereco='$endereco', bairro='$bairro', cidade='$cidade', cep='$cep', telefone='$telefone', celular='$celular', email='$email', observacoes='$observacoes', naturalidade='$naturalidade', sexo='$sexo', raca='$raca', estadoCivil='$estadoCivil', numeroPessoasNaResidencia='$numeroPessoasNaResidencia', renda='$renda', situacaoTrabalho='$situacaoTrabalho', tipoTrabalho='$tipoTrabalho', empresaTrabalha='$empresaTrabalha', cargoEmpresa='$cargoEmpresa', tempo='$tempo', meioTransporte='$meioTransporte', transporteGratuito='$transporteGratuito', necessidadesEspeciais='$necessidadesEspeciais', descricaoNecessidadesEspeciais='$descricaoNecessidadesEspeciais', ano1g='$ano1g', escola1g='$escola1g', escolaPublica='$escolaPublica' where codigo=$codigo";
		}
		//print $sql;		
		$resultado = mysql_query($sql);
    	$codigo = crip($_POST["campoCodigo"]);
        if ($resultado==1) {
			mensagem('OK', 'TRUE_UPDATE');
            $_GET["codigo"] = crip($_POST["campoCodigo"]); 
            $COD=$_POST["campoCodigo"];
        } else {
			mensagem('NOK', 'FALSE_UPDATE');
        }
    }
    
    if ($COD) {
	    //DEFININDO OS TIPOS DA PESSOA
		$resultado = mysql_query("SELECT tipo FROM PessoasTipos WHERE pessoa = $COD");
		while ($linha = mysql_fetch_array($resultado)){
			if (!in_array($linha[0], $tipo)) {
				mysql_query("DELETE FROM PessoasTipos WHERE pessoa = $COD and tipo =".$linha[0]);
			} else {
				$tipo_existe[] = $linha[0];
			}
		}
		
		foreach($tipo as $reg) {
			if (!in_array($reg, $tipo_existe)) {
				mysql_query("INSERT INTO PessoasTipos VALUES (NULL, $COD, $reg)");
			}
		}
	}
}

if ($_GET["opcao"] == 'delete') {
	$codigo = dcrip($_GET["codigo"]);
    $sql = "delete from Pessoas where codigo=$codigo";
    $resultado = mysql_query($sql);
    if ($resultado==1) {
		mensagem('OK', 'TRUE_DELETE');
    } else
		mensagem('NOK', 'FALSE_DELETE_DEP');
    $_GET["codigo"] = null;
    $_GET["nome"] = null;
    $_GET["prontuario"] = null;
}

if ($_GET["opcao"] == 'removeFoto') {
	$codigo = dcrip($_GET["codigo"]);
  $sql = "UPDATE Pessoas SET foto='' WHERE codigo=$codigo";
  print $sql;
  $resultado = mysql_query($sql);
  print_r($resultado);
  if ($resultado==1)
		mensagem('OK', 'TRUE_UPDATE');
  else
		mensagem('NOK', 'FALSE_UPDATE');
}
?>
<h2><?php print $TITLE; ?></h2>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>


<?php
	if ($_GET["opcao"] == 'validacao') {
		if ($_GET["codigo"]) {
			$erro=0;
			$validados = explode(',', $_GET["codigo"]);
			foreach($validados as $valido) {
      	$sql = "UPDATE Pessoas SET bloqueioFoto='' WHERE codigo=$valido";
				if (!$resultado = mysql_query($sql))
					$erro=1;
			}
			if ($erro == 0)
		    mensagem('OK', 'TRUE_UPDATE');
			else
	    	mensagem('NOK', 'NOT_SELECT');
    }
	?>
  <table align="center" id="form" width="100%">
	<tr>
	<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Voltar</a></td>
	<td align="right"><input type="submit" name="liberar" id="liberar" value="Liberar">
	</tr>
	</table>
 
  <table id="form" border="0" align="center" width="100%">
	<tr><th align="center" width="40">#</th><th align="left">Aluno</th><th align="left">Foto</th>
	<th width="40" align="center"><input type='checkbox' checked id='select-all' name='select-all' class='campoTodos' value='' /></th></tr>
	<?php
	// efetuando a consulta para listagem
	$sql = "SELECT p.codigo, p.nome, bloqueioFoto
		    FROM Pessoas p
		    WHERE bloqueioFoto = 1
		    ORDER BY p.nome";
	//echo $sql;
	$resultado = mysql_query($sql);
	$i = 1;
	if ($resultado){
    while ($linha = mysql_fetch_array($resultado)) {
			$i%2==0 ? $cdif="class='cdif'" : $cdif="";
			echo "<tr $cdif><td align='center'>$i</td>";
			echo "<td align=left>$linha[1]</td>";
			echo "<td align=left><img alt=\"foto\" width=\"100\" src=\"".INC."/file.inc.php?type=pic&force=".crip('1')."&id=".crip($linha[0])."\" /></td>";
			if ($linha[2]) $bloqueado = 'checked';
			echo "<td align='center'>";
			print "<input $bloqueado type='checkbox' id='bloqueioFoto' name='bloqueioFoto[]' value='".$linha[0]."' />";
			echo "</td></tr>";
			$i++;
		}
		print "</form>";
	}
	?>
	</table>
  <script>
  $('#select-all').click(function(event) {
			if(this.checked) {
	    	// Iterate each checkbox
	    	$(':checkbox').each(function() {
					this.checked = true;
	  	  });
			} else {
	    	$(':checkbox').each(function() {
				this.checked = false;
			});
		}
  });

  $(document).ready(function(){
		$('#liberar').click(function(event) {
		var codigo = $(this).attr('id');
		jConfirm('Deseja prosseguir com o desbloqueio?', '<?php print $TITLE; ?>', function(r) {
			if ( r ) {
				var validado = $.map($('input:checkbox:checked'), function(e,i) {
			    return +e.value;
				});
				$('#index').load('<?php print $SITE; ?>?opcao=validacao&codigo=' + validado);
			}
			});
		});
	}); 
	</script>	
	
	<?php	
	die;
}

  
    $codigo="";
    $nome="";
    $prontuario="";
    $senha="";
    $cpf="";
    $rg="";
    $estadoNaturalidade="";
    $naturalidade="";
    $nascimento="";
    $endereco="";
    $bairro="";
    $estado="";
    $cidade="";
    $cep="";
    $telefone="";
    $celular="";
    $email="";
    $observacoes="";
    $tipo="";
    $pesquisa="";
    $restricao="";
    $sexo="";
    $raca="";
    $estadoCivil="";
    $numeroPessoasNaResidencia="";
    $renda="";
    $situacaoTrabalho="";
    $tipoTrabalho="";
    $empresaTrabalha="";
    $cargoEmpresa="";
    $tempo="";
    $meioTransporte="";
    $transporteGratuito="";
    $necessidadesEspeciais="";
    $descricaoNecessidadesEspeciais="";
    $ano1g="";
    $escola1g="";
    $escolaPublica;

    if (!empty ($_GET["codigo"])){ // se o parÃƒÂ¢metro nÃƒÂ£o estiver vazio
        $codigo = dcrip($_GET["codigo"]);
        
        // consulta no banco
        $sql = "select codigo, nome, prontuario, senha, cpf, rg, date_format(nascimento, '%d/%m/%Y') nascimento, 
            endereco, bairro, cidade,cep, telefone, celular, email, observacoes, naturalidade, 
            sexo,raca,estadoCivil,numeroPessoasNaResidencia, renda,situacaoTrabalho,tipoTrabalho,empresaTrabalha,
            cargoEmpresa,tempo,meioTransporte,transporteGratuito,necessidadesEspeciais, descricaoNecessidadesEspeciais,
            ano1g, escola1g, escolaPublica
            from Pessoas where codigo=".$codigo;
       	//print $sql;
        $resultado = mysql_query($sql);
        $linha = mysql_fetch_row($resultado);

        // armazena os valores nas variÃƒÂ¡veis
        $codigo = $linha[0];
        $nome = $linha[1];
        $prontuario = $linha[2];
        $senha = "";
        $cpf = $linha[4];
        $rg = $linha[5];
        $nascimento = $linha[6];
        $endereco = $linha[7];
        $bairro = $linha[8];
        $cidade = $linha[9];
        $cep = $linha[10];
        $telefone = $linha[11];
        $celular = $linha[12];
        $email = $linha[13];
        $observacoes = $linha[14];      
        $naturalidade = $linha[15];
        $sexo=$linha[16];
        $raca=$linha[17];
        $estadoCivil=$linha[18];
        $numeroPessoasNaResidencia=$linha[19];
        $renda=$linha[20];
        $situacaoTrabalho=$linha[21];
        $tipoTrabalho=$linha[22];
        $empresaTrabalha=$linha[23];
        $cargoEmpresa=$linha[24];
        $tempo=$linha[25];
        $meioTransporte=$linha[26];
        $transporteGratuito=$linha[27];
        $necessidadesEspeciais=$linha[28];
        $descricaoNecessidadesEspeciais=$linha[29];
        $ano1g=$linha[30];
        $escola1g=$linha[31];
        $escolaPublica=$linha[32];
        $restricao = " and p.codigo=".dcrip($_GET["codigo"]);
        
        $sql = "SELECT estado FROM Cidades WHERE codigo = $cidade";
    		$result = mysql_query($sql);
    		$estado = @mysql_fetch_object($result);

        $sql = "SELECT estado FROM Cidades WHERE codigo = $naturalidade";
    		$result = mysql_query($sql);
    		$estadoNaturalidade = @mysql_fetch_object($result);
    		   
        $tipo = getTipoPessoa($codigo);
   		if (in_array($ALUNO, $tipo) || in_array($PROFESSOR, $tipo))
   			$NOT_PERM = 1;

    }
    
	if ($_GET["pesquisa"] == 1) {
		$_GET["prontuario"] = crip($_GET["prontuario"]);
		$_GET["nome"] = crip($_GET["nome"]);
	}
	
    if (dcrip($_GET["prontuario"])){
	    $prontuario=dcrip($_GET["prontuario"]);
		$restricao .= " and p.prontuario like '%".$prontuario."%'";
    }
   
    if (dcrip($_GET["nome"])){
        $nome=dcrip($_GET["nome"]);
		$restricao .= " and p.nome like '%".$nome."%'";
    }

?>
<script>
$(document).ready(function(){  

	$(function(){
		$('#campoEstadoNaturalidade').change(function(){
			if( $(this).val() ) {
				$('#campoNaturalidade').hide();
				$('.carregando').show();
				$.getJSON('cidade.php?search=',{codigo: $(this).val(), ajax: 'true', ajaxCidade:1}, function(j){
					var options = '<option value=""></option>';	
					for (var i = 0; i < j.length; i++) {
						options += '<option value="' + j[i].codigo + '">' + j[i].nome + '</option>';
					}	
					$('#campoNaturalidade').html(options).show();
					$('.carregando').hide();
				});
			} else {
				$('#campoNaturalidade').html('<option value="">-- Escolha um estado --</option>');
			}
		});
	});

	$(function(){
		$('#campoEstado').change(function(){
			if( $(this).val() ) {
				$('#campoCidade').hide();
				$('.carregando').show();
				$.getJSON('cidade.php?search=',{codigo: $(this).val(), ajax: 'true', ajaxCidade:1}, function(j){
					var options = '<option value=""></option>';	
					for (var i = 0; i < j.length; i++) {
						options += '<option value="' + j[i].codigo + '">' + j[i].nome + '</option>';
					}	
					$('#campoCidade').html(options).show();
					$('.carregando').hide();
				});
			} else {
				$('#campoCidade').html('<option value="">-- Escolha um estado --</option>');
			}
		});
	});
	
});
</script>
<link rel="stylesheet" type="text/css" href="<?php print VIEW; ?>/css/aba.css" media="screen" />
<script src="<?php print VIEW; ?>/js/aba.js"></script>

<?php
print "<script>\n";
print "    $('#form_padrao').html5form({ \n";
print "        method : 'POST', \n";
print "        action : '$SITE', \n";
print "        responseDiv : '#index', \n";
print "        colorOn: '#000', \n";
print "        colorOff: '#000', \n";
print "        messages: 'br' \n";
print "    }) \n";
print "</script>\n";
print "<div id=\"html5form\" class=\"main\">\n";
print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\" enctype=\"multipart/form-data\">\n";
?>
<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
<input type="hidden" name="opcao" value="InsertOrUpdate" /> 
<ul class="tabs">
	<li><a href="#Dados">Cadastro</a></li>
	<li><a href="#Dados2">Contato</a></li>
	<li><a href="#Dados3">SocioEcon&ocirc;mico</a></li>
	<li><a href="#Dados4">Tipos</a></li>
	<li><a href="#Dados5">Foto</a></li>
	<li><a href="#Dados6">Desbloqueio de Fotos</a></li>
</ul>
<div class="tab_container" id="form">
<div class="cont_tab" id="Dados">
<table border="0">
  <tr>
    <td align="right">Nome: </td>
    <td><input type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 250pt" id="campoNome" name="campoNome" maxlength="45" value="<?php echo $nome; ?>"/>
    	<a href="#" id="setNome" title="Buscar"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' /></a>
  </tr>
  <tr>
    <td align="right">Prontuario: </td>
    <td><input type="text" <?php if ($codigo) print "readonly"; ?> id="campoProntuario" name="campoProntuario" maxlength="45" value="<?php echo $prontuario; ?>"/>
        <a href="#" id="setProntuario" title="Buscar"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' /></a>
  </tr>
  <tr>
    <td align="right">Senha: </td>
    <td><input type="password" name="campoSenha" id="campoSenha1" maxlength="20" value="<?php echo $senha; ?>"/></td>
  </tr>
  <tr>
    <td align="right">Estado: </td>
    <td>
    	<select <?php if ($NOT_PERM) print "disabled"; ?> name="campoEstado" id="campoEstado" value="<?php echo $estado->estado; ?>">
    	<option></option>
      <?php
         $resultado = mysql_query("select * from Estados order by nome");
         while ($linha = mysql_fetch_array($resultado)){
			$selected="";
            if ($linha[0]==$estado->estado)
                $selected="selected";
            echo "<option $selected value='$linha[0]'>$linha[1]</option>";
         }
     ?>
    </select>
    Cidade: <select <?php if ($NOT_PERM) print "disabled"; ?> name="campoCidade" id="campoCidade" value="<?php echo $cidade; ?>">
        <?php
         $resultado = mysql_query("select * from Cidades where estado=$estado->estado order by nome");
         while ($linha = mysql_fetch_array($resultado)){
         	$selected="";
             if ($linha[0]==$cidade)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
         }
     ?>
	</td>
  </tr>
  <tr>
    <td align="right">Email: </td>
    <td><input type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 300pt" name="campoEmail" maxlength="100" value="<?php echo $email; ?>"/></td>
  </tr>
	<tr><td></td><td>
	<input type="hidden" name="opcao" value="InsertOrUpdate" />
	<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
		<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
	</tr></table>
	</td></tr>
</table>
</div>

<div class="cont_tab" id="Dados2">
	<table width="492" border="0">
  <tr>
    <td width="70" align="right">CPF: </td>
    <td width="406"><input id="cpf" <?php if ($NOT_PERM) print "readonly"; ?> type="text" name="campoCPF" maxlength="14" value="<?php echo $cpf; ?>"/></td>
    <td align="right">RG: </td>
    <td><input type="text" size="20" <?php if ($NOT_PERM) print "readonly"; ?> name="campoRG" maxlength="14" value="<?php echo $rg; ?>"/></td>
  </tr>
  <tr>
    <td align="right">Naturalidade: </td>
    <td><select <?php if ($NOT_PERM) print "disabled"; ?> name="campoEstadoNaturalidade" id="campoEstadoNaturalidade" value="<?php echo $estadoNaturalidade->estado; ?>">
    	<option></option>
      <?php
         $resultado = mysql_query("select * from Estados order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$estadoNaturalidade->estado)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }

     ?>
      </select>
      <select <?php if ($NOT_PERM) print "disabled"; ?> name="campoNaturalidade" id="campoNaturalidade" value="<?php echo $naturalidade; ?>">
        <?php
         $resultado = mysql_query("select * from Cidades where estado=$estadoNaturalidade->estado order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$naturalidade)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
      </select></td>
    <td align="right">Nascimento: </td>
    <td><input id="date" <?php if ($NOT_PERM) print "readonly"; ?> type="text" style="width: 80pt" name="campoNascimento" maxlength="12" value="<?php echo $nascimento; ?>"/></td>
  </tr>
  <tr>
    <td align="right">Endere&ccedil;o: </td>
    <td><input type="text" style="width: 260pt" <?php if ($NOT_PERM) print "readonly"; ?> name="campoEndereco" maxlength="45" value="<?php echo $endereco; ?>"/></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right">Bairro: </td>
    <td><input type="text" style="width: 200pt" <?php if ($NOT_PERM) print "readonly"; ?> name="campoBairro" maxlength="45" value="<?php echo $bairro; ?>"/></td>
    <td align="right">CEP: </td>
    <td><input id="cep" type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 80pt" name="campoCEP" maxlength="10" value="<?php echo $cep; ?>"/></td>
  </tr>
  <tr>
    <td align="right">Telefone: </td>
    <td><input id="phone" type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 80pt" name="campoTelefone" maxlength="12" value="<?php echo $telefone; ?>"/></td>
    <td align="right">Celular: </td>
    <td><input id="celular" type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 80pt" name="campoCelular" maxlength="12" value="<?php echo $celular; ?>"/></td>
  </tr>
  <tr>
    <td align="right">Observa&ccedil;&otilde;es: </td>
    <td><textarea name="campoObservacoes" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 300pt" rows="5"><?php echo $observacoes; ?></textarea></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
	<tr><td></td><td>
	<input type="hidden" name="opcao" value="InsertOrUpdate" />
	<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
		<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
	</tr></table>
	</td></tr>
</table>
</div>

<div class="cont_tab" id="Dados3">
<table width="772" border="0">
  <tr>
    <td width="152" align="right">Estado Civil: </td>
    <td width="215"><select style="width:200px;" id="campoEstadoCivil" name="campoEstadoCivil" value="<?php echo $estadoCivil; ?>">
      <option value="">N&atilde;o declarado</option>
      <?php
         $resultado = mysql_query("select * from EstadosCivis order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$estadoCivil)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
    <td width="152" align="right">Sexo: </td>
    <td width="215"><select style="width:200px;" <?php if ($NOT_PERM) print "disabled"; ?> id="campoSexo" name="campoSexo" value="<?php echo $sexo; ?>">
      <option value=""></option>
      <?php
         $resultado = mysql_query("select * from Sexos order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$sexo)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
  </tr>
  <tr>
    <td align="right">Renda: </td>
    <td><select id="campoRenda" style="width:200px;" name="campoRenda" value="<?php echo $renda; ?>">
      <option value="">N&atilde;o declarado</option>
      <?php
         $resultado = mysql_query("select * from Rendas order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$renda)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
    <td align="right">Ra&ccedil;a: </td>
    <td><select id="campoRaca" style="width:200px;" name="campoRaca" value="<?php echo $raca; ?>">
      <option value=""></option>
      <?php
         $resultado = mysql_query("select * from Racas order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$raca)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
  </tr>
  <tr>
    <td align="right">Trabalho: </td>
    <td><select id="campoTrabalho" style="width:200px;" name="campoTrabalho" value="<?php echo $situacaoTrabalho; ?>">
      <option value="">N&atilde;o declarado</option>
      <?php
         $resultado = mysql_query("select * from SituacoesTrabalho order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$situacaoTrabalho)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
    <td align="right">Tempo: </td>
    <td><select id="campoTempo" style="width:200px;" name="campoTempo" value="<?php echo $tempo; ?>">
      <option value="">N&atilde;o declarado</option>
      <?php
         $resultado = mysql_query("select * from TemposPesquisa order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$tempo)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
  </tr>
  <tr>
    <td align="right">Empresa: </td>
    <td><input type="text" size="20" name="campoEmpresaTrabalha" maxlength="45" value="<?php echo $empresaTrabalha; ?>"/></td>
    <td align="right">Cargo: </td>
    <td><input type="text" size="20" name="campoCargoEmpresa" maxlength="45" value="<?php echo $cargoEmpresa; ?>"/></td>
  </tr>
  <tr>
    <td align="right">Tipo: </td>
    <td><select id="campoTipoTrabalho" style="width:200px;" name="campoTipoTrabalho" value="<?php echo $tipoTrabalho; ?>">
      <option value="">N&atilde;o declarado</option>
      <?php
         $resultado = mysql_query("select * from TiposTrabalho order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$tipoTrabalho)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
    <td align="right">N&ordm; Pessoas Resid&ecirc;ncia: </td>
    <td><input type="text" size="2" name="campoNumeroPessoasNaResidencia" maxlength="4" value="<?php echo $numeroPessoasNaResidencia; ?>"/></td>
  </tr>
  <tr>
    <td align="right">Meio transporte: </td>
    <td><select id="campoMeioTransporte" style="width:200px;" name="campoMeioTransporte" value="<?php echo $meioTransporte; ?>">
      <option value="">N&atilde;o declarado</option>
      <?php
         $resultado = mysql_query("select * from MeiosTransporte order by nome");
         $selected=""; // controla a altera&ccedil;&atilde;o no campo select
         while ($linha = mysql_fetch_array($resultado)){
             if ($linha[0]==$meioTransporte)
                $selected="selected";
             echo "<option $selected value='$linha[0]'>$linha[1]</option>";
             $selected="";
         }
     ?>
    </select></td>
    <td align="right">Necessidades Especiais: </td>
    <td><select id="campoNecessidadesEspeciais" style="width:200px;" name="campoNecessidadesEspeciais" value="<?php echo $necessidadesEspeciais; ?>">
      <option value="">N&atilde;o declarado</option>
      <option <?php echo ($necessidadesEspeciais=='n')?"selected='selected'":""; ?> value='n'>N&atilde;o</option>
      <option <?php echo ($necessidadesEspeciais=='s')?"selected='selected'":""; ?> value='s'>Sim</option>
    </select></td>
  </tr>
  <tr>
    <td align="right">Transporte Gratuito: </td>
    <td><select id="campoTransporteGratuito" name="campoTransporteGratuito" value="<?php echo $transporteGratuito; ?>">
      <option value="">N&atilde;o declarado</option>
      <option <?php echo ($transporteGratuito=='n')?"selected='selected'":""; ?> value='n'>N&atilde;o</option>
      <option <?php echo ($transporteGratuito=='s')?"selected='selected'":""; ?> value='s'>Sim</option>
    </select></td>
    <td align="right">Necessidades Especiais: </td>
    <td><textarea name="campoDescricaoNecessidadesEspeciais"><?php echo $descricaoNecessidadesEspeciais; ?></textarea></td>
  </tr>
  <tr>
    <td align="right">Ano 1G: </td>
    <td><input type="text" <?php if ($NOT_PERM) print "readonly"; ?> size="10" name="campoAno1g" maxlength="4" value="<?php echo $ano1g; ?>"/></td>
    <td align="right">Escola 1G: </td>
    <td><input type="text" <?php if ($NOT_PERM) print "readonly"; ?> size="20" name="campoEscola1g" maxlength="145" value="<?php echo $escola1g; ?>"/></td>
  </tr>
	<tr>
	<td>&nbsp;</td><td>&nbsp;</td><td align="right">Escola P&uacute;blica: </td>
    <td><select id="campoEscolaPublica" name="campoEscolaPublica" value="<?php echo $escolaPublica; ?>">
      <option value="">N&atilde;o declarado</option>
      <option <?php echo ($escolaPublica=='n')?"selected='selected'":""; ?> value='n'>N&atilde;o</option>
      <option <?php echo ($escolaPublica=='s')?"selected='selected'":""; ?> value='s'>Sim</option>
    </select></td></tr>
	<tr><td colspan="4">
	<input type="hidden" name="opcao" value="InsertOrUpdate" />
	<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
		<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
	</tr></table>    
</table>
</div>

<div class="cont_tab" id="Dados4">
<script>
$(document).ready(function(){
	$('#campoTipo option').prop('selected', true);

	$("#btnLeft").click(function () {  		
   		var selectedItem = $("#rightValues option:selected");
    	$("#campoTipo").append(selectedItem);
		$('#campoTipo option').prop('selected', true);
	});
	
	$("#btnRight").click(function () {
	    var selectedItem = $("#campoTipo option:selected");
	    $("#rightValues").append(selectedItem);
		$('#campoTipo option').prop('selected', true);
	});
	
	$("#rightValues").change(function () {
	    var selectedItem = $("#rightValues option:selected");
	    $("#txtRight").val(selectedItem.text());
	});
});
</script>
<table width="100%">
	<tr><td>Aten&ccedil;&atilde;o: n&atilde;o remova o tipo de um aluno ou professor, pois o sistema n&atilde;o permite incluir esses tipos.</td></tr>
</table>
<table width="60%">
	<tr><td>&nbsp;<br>
	<tr><td>Tipos Inseridos<br>
        <select id="campoTipo" size="6" multiple name="campoTipo[]" style="width: 200px;">
        <?php
		if (!in_array($ADM, $_SESSION["loginTipo"]))
	    	$restricaoADM = 'WHERE codigo <> (SELECT adm FROM Instituicoes)';
         $resultado = mysql_query("SELECT codigo,nome FROM Tipos $restricaoADM ORDER BY nome");
         while ($linha = mysql_fetch_array($resultado)){
            if (in_array($linha[0], $tipo) ) {
                echo "<option value='$linha[0]'>$linha[1]</option>";
			} else {
				if ($linha[0] != $ALUNO && $linha[0] != $PROFESSOR)
					$TPS[$linha[0]] = $linha[1];
			}
         }
		?>
        </select>
    </td>
    <td>
        <input type="text" id="btnLeft" readonly <?php if (in_array($ALUNO, $tipo)) print "disabled"; ?> style="width: 20px;" value="&lt;&lt;" /><br>
        <input type="text" id="btnRight" readonly <?php if (in_array($ALUNO, $tipo)) print "disabled"; ?> style="width: 20px;" value="&gt;&gt;" />
    </td>
    <td>Todos os Tipos<br>
	<select id="rightValues" size="6" multiple style="width: 200px;">
    <?php
		foreach($TPS as $TP_COD => $TP_NM)
	        echo "<option value='$TP_COD'>$TP_NM</option>";
	?>
    </select>
    </tr></table>
<table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
	<td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
</tr></table>
</div>
</form>

<div class="cont_tab" id="Dados5">
<div id="retorno"></div>

<?php
$max_file = ini_get('post_max_size') * 1024 * 1024;
	
if (!$codigo) {
	print "<font size=\"2\">Aten&ccedil;&atilde;o: as fotos podem ser inseridas de 3 formas: </font><br>\n";
	print "<font size=\"1\">1 - Selecionando um usu&aacute;rio de cada vez e carregando sua foto.</font><br>\n";
	print "<font size=\"1\">2 - Carregar uma foto sem selecionar o usu&aacute;rio, mas o nome da foto precisa seguir o padr&atilde;o: XXXXXXX.JGP (XXXXX - Prontu&aacute;rio, s&atilde;o aceitas as extens&otilde;es: JPG, GIF e PNG)</font><br>\n";
	print "<font size=\"1\">3 - Com um arquivo ZIP contendo todas as fotos com o padr&atilde;o: XXXXXXX.JGP</font><br>\n";
	print "<br>\n";
}	
print "Tamanho m&aacute;ximo do arquivo: ".ini_get('post_max_size')."<br>";
?>
       
	<script type="text/javascript" src="js/jquery.form.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() { 
    
    	$('#imageInput').change(function(){
       		$('#MyUploadForm').submit();
    	});
    
		var options = { 
			target:   '#output, <?php if (!$codigo) print "#retorno"; ?>',   // target element(s) to be updated with server response 
			beforeSubmit:  beforeSubmit,  // pre-submit callback 
			success:       afterSuccess,  // post-submit callback 
			resetForm: true        // reset the form after successful submit 
		}; 
		
		$('#MyUploadForm').submit(function() { 
			$(this).ajaxSubmit(options);  			
			// always return false to prevent standard browser submit and page navigation 
			return false; 
		}); 
}); 

function afterSuccess()
{
	$('#submit-btn').show(); //hide submit button
	$('#loading-img').hide(); //hide submit button
	$("#divFoto").attr("src", "<?php print INC; ?>/file.inc.php?type=pic&id=<?php print crip($codigo); ?>&timestamp=" + new Date().getTime());

}

//function to check file size before uploading.
function beforeSubmit(){
    //check whether browser fully supports all File API
   if (window.File && window.FileReader && window.FileList && window.Blob)
	{
		if( !$('#imageInput').val()) //check empty input filed
		{
			$("#retorno").html("Foto não selecionada!");
			return false
		}
		
		var fsize = $('#imageInput')[0].files[0].size; //get file size
		var ftype = $('#imageInput')[0].files[0].type; // get file type
		
		//Allowed file size is less than 1 MB (1048576)
		if(fsize><?php print $max_file; ?>) 
		{
			$("#retorno").html("<b>"+bytesToSize(fsize) +"</b> Imagem muito grande, utilize um editor para diminuir o tamanho da foto!");
			return false
		}
				
		$('#submit-btn').hide(); //hide submit button
		$('#loading-img').show(); //hide submit button
		$("#retorno").html("");  
	}
	else
	{
		//Output error to older unsupported browsers that doesn't support HTML5 File API
		$("#retorno").html("Por favor, atualize seu browser para suportar essa função!");
		return false;
	}
}

//function to format bites bit.ly/19yoIPO
function bytesToSize(bytes) {
   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
   if (bytes == 0) return '0 Bytes';
   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

</script>
<div id="upload-wrapper">
<?php if ($codigo) 
	print "<img id=\"divFoto\" style=\"width: 200px; height: 200px\" src='".INC."/file.inc.php?type=pic&id=".crip($codigo)."&timestamp=".time()."' />\n";
?>
<div align="center">
    <form action="<?php print INC; ?>/processupload.inc.php" method="post" enctype="multipart/form-data" id="MyUploadForm" style="text-align: left">
    <input type="hidden" name="codigo" value="<?php print $codigo; ?>"/>
    <input name="ImageFile" id="imageInput" type="file" accept="application/zip,image/*" />

   	<br><br><a href="javascript:$('#index').load('<?php print "$SITE?opcao=removeFoto&codigo=".crip($codigo).""; ?>'); void(0);">Remover Foto</a>
</form>
</div>
</div>


<table width="100%"><tr><td>&nbsp;</td>
	<td align="right"><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar
</a></td>
</tr></table>
</div>

<div class="cont_tab" id="Dados6">
<?php
	$sql = "SELECT p.codigo, p.nome, bloqueioFoto
		    FROM Pessoas p
		    WHERE bloqueioFoto = 1
		    ORDER BY p.nome";
	//echo $sql;
	$resultado = mysql_query($sql);
?>
<table width="100%"><tr><td>&nbsp;</td>
	<td>Fotos bloqueadas: <?php print mysql_num_rows($resultado); ?> 
	<br><br>
		<a href="javascript:$('#index').load('<?php print $SITE."?opcao=validacao"; ?>'); void(0);">Visualizar Fotos</a>
	</td>
</tr></table>
</div>

</div>
</div>
<?php

    // inicializando as variÃƒÂ¡veis
    $item = 1;
    $itensPorPagina = 50;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a pÃƒÂ¡gina atual
    if (!empty($_GET["item"])){
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a pÃƒÂ¡gina anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

    // descobrindo a quantidade total de registros
   	$sql = "Select COUNT(*) 
    		from Pessoas p WHERE 1
    		$restricao";
    $resultado = mysql_query($sql);
    $linha = mysql_fetch_row($resultado);
    //echo "linha: $linha[0] <br >";
    $ultimo = ($linha[0]);

    // validando o prÃƒÂ³ximo item
    if ($proximo > $ultimo){
        $proximo = $item;
        $ultimo = $item;
    }

    // validando o ÃƒÂºltimo item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;
    
//    echo "ultimo: $ultimo <br>";

$SITENAV = $SITE."?nome=".crip($nome)."&prontuario=".crip($prontuario);

require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
    <tr><th align="left" width="80">Prontu&aacute;rio</th><th align="left">Nome</th><th align="left">E-mail</th><th align="left" width="170">Tipo</th><th align="center" width="40">A&ccedil;&atilde;o</th></tr>
    <?php
    $sql="SELECT codigo, nome From Tipos ORDER BY nome";
    $resultado = mysql_query($sql);
    while ($linha = mysql_fetch_array($resultado))
    	$TP[$linha[0]] = $linha[1];
    
    // efetuando a consulta para listagem
    $sql="SELECT p.codigo, p.nome, p.email, p.prontuario, p.nome 
    		FROM Pessoas p WHERE 1 $restricao
    		ORDER BY p.nome limit " . ($item - 1) . ",$itensPorPagina";
    $resultado = mysql_query($sql);
   	//echo $sql;
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i%2==0 ? $cdif="class='cdif'" : $cdif="";
        $tp = "|";
        foreach(getTipoPessoa($linha[0]) as $tc => $tn)
        	$tp .= $TP[$tn]." | ";

        if (strlen($tp)>20)
            $tp = "<a href='#' title='$tp'>".abreviar ($tp, 20)."</a>";
       
		if ($codigo) $output = "id='output'";
       
        print "<tr $cdif><td align='left'>$linha[3]</td><td><div $output style='float: left; margin-right: 5px'><a href='#' rel='".INC."/file.inc.php?type=pic&id=".crip($linha[0])."&timestamp=".time()."' class='screenshot' title='".mostraTexto($linha[1])."'>\n";
        print "<img style='width: 20px; height: 20px' alt='Embedded Image' src='".INC."/file.inc.php?type=pic&id=".crip($linha[0])."&timestamp=".time()."' /></a></div>".mostraTexto($linha[1])."</td>\n";
        print "<td>$linha[2]</td><td>$tp</td>\n";
  		if ( (in_array($ADM, $_SESSION["loginTipo"])) || (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($ADM, $tipo) )) {
	        print "<td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='".ICONS."/config.png' /></a></td></tr>";
		} else {
			print "<td align=\"center\">&nbsp;</td>\n";
		}
        $i++;
    }
    mysql_close($conexao);
    ?>
</table>

 <?php require(PATH.VIEW.'/navegacao.php'); ?>

<script>
	
function valida() {
	if ( $('#campoNome').val() == "" || 
		<?php if (!$codigo) print "$('#campoSenha1').val() == \"\" || "; ?>
		$('#campoProntuario').val() == "" ) {
        $('#salvar').attr('disabled', 'disabled');
    } else {
        $('#salvar').enable();
    }
}

function atualizar(getLink){
    var nome = encodeURIComponent($('#campoNome').val());
    var prontuario = encodeURIComponent($('#campoProntuario').val());
    var URLS = '<?php print $SITE; ?>?nome=' + nome + '&prontuario=' + prontuario;
	if (!getLink)
		$('#index').load(URLS + '&pesquisa=1&item=<?php print $item; ?>');
	else
		return URLS;
}
$(document).ready(function(){
    valida();
    $('#campoNome, #campoProntuario, #campoSenha1').keyup(function(){
        valida();
    });
	
	$("#cpf").mask("999.999.999-99");
	$("#phone").mask("(99) 9999-9999");
	$("#celular").mask("(99) 99999-9999");
	$("#cep").mask("99.999-999");

	$("#date").datepicker({
	    dateFormat: 'dd/mm/yy',
	    dayNames: ['Domingo','Segunda','TerÃ§a','Quarta','Quinta','Sexta','SÃ¡bado'],
	    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
	    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','SÃ¡b','Dom'],
	    monthNames: ['Janeiro','Fevereiro','MarÃ§o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
	    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
	    nextText: 'PrÃ³ximo',
	    prevText: 'Anterior'
	});
		
	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load(atualizar(1) + '&pesquisa=1&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});

	$(".item-alterar").click(function(){
		var codigo = $(this).attr('id');
		$('#campoNome').val('');
    	$('#campoProntuario').val('');
		$('#index').load(atualizar(1) + '&pesquisa=1&codigo=' + codigo + '&item=<?php print $item; ?>');
	});
	
   	$('#setNome, #setProntuario').click(function(){
    	atualizar();
	});
});    
</script>