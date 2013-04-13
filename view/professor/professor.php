<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

if ($_POST["opcao"] == 'InsertOrUpdateObs') {
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    $_GET['atribuicao'] = $_POST['atribuicao'];

    $_POST['codigo'] = $_POST['atribuicao'];
    unset($_POST['atribuicao']);
    unset($_POST['opcao']);

    $ret = $att->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

if ($_GET["opcao"] == 'controleDiario') {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $status = $_GET["status"];
    if (!$erro = fecharDiario($atribuicao)) {
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

<table width="100%" align="center" border="0">
    <?php
    if ($_GET["atribuicao"]) {
        $atribuicao = dcrip($_GET["atribuicao"]);

        $res = $att->getAtribuicao($atribuicao, $LIMITE_AULA_PROF);

        extract(array_map("htmlspecialchars", $res), EXTR_OVERWRITE);

        if (!$bimestre && !$semestre)
            $bimestre = 'ANUAL';
        elseif ($bimestre && $semestre)
            $bimestre .= 'º BIMESTRE';
        elseif (!$bimestre && $semestre)
            $bimestre = 'SEMESTRAL';

        // verificando se o prazo foi atingido.
        if ((!$prazoDiff && $dataFimDiff < 0) || ($prazoDiff && $prazoDiff < 0)) {
            $params['codigo'] = $atribuicao;
            $att->insertOrUpdate($params);
            $status = 4;
            $dataExpirou = true;
        } else
            $dataExpirou = false;
        $_SESSION['dataExpirou'] = $dataExpirou;

        require CONTROLLER . "/aula.class.php";
        $aula = new Aulas();
        $qdeAulas = $aula->countQdeAulas($atribuicao);

        require CONTROLLER . "/avaliacao.class.php";
        $avaliacao = new Avaliacoes();
        $qdeAvaliacoes = $avaliacao->getQdeAvaliacoes($atribuicao);

        if ($bimestre <> 0 && $CH)
            $CH = $CH / 4;

        // desabilita edição se o status for igual a 1 e informa se o prazo foi estendido.
        if ($status > 0 || ($prazoFormat != '00:00 de 00/00/0000' && $prazoFormat != NULL)) {
            if ($status > 0)
                $disabled = "disabled='disabled'";
            if ($status == 1)
                $info = "Este diário foi fechado pelo Coordenador!";
            if ($status == 2)
                $info = "Você já finalizou este diário!";
            if ($status == 3)
                $info = "Este diário foi fechado pela Secretaria!";
            if ($status == 4)
                $info = "Este diário foi fechado pelo Sistema pois o prazo para finalização do diário foi atingido!";
            if ($prazo && !$status)
                $info = "Seu prazo para altera&ccedil;&atilde;o do di&aacute;rio foi estentido at&eacute; &agrave;s $prazo";
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
        <h2 id='titulo_disciplina_modalidade'><?php print $bimestre; ?></h2><br />
        <tr valign="top" align='center'>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/aula.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/aulas.png' /><br />Aulas</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/avaliacao.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/avaliacoes.png' /><br />Avalia&ccedil;&otilde;es</a></td>
            <td valign="top" width="90"><a class='professores_item' id='diario' target='_blank' href='<?php print VIEW; ?>/secretaria/relatorios/inc/diario.php?atribuicao=<?php print crip($atribuicao); ?>');  void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/diario.png' /><br />Di&aacute;rio de Classe</a></td>
            <td valign="top" width="90"><a class='professores_item' id='listaChamada' target='_blank' href='<?php print VIEW; ?>/secretaria/relatorios/inc/chamada.php?atribuicao=<?php print crip($atribuicao); ?>');  void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/chamada.png' /><br />Lista de Chamada</a></td>
            <?php
            if ($bimestre == "SEMESTRAL" || $bimestre == "1º BIMESTRE" || $bimestre == "ANUAL") {
                ?>
                <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/plano.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/planoEnsino.png' /><br />Plano de Ensino</a></td>
                <?php
            }
            ?>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/aviso.php?atribuicao=<?php print crip($atribuicao); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/aviso.png' /><br />Avisos para Turma</a></td>
            <td valign="top" width="90"><a class='nav professores_item' href="javascript:$('#professor').load('<?php print VIEW; ?>/professor/ensalamento.php?turma=<?php print crip($atribuicao); ?>&subturma=<?php print crip($subturma); ?>'); void(0);"><img style='width: 80px' src='<?php print IMAGES; ?>/horario.png' /><br />Hor&aacute;rio da Turma</a></td>
        </tr>
        <tr><td colspan="7"><hr></td></tr>
    </table>

    <div id="professor">

        <table border="0">
            <tr><td><b><font size="1">Aulas dadas:</b> <?php print $qdeAulas; ?><br><b>Carga Hor&aacute;ria:</b> <?php print $CH; ?>
                    <?php
                    if ($bimestre == 0) {
                        ?>
                        <br><b>Aulas previstas:</b> <?php print $aulaPrevista; ?></font>
                        <?php
                    }
                    ?>
                </td>
                <?php
                $professores = '';
                foreach (getProfessor($atribuicao) as $key => $reg)
                    $professores[] = "<a target=\"_blank\" href=" . $reg['lattes'] . ">" . $reg['nome'] . "</a>";
                $professor_disc = implode(" / ", $professores);
                ?>
                <td><b><font size="1">N&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es:</b> <?php print $qdeAvaliacoes['qdeMinima']; ?><br><b>Avalia&ccedil;&otilde;es aplicadas:</b> <?php print $qdeAvaliacoes['avalCadastradas']; ?></font></td></tr>
            <tr><td colspan="2"><font size="1"><b>Professores da disciplina:</b> <?php print $professor_disc; ?></font></td></tr>
            <tr><td colspan="7"><hr></td></tr>

            <tr><td colspan="7"><font size="1">Para esse di&aacute;rio ser finalizado, &eacute; necess&aacute;rio que a quantidade de aulas dadas seja maior ou igual a carga hor&aacute;ria e atingido o n&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es.
                    Caso deseje finalizar o di&aacute;rio manualmente, <a href='#' title='Excluir' class='finalizar' id='2'>clique aqui</a></td></tr>

            <tr><td colspan="2"><hr></td></tr>
            <tr><td colspan="2"><font size="1">Aten&ccedil;&atilde;o: esse di&aacute;rio ser&aacute; bloqueado automaticamente pelo sistema em <?php print $dataFimFormat; ?>.</font></td></tr>
        </table>

        <tr><td colspan=\"7\"><hr></td></tr>
        <?php
        if ($bimestre == "SEMESTRAL" || $bimestre == "1º BIMESTRE") {
            // Verificando se há correções para o Plano de Ensino.
            require CONTROLLER . "/planoEnsino.class.php";
            $plano = new PlanosEnsino();
            $hasPlano = $plano->listPlanoEnsino($atribuicao);
            ?>
            <tr><td colspan=10 align='center'><br />
                    <div id="resposta_erro"></div>
                    <div id="resposta">
                        <?php
                        if (!$hasPlano) {
                            ?>
                            <p style='color: red; font-weight: bold'>Aten&ccedil;&atilde;o: o plano de ensino desta disciplina n&atilde;o foi digitado!</p>
                            <?php
                        } else {
                            $res = $plano->hasChangePlano($_SESSION['loginCodigo'], $atribuicao);
                            if ($res[0]['PlanoSolicitacao']) {
                                ?>
                                <p style='color: red; font-weight: bold'>Aten&ccedil;&atilde;o: <?php print mostraTexto($res[0]['PlanoSolicitante']); ?> solicitou a seguinte correção em seu plano de ensino:</p>
                                <p><?php print stripslashes($res[0]['PlanoSolicitacao']); ?></p><br>
                                <?php
                            }
                        }
                    }
                    ?>
                    <tr><td colspan=10 align='center'>

                            <script>
                                $('#form_padrao').html5form({
                                    method: 'POST',
                                    action: '<?php print $SITE; ?>',
                                    responseDiv: '#index',
                                    colorOn: '#000',
                                    colorOff: '#999',
                                    messages: 'br'
                                })
                            </script>

                            <div id="html5form" class="main">
                                <form id="form_padrao">
                                    <h2>Competências Desenvolvidas:</h2>
                                    <div class='professores_textarea'>
                                        <textarea <?php print $disabled; ?> maxlength='500' id='4' name='competencias'><?php print $competencias; ?></textarea>
                                    </div>
                                    <h2>Observações a serem incluídas no diário da disciplina:</h2>
                                    <div class='professores_textarea'>
                                        <textarea <?php print $disabled; ?> maxlength='500' id='3' name='observacoes'><?php print $observacoes; ?></textarea>
                                    </div>
                                    <input type='hidden' value='<?php print crip($atribuicao); ?>' name='atribuicao' id='atribuicao' />
                                    <input type='hidden' name='opcao' value='InsertOrUpdateObs' />
                                    <input id='professores_botao' <?php print $disabled; ?> type='submit' value='Salvar' />

                                </form>
                            </div>
                        </td></tr>
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
                    $('#3, #4').maxlength({
                        events: [], // Array of events to be triggerd    
                        maxCharacters: 500, // Characters limit   
                        status: true, // True to show status indicator bewlow the element    
                        statusClass: "status", // The class on the status div  
                        statusText: "caracteres restando", // The status text  
                        notificationClass: "notification", // Will be added when maxlength is reached  
                        showAlert: false, // True to show a regular alert message    
                        alertText: "Limite de caracteres excedido!", // Text in alert message   
                        slider: true // True Use counter slider    
                    });
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
                function pergunta() {
                    $.Zebra_Dialog('<strong><b>Deseja finalizar a digitação do diário e efetuar a entrega à secretaria?</b> <br /> <br />Atenção: O seu diário será bloqueado e somente o coordenador poderá desfazer esta operação.', {
                        'type': 'question',
                        'title': '<?php print $TITLE; ?>',
                        'buttons': ['Sim', 'Não'],
                        'onClose': function(caption) {
                            if (caption == 'Sim') {
                                $('#index').load('<?php print $SITE; ?>?opcao=controleDiario&status=2&atribuicao=<?php print crip($atribuicao); ?>');
                                                }
                                            }
                                        });
                                    }
            </script>