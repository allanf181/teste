<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Visualização do plano de ensino cadastrado e finalizado pelo professor.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/planoEnsino.class.php";
$plano = new PlanosEnsino();

require CONTROLLER . '/professor.class.php';
$prof = new Professores();

if ($_GET["opcao"] == 'controlePlano') {
    $_GET['codigo'] = crip($_GET['codigo']);
    $_GET['solicitante'] = $_SESSION['loginCodigo'];
    
    $GET = $_GET;
    
    unset($_GET['opcao']);
    unset($_GET['turma']);
    unset($_GET['curso']);
    unset($_GET['professor']);
    unset($_GET['_']);

    if ($_GET["conferido"] != 'false' && !$_GET["solicitacao"]) {
        $_GET["valido"] = date('Y-m-d H:m:s');
    } else {
        $_GET["valido"] = null;
        $_GET["finalizado"] = null;
    }
    unset($_GET['conferido']);

    $ret = $plano->insertOrUpdate($_GET);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    
    $_GET = $GET;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';

    if ($_SESSION['regAnterior'] && $curso != $_SESSION['regAnterior']) {
        unset($_GET["turma"]);
        unset($_GET["professor"]);
    }
    $_SESSION['regAnterior'] = $curso;    
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

if (dcrip($_GET["professor"])) {
    $professor = dcrip($_GET["professor"]);
    $params['professor'] = $professor;
    $sqlAdicional .= ' AND p.professor = :professor ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsCurso['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCurso = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord)";
}
?>
<table align="center" id="form" width="100%">
    <tr>
        <td align="right" style="width: 100px">Curso: </td>
        <td>
            <select name="curso" id="curso" value="<?php echo $curso; ?>" style="width: 350px">
                <option></option>
                <?php
                require CONTROLLER . '/curso.class.php';
                $cursos = new Cursos();
                foreach ($cursos->listCursos($paramsCurso, $sqlAdicionalCurso, null, null) as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $curso)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['curso'] . " [" . $reg['codigo'] . "]</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td align="right">Turma: </td>
        <td>
            <select name="turma" id="turma" style="width: 350px">
                <option></option>
                <?php
                require CONTROLLER . '/turma.class.php';
                $turmas = new Turmas();
                $sqlAdicionaTurma = ' AND c.codigo = :curso ';
                $paramsTurma = array(':curso' => $curso, ':ano' => $ANO, ':semestre' => $SEMESTRE);
                foreach ($turmas->listTurmas($paramsTurma, $sqlAdicionaTurma) as $reg) {
                    $selected = "";
                    if ($reg['codTurma'] == $turma)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td align="right">Professor: </td>
        <td>
            <select name="professor" id="professor" style="width: 350px">
                <option></option>
                <?php
                $sqlAdicionalProf = "AND pr.atribuicao "
                        . "IN (SELECT a1.codigo "
                        . "FROM Atribuicoes a1 "
                        . "WHERE a1.turma = :turmaP)";
                $paramsProf['tipo'] = $PROFESSOR;
                $paramsProf['turmaP'] = $turma;

                foreach ($prof->listProfessores($paramsProf, $sqlAdicionalProf) as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $professor)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select>
        </td>
    </tr>		        	            
</table>
<?php
if (!empty($curso)) {
    $params['ano'] = $ANO;
    $params['semestre'] = $SEMESTRE;

    $sqlAdicional .= " AND (a.bimestre = 0 OR a.bimestre = 1)
                   AND t.ano=:ano
		   AND (t.semestre=:semestre OR t.semestre=0)
                   AND ((pe.finalizado IS NOT NULL 
                            AND pe.finalizado <> '' 
                            AND pe.finalizado <> '0000-00-00 00:00:00')
                        OR ( pe.solicitacao <> '' ) )";
    $res = $plano->listPlanoEnsino($params, $sqlAdicional);
    ?>
    <br />
    <table id="listagem" border="0" align="center" width="100%">
        <tr>
            <th align="center" width="40">#</th>
            <th align="left">Disciplina</th>
            <th align="left">Professor</th>
            <th align="left" style="width: 80px">Turma</th>
            <?php
            if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                ?>
                <th align="center" style="width: 100px">Coordenador</th>
                <?php
            }
            if (in_array($COORD, $_SESSION["loginTipo"]) || in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                ?>
                <th align='center' style="width: 100px" title='Solicitar Corre&ccedil;&atilde;o?'>Corre&ccedil;&atilde;o</th>
                <th align='center' title='Marcar como conferido?'>Conf?</th>
                <?php
            }
            ?>
        </tr>
        <?php
        $i = 1;
        if ($res) {
            foreach ($res as $reg) {
                $checked = '';
                $correcao = 0;
                $bloqueado = '';

                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?> style='height: 40px'>
                    <td align='center'><?= $i ?></td>
                    <td>
                        <a target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/planoEnsino.php?atribuicao=<?= crip($reg['atribuicao']) ?>' title="<?= $reg['disciplina'] ?><br>Clique aqui para ver o plano."><?= mostraTexto($reg['numero']) ?></a>
                    </td>
                    <td align='left'>
                        <?= $prof->getProfessor($reg['atribuicao'], '<br>', 1, 1) ?>
                    </td>
                    <td align='left'><?= $reg['turma'] ?></td>
                    <?php
                    if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                        if ($reg['valido'] != "00/00/0000 00:00" && $reg['valido'] != "") {
                            ?>
                            <td align = 'left'><?= $reg['solicitante'] ?></td>
                            <?php
                        } else {
                            ?>
                            <td align = 'left' style = 'color:red; font-weight: bold'>pendente</td>
                            <?php
                        }
                    }

                    // VERIFICA SE JÀ FOI CORRIGIDO
                    if ($reg['valido'] != "00/00/0000 00:00" && $reg['valido'] != "") {
                        ?>
                        <td align='center'>Plano corrigido</a></td>
                        <?php
                        $checked = "checked = 'checked'";
                    } else if ($reg['solicitacao']) {
                        ?>
                        <td align='center' colspan='2'>
                            <a href='#' title='Corre&ccedil;&atilde;o solicitada por <?= $reg['solicitante'] ?>'>Correção solicitada</a>
                        </td>
                        <?php
                        $correcao = 1;
                    } else {
                        if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"])) {
                            ?>
                            <td align='center'>
                                <a href='#' title='Solicitar corre&ccedil;&atilde;o' onclick="return plano('<?= $reg['codigo'] ?>', '<?= $reg['disciplina'] ?>', '<?= crip($curso) ?>', '<?= crip($professor) ?>', '<?= crip($turma) ?>');">
                                    <img class='botao campoCorrecao' src='<?= ICONS ?>/cancel.png' />
                                </a>
                            </td>
                            <?php
                        }
                    }

                    if (!$correcao) {
                        if ($reg['valido'] != "00/00/0000 00:00" || $reg['valido'] != "") {
                            if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($COORD, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]))
                                $bloqueado = "disabled='disabled' title='pendente'";

                            if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]) && $checked)
                                $bloqueado = "disabled='disabled' title='Somente GED'";
                            ?>
                            <td align='center'>
                                <input <?= $bloqueado ?> type='checkbox' <?= $checked ?> id='<?= $reg['codigo'] ?>' onclick="return confPlano(this.checked, '<?= $reg['disciplina'] ?>', '<?= $reg['codigo'] ?>', '<?= crip($curso) ?>', '<?= crip($professor) ?>', '<?= crip($turma) ?>');">
                            </td>
                            <?php
                        } else {
                            ?>
                            <td>&nbsp;</td>
                            <?php
                        }
                    }
                    ?>
                </tr>
                <?php
                $i++;
            }
        }
    }
    ?>
</table>
<script>
    function valida() {
        turma = $('#turma').val();
        curso = $('#curso').val();
        bimestre = $('#bimestre').val();
        professor = $('#professor').val();
        $('#index').load('<?php print $SITE; ?>?&turma=' + turma + '&curso=' + curso + '&bimestre=' + bimestre + '&professor=' + professor);
    }

    $('#turma, #curso, #bimestre, #professor').change(function() {
        valida();
    });

    function plano(codigo, nome, curso, professor, turma) {
        $.Zebra_Dialog('<strong>Confirma a solicitação de correção do plano de ensino de ' + nome + '?<br><br>Motivo:</strong>', {
            'type': 'prompt',
            'title': '<?php print $TITLE; ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption, valor) {
                if (caption == 'Sim') {
                    $('#index').load('<?php print $SITE; ?>?opcao=controlePlano&codigo=' + codigo + '&curso=' + curso + '&solicitacao=' + valor + "&professor=" + professor + '&turma=' + turma);
                }
            }
        });
    }

    function confPlano(checked, nome, codigo, curso, professor, turma) {
        if (!checked)
            modo = 'Confirma reabrir o plano de ensino ' + nome + ' para alteração?';
        else
            modo = 'Confirma a conferência do plano de ' + nome + '?<br><br>Atenção: somente o GED poderá abrir novamente!';

        $.Zebra_Dialog('<strong>' + modo + '</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption) {
                if (caption == 'Sim') {
                    $('#index').load('<?php print $SITE; ?>?opcao=controlePlano&curso=' + curso + '&codigo=' + codigo + '&conferido=' + checked + "&professor=" + professor + '&turma=' + turma);
                } else
                    document.getElementById(codigo).checked = !checked;
            }
        });
    }
</script>