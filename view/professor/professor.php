<?php
//Esse arquivo é fixo para o professor.
//Habilita a tela principal do professor.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

require CONTROLLER . "/prazoDiario.class.php";
$prazoDiario = new PrazosDiarios();

// PEDIDO DE LIBERAÇÃO DO DIÁRIO
if ($_GET["motivo"]) {
    $_GET['data'] = date('Y-m-d h:i:s');
    unset($_GET['_']);
    $ret = $prazoDiario->insertOrUpdate($_GET);
    mensagem($ret['STATUS'], 'PRAZO_DIARIO');
}

if ($_GET["opcao"] == 'controleDiario') {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $status = $_GET["status"];

    require CONTROLLER . "/notaFinal.class.php";
    $nota = new NotasFinais();

    if (!$erro = $nota->fecharDiario($atribuicao)) {
        $params['codigo'] = $atribuicao;
        $params['status'] = $status;
        $ret = $att->insertOrUpdate($params);
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    } else {
        if ($erro == 2)
            mensagem('NOK', 'FALSE_CLOSE_CLASS_REGISTRY');
        else
            mensagem('NOK', 'FALSE_UPDATE');
    }
}

$atribuicao = $_GET["atribuicao"];
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

<?php
if ($_GET["atribuicao"]) {
    ?>
    <table width="100%" align="center" border="0">
        <?php
        $atribuicao = dcrip($_GET["atribuicao"]);

        $res = $att->getAtribuicao($atribuicao, $LIMITE_AULA_PROF, $LIMITE_DIARIO_PROF);
        extract(array_map("htmlspecialchars", $res), EXTR_OVERWRITE);

        if (!$bimestre && !$semestre)
            $bimestre = 'ANUAL';
        elseif ($bimestre && $semestre)
            $bimestre .= 'º BIMESTRE';
        elseif (!$bimestre && $semestre)
            $bimestre = 'SEMESTRAL';

        // CASO O LIMITE NÃO ESTEJA DESATIVADO
        if ($LIMITE_DIARIO_PROF != 0) {
            // Anulando o prazo se a dataFim está maior
            $dataExpirou = false;
            if ($prazoDiff && $prazoDiff <= 0 && $dataFimDiff >= 0) {
                $params['codigo'] = crip($atribuicao);
                $params['prazo'] = crip('NULL');
                $params['status'] = crip(0);
                $status = 0;
                $prazo = null;
                $att->insertOrUpdate($params);
            } else {
                // verificando se o prazo foi atingido.
                $dataExpirou = false;
                if ($status == 0 && (!$prazoDiff && $dataFimDiff < 0) || ($prazoDiff && $prazoDiff < 0)) {
                    $params['codigo'] = crip($atribuicao);
                    $params['status'] = crip(4);
                    $att->insertOrUpdate($params);
                    $status = 4;
                    $dataExpirou = true;
                }
            }
        } else {// SEM LIMITE CASO O FECHAMENTO NÂO FOR MANUAL
            if ($status > 3)
                $status = 0; 
            $prazo = null;
            $prazoDiff = -1;
            $dataExpirou = false;

            if ($diarioAberto < 0) { // diário ainda não começou
                $dataExpirou = true;
                $status = 100;
            }
        }
        
        if ($status != 0)
            $dataExpirou = true;
        $_SESSION['dataExpirou'] = $dataExpirou;

        // Informa se o diário foi aberto
        if ($prazoDiff > 0 && !$dataExpirou)
            mensagem('INFO', 'PRAZO_DIARIO', $prazoFormat);

        require CONTROLLER . "/aula.class.php";
        $aula = new Aulas();
        $qdeAulas = $aula->countQdeAulas($atribuicao);

        require CONTROLLER . "/avaliacao.class.php";
        $avaliacao = new Avaliacoes();
        $qdeAvaliacoes = $avaliacao->getQdeAvaliacoes($atribuicao);

        // desabilita edição se o status for igual a 1 e informa se o prazo foi estendido.
        if ($status > 0 || ($prazoFormat != '00:00 de 00/00/0000' && $prazoFormat != NULL)) {
            if ($status == 1)
                $info = "Este diário foi fechado pelo Coordenador!";
            if ($status == 2)
                $info = "Você já finalizou este diário!";
            if ($status == 3)
                $info = "Este diário foi fechado pela Secretaria!";
            if ($status == 4)
                $info = "Este diário foi fechado pelo Sistema pois o prazo para finalização do diário foi atingido!";
            if ($status == 100)
                $info = "Esse di&aacute;rio ainda n&atilde;o come&ccedil;ou!";
            if ($prazo && !$status)
                $info = "Seu prazo para altera&ccedil;&atilde;o do di&aacute;rio foi estentido at&eacute; &agrave;s $prazoFormat";
        }
        else {
            if ($dataExpirou || ($CH && $qdeAulas >= $aulaPrevista && $qdeAvaliacoes['avalCadastradas'] >= $qdeAvaliacoes['qdeMinima'] && $status == 0 )) { // está desbloqueado e já tem a quantidade de aulas previstas e pelo menos uma avaliação ou a data final do período foi atingida
                $pergunta = "Sr. Professor:<br />";

                if ($dataExpirou) {
                    $pergunta.= "A data final do período letivo ($dataFimFormat) foi atingida. <br /> <br />";
                    $pergunta.="Atenção: O seu diário foi bloqueado e somente o coordenador poderá desfazer esta operação.";
                    $pergunta.="<br>Deseja tentar finalizar seu di&aacute;rio, caso j&aacute; tenha conclu&iacute;do a digita&ccedil;&atilde;o de aulas a notas?";
                } else {
                    $pergunta.= "O número de aulas do diário está completo e avaliações foram aplicadas. <br /> <br />";
                    $pergunta.="<b>Deseja finalizar a digitação do diário e efetuar a entrega à secretaria?</b> <br /> <br />";
                    $pergunte.="Atenção: O seu diário será bloqueado e somente o coordenador poderá desfazer esta operação.";
                }
            }
        }
        ?>
        <h2><?php print abreviar("$disciplina [$subturma]: $turma/$curso", 150); ?></h2>
        <div style="float: left">
            <a title='IN&Iacute;CIO' href="javascript:$('#index').load('<?= VIEW; ?>/professor/professor.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);">
                <img src='<?= ICONS ?>/home.png'>
            </a>
        </div>
        <div>
            <h2 id='titulo_disciplina_modalidade'><?= $bimestre ?></h2>
        </div>
        <br />
        <tr valign="top" align='center'>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/aula.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/aulas.png' /><br />Aulas</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/avaliacao.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/avaliacoes.png' /><br />Avalia&ccedil;&otilde;es</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/diario.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/diario.png' /><br />Di&aacute;rio de Classe</a></td>
            <td valign="top" width="90"><a class='nav professores_item' id='listaChamada' target='_blank' href='<?php print VIEW; ?>/secretaria/relatorios/inc/chamada.php?atribuicao=<?php print crip($atribuicao); ?>'><img style='width: 80px' src='<?php print IMAGES; ?>/chamada.png' /><br />Lista de Chamada</a></td>
            <?php
            if ($bimestre == "SEMESTRAL" || $bimestre == "1º BIMESTRE" || $bimestre == "ANUAL") {
                ?>
                <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/plano.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/planoEnsino.png' /><br />Plano de Ensino</a></td>
                <?php
            }
            ?>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/aviso.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/aviso.png' /><br />Avisos para Turma</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/ensalamento.php?turma=<?php print crip($turmaCodigo); ?>&subturma=<?php print crip($subturma); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/horario.png' /><br />Hor&aacute;rio da Turma</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/arquivo.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/arquivo.png' /><br />Material de Aula</a></td>
        </tr>
        <tr><td colspan="8"><hr></td></tr>
    </table>

    <div id="professor">

        <table border="0">
            <tr>
                <td><b><font size="1">Aulas dadas:</b> <?php print $qdeAulas; ?><br>
                    <b>Carga Hor&aacute;ria:</b> <?php print $CH; ?>
                    <br><b>Aulas previstas:</b> <?php print $aulaPrevista; ?></font>
                </td>
                <?php
                require CONTROLLER . "/professor.class.php";
                $professor = new Professores();
                ?>
                <td>
                    <b><font size="1">N&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es:</b> <?php print $qdeAvaliacoes['qdeMinima']; ?>
                    <br>
                    <b>Avalia&ccedil;&otilde;es aplicadas:</b> <?php print $qdeAvaliacoes['avalCadastradas']; ?></font>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <font size="1"><b>Professores da disciplina:</b> <?= $professor->getProfessor($atribuicao, '', 1, 1) ?></font>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <?php
            if (!$dataExpirou) {
                ?>
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
                <td colspan="2"><font size="1">Aten&ccedil;&atilde;o: esse di&aacute;rio ser&aacute; bloqueado automaticamente pelo sistema em <?php print $dataFimFormat; ?>.</font>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h2>Prazos do Di&aacute;rio</h2>
                    <div>
                        <a href='#' id="unlock" title='Clique aqui para solicitar a liberação do diário.'>
                            <img src="<?= ICONS ?>/unlock.png">
                        </a>
                    </div>
                    <div>
                        <font size="1">Professor, se o diário foi finalizado pelo sistema e ainda tem pendências ou perdeu o prazo para digitação de aulas, clique no cadeado e informe o motivo para seu coordenador analisar sua solicitação. Ap&oacute;s liberado, voc&ecirc; ter&aacute; 24 horas para regularizar o di&aacute;rio.</font>
                    </div>
                </td>
            </tr>            
            <?php
            $res = $prazoDiario->listPrazos($atribuicao);
            if ($res) {
                ?>
                <tr>
                    <td colspan="2"><br />
                        <table id="listagem" border="0" align="center">
                            <tr>
                                <th align="center" width="40">#</th>
                                <th width="100">Data</th>
                                <th width="250">Motivo</th>
                                <th width="150">Concessão</th>
                            </tr>
                            <?php
                            $i = count($res);
                            foreach ($res as $reg) {
                                $reg['data'] = dataPTBR($reg['data']);
                                $title = $reg['motivo'];
                                if (strlen($reg['motivo']) > 70)
                                    $reg['motivo'] = abreviar($reg['motivo'], 70);
                                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                                ?>
                                <tr <?= $cdif ?>>
                                    <td align='center'><?= $i ?></td>
                                    <td><?= $reg['data'] ?></td>
                                    <td><a href='#' title='<?= $title ?>'><?= $reg['motivo'] ?></a></td>
                                    <td><?= dataPTBR($reg['dataConcessao']) ?></td>
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
<?php if ($info) print "info();" ?>
<?php if ($pergunta) print "pergunta();" ?>

    $(document).ready(function() {
        $(".finalizar").click(function() {
            pergunta();
        });
    });

    function info() {
        $.Zebra_Dialog('<strong><?php print $info; ?></strong>', {
            'type': 'information',
            'title': '<?php print $SITE_TITLE; ?>'
        });
    }

    $("#unlock").click(function() {
        $.Zebra_Dialog('<strong>Professor, informe o motivo da solicitação:</strong>', {
            'type': 'prompt',
            'title': '<?php print $TITLE; ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption, valor) {
                if (caption == 'Sim') {
                    $('#index').load('<?= $SITE ?>?motivo=' + encodeURIComponent(valor) + '&atribuicao=' + '<?= crip($atribuicao) ?>');
                }
            }
        });
    });

    function pergunta() {
        $.Zebra_Dialog('<strong><b>Deseja finalizar a digitação do diário e efetuar a entrega à secretaria?</b> <br /> <br />Atenção: O seu diário será bloqueado e somente o coordenador poderá desfazer esta operação.</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption) {
                if (caption == 'Sim') {
                    $('#index').load('<?php print $SITE; ?>?opcao=controleDiario&status=2&atribuicao=<?= crip($atribuicao) ?>');
                                    }
                                }
                            });
                        }
</script>