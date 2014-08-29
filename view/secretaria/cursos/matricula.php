<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Lista a situação do aluno com relação as disciplinas cursadas por ele.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_GET["opcao"] == 'delete') {
   	$codigo = dcrip($_GET["codigo"]);
    $sql = "delete from Matriculas where codigo=$codigo";
    $r = mysql_query($sql);
	if ($r == 1)
		mensagem('OK', 'TRUE_DELETE');
   	else
		mensagem('INFO', 'DELETE');

	$_GET["prontuario"] = '';
	$_GET["codigo"] = '';
}

// inicializando as variáveis do formulário
$codigo = "";
$aluno = "";
$atribuicao = "";
$situacao = "";
$prontuario = "";
$Nprontuario = "";
$nome = "";
$Nnome = "";
$turma = "";
$restricao = "";
$restricaoTurma = "";

if (dcrip($_GET["turma"]) != '') {
   	$turma = dcrip($_GET["turma"]);
   	if (!empty($turma))
    	$restricaoTurma = " and t.codigo=$turma";
}

if (dcrip($_GET["atribuicao"]) != '') {
   	$atribuicao = dcrip($_GET["atribuicao"]);
   	$restricao.=" and a.codigo=$atribuicao";
}

if ($_GET["pesquisa"] == 1) {
	$_GET["nome"] = crip($_GET["nome"]);
	$_GET["prontuario"] = crip($_GET["prontuario"]);
}

if (dcrip($_GET["prontuario"]) != '') {
   	$prontuario = " and p.prontuario='" . dcrip($_GET["prontuario"]) . "'";
   	$Nprontuario = dcrip($_GET["prontuario"]);
   	$restricao = " and p.prontuario='" . dcrip($_GET["prontuario"]) . "'";
}

if (dcrip($_GET["nome"]) != '') {
   	$Nnome = dcrip($_GET["nome"]);
   	$nome = " and p.nome like '%" . dcrip($_GET["nome"]) . "%'";
}

if (dcrip($_GET["bimestre"]) != '') {
   	$bimestre = dcrip($_GET["bimestre"]);
   	$restricao.=" and a.bimestre=$bimestre";
}

?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<?php
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
    <table align="center" width="100%" id="form" border="0">
    <input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
    <tr><td align="right" style="width: 100px">Turma: </td><td>
    <select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>">
    <option></option>
    <?php
      $resultado = mysql_query("SELECT t.codigo, t.numero, c.nome, m.nome, m.codigo, c.codigo
	                        							FROM Turmas t, Cursos c, Modalidades m
	                        							WHERE t.curso = c.codigo 
	                        							AND m.codigo = c.modalidade
	                        							AND ano = $ano 
	                        							AND (semestre=$semestre OR semestre=0) 
	                        							ORDER BY c.nome, t.numero");
                        $selected = ""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)) {
                            if ($linha[0] == $turma)
                                $selected = "selected";
                     				if ($linha[4] < 1000 || $linha[4] >= 2000) $linha[2] = "$linha[2] [$linha[3]]";				
	                          echo "<option $selected value='".crip($linha[0])."'>[$linha[1]] $linha[2] ($linha[5])</option>";
                            $selected = "";
                        }
                        ?>
                    </select>
                </td></tr>
            <tr><td align="right">Bimestre: </td><td>
                <select name="campoBimestre" id="campoBimestre">
                    <option></option>
                    <option <?php if ($bimestre=='0') echo "selected='selected'"; else echo ""; ?> value="<?=crip(0)?>">Não se aplica</option>
                    <option <?php if ($bimestre=='1') echo "selected='selected'"; else echo ""; ?> value="<?=crip(1)?>">1º bimestre</option>
                    <option <?php if ($bimestre=='2') echo "selected='selected'"; else echo ""; ?> value="<?=crip(2)?>">2º bimestre</option>
                    <option <?php if ($bimestre=='3') echo "selected='selected'"; else echo ""; ?> value="<?=crip(3)?>">3º bimestre</option>
                    <option <?php if ($bimestre=='4') echo "selected='selected'"; else echo ""; ?> value="<?=crip(4)?>">4º bimestre</option>
                </select>
                
            </td>
        </tr>
            <tr><td align="right">Disciplina: </td><td>
                    <select name="campoAtribuicao" id="campoAtribuicao" value="<?php echo $atribuicao; ?>">
                        <option></option>
					    <?php
					    $sql = "select a.codigo, t.numero, d.nome, a.bimestre, a.grupo, d.codigo, a.subturma, a.eventod
					                            from Turmas t, Cursos c, Turnos tu, Atribuicoes a, Disciplinas d
					                            where t.curso = c.codigo
					                            and t.ano=$ano
					                            and t.turno = tu.codigo
					                            and a.turma=t.codigo
					                            and ( (a.bimestre=0 AND (t.semestre=$semestre OR t.semestre=0)) OR (a.bimestre = $bimestre))
					                            and t.codigo=$turma
					                            and a.disciplina = d.codigo
					                            order by d.nome, t.numero, a.bimestre, a.grupo";
					    $resultado = mysql_query($sql);
					    $selected = ""; // controla a alteraÃ§ no campo select
					    while ($linha = mysql_fetch_array($resultado)) {
					        if ($linha[0] == $atribuicao)
					            $selected = "selected";
					        ($linha[4] != "0") ? $grupo=" Grupo $linha[4]":$grupo="";
					        if (!$linha[6]) $linha[6] = $linha[7];
					        echo "<option $selected value='" . crip($linha[0]) . "'>$linha[2] $grupo [$linha[6]]</option>";
					        $selected = "";
					    }
					    ?>

                    </select>
                </td></tr>
            <tr><td align="right">Prontu&aacute;rio: </td><td>
                    <input type="text" size="5" value="<?php echo $Nprontuario; ?>" name="campoProntuario" id="campoProntuario" />
                    <a href="#" title="Buscar" id="setProntuario"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' /></a>
                </td>
            </tr>
            <tr><td align="right">Nome: </td><td>   
                    <input type="text" size="25" value="<?php echo $Nnome; ?>" name="campoPesquisaNome" id="campoPesquisaNome" />
                    <a href="#" title="Buscar" id="setNome"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' /></a>
                </td>
            </tr>
			 <?php if ($codigo) { ?>
            <tr><td align="right"></td><td>
                    <input type="checkbox" id="copiarSituacao" name="copiarSituacao" /> Copiar situa&ccedil;&atilde;o para todas as disciplinas do aluno.
                </td>
            </tr>
			<?php } ?>
            <tr><td></td><td>
					<input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <input type="hidden" name="matricula" value="<?php echo $matricula ?>" />
					<table width="100%"><tr>
						<td><a href="javascript:$('#index').load('<?php print $SITE."?matricula=$matricula"; ?>'); void(0);">Limpar</a></td>
					</tr></table>
                </td></tr>
        </table>
    </form><br>
<div id="dialog2" title="Assinatura para o atestado de matr&iacute;cula">
 <form>
    <table align="center" width="100%" id="form" border="0">
	
	<tr><td align="left" width="100">Assinatura 1: </td><td align="left">
           <select name="campoAssinatura1" id="campoAssinatura1" >
                    <option></option>
                        <?php
                        $resultado = mysql_query("SELECT p.codigo, upper(p.nome), t.nome
                        				FROM Pessoas p, PessoasTipos pt, Tipos t 
                        				WHERE pt.pessoa = p.codigo AND pt.tipo = t.codigo 
                        				AND t.codigo IN ($COORD, $SEC, $GED) ORDER BY p.nome");
                        
                        $selected = ""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)) {
                            echo "<option value='" . crip($linha[0]) . "'>$linha[1] [$linha[2]]</option>";
                        }
                        ?>
                    </select>
                </td></tr>
         		<tr><td align="left" width="100">Assinatura 2: </td><td align="left">
                    <select name="campoAssinatura2" id="campoAssinatura2" >
                        <option></option>
                        <?php
                        $resultado = mysql_query("SELECT p.codigo, upper(p.nome), t.nome
                        				FROM Pessoas p, PessoasTipos pt, Tipos t 
                        				WHERE pt.pessoa = p.codigo AND pt.tipo = t.codigo 
                        				AND t.codigo IN ($COORD, $SEC, $GED) ORDER BY p.nome");
                        
                        $selected = ""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)) {
                            echo "<option value='" . crip($linha[0]) . "'>$linha[1] [$linha[2]]</option>";
                        }
                        ?>
                    </select>
                </td></tr>
        </table>
 </form>
</div>
    <br />

    <?php
    // inicializando as variáveis
    $item = 1;
    $itensPorPagina = 25;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a página atual
    if (!empty($_GET["item"])) {
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a página anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

    // descobrindo a quantidade total de registros
    $sql = ("SELECT COUNT(*)
	        FROM Matriculas m, Pessoas p, Turmas t, Cursos c, Turnos tu, Situacoes s, Atribuicoes a, Disciplinas d
	        WHERE m.situacao=s.codigo 
	        and a.disciplina = d.codigo
	        and m.aluno = p.codigo 
	        and m.atribuicao = a.codigo 
	        and t.curso = c.codigo 
	        and t.turno = tu.codigo 
	        and t.ano=$ano 
	        and ( (a.bimestre=0 AND (t.semestre=$semestre OR t.semestre=0)) OR (a.bimestre = '$bimestre'))
	        and a.turma = t.codigo
	        $nome
	        $restricaoTurma
	        $restricao
	        ORDER BY p.nome, d.numero, a.bimestre");
	$resultado = mysql_query($sql);
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];

    // validando o próximo item
    if ($proximo > $ultimo) {
        $proximo = $item;
        $ultimo = $item;
    }

    // validando o último item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo = $ultimo - ($ultimo % $itensPorPagina) + 1;

   	$SITENAV = $SITE.'?matricula='.$matricula.'&bimestre='.crip($bimestre).'&turma='.crip($turma).'&atribuicao='.crip($atribuicao);
    
    if (!empty($_GET["nome"]))
    	$SITENAV .= "&nome=".crip($Nnome)."&prontuario=".crip($Nprontuario);

    require(PATH.VIEW.'/navegacao.php'); ?>

    <table id="listagem" border="0" align="center">
        <tr><th align="center" width="100">Prontu&aacute;rio</th><th align="left">Aluno</th><th>Disciplina</th><th>Situa&ccedil;&atilde;o</th><th>Data</th><th align="center" width="50">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT p.prontuario, upper(p.nome), t.numero, s.nome, 
        date_format(m.data, '%d/%m/%Y') data, d.nome, a.bimestre,
        a.codigo, d.numero, a.grupo, p.codigo, c.codigo, m.codigo, a.subturma, d.codigo, a.eventod
        FROM Matriculas m, Pessoas p, Turmas t, Cursos c, Turnos tu, Situacoes s, Atribuicoes a, Disciplinas d
        WHERE m.situacao=s.codigo 
        and a.disciplina = d.codigo
        and m.aluno = p.codigo 
        and m.atribuicao = a.codigo 
        and t.curso = c.codigo 
        and t.turno = tu.codigo 
        and t.ano=$ano 
        and ( (a.bimestre=0 AND (t.semestre=$semestre OR t.semestre=0)) OR (a.bimestre = '$bimestre'))
        and a.turma = t.codigo
        $nome
        $restricaoTurma
        $restricao
        ORDER BY p.nome, d.numero, a.bimestre limit " . ($item - 1) . ",$itensPorPagina";

    //print $sql;
    $resultado = mysql_query($sql);
    $i = $item;
    if ($resultado) {
        require CONTROLLER . "/professor.class.php";
        $professor = new Professores();
            
        while ($linha = mysql_fetch_array($resultado)) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ($linha[6] > 0) ? $bimestre = "$linha[6]ºBIM" : $bimestre = "";
            ($linha[9] != "0") ? $grupo=" Grupo $linha[9]":$grupo="";
			$codigo = crip($linha[0]);
			if (!$linha[13]) $linha[13] = $linha[15];
			$professores='';			
			foreach($professor->getProfessor($linha[7]) as $key => $reg)
				$professores[] = $reg['nome'];
			$professores = implode(" / ", $professores);

            print "<tr $cdif ><td align='left'>$linha[0]</td><td>".mostraTexto($linha[1])."</td><td align='left'><a target='_blank' href='".VIEW."/secretaria/relatorios/inc/diario.php?atribuicao=".crip($linha[7])."' title='$bimestre $linha[5] $grupo | $professores'>$linha[2] [$linha[13]] $linha[8] $grupo</a></td>\n";
            print "<td align='left'>".mostraTexto($linha[3])."</td><td align='left'>$linha[4]</td>\n";
            print "<td align='center'><a href='#Excluir' title='Excluir' class='item-excluir' id='" . crip($linha[12]) . "'><img class='botao' src='".ICONS."/remove.png' /></a>\n";
            print "<a href='#' class='item-atestado' id='" . crip($linha[12]) . "' title='Atestado'><img class='botao' src='".ICONS."/icon-printer.gif' /></a></td></tr>";
            print "</tr>";
            $i++;
        }
    }

	require(PATH.VIEW.'/navegacao.php'); ?>

</table>
<?php

mysql_close($conexao);
?>

<script>
function atualizarMatricula(getLink){
    var bimestre = $('#campoBimestre').val();
    var turma = $('#campoTurma').val();
    var atribuicao = $('#campoAtribuicao').val();
    var aluno = $('#campoNome').val();
    var nome = encodeURIComponent($('#campoPesquisaNome').val());
    var prontuario = encodeURIComponent($('#campoProntuario').val());
	var URLS = '<?php print $SITE; ?>?pesquisa=1&bimestre='+ bimestre +'&turma='+ turma +'&atribuicao='+ atribuicao +'&aluno='+ aluno +'&nome=' + nome + '&prontuario=' + prontuario;
	if (!getLink)
		$('#index').load(URLS + '&item=<?php print $item; ?>');
	else
		return URLS;
}

$(document).ready(function(){
	$(".item-atestado").click(function(){
		var codigo = $(this).attr('id');
		window.open('<?php print VIEW; ?>/secretaria/relatorios/inc/atestadoMatricula.php?codigo=' + codigo +'&assinatura1='+$('#campoAssinatura1').val() + '&assinatura2='+$('#campoAssinatura2').val());
	});

	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load(atualizarMatricula(1) + '&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
		
 	$('#setNome, #setProntuario').click(function(){
		$('#index').load(atualizarMatricula(1) + '&pesquisa=1');
  	});

   	$('#campoAtribuicao, #campoBimestre, #campoTurma').change(function(){
    	atualizarMatricula();
  	}); 	
});
</script>