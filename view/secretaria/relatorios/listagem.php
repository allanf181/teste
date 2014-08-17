<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita gerar diversos relatórios em PDF.
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

// especifica o ano e o semestre
    $data = date("d/m/Y");
    $turma = "";
    $curso = "";
    
    $restricaoCoordenador="";
    $restricaoCoordenadorAnd="";
    if (in_array($COORD, $_SESSION["loginTipo"])){
        $restricaoCoordenador = " WHERE c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador=".$_SESSION['loginCodigo'].")";
        $restricaoCoordenadorAnd = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador=".$_SESSION['loginCodigo'].")";
    }

    if (isset($_GET["relatorio"]) && $_GET["relatorio"]!="")
        $relatorio = $_GET["relatorio"];

    if (isset($_GET["turma"]) && $_GET["turma"]!="")
        $turma = dcrip($_GET["turma"]);
    if (isset($_GET["curso"]))
        $curso = dcrip($_GET["curso"]);
    if (isset($_GET["bimestre"]))
        $bimestre = dcrip($_GET["bimestre"]);

    ?>
    <script>
    function valida() {
       	var turma = '';
       	var curso = '';
       	var relatorio = '';
       	try { turma = $('#campoTurma').val(); } catch (e) {}
       	try { curso = $('#campoCurso').val(); } catch (e) {}
      	try { bimestre = $('#campoBimestre').val(); } catch (e) {}
      	try { relatorio = $('#campoRelatorio').val(); } catch (e) {}
		$('#index').load('<?php print $SITE; ?>?relatorio='+ $('#campoRelatorio').val() +'&turma='+ turma +'&curso=' + $('#campoCurso').val() +'&bimestre=' + $('#campoBimestre').val());
    }
    
    $(document).ready(function(){      
		$("#campoData").datepicker({
		    dateFormat: 'dd/mm/yy',
		    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
		    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
		    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
		    monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
		    nextText: 'Próximo',
		    prevText: 'Anterior'
		});
		
		
        $('#campoTurma, #campoCurso, #campoRelatorio, #campoBimestre').change(function(){
			valida();
        });
	});
        function relatorio(impressao, tipo){
            try { var bimestre = document.getElementById("campoBimestre").value; } catch (e) {}
            try { var aluno = document.getElementById("campoAluno").value; } catch (e) {}
            try { var curso = document.getElementById("campoCurso").value; } catch (e) {}
            try { var turma = document.getElementById("campoTurma").value; } catch (e) {}
            try { var disciplina = document.getElementById("campoDisciplina").value; } catch (e) {}
            try { var situacao = document.getElementById("campoSituacao").value; } catch (e) {}
            try { var data1 = document.getElementById("campoData").value; } catch (e) {}
            try { var professor = document.getElementById("campoProfessor").value; } catch (e) {}

            try { var rg = document.getElementById("alunos_rg").checked; } catch (e) {}
            try { var cpf = document.getElementById("alunos_cpf").checked; } catch (e) {}
            try { var nasc = document.getElementById("alunos_nasc").checked; } catch (e) {}
            try { var endereco = document.getElementById("alunos_endereco").checked; } catch (e) {}
            try { var bairro = document.getElementById("alunos_bairro").checked; } catch (e) {}
            try { var cidade = document.getElementById("alunos_cidade").checked; } catch (e) {}
            try { var telefone = document.getElementById("alunos_telefone").checked; } catch (e) {}
            try { var celular = document.getElementById("alunos_celular").checked; } catch (e) {}
            try { var email = document.getElementById("alunos_email").checked; } catch (e) {}
            try { var obs = document.getElementById("alunos_obs").checked; } catch (e) {}

						var det = 0;
						if (tipo == 'ftdd')
							var det = 1;
							
						if (tipo == 'ftdr' || tipo == 'ftdd')
							tipo = 'ftd';
						
            if (impressao=='pdf')
                window.open('<?php print VIEW; ?>/secretaria/relatorios/inc/'+tipo+'.php?curso='+curso+'&turma='+turma+'&bimestre='+bimestre+'&aluno='+aluno+'&atribuicao='+disciplina+'&data='+data1+'&situacao='+situacao
                +'&rg='+rg+'&cpf='+cpf+'&nasc='+nasc+'&endereco='+endereco+'&bairro='+bairro+'&cidade='+cidade+'&telefone='+telefone+'&celular='+celular+'&professor='+professor+
            '&email='+email+'&obs='+obs+'&detalhada='+det,'_blank');
            else
                window.open('<?php print VIEW; ?>/secretaria/relatorios/inc/'+tipo+'Html.php?curso='+curso+'&turma='+turma,'_blank');
        }

    </script>
    <script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
    <h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>


	<?php
	$T=0;
	$D=0;
	$A=0;
	$C=0;
	$DT=0;
	$SIT=0;
	
	if ($relatorio == 'alunos') { $C=1; $T=1; }
	if ($relatorio == 'atendimento') {  }
	if ($relatorio == 'boletim') { $C=1; $T=1; $A=1; }
	if ($relatorio == 'boletimTurma') { $C=1; $T=1; }
	if ($relatorio == 'carometro') { $C=1; $T=1; }
	if ($relatorio == 'diario') { $C=1; $T=1; $D=1; }
	if ($relatorio == 'disciplinas') { $C=1; }
	if ($relatorio == 'docente') { $C=1; $T=1; }
	if ($relatorio == 'lancamentos') { $C=1; }
	if ($relatorio == 'chamada') { $C=1; $T=1; $D=1; }
	if ($relatorio == 'presenca') { $C=1; $T=1; $DT=1; }
	if ($relatorio == 'planoEnsino') { $C=1; $T=1; $D=1; }	
	if ($relatorio == 'frequencia') { $C=1; $T=1; $D=1; $DT=1; }
	if ($relatorio == 'matriculas') { $C=1; $T=1; $SIT=1; }
	if ($relatorio == 'matriculasTotais') { $C=1; $T=1; $SIT=1; }
	if ($relatorio == 'ftdr') { $P=1; }
	if ($relatorio == 'ftdd') { $P=1; }
		
	?>	

    <table border="0" width="100%" id="form" width="100%">
       <tr>
           <td colspan="3">
              <select name="campoRelatorio" id="campoRelatorio" style="width: 200px">
              		<option value=""></option>              	
              		<option <?php if ($relatorio=='alunos') print "selected"; ?> value="alunos">Alunos</option>
              		<option <?php if ($relatorio=='atendimento') print "selected"; ?> value="atendimento">Atendimento do Professor</option>              		
              		<option <?php if ($relatorio=='docente') print "selected"; ?> value="docente">Atribui&ccedil;&atilde;o Docente</option>              		
              		<option <?php if ($relatorio=='boletim') print "selected"; ?> value="boletim">Boletim Individual</option>
              		<option <?php if ($relatorio=='boletimTurma') print "selected"; ?> value="boletimTurma">Boletim Turma</option>
              		<option <?php if ($relatorio=='carometro') print "selected"; ?> value="carometro">Car&ocirc;metro</option>
              		<option <?php if ($relatorio=='diario') print "selected"; ?> value="diario">Di&aacute;rio</option>
              		<option <?php if ($relatorio=='disciplinas') print "selected"; ?> value="disciplinas">Disciplinas do Curso</option>
              		<option <?php if ($relatorio=='ftdr') print "selected"; ?> value="ftdr">FTD Resumida</option>
              		<option <?php if ($relatorio=='ftdd') print "selected"; ?> value="ftdd">FTD Detalhada</option>

              		<option <?php if ($relatorio=='lancamentos') print "selected"; ?> value="lancamentos">Lan&ccedil;amento de Aulas</option>
              		<option <?php if ($relatorio=='chamada') print "selected"; ?> value="chamada">Lista de Chamada</option>
              		<option <?php if ($relatorio=='matriculas') print "selected"; ?> value="matriculas">Lista de Matr&iacute;culas</option>
              		<option <?php if ($relatorio=='presenca') print "selected"; ?> value="presenca">Lista de Presen&ccedil;a</option>
              		<option <?php if ($relatorio=='planoEnsino') print "selected"; ?> value="planoEnsino">Planos de Ensino</option>
              		<option <?php if ($relatorio=='frequencia') print "selected"; ?> value="frequencia">Relat&oacute;rio de Frequ&ecirc;ncia</option>
              		<option <?php if ($relatorio=='matriculasTotais') print "selected"; ?> value="matriculasTotais">Totaliza&ccedil;&atilde;o de Matr&iacute;culas</option>
               </select>
			</td>
        </tr>

        <!-- ================================ -->         	

		<?php if ($relatorio == 'alunos') { ?>
        <tr><td colspan="3">&nbsp;</td></tr>
        <tr>
            <td  colspan="2">Campos:
                <input type="checkbox" id="alunos_rg" value="1" checked="checked" /><label for="alunos_rg">RG</label>
                <input type="checkbox" id="alunos_cpf" value="1" checked="checked" /><label for="alunos_cpf">CPF</label>
                <input type="checkbox" id="alunos_nasc" value="1" checked="checked" /><label for="alunos_nasc">D.Nasc</label>
                <input type="checkbox" id="alunos_endereco" value="1" /><label for="alunos_endereco">Endereço</label>
                <input type="checkbox" id="alunos_bairro" value="1" /><label for="alunos_bairro">Bairro</label>
                <input type="checkbox" id="alunos_cidade" value="1" /><label for="alunos_cidade">Cidade</label>
                <input type="checkbox" id="alunos_telefone" value="1" checked="checked" /><label for="alunos_telefone">Telefone</label>
                <input type="checkbox" id="alunos_celular" value="1" checked="checked" /><label for="alunos_celular">Celular</label>
            </td></tr>
        <?php } ?>
        <tr><td colspan="3">&nbsp;</td></tr>
		<tr>
           	<td>

 				<?php if ($C) { ?>
                <tr><td>Curso: </td><td><select name="campoCurso" id="campoCurso" style="width: 350px">
                    <?php if ((!$restricaoCoordenador && !$restricaoCoordenadorAnd ) && ($relatorio == 'docente' || $relatorio == 'matriculasTotais' || $relatorio == 'matriculas' || $relatorio == 'disciplinas' || $relatorio == 'alunos' || $relatorio == 'lancamentos')) { ?>
                    	<option value="">Todos os cursos</option>
           	        <?php } ?>
                    <?php
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
		        <?php } ?>
				<?php if ($T) { ?>
                <tr><td>Turma: </td>
                	<td><select name="campoTurma" id="campoTurma" style="width: 350px">
             	     <option></option>
		            <?php if ($relatorio == 'alunos') {
		            	if (!$turma) $selected = 'selected';
						print "<option $selected value=\"\">Todos as turmas</option>\n";
			        } ?>
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
                        while ($linha = mysql_fetch_array($resultado)) {
                        	if ($linha[6] == 'b' && $relatorio != 'matriculas' && $relatorio != 'matriculasTotais') $S=1;
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
		        <?php } ?>

				<?php if ($S) { ?>
	            <tr><td>Fechamento: </td><td><select name="campoBimestre" id="campoBimestre" style="width: 350px">
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
                        if ($bim != 'Semestral' && $relatorio!='diario' && $relatorio!='chamada' && $relatorio!='presenca' && $relatorio!='planoEnsino')
							print "<option $selected1 value=\"".crip('final')."\">Anual</option>\n";

                    }
                    ?>
    	        </select>
    	        </td></tr>
		        <?php } //print $bimestre; ?>

				<?php if ($D) { ?>
                <tr><td>Disciplina: </td>
                	<td><select name="campoDisciplina" id="campoDisciplina" style="width: 350px">
                		<option value=""></option>
		            <?php if ($relatorio != 'diario' && $relatorio != 'chamada' && $relatorio != 'presenca' && $relatorio != 'planoEnsino') { ?>
						<option value="">Todos as disciplinas</option>
			        <?php } ?>
                    <?php
                    if (isset($turma) && !empty($turma)) {
                    	if ($S) $sqlBim = "and a.bimestre = $bimestre";
                        $sql = "select DISTINCT a.codigo, d.codigo, d.nome
                        			from Disciplinas d, Atribuicoes a, Turmas t 
                        			where t.codigo=a.turma 
                        			and a.disciplina=d.codigo 
                        			and t.codigo=$turma
                        			$sqlBim
                        			GROUP BY d.codigo";
                        $resultado = mysql_query($sql);
                        
                        while ($linha = mysql_fetch_array($resultado)) {
                            echo "<option value='".crip($linha[0])."'>$linha[2]</option>";
                        }
                    }
                    ?>
                </select>
                </td></tr>
		        <?php } //echo $sql; ?>
				
				<?php if ($A) { ?>
                <tr><td>Aluno: </td><td><select name="campoAluno" id="campoAluno" style="width: 350px">
                    <?php
                    $sql = "SELECT p.codigo, p.nome FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t
                    							WHERE t.codigo = a.turma
                    							AND m.atribuicao = a.codigo
                    							AND m.aluno = p.codigo 
                    							AND t.codigo = $turma
                    							GROUP BY p.codigo
                    							ORDER BY p.nome";
                    $resultado = mysql_query($sql);
                    $selected = "";
                    if (mysql_num_rows($resultado) > 0) {
                        echo "<option value='".crip("Todos")."'>Todos</option>";
                        while ($linha = mysql_fetch_array($resultado)) {
                            if ($linha[0] == $turma)
                                $selected = "selected";
                            echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
                            $selected = "";
                        }
                    }
                    else {
                        echo "<option>Não há alunos cadastrados nessa turma.</option>";
                    }
                    ?>
                </select>
                </td></tr>
   		        <?php } //print $sql; ?>

				<?php if ($SIT) { ?>
                <tr><td>Situa&ccedil;&atilde;o: </td>
                	<td><select name="campoSituacao" id="campoSituacao" style="width: 350px">
						<option value="">Todas as situa&ccedil;&otilde;es</option>
                    <?php
                    	if ($curso) $sql1 = " and c.codigo = $curso";
                    	if ($turma) $sql1 .= " and t.codigo = $turma";
	                    $sql = "SELECT s.codigo, s.nome 
	                    		FROM Situacoes s, Matriculas m, Atribuicoes a, Turmas t, Cursos c
	                    		WHERE s.codigo = m.situacao
	                    		AND m.atribuicao = a.codigo
	                    		AND t.codigo = a.turma
	                    		AND c.codigo = t.curso
	                    		$sql1
	                    		GROUP BY s.codigo
	                    		ORDER BY codigo";
                        $resultado = mysql_query($sql);
                        
                        while ($linha = mysql_fetch_array($resultado)) {
                        	if ($linha[0] >= 100) $linha[1] .= " (CURSO ANTIGO)";
                            echo "<option value='".crip($linha[0])."'>$linha[1]</option>";
                        }
                    ?>
                </select>
                </td></tr>
		        <?php } //echo $sql; ?>

				<?php if ($P) { ?>
                <tr><td>Professor: </td><td><select name="campoProfessor" id="campoProfessor" style="width: 350px">
                    <?php
                    $sql = "SELECT p.codigo, p.nome FROM Pessoas p, PessoasTipos pt
                    							WHERE p.codigo = pt.pessoa
                    							AND pt.tipo = $PROFESSOR
                    							ORDER BY p.nome";
                    $resultado = mysql_query($sql);
                    $selected = "";
                    if (mysql_num_rows($resultado) > 0) {
                        echo "<option value='".crip("Todos")."'>Todos</option>";
                        while ($linha = mysql_fetch_array($resultado)) {
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
   		        <?php } //print $sql; ?>
   		        		        
                <?php if ($DT) { ?>
                <tr><td>Data: </td><td><input value="<?php echo $data; ?>" readonly type="text" class="data1" name="campoData" id="campoData" /></td></tr>
   		        <?php } ?>
            </td>

		    <?php if ($relatorio) { ?>
		      <tr><td colspan="3">&nbsp;</td></tr>
	    	  <td width="60">
	                <a href="#" onclick="relatorio('pdf', '<?php print $relatorio; ?>')"><img style="width: 30px" src="<?php print ICONS; ?>/icon-printer.gif" title="Imprimir em PDF" /></a>
	                <?php if ($relatorio=='alunos') { ?><a href="#" onclick="relatorio('html', '<?php print $relatorio; ?>')"><img style="width: 23px" src="<?php print ICONS; ?>/info.png" title="Imprimir em HTML" /></a><?php } ?>
	          </td>
	        <?php } ?>

        </tr>
    </table>