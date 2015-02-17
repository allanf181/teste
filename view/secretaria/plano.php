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

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

if ($_GET["opcao"] == 'historico') {
    $_GET['tabela'] = 'PlanoEnsino';
    // COPIA DE:
    require PATH.VIEW.'/common/logSolicitacao.php';
    die;
}

if ($_GET["opcao"] == 'change') {
    $params['nomeTabela'] = 'PlanoEnsino';
    $params['solicitante'] = $_SESSION['loginCodigo'];
    $params['dataSolicitacao'] = date('Y-m-d H:m:s');
    $params['codigoTabela'] = $_GET['atribuicao'];
    $params['solicitacao'] = 'Correção solicitada: '.$_GET['solicitacao'];

    $ret = $log->insertOrUpdate($params);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    $paramsPlano["finalizado"] = null;
    $paramsPlano['codigo'] = $_GET['codigo'];
    $ret = $plano->insertOrUpdate($paramsPlano);
    
    //ENVIANDO EMAIL
    if ($ret['STATUS'] == 'OK') {
        $resPlano = $plano->listRegistros(array('codigo' => $_GET['codigo']));
        if ($email = $pessoa->getEmailFromAtribuicao($resPlano[0]['atribuicao']))
            $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Foi solicitada uma corre&ccedil;&atilde;o em seu Plano de Ensino.', $email);
    }
}

if ($_GET["opcao"] == 'controle') {
    $params['codigo'] = crip($_GET['codigo']);

    if ($_GET["conferido"] != 'false' && !$_GET["solicitacao"]) {
        $params["valido"] = date('Y-m-d H:m:s');
        $paramsLog['solicitacao'] = 'Plano validado.';
    } else {
        $paramsLog['solicitacao'] = 'Plano aberto para alteração.';
        $params["valido"] = null;
        $params["finalizado"] = null;
    }

    $paramsLog['nomeTabela'] = 'PlanoEnsino';
    $paramsLog['solicitante'] = $_SESSION['loginCodigo'];
    $paramsLog['dataSolicitacao'] = date('Y-m-d H:m:s');
    $paramsLog['dataConcessao'] = date('Y-m-d H:m:s');
    $paramsLog['codigoTabela'] = $_GET['atribuicao'];
    $ret = $log->insertOrUpdate($paramsLog);

    $ret = $plano->insertOrUpdate($params);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
$params = array();

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
            <select name="curso" id="curso" value="<?= $curso ?>" style="width: 350px">
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
		   AND (t.semestre=:semestre OR t.semestre=0)";
    $res = $plano->listPlanoEnsino($params, $sqlAdicional);
    ?>
    <br />
    <table id="listagem" border="0" align="center" width="100%">
        <tr>
            <th align="center" width="40">#</th>
            <th align="left">Disciplina</th>
            <th align="left">Professor</th>
            <th align="left" width="150">Finalizado</th>
            <?php
            if (in_array($COORD, $_SESSION["loginTipo"]) || in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                ?>
                <th align='center' width="140" title='Solicitar Corre&ccedil;&atilde;o?'>Solicitar Corre&ccedil;&atilde;o</th>
                <th width="80" align='center' title='Marcar como conferido?'>Validado?</th>
                <th width="80" align='center'>Hist&oacute;rico</th>
                <?php
            }
            ?>
        </tr>
        <?php
        $i = 1;
        if ($res) {
            foreach ($res as $reg) {
                if ($reg['finalizado'] == '' || $reg['finalizado'] == '00/00/0000 00:00')
                    $reg['finalizado'] = 'NÃO';

                $checked = '';
                $correcao = 0;
                $bloqueado = '';

                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?> style='height: 40px'>
                    <td align='center'><?= $i ?></td>
                    <td>
                        <a target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/planoEnsino.php?atribuicao=<?= crip($reg['atribuicao']) ?>' title="<?= $reg['disciplina'] ?><br>Clique aqui para ver o plano."><?= mostraTexto($reg['numero']) ?> [<?= $reg['turma'] ?>] [<?= $reg['turno'] ?>]</a>
                    </td>
                    <td align='left'>
                        <?= $prof->getProfessor($reg['atribuicao'], 1, '<br>', 1, 1) ?>
                    </td>
                    <td align='left'><?= $reg['finalizado'] ?></td>
                    <?php

                    // VERIFICA SE JÀ FOI CORRIGIDO
                    if ($reg['valido'] != "00/00/0000 00:00" && $reg['valido'] != "") {
                        ?>
                        <td align='left'>Plano corrigido</a></td>
                        <?php
                        $checked = "checked = 'checked'";
                    } else if ($reg['solicitacao']) {
                        ?>
                        <td align='left' colspan='2'>
                            Correção solicitada
                        </td>
                        <?php
                        $correcao = 1;
                    } else {
                        if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"])) {
                            ?>
                            <td align='center'>
                                <a href='#' title='Solicitar corre&ccedil;&atilde;o' onclick="return change('<?= $reg['codigo'] ?>', '<?= $reg['disciplina'] ?>', '<?= crip($curso) ?>', '<?= crip($professor) ?>', '<?= crip($turma) ?>', '<?= $reg['atribuicao'] ?>');">
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
                                <input <?= $bloqueado ?> type='checkbox' <?= $checked ?> id='<?= $reg['codigo'] ?>' onclick="return conf(this.checked, '<?= $reg['disciplina'] ?>', '<?= $reg['codigo'] ?>', '<?= crip($curso) ?>', '<?= crip($professor) ?>', '<?= crip($turma) ?>', '<?= $reg['atribuicao'] ?>');">
                            </td>
                            <?php
                        } else {
                            ?>
                            <td>&nbsp;</td>
                            <?php
                        }
                    }
                    ?>
                    <td align='center'>
                        <a href='#' title='Ver hist&oacute;rico de solicita&ccedil;&otilde;es'>
                            <img class='botao search' id='<?= crip($reg['atribuicao']) ?>' src='<?= ICONS ?>/search.png' />
                        </a>
                    </td>                             
                </tr>
                <?php
                $i++;
            }
        }
    }
    ?>
</table>
<script>
    $(".search").click(function (event) {
        var codigo = $(this).attr('id');
        new $.Zebra_Dialog('<strong>Hist&oacute;rico de Solicita&ccedil;&otilde;es</strong>', {
            source: {'iframe': {
                    'src': 'view/secretaria/plano.php?opcao=historico&codigo=' + codigo,
                    'height': 300
                }},
            width: 600,
            title: 'PLANO DE ENSINO'
        });
    });
    
    function valida() {
        turma = $('#turma').val();
        curso = $('#curso').val();
        bimestre = $('#bimestre').val();
        professor = $('#professor').val();
        $('#index').load('<?= $SITE ?>?&turma=' + turma + '&curso=' + curso + '&bimestre=' + bimestre + '&professor=' + professor);
    }

    $('#turma, #curso, #bimestre, #professor').change(function () {
        valida();
    });

    function change(codigo, nome, curso, professor, turma, atribuicao) {
        $.Zebra_Dialog('<strong>Confirma a solicitação de correção do plano de ensino de ' + nome + '?<br><br>Motivo:</strong>', {
            'type': 'prompt',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption, valor) {
                if (caption == 'Sim') {
                    $('#index').load('<?= $SITE ?>?opcao=change&codigo=' + codigo + '&curso=' + curso + '&solicitacao=' + valor + "&professor=" + professor + '&turma=' + turma + '&atribuicao=' + atribuicao);
                }
            }
        });
    }

    function conf(checked, nome, codigo, curso, professor, turma, atribuicao) {
        if (!checked)
            modo = 'Confirma reabrir o plano de ensino ' + nome + ' para alteração?';
        else
            modo = 'Confirma a conferência do plano de ' + nome + '?<br><br>Atenção: somente o GED poderá abrir novamente!';

        $.Zebra_Dialog('<strong>' + modo + '</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    $('#index').load('<?= $SITE ?>?opcao=controle&curso=' + curso + '&codigo=' + codigo + '&conferido=' + checked + "&professor=" + professor + '&turma=' + turma + '&atribuicao=' + atribuicao);
                } else
                    document.getElementById(codigo).checked = !checked;
            }
        });
    }
</script>