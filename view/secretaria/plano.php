<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Visualização do plano de ensino cadastrado e finalizado pelo professor.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/professor.class.php";
$prof = new Professores();

require CONTROLLER . "/planoEnsino.class.php";
$plano = new PlanosEnsino();

if ($_GET["opcao"] == 'controlePlano') {
    $atribuicao = $_GET["atribuicao"];
    $v = $_GET["valor1"];
    $conferido = $_GET["conferido"];

    if ($conferido != 'false' && !$v) {
        $sql = "update PlanosEnsino set valido=now() where atribuicao=$atribuicao";
    } else
        $sql = "update PlanosEnsino set valido='', finalizado='', solicitacao='$v', solicitante='" . $_SESSION['loginCodigo'] . "' where atribuicao=$atribuicao";
    //print $sql;
    $resultado = mysql_query($sql);
    if ($resultado == 1)
        mensagem('OK', 'TRUE_INSERT');
    else
        mensagem('NOK', 'FALSE_INSERT');
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (isset($_GET["turma"]) && $_GET["turma"] != "") {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional = ' AND t.codigo = :turma ';
}

if (isset($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $turma;
    $sqlAdicional .= ' AND c.codigo = :curso ';
}

if (isset($_GET["bimestre"]) && $_GET["bimestre"] != "") {
    if ($_GET["bimestre"] != "undefined") {
        $bimestre = dcrip($_GET["bimestre"]);
        $params['bimestre'] = $turma;
        $sqlAdicional .= ' AND a.bimestre = :bimestre ';
    }
}

if (isset($_GET["professor"]) && dcrip($_GET["professor"]) != 'Todos') {
    $professor = dcrip($_GET["professor"]);
    $params['professor'] = $turma;
    $sqlAdicional .= ' AND p.professor = :professor ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsCurso['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCurso = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord)";
}
?>
<table align="center" id="form" width="100%">
    <tr><td align="right" style="width: 100px">Curso: </td><td>
            <select name="curso" id="curso" value="<?php echo $curso; ?>" style="width: 350px">
                <option></option>
                <?php
                require CONTROLLER . '/curso.class.php';
                $cursos = new Cursos();
                $res = $cursos->listCursos($paramsCurso, $sqlAdicionalCurso, null, null);
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $curso)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['curso'] ."</option>";
                }
                ?>
            </select>
        </td></tr>
    <tr><td align="right">Turma: </td>
        <td><select name="turma" id="turma" style="width: 350px">
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
                    echo "<option>Não há professores cadastrados.</option>";
                }
                ?>
            </select>
        </td></tr>		        	            
</table>
<?php
if (!empty($curso)) {

    // efetuando a consulta para listagem
    $sql = "SELECT a.codigo, d.numero, d.codigo, t.numero, pe.solicitacao, 
    				date_format(pe.valido,'%d/%m/%Y %H:%i'), pe.solicitante, d.nome,
    				(SELECT p2.nome FROM Pessoas p2 WHERE pe.solicitante = p2.codigo) as coordenador, p.professor
				FROM Atribuicoes a, Disciplinas d, Turmas t, Cursos c, PlanosEnsino pe, PlanosAula pa, Professores p
				WHERE a.disciplina = d.codigo
				AND t.codigo = a.turma
				AND c.codigo = t.curso
				AND pe.atribuicao = a.codigo
				AND pa.atribuicao = a.codigo
				AND pa.atribuicao = pe.atribuicao
        AND p.atribuicao = a.codigo
				AND (a.bimestre = 0 OR a.bimestre = 1)
				AND t.ano=$ano
				AND (t.semestre=$semestre OR t.semestre=0)
                                AND (pe.finalizado IS NOT NULL AND pe.finalizado <> '' AND pe.finalizado <> '0000-00-00 00:00:00')
        and d.curso=c.codigo $restricao
        GROUP BY a.codigo
        order by d.nome";
    //echo $sql;
    $resultado = mysql_query($sql);

    if ($resultado) {
        ?>
        <table id="form" border="0" align="center" width="100%">
            <tr><th align="center" width="40">#</th><th align="left">Disciplina</th><th align="left">Professor</th><th align="left" style="width: 80px">Turma</th>
                    <?php
                    if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]))
                        echo "<th align=\"center\" style=\"width: 100px\">Coordenador</th>";

                    if (in_array($COORD, $_SESSION["loginTipo"]) || in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                        echo "<th align='center' style=\"width: 100px\" title='Solicitar Corre&ccedil;&atilde;o?'>Corre&ccedil;&atilde;o</th>";
                        echo "<th align='center' title='Marcar como conferido?'>Conf?</th>";
                    }
                    echo "</tr>";


                    $i = 1;
                    if ($resultado) {
                        while ($linha = mysql_fetch_array($resultado)) {
                            $checked = '';
                            $correcao = 0;
                            $bloqueado = '';
                            $solicitacao = $linha[4];
                            $valido = $linha[5];
                            $solicitante = $linha[6];
                            $coordenador = $linha[8];

                            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                            echo "<tr $cdif style='height: 40px'><td align='center'>$i</td>";
                            echo "<td><a target='_blank' href='" . VIEW . "/secretaria/relatorios/inc/planoEnsino.php?atribuicao=" . crip($linha[0]) . "' title=\"$linha[7]\">" . mostraTexto($linha[1]) . "</a></td>";
                            echo "<td align='left'>" . $prof->getProfessor($linha[0], '<br>', 1, 1) . "</td><td align='left'>$linha[3]</td>";

                            if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                                if ($valido != "00/00/0000 00:00" && $valido != "")
                                    echo "<td align='left'>$coordenador</td>";
                                else
                                    echo "<td align='left' style='color:red; font-weight: bold'>pendente</td>";
                            }

                            // VERIFICA SE JÀ FOI CORRIGIDO
                            if ($valido != "00/00/0000 00:00" && $valido != "") {
                                echo "<td align='center'>Plano corrigido</a></td>";
                                $checked = "checked='checked'";
                            } else if ($solicitacao) {
                                echo "<td align='center' colspan='2'><a href='#' title='Correção solicitada por $linha[8]'>Correção solicitada</a></td>";
                                $correcao = 1;
                            } else {
                                if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"]))
                                    echo "<td align='center'><a href='#' title='Solicitar correção' onclick=\"return plano('" . $linha[0] . "', '" . $linha[1] . "', '" . crip($curso) . "', '" . crip($linha[9]) . "')\";><img class='botao campoCorrecao' id='" . crip($linha[0]) . "' name='$linha[0]' src='" . ICONS . "/cancel.png' /></a></td>";
                            }

                            if (!$correcao) {
                                if ($valido != "00/00/0000 00:00" || $valido != "") {
                                    if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($COORD, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]))
                                        $bloqueado = "disabled='disabled' title='pendente'";

                                    if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]) && $checked)
                                        $bloqueado = "disabled='disabled' title='Somente GED'";

                                    echo "<td align='center'>
                		<input $bloqueado type='checkbox' $checked id='" . $linha[0] . "' value='$valor' onclick=\"return confPlano(this.value, this.checked, '" . $linha[7] . "', '" . $linha[0] . "', '" . crip($curso) . "', '" . crip($linha[9]) . "')\";
	              		</td>";
                                } else {
                                    echo "<td>&nbsp;</td>";
                                }
                            }
                            echo "</tr>";
                            $i++;
                        }
                    }
                } else {
                    print "Nenhum plano de ensino foi entregue.";
                }
                mysql_close($conexao);
                ?>
    </table>
<?php } ?>
<script>
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

    function plano(atribuicao, nome, curso, professor) {
        modo = 'Confirma a solicitação de correção do plano de ensino de ' + nome + '? \n\n Motivo:';
        jPrompt(modo, '', '<?php print $TITLE; ?>', function(r)
        {
            if (r) {
                r = encodeURI(r);
                $('#index').load('<?php print $SITE; ?>?opcao=controlePlano&atribuicao=' + atribuicao + '&curso=' + curso + '&valor1=' + r + "&professor=" + professor);
            }
        });
    }
    function confPlano(value, checked, nome, atribuicao, curso, professor) {
        if (!checked)
            modo = 'Confirma abrir o diario de ' + nome + '?';
        else
            modo = 'Confirma a conferência do plano de ' + nome + '?\n\nAtenção: somente o GED poderá abrir novamente!';
        jConfirm(modo, 'Fechamento', function(r) {
            if (r)
                $('#index').load('<?php print $SITE; ?>?opcao=controlePlano&curso=' + curso + '&atribuicao=' + atribuicao + '&conferido=' + checked + "&professor=" + professor);
            else
                document.getElementById(atribuicao).checked = !checked;
        });
    }
</script>