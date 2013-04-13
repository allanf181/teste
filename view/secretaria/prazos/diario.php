<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela em que é possível visualizar de forma individual a versão em pdf de todos os diários lançados pelos docentes de todas as disciplinas de todos os cursos dados pelo Campus.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if ($_GET["opcao"] == 'controleDiario') {
	$atribuicao = $_GET["atribuicao"];
  $status = $_GET["status"];
	$bimestre = $_GET["bimestre"];

	$erro=0;
	if ($status <> 0) {
		if ($r = fecharDiario($atribuicao)) {
			mensagem('NOK', 'FALSE_UPDATE');
			$erro=1;
		}
  }

  if (!$erro) {
  	$sql = "update Atribuicoes set status=$status where codigo=$atribuicao";
  	if ($resultado = mysql_query($sql))
			mensagem('OK', 'TRUE_UPDATE');
		else
			mensagem('OK', 'FALSE_UPDATE');
  	
  	//ALTERAR NOTASFINAIS PARA SINCRONIZAR NOVAMENTE
  	$sql = "update NotasFinais set sincronizado='' where atribuicao=$atribuicao";
  	$resultado = mysql_query($sql);
  }
  $_GET['curso'] = crip($_GET['curso']);
  $_GET['bimestre'] = crip($_GET['bimestre']);
}

?>

<h2><?php print $TITLE; ?></h2>

<script>
	function valida() {
  	turma = $('#campoTurma').val();
    curso = $('#campoCurso').val();
    bimestre = $('#campoBimestre').val();
    professor = $('#campoProfessor').val();
		$('#index').load('<?php print $SITE; ?>?&turma='+ turma +'&curso=' + curso +'&bimestre=' + bimestre +'&professor=' + professor);
  }
    	
	$('#campoTurma, #campoCurso, #campoBimestre, #campoProfessor').change(function(){
		valida();
  });
function diario(codigo, value, checked, nome, atribuicao, bimestre, curso, professor) {
  turma = $('#campoTurma').val();
	if (!checked)
		modo='Confirma abrir o di&aacute;rio de '+nome+'?';
	else
		modo='Confirma o fechamento do di&aacute;rio de '+nome+'?\n\nAten&ccedil;&atilde;o: somente a secretaria poder&aacute; abrir novamente!';
	jConfirm(modo, 'Fechamento', function(r) {
	  if ( r )
			$('#index').load("<?php print $SITE; ?>?opcao=controleDiario&status="+value+"&atribuicao="+atribuicao+"&bimestre="+bimestre+"&curso="+curso+"&turma="+turma+"&professor="+professor);
		else
			document.getElementById(codigo).checked = !checked;
	});
}
</script>

<?php
$curso="";
$turma="";
$restricao="";
$bimestre="";
$professor="";

if (isset($_GET["turma"]) && $_GET["turma"]!="") {
	$turma = dcrip($_GET["turma"]);
	$restricao = " AND t.codigo = $turma";
}

if (isset($_GET["curso"])) {
  $curso = dcrip($_GET["curso"]);
  $restricao .= " AND c.codigo = $curso";
}

if (isset($_GET["bimestre"]) && $_GET["bimestre"]!="" ) {
	if (  $_GET["bimestre"] != "undefined") {
  	$bimestre = dcrip($_GET["bimestre"]);
	  $restricao .= " AND a.bimestre = $bimestre";
	}
}

if (isset($_GET["professor"]) && dcrip($_GET["professor"]) != 'Todos') {
  $professor = dcrip($_GET["professor"]);
  $restricao .= " AND p.professor = $professor";
}

?>
    <table align="center" id="form" width="100%">
        <tr><td align="right" style="width: 100px">Curso: </td><td>
                <select name="campoCurso" id="campoCurso" value="<?php echo $curso; ?>" style="width: 350px">
					<option></option>
                    <?php
						if (in_array($COORD, $_SESSION["loginTipo"]))
							$restricaoCoord = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador=".$_SESSION['loginCodigo'].")";
                        $resultado = mysql_query("select distinct c.codigo, c.nome, m.nome, m.codigo
                        							from Cursos c, Turmas t, Modalidades m
                        							where t.curso=c.codigo
                        							and m.codigo = c.modalidade
                        							and (t.semestre=$semestre OR t.semestre=0)
                        							and t.ano=$ano $restricaoCoord order by c.nome");
                        $selected=""; // controla a alteração no campo select
                        while ($linha = mysql_fetch_array($resultado)){
                            if ($linha[0]==$curso)
                               $selected="selected";
                            if ($linha[3] < 1000 || $linha[3] >= 2000) $linha[1] = "$linha[1] [$linha[2]]";
                            echo "<option $selected value='".crip($linha[0])."'>[$linha[0]] $linha[1]</option>";
                            $selected="";
                        }
                    ?>
                </select>
            </td></tr>
                <tr><td align="right">Turma: </td>
                	<td><select name="campoTurma" id="campoTurma" style="width: 350px">
             	     <option></option>
                    <?php
                    $resultado = mysql_query("select t.codigo, t.numero, c.nome, tu.nome, t.semestre, t.ano, c.fechamento
                    							from Turmas t, Cursos c, Turnos tu 
                    							where t.curso=c.codigo 
                    							and t.ano=$ano 
                    							and t.turno=tu.codigo
                    							and c.codigo = $curso
                    							and (t.semestre=$semestre OR t.semestre=0) $restricaoCoord");
                    $selected = "";
                    if (mysql_num_rows($resultado) > 0) {
                        while ($linha = mysql_fetch_array($resultado)) {
                        	if ($linha[6] == 'b') $S=1;
                            if ($linha[0] == $turma)
                                $selected = "selected";
                            echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
                            $selected = "";
                        }
                    }
                    else {
                        echo "<option value=''>Não há turmas cadastrados neste semestre/ano letivo</option>";
                    }
                    ?>
                </select>
                </td></tr>
					<?php if ($S) { ?>
	            <tr><td align="right">Fechamento: </td><td><select name="campoBimestre" id="campoBimestre" style="width: 350px">
                		<option value=""></option>
                    <?php
                    if (isset($turma) && !empty($turma)) {
                        $sql = "select a.bimestre
                        			from Atribuicoes a, Turmas t 
                        			where t.codigo=a.turma 
                        			and t.codigo=$turma
                        			GROUP BY a.bimestre";
                        $resultado = mysql_query($sql);
                        while ($linha = mysql_fetch_array($resultado)) {
   	   	                    $selected = "";
                            if ($linha[0] == $bimestre)
                                $selected = "selected";
                        	if ($linha[0] == 0) { $linha[0] = 'semestre'; $bim = 'Semestral'; }
                        	if ($linha[0] != 0) $bim = $linha[0].'º Bimestre';
                            echo "<option $selected value='".crip($linha[0])."'>$bim</option>";
                        }
                        if ($bimestre == 'final') $selected1 = "selected";
                        if ($bim != 'Semestral' && $relatorio!='diarioProfessor' && $relatorio!='chamada' && $relatorio!='presenca' && $relatorio!='planoEnsino')
												print "<option $selected1 value=\"".crip('final')."\">Anual</option>\n";
                    }
                    ?>
    	        </select>
    	        </td></tr>
		        <?php } //print $bimestre; ?>
                <tr><td align="right">Professor: </td><td><select name="campoProfessor" id="campoProfessor" style="width: 350px">
                    <?php
                    if ($turma) $profSQL = "AND pr.atribuicao IN (SELECT a1.codigo FROM Atribuicoes a1 WHERE a1.turma = $turma)";
                    $sql = "SELECT DISTINCT p.codigo, p.nome 
                    				FROM Pessoas p, PessoasTipos pt, Professores pr
                    				WHERE p.codigo = pt.pessoa
                    				AND pt.tipo = $PROFESSOR
                    				AND pr.professor = p.codigo
                    				$profSQL
                     				ORDER BY p.nome";
                    $resultado = mysql_query($sql);
                    $selected = "";
                    if (mysql_num_rows($resultado) > 0) {
                        echo "<option value='".crip("Todos")."'>Todos</option>";
                        while ($linha = mysql_fetch_array($resultado)) {
                        	$selected = "";
                          if ($linha[0] == $professor)
                          	$selected = "selected";
                          echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
                          $selected = "";
                        }
                    }
                    else {
                        echo "<option>Não há professores cadastrados.</option>";
                    }
                    ?>
                </select>
                </td></tr>		        
    </table>
<br />
<?php
    if (!empty($curso)){
?>

<table id="form" border="0" align="center" width="100%">
    <tr><th align="center">Disciplina</th><th align="center">Professor</th><th align="center" width="50">Turma</th><th width="70" align="center">Concluído</th><th width="50" align="center">Origem</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "select a.codigo,d.nome, d.codigo, t.numero, a.status, a.bimestre, p.professor
                from Atribuicoes a, Disciplinas d, Cursos c, Turmas t, Professores p
                where a.turma=t.codigo
                and t.curso=c.codigo
                and a.disciplina=d.codigo
                and p.atribuicao = a.codigo
                and t.ano=$ano
                and (t.semestre=$semestre OR t.semestre=0)
                and d.curso=c.codigo $restricao group by a.codigo order by d.nome";
    //echo $sql;
    $resultado = mysql_query($sql);
    $i = 1;
    if ($resultado){
        while ($linha = mysql_fetch_array($resultado)) {
	
					$professores='';			
					foreach(getProfessor($linha[0]) as $key => $reg)
						$professores[] = $reg['nome'];
					$professor = implode("<br>", $professores);

            $i%2==0 ? $cdif="class='cdif'" : $cdif="";
            if ($linha[5]!=0)
                $bimestre="[".$linha[5]."&ordm; Bim]";
            echo "<tr $cdif>";
            echo "<td><a target=\"_blank\" href='".VIEW."/secretaria/relatorios/inc/diario.php?atribuicao=".crip($linha[0])."'>$bimestre ".mostraTexto($linha[1])."</a></td>";
            echo "<td align='left'>".mostraTexto($professor)."</td><td align=left>$linha[3]</td>";
            $checked="";
            $origem="";
            $valor=1;
            if ((in_array($SEC, $_SESSION["loginTipo"]) && in_array($COORD, $_SESSION["loginTipo"])) || in_array($ADM, $_SESSION["loginTipo"]))
                $valor=3;
            $bloqueado="";
            if ($linha[4]>0){
                $checked="checked='checked'";
                $valor=0;
                if ($linha[4]==1)
                    $origem="Coord";
                if ($linha[4]==2)
                    $origem="Prof";
                if ($linha[4]==3)
                    $origem="Secre";
                if ($linha[4]==4)
                    $origem="SYS";
                if (!in_array($SEC, $_SESSION["loginTipo"]) && !in_array($ADM, $_SESSION["loginTipo"])){//Secretaria ou adm
                    $bloqueado="disabled='disabled'";
                }
            }
            echo "<td align='center'>
                    <input $bloqueado type='checkbox' id='$i' $checked value='$valor' onclick=\"return diario($i, this.value, this.checked, '".$linha[1]."', '".$linha[0]."', '".$linha[5]."', $curso, '".crip($linha[6])."');\" />
                  </td>
                  <td align='center'>$origem</td>";
            echo "</tr>";
            $i++;
        }
    }
    mysql_close($conexao);
    ?>
</table>

    <?php
    }
?>
