<?php
//Esse arquivo é fixo para o professor.
//Habilita a tela principal do professor.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();

require CONTROLLER . "/coordenador.class.php";
$coordenador = new Coordenadores();

// PEDIDO DE LIBERAÇÃO DO DIÁRIO
if ($_GET["motivo"]) {
    $paramsLog['dataSolicitacao'] = date('Y-m-d h:i:s');
    $paramsLog['solicitacao'] = 'Docente solicitou abertura do diário, motivo: '.$_GET['motivo'];
    $paramsLog['codigoTabela'] = $_GET['atribuicao'];
    $paramsLog['nomeTabela'] = 'DIARIO';
    $paramsLog['solicitante'] = $_SESSION['loginCodigo'];
    $ret = $log->insertOrUpdate($paramsLog);
    mensagem($ret['STATUS'], 'PRAZO_DIARIO');
    
    if ($ret['STATUS'] == 'OK') {
        if ($coodEmail = $coordenador->getEmailCoordFromAtribuicao(dcrip($_GET['atribuicao']))) {
            $logEmail->sendEmailLogger($_SESSION['loginNome'], $paramsLog['solicitacao'], $coodEmail);
        }
    }
}

if ($_GET["opcao"] == 'controleDiario') {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $status = $_GET["status"];

    require CONTROLLER . "/notaFinal.class.php";
    $nota = new NotasFinais();

    if (!$erro = $nota->fecharDiario($atribuicao)) {
        $params['codigo'] = $atribuicao;
        $params['status'] = $status;
        $params['prazo'] = null;
        $ret = $att->insertOrUpdate($params);
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

        $paramsLog['nomeTabela'] = 'DIARIO';
        $paramsLog['solicitante'] = $_SESSION['loginCodigo'];
        $paramsLog['dataSolicitacao'] = date('Y-m-d H:m:s');
        $paramsLog['dataConcessao'] = date('Y-m-d H:m:s');
        $paramsLog['codigoTabela'] = $atribuicao;
        $paramsLog['solicitacao'] = 'Professor fechou o diário manualmente.';
        $log->insertOrUpdate($paramsLog);
        
    } else {
        if ($erro == 2)
            mensagem('NOK', 'FALSE_CLOSE_CLASS_REGISTRY');
        else
            mensagem('NOK', 'FALSE_UPDATE');
    }
}

?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>

<?php
if ($_GET["atribuicao"]) {
    ?>
    <table width="100%" align="center" border="0">
        <?php
        $atribuicao = dcrip($_GET["atribuicao"]);

        $res = $att->getAtribuicao($atribuicao, $LIMITE_DIARIO_PROF);
        extract(array_map("htmlspecialchars", $res), EXTR_OVERWRITE);

        //INFORMA AS CONDICOES DO DIARIO
        if ($info1)
            mensagem('INFO', $info1, $info2);

        require CONTROLLER . "/aula.class.php";
        $aula = new Aulas();
        $qdeAulas = $aula->countQdeAulas($atribuicao);

        require CONTROLLER . "/avaliacao.class.php";
        $avaliacao = new Avaliacoes();
        $params = array('atribuicao' => $atribuicao);
        $qdeAvaliacoes = $avaliacao->getQdeAvaliacoes($params, " AND t.tipo = 'avaliacao' ");

        if ($status == 4) {
            $pergunta = $QUESTION_DIARIO1;
        }

        if (!$status && $qdeAulas >= $aulaPrevista && $qdeAvaliacoes['avalCadastradas'] >= $qdeAvaliacoes['qdeMinima']) {
            $pergunta = $QUESTION_DIARIO2;
        }
        ?>
        <h2><?= abreviar("$disciplina [$subturma]: $turma/$curso", 150) ?></h2>
        <div style="float: left">
            <a title='IN&Iacute;CIO' href="javascript:$('#index').load('<?= VIEW; ?>/professor/professor.php?atribuicao=<?= crip($atribuicao) ?>');void(0);">
                <img src='<?= ICONS ?>/home.png'>
            </a>
        </div>
        <div>
            <h2 id='titulo_disciplina_modalidade'><?= $bimestreNome ?></h2>
        </div>
        <?php
        if ($codModalidade != 1004 && $codModalidade != 1006 && $codModalidade != 1007 && ($bimestre == 4 || $bimestre == 0))
            print "<tr><td colspan=\"9\"><font color=\"red\">A Recupera&ccedil;&atilde;o Final / Reavalia&ccedil;&atilde;o ser&aacute; realizada pelo Nambei e n&atilde;o estar&aacute; dispon&iacute;vel no Webdi&aacute;rio.</font></td></tr>\n";
        ?>

        <tr valign="top" align='center'>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/aula.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/aulas.png' /><br />Aulas</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/avaliacao.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/avaliacoes.png' /><br />Avalia&ccedil;&otilde;es</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/questionario.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/questionario.png' /><br />Question&aacute;rios</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/diario.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/diario.png' /><br />Di&aacute;rio de Classe</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/chamada.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/chamada.png' /><br />Lista de Chamada</a></td>
            <?php
            if ($bimestreNome == "SEMESTRAL" || $bimestreNome == "1º BIMESTRE" || $bimestreNome == "ANUAL") {
                ?>
                <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/plano.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/planoEnsino.png' /><br />Plano de Ensino</a></td>
                <?php
            }
            ?>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/aviso.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/aviso.png' /><br />Avisos para Turma</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/chat.php?atribuicao=<?= crip($atribuicao) ?>&timestamp=<?= time() ?>');void(0);"><div id="imageChat"><img style='width: 60px' src='<?= INC ?>/file.inc.php?type=chat&atribuicao=<?= crip($atribuicao) ?>' /></div>Chat (Atendimento)</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/ensalamento.php?turma=<?= crip($turmaCodigo) ?>&subturma=<?= crip($subturma) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/horario.png' /><br />Hor&aacute;rio da Turma</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?= VIEW ?>/professor/arquivo.php?atribuicao=<?= crip($atribuicao) ?>');void(0);"><img style='width: 60px' src='<?= IMAGES ?>/arquivo.png' /><br />Material de Aula</a></td>
        </tr>
        <tr><td colspan="9"><hr></td></tr>
    </table>

    <div id="professor">

        <table border="0">
            <tr>
                <td><b><font size="1">Aulas dadas:</b> <?= $qdeAulas ?><br>
                    <b>Carga Hor&aacute;ria:</b> <?= $CH ?>
                    <br><b>Aulas previstas:</b> <?= $aulaPrevista ?></font>
                </td>
                <?php
                require CONTROLLER . "/professor.class.php";
                $professor = new Professores();
                ?>
                <td>
                    <b><font size="1">N&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es:</b> <?= $qdeAvaliacoes['qdeMinima'] ?>
                    <br>
                    <b>Avalia&ccedil;&otilde;es aplicadas:</b> <?= $qdeAvaliacoes['avalCadastradas'] ?></font>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <font size="1"><b>Professores da disciplina:</b> <?= $professor->getProfessor($atribuicao, 1, '', 1, 1) ?></font>
                </td>
            </tr>
            <?php
            if (!$status || $status == 4) {
                ?>
                <tr>
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>            
                <tr>
                    <td colspan="2"><font size="1">Para esse di&aacute;rio ser finalizado, &eacute; necess&aacute;rio que a quantidade de aulas dadas seja maior ou igual a carga hor&aacute;ria e atingido o n&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es.
                        Caso deseje finalizar o di&aacute;rio manualmente, <a href='#' title='Finalizar Di&aacute;rio' class='finalizar' id='2'>clique aqui</a>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2"><font size="1">Aten&ccedil;&atilde;o: esse di&aacute;rio ser&aacute; bloqueado automaticamente pelo sistema em <?= $dataFimFormat ?>.</font>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td width="50px" align="center">
                                <a href='#' id="unlock" title='Clique aqui para solicitar a liberação do diário.'>
                                    <img src="<?= ICONS ?>/unlock.png">
                                </a>
                            </td>
                            <td>
                                <font size="1">Professor, se o diário foi finalizado pelo sistema e ainda tem pendências ou perdeu o prazo para digitação de aulas, clique no cadeado e informe o motivo para seu coordenador analisar sua solicitação. Ap&oacute;s liberado, voc&ecirc; ter&aacute; 24 horas para regularizar o di&aacute;rio.</font>
                            </td>
                        </tr>
                    </table>
            </tr>
            <?php
            $params = array('nomeTabela' => 'DIARIO', 'codigoTabela' => $atribuicao);
            $res = $log->listSolicitacoes($params, ' ORDER BY l.codigo DESC ');
            if ($res) {
                ?>
                <tr>
                    <td colspan="2">
                        <h2>Hist&oacute;rico de Solicita&ccedil;&otilde;es</h2>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br />
                        <table id="listagem" border="0" align="center">
                            <tr>
                                <th width="70">Data</th>
                                <th width="150">Solicitante</th>
                                <th width="150">Solicita&ccedil;&atilde;o</th>
                                <th width="70">Concess&atilde;o</th>
                            </tr>
                            <?php
                            $i = count($res);
                            foreach ($res as $reg) {
                                $title = $reg['solicitacao'];

                                if (strlen($reg['solicitacao']) > 40)
                                    $reg['solicitacao'] = abreviar($reg['solicitacao'], 40);
                                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                                ?>
                                <tr <?= $cdif ?>>
                                    <td><?= $reg['dataSolicitacao'] ?></td>
                                    <td><?= $reg['solicitante'] ?></td>
                                    <td><a href='#' data-placement="top" title='Solicita&ccedil;&atilde;o' data-content='<?= $title ?>'><?= $reg['solicitacao'] ?></a></td>
                                    <td><?= $reg['dataConcessao'] ?></td>
                                    <?php
                                    $i--;
                                }
                                ?>
                        </table>
                    </td>
                </tr>            
                <?php
            }
            ?>                
        </table>
    </div>
    <?php
} else {
    print "<p>Escolha uma disciplina</p>\n";
}
?>

<script>

<?php if ($pergunta && !$erro) print "pergunta('$pergunta');" ?>

    $(document).ready(function () {
        $(".finalizar").click(function () {
            pergunta('<?= $QUESTION_DIARIO3 ?>');
        });
    });

    $("#unlock").click(function () {
        $.Zebra_Dialog('<strong>Professor, informe o motivo da solicitação:</strong>', {
            'type': 'prompt',
            'promptInput': '<textarea rows="2" cols="30" name="Zebra_valor" maxlength="200" id="Zebra_valor"></textarea>',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption, valor) {
                if (caption == 'Sim') {
                    $('#index').load('<?= $SITE ?>?motivo=' + encodeURIComponent(valor) + '&atribuicao=' + '<?= crip($atribuicao) ?>');
                }
            }
        });
    });

    function pergunta(texto) {
        $.Zebra_Dialog('<strong>' + texto + '</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    $('#index').load('<?= $SITE ?>?opcao=controleDiario&status=2&atribuicao=' + '<?= crip($atribuicao) ?>');
                }
            }
        });
    }
</script>