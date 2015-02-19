<?php
//Esse arquivo é fixo para o professor.
//Permite o registro de aulas no WebDiário.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
$atribuicao = dcrip($_GET["atribuicao"]);

require CONTROLLER . "/aula.class.php";
$aula = new Aulas();

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

// PEDIDO DE LIBERAÇÃO DO DIÁRIO
if ($_GET["motivo"]) {
    $paramsLog['dataSolicitacao'] = date('Y-m-d h:i:s');
    $paramsLog['solicitacao'] = 'Docente solicitou abertura do diário, motivo: ' . $_GET['motivo'];
    $paramsLog['codigoTabela'] = $_GET['atribuicao'];
    $paramsLog['nomeTabela'] = 'DIARIO';
    $paramsLog['solicitante'] = $_SESSION['loginCodigo'];
    $ret = $log->insertOrUpdate($paramsLog);
    mensagem($ret['STATUS'], 'PRAZO_DIARIO');
}

// INSERT E UPDATE DE AVALIACOES
if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['data'] = dataMysql($_POST['data']);
    unset($_POST['opcao']);
    unset($_POST['plano']);
    unset($_POST['regAtv']);

    $ret = $aula->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $atribuicao = $_POST['atribuicao'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $aula->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['opcao'] = null;
}

if ($_GET['opcao'] == 'insert') {
    // LISTAGEM
    if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
        // consulta no banco
        $params = array('codigo' => dcrip($_GET["codigo"]));
        $res = $aula->listRegistros($params);
        extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
        $data = dataPTBR($data);
    }
    ?>
    <script>
        $('#form_padrao').html5form({
            method: 'POST',
            action: '<?= $SITE ?>',
            responseDiv: '#professor',
            colorOn: '#000',
            colorOff: '#999',
            messages: 'br'
        })
    </script>
    <?php
    require CONTROLLER . "/planoAula.class.php";
    $planoAula = new PlanosAula();
    $res = $planoAula->getConteudosAulas($atribuicao);
    ?>
    <div id="html5form" class="main">
        <form id="form_padrao">
            <table align="center">
                <tr>
                    <td align="right">Semana:</td>
                    <td>
                        <select name="plano" id="plano">
                            <option></option>;
                            <?php
                            foreach ($res as $reg) {
                                echo "<option title='" . $reg['conteudo'] . "' $selected value='" . $reg['conteudo'] . "'>Semana " . $reg['semana'] . " [" . abreviar($reg['conteudo'], 85) . "]</option>";
                            }
                            if (!$quantidade)
                                $quantidade = $res[0]['numeroAulaSemanal'];
                            ?>
                        </select>
                    </td>
                </tr>
                <tr><td align="right">Data: </td><td><input type="text" readonly class="data" size="10" id="data" name="data" value="<?= $data ?>" />
                        <a href='#' id="unlock" title='Perdeu o prazo? Clique aqui e solicite ao coordenador a libera&ccedil;&atilde;o do di&aacute;rio.'><img style="width: 20px;" src="<?= ICONS ?>/unlock.png"></a></td></tr>
                <tr><td align="right">Quantidade: </td><td><input style="width: 50px" <?php if ($codigo) print 'readonly'; ?> type="text" maxlength="4" id="quantidade" name="quantidade" value="<?= $quantidade ?>" /></td></tr>
                <tr><td align="right">Bases/Conhecimentos Desenvolvidos: </td><td><textarea maxlength="400" rows="5" cols="80" id="conteudo" name="conteudo" style="width: 600px; height: 60px"><?= $conteudo ?></textarea></td></tr>
                <tr>
                    <td align="right">Atividades:</td>
                    <td>
                        <select name="regAtv" id="regAtv">
                            <option></option>;
                            <?php
                            $regAtv[] = 'Exercício';
                            $regAtv[] = 'Trabalho individual';
                            $regAtv[] = 'Trabalho coletivo';
                            $regAtv[] = 'Ficha de observações';
                            $regAtv[] = 'Relatório';
                            $regAtv[] = 'Autoavaliação';
                            $regAtv[] = 'Prova escrita';
                            $regAtv[] = 'Prova prática';
                            $regAtv[] = 'Prova oral';
                            $regAtv[] = 'Seminário';
                            $regAtv[] = 'Projeto interdisciplinar';
                            foreach ($regAtv as $reg) {
                                echo "<option value='$reg'>$reg</option>";
                            }
                            ?>
                        </select>
                        <br /><textarea maxlength="400" rows="5" cols="80" id="atividade" name="atividade" style="width: 600px; height: 60px"><?= $atividade ?></textarea>
                    </td>
                </tr>
                <tr><td align="right">Anota&ccedil;&atilde;o de Aula: </td><td><textarea maxlength="500" rows="5" cols="80" id="anotacao" name="anotacao" style="width: 600px; height: 60px"><?= $anotacao ?></textarea></td></tr>
                <tr><td align="right" colspan="2">A anota&ccedil;&atilde;o de aula n&atilde;o entra no di&aacute;rio, apenas o conte&uacute;do.</td></tr>

                <tr><td></td><td>
                        <input type="hidden" name="atribuicao" value="<?= $atribuicao ?>" />
                        <input type="hidden" name="codigo" value="<?= $codigo ?>" />
                        <input type="hidden" name="opcao" value="InsertOrUpdate" />
                        <input type="submit" disabled value="Salvar" id="salvar" />
                    </td></tr>
            </table>
        </form>
    </div>
    <br><div style='margin: auto'><a href="javascript:$('#professor').load('<?= $SITE ?>?atribuicao=<?= crip($atribuicao) ?>');void(0);" class='voltar' title='Voltar' ><img class='botao' src='<?= ICONS ?>/left.png'/></a></div>
    <?php
}
if ($_GET['opcao'] == '') {
    $res = $aula->listAulasProfessor($atribuicao, 'ORDER BY data DESC');
    ?>
    <div id="etiqueta" align="center">
        <span class='rotulo_professor'>Turma: </span><?= $res[0]['turma'] ?><br />
        <span class='rotulo_professor'>Disciplina: </span><?= $res[0]['disciplina'] ?><br />
        <span class='rotulo_professor'>Dias: </span><?= $res[0]['dias'] ?><br />
        <span class='rotulo_professor'>Aulas dadas: </span><?= $res[0]['aulasDadas'] ?><br />
    </div>
    <hr><br>

    <?php if ($res[0]['aulasDadas']) { ?>
        <table id="listagem" border="0" align="center">
            <tr class="listagem_tr"><th align="center" width="40">#</th><th align="center" width="100">Data</th><th align='center' width="50">Qtd</th><th align='center'>Conte&uacute;do</th><th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value=""><a href="#" class='item-excluir'><img class='botao' src='<?= ICONS ?>/delete.png' /></a></th></tr>
            <?php
            $i = count($res);
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?>><td><?= $i-- ?></td>
                    <td><a class='nav' title='Clique aqui para lan&ccedil;ar as faltas.' href="javascript:$('#professor').load('<?= VIEW ?>/professor/frequencia.php?atribuicao=<?= crip($atribuicao) ?>&aula=<?= crip($reg['codigo']) ?>');void(0);"><?= $reg['data_formatada'] ?></a></td>
                    <td><?= $reg['quantidade'] ?></td><td><?= htmlspecialchars($reg['conteudo']) ?></td>
                    <?php
                    if ($_SESSION['dataExpirou']) {
                        ?>
                        <td align='center'><a href='#' title='Di&aacute;rio Fechado'>Fechado</a></td>
                        <?php
                    } else {
                        ?>
                        <td align='center' width="20"><input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>'>
                            <a href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&codigo=<?= crip($reg['codigo']) ?>&atribuicao=<?= crip($atribuicao) ?>');void(0);" class='nav' title='Alterar'>
                                <img class='botao' src='<?= ICONS ?>/config.png' />
                            </a>
                        </td>
                        <?php
                    }
                }
                ?>
        </table>
    <?php } ?>

    <br />
    <center>    
        <?php if (!$_SESSION['dataExpirou']) {
            ?>
            <a class="nav" href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&atribuicao=<?= crip($atribuicao) ?>');void(0);" title="Cadastrar Nova">
                <img class='botao' src='<?= ICONS ?>/add.png' /></a>
            <?php
        } else {
            ?>
            <p style='text-align: center; font-weight: bold; color: red'>Di&aacute;rio Fechado.</p>
            <a href='#' id="unlock" title='Clique aqui para solicitar a liberação do diário.'><img src="<?= ICONS ?>/unlock.png"></a>
                <?php
            }
            ?>
    </center>

    <?php
}

if ($LIMITE_DIARIO_PROF != 0) {
    // DATA DE INICIO E FIM DA ATRIBUICAO PARA RESTRINGIR O CALENDARIO
    require CONTROLLER . "/atribuicao.class.php";
    $att = new Atribuicoes();
    $res = $att->getAtribuicao($atribuicao, $LIMITE_DIARIO_PROF);
}

require CONTROLLER . "/calendario.class.php";
$cal = new Calendarios();
print "<script>\n";
print "var disabledDates = []; \n";
foreach ($cal->getFeriados() as $f) {
    print "disabledDates.push( \"$f\" ); \n";
}
print "</script>\n";
?>

<script>
    function editDays(date) {
        for (var i = 0; i < disabledDates.length; i++) {
            if (new Date(disabledDates[i]).toString() == date.toString()) {
                return [false];
            }
        }
        return [true];
    }

    valida();
    function valida() {
        if ($('#data').val() != "" && $('#conteudo').val() != "" && $('#quantidade').val() != "")
            $('#salvar').removeAttr('disabled');
        else
            $('#salvar').attr('disabled', 'disabled');
    }

    $(document).ready(function () {
        $(".item-excluir").click(function () {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function () {
                            selected.push($(this).val());
                        });
                        $('#professor').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&atribuicao=<?= crip($atribuicao) ?>');
                    }
                }
            });
        });

        $('#data, #conteudo, #quantidade, #plano').hover(function () {
            valida();
        });
        $('#data, #conteudo, #quantidade, #plano').change(function () {
            valida();
        });

        $('#plano').change(function () {
            $('#conteudo').val($('#plano').val());
        });

        $('#regAtv').change(function () {
            $('#atividade').val($('#regAtv').val());
        });

        $('#conteudo, #atividade').maxlength({
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

        $('#anotacao').maxlength({
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

        $("#data").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            minDate: '<?= $res['inicioCalendar'] ?>',
            maxDate: '<?= $res['fimCalendar'] ?>',
            beforeShowDay: editDays
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

    $("#unlock").click(function () {
        $.Zebra_Dialog('<strong>Professor, informe o motivo da solicitação:</strong>', {
            'type': 'prompt',
            'promptInput': '<textarea rows="2" cols="30" name="Zebra_valor" maxlength="200" id="Zebra_valor"></textarea>',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption, valor) {
                if (caption == 'Sim') {
                    $('#professor').load('<?= $SITE ?>?motivo=' + encodeURIComponent(valor) + '&atribuicao=' + '<?= crip($atribuicao) ?>');
                }
            }
        });
    });
</script>