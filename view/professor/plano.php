<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção do Plano de Ensino/Aula pelo professor.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_POST['pagina'])
    $_GET['pagina'] = $_POST['pagina'];
if (!$_GET['pagina'])
    $_GET['pagina'] = "entregarPlano";

require CONTROLLER . "/planoEnsino.class.php";
$planoEnsino = new PlanosEnsino();

require CONTROLLER . "/planoAula.class.php";
$planoAula = new PlanosAula();

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

require CONTROLLER . "/coordenador.class.php";
$coordenador = new Coordenadores();

require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();

$caracteres = 3000; // total do textarea

if ($_POST['atribuicao'])
    $_GET['atribuicao'] = $_POST['atribuicao'];
$atribuicao = $_GET["atribuicao"];

$sqlAdicional = ' AND a.codigo = :atribuicao ';
$params = array('atribuicao' => dcrip($atribuicao));

if ($_GET['pagina']) {
    // INSERT E UPDATE
    if ($_POST["opcao"] == 'InsertOrUpdate') {
        unset($_POST['opcao']);
        unset($_POST['pagina']);

        $tipo = $_GET['pagina'];
        $ret = $$tipo->insertOrUpdate($_POST);
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
        if ($_POST['codigo'])
            $_GET["codigo"] = $_POST['codigo'];
        else
            $_GET["codigo"] = crip($ret['RESULTADO']);
    }
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<link rel="stylesheet" type="text/css" href="<?= VIEW; ?>/css/aba.css" media="screen" />

<ul class="tabs">
    <li><a href="javascript:$('#professor').load('<?= $SITE . "?atribuicao=$atribuicao"; ?>&pagina=entregarPlano'); void(0);">Entregar Plano</a></li>	
    <li><a href="javascript:$('#professor').load('<?= $SITE . "?atribuicao=$atribuicao"; ?>&pagina=planoEnsino'); void(0);">Plano de Ensino</a></li>
    <li><a href="javascript:$('#professor').load('<?= $SITE . "?atribuicao=$atribuicao"; ?>&pagina=planoAula'); void(0);">Plano de Aula</a></li>
    <li><a href="javascript:$('#professor').load('<?= $SITE . "?atribuicao=$atribuicao"; ?>&pagina=planoCopiar'); void(0);">Copiar Plano</a></li>
    <li><a target="_blank" href="<?= VIEW; ?>/secretaria/relatorios/inc/planoEnsino.php?atribuicao=<?= $atribuicao; ?>"><img src="<?= ICONS; ?>/icon-printer.gif" width="30"></a></li>
</ul>
<div class="tab_container" id="form">
    <?php
    if ($_GET['pagina'] == "entregarPlano") {
        ?>
        <p>Caso tenha terminado de digitar o Plano de Ensino e as Aulas, clique em ENTREGAR para submeter ao seu coordenador.</p>
        <?php
        if ($_GET["entregar"]) {
            $ret = $planoEnsino->entregarPlano(dcrip($atribuicao));
            mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

            if ($ret['STATUS'] == 'OK') {
                if ($coodEmail = $coordenador->getEmailCoordFromAtribuicao(dcrip($atribuicao)))
                    $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Docente realizou entrega de Plano de Ensino. Necessita valida&ccedil;&atilde;o.', $coodEmail);
            }
        }

        $res = $planoEnsino->listPlanoEnsino($params, $sqlAdicional);
        $BLOQ = 0;
        $VALIDO = 0;

        if ($res[0]['finalizado'] && $res[0]['finalizado'] != '00/00/0000 00:00')
            $BLOQ = 1;

        if ($res[0]['valido'] && $res[0]['valido'] != '00/00/0000 00:00')
            $VALIDO = 1;

        $paramsLog['codigoTabela'] = dcrip($atribuicao);
        $paramsLog['nomeTabela'] = 'PlanoEnsino';
        $l = $log->listSolicitacoes($paramsLog, " AND ( l.dataConcessao = '0000-00-00 00:00:00' OR l.dataConcessao IS NULL) ");

        if ($l[0]['solicitacao']) {
            $OPT[0] = $l[0]['solicitante'];
            $OPT[1] = $l[0]['solicitacao'];
            mensagem('ERRO', 'SOLICITACAO_PLANO', $OPT);
            $BLOQ = 0;
        }
        if ($VALIDO) {
            mensagem('OK', 'PLANO_VALIDO');
        }

        $pd = ($res) ? 'SIM' : 'N&Atilde;O';
        print "<br>Plano de Ensino digitado: $pd<br>";

        $count = 0;
        if ($ret = $planoAula->listPlanoAulas(dcrip($atribuicao)))
            $count = count($ret);

        if ($count <= 0)
            $disabled = 'disabled';
        print "Quantidade de aulas cadastradas no Plano de Aula: $count<br>";

        if (!$BLOQ)
            print "<br><input type=\"submit\" $disabled value=\"Entregar\" id=\"item-entregar\"></th>";
        else
            print "<br><b>Esse plano foi finalizado e entregue</b><br>";
    }

    if ($_GET['pagina'] == "planoCopiar") {
        ?>
        <p>Permite copiar planos de ensino cadastrados para a mesma disciplina no semestre atual ou outros semestres.</p>
        <?php
        if ($_GET["codigoCopy"]) {
            $ret = $planoEnsino->copyPlano(dcrip($_GET["atribuicao"]), dcrip($_GET["codigoCopy"]));
            $atribuicao = dcrip($_GET["atribuicao"]);
            mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
        }

        // VERIFICANDO SE O PLANO FOI FINALIZADO
        $pe = $planoEnsino->listPlanoEnsino($params, $sqlAdicional);
        $disabled = '';
        if ($pe[0]['finalizado'] && $pe[0]['finalizado'] != '00/00/0000 00:00')
            $disabled = 'disabled';
        ?>

        <script>
            $('#form_padrao').html5form({
                method: 'POST',
                action: '<?= $SITE; ?>',
                responseDiv: '#professor',
                colorOn: '#000',
                colorOff: '#999',
                messages: 'br'
            })
        </script>

        <div id="html5form" class="main">
            <form id="form_padrao">
                <table border="0" width="100%">
                    <tr><td align="left" style="width: 160px">Disciplinas equivalentes: </td><td>
                            <select name="campoDisciplina" <?= $disabled ?> id="campoDisciplina" value="<?php echo $disciplina; ?>" >
                                <option></option>
                                <?php
                                $res = $planoEnsino->getPlanoEquivalente(dcrip($_GET["atribuicao"]));
                                foreach ($res as $reg) {
                                    if (!$reg['subturma'])
                                        $reg['subturma'] = $reg['eventod'];
                                    ?>
                                    <option value='<?= crip($reg['codigo']) ?>'><?= $reg['nome'] . ' [' . $reg['semestre'] . '/' . $reg['ano'] . '] [' . $reg['numero'] . '] [' . $reg['subturma'] . '] (' . $reg['curso'] ?>)</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td></tr>
                </table>
                <br />
                <table width="100%"><tr><td>
                            <a class="nav" id='item-copiar' href="#" title="Copiar Plano de Ensino"><img src='<?= ICONS ?>/copiar.gif' width="30" /></a>
                        </td></tr></table> 
            </form>
        </div>
    </div>
    <?php
}

if ($_GET['pagina'] == "planoEnsino") {

    // LISTAGEM
    if (!empty($_GET["atribuicao"])) { // se o parâmetro não estiver vazio
        // consulta no banco
        $res = $planoEnsino->listPlanoEnsino($params, $sqlAdicional);
        extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
        $rfTitle = $planoEnsino->getTipoRecuperacao(dcrip($_GET["atribuicao"]));
    }

    $disabled = '';
    if ($finalizado && $finalizado != '00/00/0000 00:00')
        $disabled = 'disabled';
    ?>

    <script>
        $('#form_padrao').html5form({
            method: 'POST',
            action: '<?= $SITE; ?>',
            responseDiv: '#professor',
            colorOn: '#000',
            colorOff: '#999',
            messages: 'br'
        })
    </script>

    <div id="html5form" class="main">
        <form id="form_padrao">
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <input type="hidden" name="pagina" value="planoEnsino" />
            <input type="hidden" name="codigo" value="<?= crip($codigo); ?>" />
            <input type="hidden" name="atribuicao" value="<?= $_GET['atribuicao']; ?>" />
            <table id="form" border="0" width="100%">
                <tr>
                    <td align="left">N&uacute;m. Aulas Semanais: </td>
                    <td><input type="text" size="2" <?= $disabled; ?> id="numeroAulaSemanal" onchange="validaItem(this)" name="numeroAulaSemanal" maxlength="2" value="<?php echo $numeroAulaSemanal; ?>"/>
                    <td>&nbsp;</td>
                    <td align="left">Total de Horas: </td>
                    <td><input type="text" size="5" <?= $disabled; ?> id="totalHoras" onchange="validaItem(this)" name="totalHoras" maxlength="5" value="<?php echo $totalHoras; ?>"/>
                    <td>&nbsp;</td>
                    <td align="left">Total de Aulas: </td>
                    <td><input type="text" size="5" <?= $disabled; ?> id="totalAulas" onchange="validaItem(this)" name="totalAulas" maxlength="5" value="<?php echo $totalAulas; ?>"/>
                    <td>&nbsp;</td>
                    <td align="left">N&uacute;m. Professores: </td>
                    <td><input type="text" size="1" <?= $disabled; ?> id="numeroProfessores" name="numeroProfessores" maxlength="1" value="<?php echo $numeroProfessores; ?>"/>
                </tr>
                <tr>
                    <td align="left">2 - Ementa: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='ementa' name='ementa'><?= $ementa; ?></textarea>
                </tr>
                <tr>
                    <td align="left">3.1 - Objetivo Geral: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='objetivoGeral' name='objetivoGeral'><?= $objetivoGeral; ?></textarea>
                </tr>
                <tr>
                    <td align="left">3.2 - Objetivo Espec&iacute;fico/Compet&ecirc;ncias: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='objetivoEspecifico' name='objetivoEspecifico'><?= $objetivoEspecifico; ?></textarea>
                </tr>
                <tr>
                    <td align="left">4 - Conte&uacute;do Program&aacute;tico: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='conteudoProgramatico' name='conteudoProgramatico'><?= $conteudoProgramatico; ?></textarea>
                </tr>
                <tr>
                    <td align="left">5 - Metodologia: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='metodologia' name='metodologia'><?= $metodologia; ?></textarea>
                </tr>
                <tr>
                    <td align="left">6 - Recurso Did&aacute;tico: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='recursoDidatico' name='recursoDidatico'><?= $recursoDidatico; ?></textarea>
                </tr>
                <tr>
                    <td align="left">7 - Avalia&ccedil;&atilde;o: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='avaliacao' name='avaliacao'><?= $avaliacao; ?></textarea>
                </tr>  
                <tr>
                    <td align="left">7.1 - Recupera&ccedil;&atilde;o Paralela: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='recuperacaoParalela' name='recuperacaoParalela'><?= $recuperacaoParalela; ?></textarea>
                </tr>  
                <tr>
                    <td align="left"><?= $rfTitle ?>:</td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='recuperacaoFinal' name='recuperacaoFinal'><?= $recuperacaoFinal; ?></textarea>
                </tr>
                <tr>
                    <td align="left">8 - Bibliografia B&aacute;sica: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='bibliografiaBasica' name='bibliografiaBasica'><?= $bibliografiaBasica; ?></textarea>
                </tr>
                <tr>
                    <td align="left">8.1 - Bibliografia Complementar: </td>
                    <td colspan="10"><textarea rows="10" <?= $disabled; ?> cols="70" maxlength='<?= $caracteres ?>' id='bibliografiaComplementar' name='bibliografiaComplementar'><?= $bibliografiaComplementar; ?></textarea>
                </tr>
            </table>
            <table width="100%"><tr><td><input type="submit" <?= $disabled; ?> value="Salvar" /></td></tr></table>
        </form>
    </div>
    </div>
    <?php
}

if ($_GET['pagina'] == "planoAula") {
    // DELETE
    if ($_GET["opcao"] == 'delete') {
        $ret = $planoAula->delete($_GET["codigo"]);
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
        $_GET["codigo"] = null;
    }

    // VERIFICANDO SE O PLANO FOI FINALIZADO
    $pe = $planoEnsino->listPlanoEnsino($params, $sqlAdicional);
    $disabled = '';
    if ($pe[0]['finalizado'] && $pe[0]['finalizado'] != '00/00/0000 00:00')
        $disabled = 'disabled';

    if (!$pe[0]) {
        mensagem('INFO', 'EMPTY_PLANO_ENSINO');
        $disabled = 'disabled';
    }

// LISTAGEM
    if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
        // consulta no banco
        $params = array('codigo' => dcrip($_GET["codigo"]));
        $res = $planoAula->listRegistros($params);
        extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
        $codigo = crip($codigo);
    }
    ?>
    <script>
        function validaItem(item) {
            item.value = item.value.replace(",", ".");
        }

        $('#form_padrao').html5form({
            method: 'POST',
            action: '<?= $SITE; ?>',
            responseDiv: '#professor',
            colorOn: '#000',
            colorOff: '#999',
            messages: 'br'
        })
    </script>

    <div id="html5form" class="main">
        <form id="form_padrao">
            <input type="hidden" name="pagina" value="planoAula" />
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <input type="hidden" name="codigo" value="<?= $codigo; ?>" />
            <input type="hidden" name="atribuicao" value="<?= $_GET['atribuicao']; ?>" />
            <table id="form" border="0" width="100%">
                <tr>
                    <td align="left">Semana: </td>
                    <td><input type="text" size="4" <?= $disabled; ?> onchange="validaItem(this)" id="semana" name="semana" maxlength="4" value="<?= $semana; ?>"/>
                </tr>
                <tr>
                    <td align="left">Conte&uacute;do: </td>
                    <td colspan="10"><textarea rows="3" <?= $disabled; ?> cols="80" maxlength='400' id='conteudo' name='conteudo'><?= $conteudo; ?></textarea>
                </tr>
            </table>
            <table width="100%"><tr><td><input type="submit" <?= $disabled; ?> value="Salvar" /></td>
                    <td><a href="javascript:$('#professor').load('<?= "$SITE?pagina=planoAula&atribuicao=" . $_GET['atribuicao']; ?>'); void(0);">Novo/Limpar</a></td>
                </tr></table>	
            <br><br>

            <table id="listagem" border="0" align="center">
                <tr class="listagem_tr"><th align="center" width="80">Semana</th><th align="left">Conte&uacute;do</th><th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value=""><a href="#" class='item-excluir'><img class='botao' src='<?php print ICONS; ?>/delete.png' /></a></th></tr>
                <?php
                // efetuando a consulta para listagem
                $i = 0;
                $res = $planoAula->listPlanoAulas(dcrip($_GET["atribuicao"]));
                foreach ($res as $reg) {
                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    ?>
                    <tr <?= $cdif ?>><td align='left'><?= $reg['semana'] ?></td>
                        <td><?= nl2br(mostraTexto($reg['conteudo'])) ?></td>
                        <?php
                        if (!$$disabled) {
                            ?>   
                            <td align='center'><input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                                <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                                    <img class='botao' src='<?= ICONS ?>/config.png' /></a>
                            </td>
                            <?php
                        } else {
                            ?>
                            <td>&nbsp;</td>
                            <?php
                        }
                        ?>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </table>
        </form>
    </div>
    </div>	
    <?php
}

$atribuicao = $_GET['atribuicao'];
?>
<script>
    function validaItem(item) {
        item.value = item.value.replace(",", ".");
    }

    $(document).ready(function () {
        $('#ementa,#objetivoGeral,#objetivoEspecifico,#conteudoProgramatico,#metodologia,#recursoDidatico,#avaliacao,#recuperacaoParalela,#recuperacaoFinal,#bibliografiaBasica,#bibliografiaComplementar').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: <?= $caracteres ?>, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
        });

        $('#conteudo').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 400, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
        });

        $(".item-excluir").click(function () {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?php print $TITLE; ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function () {
                            selected.push($(this).val());
                        });
                        $('#professor').load('<?= "$SITE?atribuicao=$atribuicao"; ?>&pagina=planoAula&opcao=delete&codigo=' + selected + '&item=<?= $item; ?>');
                    }
                }
            });
        });

        $(".item-alterar").click(function () {
            var codigo = $(this).attr('id');
            $('#professor').load('<?= "$SITE?atribuicao=$atribuicao"; ?>&pagina=planoAula&codigo=' + codigo);
        });

        $("#item-entregar").click(function () {
            $.Zebra_Dialog('<strong>Deseja enviar seu Plano para seu coordenador? \n O Plano ser&aacute; bloqueado, podendo ser desbloqueado somente pelo coordenador.', {
                'type': 'question',
                'title': '<?php print $TITLE; ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        $('#professor').load('<?= $SITE; ?>?pagina=entregarPlano&atribuicao=<?= $atribuicao; ?>&entregar=1');
                    }
                }
            });
        });

        $("#item-copiar").click(function () {
            var codigo = $('#campoDisciplina').val();
            $.Zebra_Dialog('<strong>Aten&ccedil;&atilde;o, seu plano de ensino ser&aacute; exclu&iacute;do e substitu&iacute;do pelo escolhido. Deseja continuar?', {
                'type': 'question',
                'title': '<?php print $TITLE; ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        $('#professor').load('<?= "$SITE?atribuicao=$atribuicao"; ?>&pagina=planoCopiar&codigoCopy=' + codigo);
                    }
                }
            });
        });

        $('#select-all').click(function (event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function () {
                    this.checked = false;
                });
            }
        });
    });
</script>