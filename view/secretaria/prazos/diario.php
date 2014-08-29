<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite que os coordenadores dos cursos liberem, de forma temporária, o diário para que os professores finalizem seus registros e/ou façam suas alterações.
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

require CONTROLLER . "/professor.class.php";
$prof = new Professores();

require CONTROLLER . "/notaFinal.class.php";
$nota = new NotasFinais();
    
if ($_GET["opcao"] == 'controleDiario') {
    $v = $_GET["valor1"];
    $prazos = explode(',', $_GET["prazo"]);

    $erro = 0;
    foreach ($prazos as $atribuicao) {
        $status = 1;
        $sql = "update Atribuicoes set status=$status, prazo=NULL where codigo=$atribuicao";
        if (!$resultado = mysql_query($sql))
            $erro = 1;

        if ($atribuicao) {
            $sql = "INSERT INTO PrazosDiarios VALUES (NULL, $atribuicao, now(), 'DIÁRIO FECHADO: $v')";
            if (!$resultado = mysql_query($sql))
                $erro = 1;
        }

        if ($status <> 0) {
            if ($r = $nota->fecharDiario($atribuicao)) {
                mensagem('NOK', 'FALSE_UPDATE');
                $erro = 1;
            }
        }

        //ALTERAR NOTASFINAIS PARA SINCRONIZAR NOVAMENTE
        $sql = "update NotasFinais set sincronizado='' where atribuicao=$atribuicao AND flag <> 5";
        $resultado = mysql_query($sql);

        if (!$erro)
            mensagem('OK', 'TRUE_UPDATE');
        else
            mensagem('NOK', 'FALSE_UPDATE');
    }
    $_GET["curso"] = crip($_GET["curso"]);
}

if ($_GET["opcao"] == 'controlePrazo') {
    $v = $_GET["valor1"];
    $prazos = explode(',', $_GET["prazo"]);

    $erro = 0;
    foreach ($prazos as $atribuicao) {
        $sql = "update Atribuicoes set prazo=DATE_ADD(NOW(), INTERVAL $LIMITE_DIARIO_PROF DAY), status='0' where codigo=$atribuicao";
        if (!$resultado = mysql_query($sql))
            $erro = 1;

        if ($atribuicao) {
            $sql = "INSERT INTO PrazosDiarios VALUES (NULL, $atribuicao, now(), 'DIÁRIO LIBERADO: $v')";
            if (!$resultado = mysql_query($sql))
                $erro = 1;
        }
    }
    if (!$erro)
        mensagem('OK', 'TRUE_UPDATE');
    else
        mensagem('NOK', 'FALSE_UPDATE');
        
    $_GET["curso"] = crip($_GET["curso"]);
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
$curso = "";
$turma = "";
$restricao = "";
$bimestre = "";
$professor = "";

if (isset($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $restricao .= " AND c.codigo = $curso";
    $lk .= "&curso=" . crip($curso);
}

if (isset($_GET["turma"]) && $_GET["turma"] != "") {
    $turma = dcrip($_GET["turma"]);
    $restricao = " AND t.codigo = $turma";
    $lk .= "&turma=" . crip($turma);
}

if (isset($_GET["bimestre"]) && $_GET["bimestre"] != "") {
    if ($_GET["bimestre"] != "undefined" && dcrip($_GET["bimestre"]) != "") {
        $bimestre = dcrip($_GET["bimestre"]);
        $restricao .= " AND a.bimestre = $bimestre";
        $lk .= "&bimestre=" . crip($bimestre);
    }
}

if (isset($_GET["professor"]) && dcrip($_GET["professor"]) != 'Todos') {
    $professor = dcrip($_GET["professor"]);
    $restricao .= " AND p.professor = $professor";
    $lk .= "&professor=" . crip($professor);
}

print "<script>\n";
print "    $('#form_padrao').html5form({ \n";
print "        method : 'POST', \n";
print "	   async : false, \n";
print "        action : '$SITE', \n";
print "        responseDiv : '#index', \n";
print "        colorOn: '#000', \n";
print "        colorOff: '#000', \n";
print "        messages: 'br' \n";
print "    }) \n";
print "</script>\n";
print "<div id=\"html5form\" class=\"main\">\n";
print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n";
?>
<table align="center" id="form" width="100%" border="0">
    <tr><td align="right" style="width: 100px">Curso: </td><td>
            <select name="campoCurso" id="campoCurso" style="width: 350px">
                <option></option>
                <?php
                if (in_array($COORD, $_SESSION["loginTipo"]))
                    $restricaoCoord = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador=" . $_SESSION['loginCodigo'] . ")";
                $resultado = mysql_query("select distinct c.codigo, c.nome, m.nome, m.codigo
										    from Cursos c, Turmas t, Modalidades m
										    where t.curso=c.codigo
										    and m.codigo = c.modalidade
										    and (t.semestre=$semestre OR t.semestre=0)
										    and t.ano=$ano $restricaoCoord order by c.nome");
                $selected = ""; // controla a alteração no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $curso)
                        $selected = "selected";
                    if ($linha[3] < 1000 || $linha[3] >= 2000)
                        $linha[1] = "$linha[1] [$linha[2]]";
                    echo "<option $selected value='" . crip($linha[0]) . "'>[$linha[0]] $linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select>
        </td>
        <td rowspan="4"><input type="submit" name="liberar" id="liberar" value="Liberar"><br /><br />
            <input type="submit" name="fechar" id="fechar" value="Fechar">
            <input type="hidden" name="botao" id="botao" value="">
    </tr>
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
                    if ($linha[6] == 'b')
                        $S = 1;
                    if ($linha[0] == $turma)
                        $selected = "selected";
                    echo "<option $selected value='" . crip($linha[0]) . "'>$linha[1]</option>";
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
                        if ($linha[0] == 0) {
                            $linha[0] = 'semestre';
                            $bim = 'Semestral';
                        }
                        if ($linha[0] != 0)
                            $bim = $linha[0] . 'º Bimestre';
                        echo "<option $selected value='" . crip($linha[0]) . "'>$bim</option>";
                    }
                    if ($bimestre == 'final')
                        $selected1 = "selected";
                    if ($bim != 'Semestral' && $relatorio != 'diarioProfessor' && $relatorio != 'chamada' && $relatorio != 'presenca' && $relatorio != 'planoEnsino')
                        print "<option $selected1 value=\"" . crip('final') . "\">Anual</option>\n";
                }
                ?>
            </select>
        </td></tr>
<?php } //print $bimestre; ?>	
<tr><td align="right">Professor: </td><td><select name="campoProfessor" id="campoProfessor" style="width: 350px">
            <?php
            if ($turma)
                $profSQL = "AND pr.atribuicao IN (SELECT a1.codigo FROM Atribuicoes a1 WHERE a1.turma = $turma)";
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
                echo "<option value='" . crip("Todos") . "'>Todos</option>";
                while ($linha = mysql_fetch_array($resultado)) {
                    $selected = "";
                    if ($linha[0] == $professor)
                        $selected = "selected";
                    echo "<option $selected value='" . crip($linha[0]) . "'>$linha[1]</option>";
                    $selected = "";
                }
            }
            else {
                echo "<option value=''>Não há professores cadastrados.</option>";
            }
            ?>
        </select>
    </td></tr>		        	
</table>
<?php
if (!empty($curso)) {
    ?>

    <table id="form" border="0" align="center" width="100%">
        <tr><th align="center" width="40">#</th><th align="left">Disciplina</th><th align="left">Professor</th><th align="left">Turma</th><th align="left">Fechado</th>
            <th width="40" align="center"><input type='checkbox' id='select-all' name='select-all' class='campoTodos' value='' /></th></tr>
        <?php
        // efetuando a consulta para listagem
        $sql = "select a.codigo, d.nome, d.codigo, t.numero, DATEDIFF(a.prazo,NOW()), a.bimestre, a.prazo, p.professor, a.status
		    from Atribuicoes a, Disciplinas d, Cursos c, Turmas t, Professores p
		    where a.turma=t.codigo
		    and t.curso=c.codigo
		    and a.disciplina=d.codigo
        and p.atribuicao = a.codigo
		    and t.ano=$ano
		    and (t.semestre=$semestre OR t.semestre=0)
		    and d.curso=c.codigo $restricao GROUP BY a.codigo order by d.nome";
        //echo $sql;
        $resultado = mysql_query($sql);
        $i = 1;
        if ($resultado) {
            while ($linha = mysql_fetch_array($resultado)) {
                $professores = '';
                foreach ($prof->getProfessor($linha[0]) as $key => $reg)
                    $professores[] = $reg['nome'];
                $professores = implode("<br>", $professores);

                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                if ($linha[5] != 0)
                    $bimestre = "[" . $linha[5] . "º Bim]";
                echo "<tr $cdif><td align='center'>$i</td>";
                echo "<td><a target='_blank' href='relatorios/diarioProfessor.php?atribuicao=" . crip($linha[0]) . "'>$bimestre " . mostraTexto($linha[1]) . "</a></td>";
                echo "<td align='left'>" . mostraTexto($professores) . "</td><td align=left>$linha[3]</td>";
                $bloqueado = "";
                $origem = "";

                if ($linha[6] != '0000-00-00 00:00:00' && $linha[4] > 0) {
                    $origem = ($linha[4] * 24) . "h";
                } else {
                    if ($linha[8] == 1)
                        $origem = "Coord";
                    if ($linha[8] == 2)
                        $origem = "Prof";
                    if ($linha[8] == 3)
                        $origem = "Secre";
                    if ($linha[8] == 4)
                        $origem = "SYS";
                }
                echo "<td align='left'>$origem</td>";


                if ($linha[6] != '0000-00-00 00:00:00' && $linha[4] < 0) {
                    mysql_query("UPDATE Atribuicoes SET status=4,prazo='' WHERE codigo = " . $linha[0]);
                    $linha[6] = '0000-00-00 00:00:00';
                }

                echo "<td align='center'>";
                print "<input $bloqueado type='checkbox' id='campoPrazo' name='campoPrazo[]' value='" . $linha[0] . "' />";
                echo "</td></tr>";

                $i++;
            }
            print "</form>";
        }
        ?>
    </table>
    </div>
    <?php
// LISTAGEM DE PRAZOS PRORROGADOS NO SEMESTRE
// inicializando as variáveis
    $item = 1;
    $itensPorPagina = 50;
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
    $sql = "select COUNT(*)
	    from PrazosDiarios l, Atribuicoes a, Turmas t, Disciplinas d, Cursos c, Professores p
	    where l.atribuicao=a.codigo
	    and t.curso=c.codigo
	    and a.turma=t.codigo
	    and a.disciplina=d.codigo
      and p.atribuicao = a.codigo	    
	    and t.ano=$ano
	    and (t.semestre=$semestre OR t.semestre=0)
	    $restricao order by l.data desc";
//    echo $sql;
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

    $sql = "select date_format(l.data, '%d/%m/%Y %H:%i'), d.nome, a.codigo, l.motivo
	    from PrazosDiarios l, Atribuicoes a, Turmas t, Disciplinas d, Cursos c, Professores p
	    where l.atribuicao=a.codigo
	    and t.curso=c.codigo
	    and a.turma=t.codigo
	    and a.disciplina=d.codigo
      and p.atribuicao = a.codigo	    
	    and t.ano=$ano
	    and (t.semestre=$semestre OR t.semestre=0)
	    $restricao order by l.data desc limit " . ($item - 1) . ",$itensPorPagina";
//print $sql;
    $SITENAV = $SITE . "?$lk";
    include PATH . VIEW . '/navegacao.php';
    ?>

    <table id="listagem" border="0" align="center">
        <tr><th align="center" width="40">#</th><th align="left">Data</th><th align="left">Disciplina</th><th>Professor</th><th width="150">Motivo</th></tr>
        <?php
        // efetuando a consulta para listagem
        $resultado = mysql_query($sql);
        $i = $item;
        while ($linha = mysql_fetch_array($resultado)) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";

            $professores = '';
            foreach ($prof->getProfessor($linha[2]) as $key => $reg)
                $professores[] = $reg['nome'];
            $professores = implode("<br>", $professores);

            echo "<tr $cdif><td align='center'>$i</td><td>$linha[0]</td><td>$linha[1]</a></td><td>$professores</td><td><a href='#Data' title='$linha[3]'>" . abreviar($linha[3], 25) . "</a></td></tr>";
            $i++;
        }
        mysql_close($conexao);
        ?>
    </table>

    <?php
}
?>
<script>
    $('#select-all').click(function(event) {
        if (this.checked) {
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

    $(document).ready(function() {
        $('#fechar').focusin(function() {
            $('#botao').val('fechar');
        });
        $('#liberar').focusin(function() {
            $('#botao').val('liberar');
        });

        function valida() {
            turma = $('#campoTurma').val();
            curso = $('#campoCurso').val();
            bimestre = $('#campoBimestre').val();
            professor = $('#campoProfessor').val();
            $('#index').load('<?php print $SITE; ?>?&turma=' + turma + '&curso=' + curso + '&bimestre=' + bimestre + '&professor=' + professor);
        }

        $('#campoTurma, #campoCurso, #campoBimestre, #campoProfessor').change(function() {
            valida();
        });

        $('#form_padrao').on('submit', function(e) {
            turma = $('#campoTurma').val();
            bimestre = $('#campoBimestre').val();
            professor = $('#campoProfessor').val();

            e.preventDefault();
            if ($('#botao').val() == 'liberar') {
                modo = 'Confirma a liberação temporária do prazo de gerenciamento do diário? \n\n Motivo:';
                opcao = 'controlePrazo';
            }
            if ($('#botao').val() == 'fechar') {
                modo = 'Confirma a fechar o diário? \n\n Motivo:';
                opcao = 'controleDiario';
            }

            jPrompt(modo, '', '<?php print $TITLE; ?>', function(r)
            {
                if (r) {
                    r = encodeURI(r);
                    var prazo = $.map($('input:checkbox:checked'), function(e, i) {
                        return +e.value;
                    });
                    var curso = '<?php print $curso; ?>';
                    $('#index').load('<?php print $SITE; ?>?opcao=' + opcao + '&prazo=' + prazo + '&curso=' + curso + '&turma=' + turma + '&bimestre=' + bimestre + '&valor1=' + r + '&professor=' + professor);
                }
            }
            );
        }
        );
    });
</script>