<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de avaliações no WebDiário.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/avaliacao.class.php";
$avaliacao = new Avaliacoes();

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

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
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    $_POST['data'] = dataMysql($_POST['data']);
    unset($_POST['opcao']);

    $_POST['nome'] = crip($_POST['nome']);
    $_POST['sigla'] = crip($_POST['sigla']);

    if ($_POST['substitutiva']) {
        $params['codigo'] = $_POST['tipo'];
        $p = $avaliacao->listRegistros($params);
        $_POST['peso'] = $p[0]['peso'];
        $_POST['substitutiva'] = crip($_POST['tipo']);
        $_POST['tipo'] = $_POST['codigo'];
        unset($_POST['codigo']);
    }

    $ret = $avaliacao->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["atribuicao"] = $_POST['atribuicao'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $avaliacao->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['opcao'] = null;
}

// INSERINDO A FORMULA
if ($_POST["opcao"] == 'InsertFormula') {
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    unset($_POST['opcao']);
    $ret = $att->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['atribuicao'] = $codigo;
}

$atribuicao = dcrip($_GET["atribuicao"]);

// INSERINDO O CALCULO ESCOLHIDO
if ($_GET['opcao'] == 'calculo') {
    $params = array('codigo' => crip($atribuicao), 'calculo' => crip($_GET['calculo']));
    $att->insertOrUpdate($params);
    $_GET['opcao'] = null;
}

//LISTANDO O CALCULO ESCOLHIDO
$params = array('codigo' => $atribuicao);
$atrib = $att->listRegistros($params);
$calculo = $atrib[0]['calculo'];
$formula = $atrib[0]['formula'];
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
if ($calculo == 'peso')
    $PONTO = 1;
if ($calculo == 'soma')
    $PONTO = 10;

if ($_GET['opcao'] == 'insert') {

    $tipoAval = strtolower(dcrip($_GET['tipo']));
    if ($tipoAval == 'pontoextra')
        $PONTO = 10;

    $pontos = dcrip($_GET["pontos"]); // pontos ja atribuidos
    $maxPontos = $PONTO - $pontos;

    $avalSiglas = $avaliacao->listAvaliacoes($atribuicao);
    ?>
    <script>
        valida();
        $(document).ready(function () {
    <?php
    if ($calculo == 'peso' || $calculo == 'soma' || $tipoAval == 'pontoextra') {
        ?>
                $("#valor").mask("99.99");
                $("#valor").change(function () {
                    if ($(this).val() > <?= $maxPontos ?>)
                        $(this).val('<?= str_pad(number_format($maxPontos, 2), 5, "0", STR_PAD_LEFT) ?>');
                });
        <?php
        if ($maxPontos > 0.1) {
            $P = "&& $('#valor').val()!=\"00.00\" && $('#valor').val()!=\"\" && $('#valor').val()!=\"__.__\" ";
            $P1 = ', #valor';
        }
    }
    ?>
            $('#data1, #tipo, #nome <?= $P1 ?>, #sigla').keyup(function () {
                $('#sigla').val($('#sigla').val().toUpperCase());
                valida();
            });

        });
        function valida() {
    <?php
    if (!$_GET['codigo']) {
        ?>
                var Siglas = new Array();
        <?php
        $i = 0;
        foreach ($avalSiglas as $r) {
            ?>
                    Siglas[<?= $i ?>] = '<?= strtoupper($r['sigla']) ?>';
            <?php
            $i++;
        }
        ?>
                if ($('#sigla').val() && Siglas.indexOf($('#sigla').val()) != -1) {
                    $('#Siglas').html('Essa sigla já existe, escolha outra');
                    $('#sigla').val('');
                } else {
                    $('#Siglas').html('');
                }
        <?php
    }
    ?>

            if ($('#data').val() != "" && $('#tipo').val() != null &&
                    $('#nome').val() != "" && $('#sigla').val() != "" <?= $P ?>)
                $('#salvar').enable();
            else
                $('#salvar').attr('disabled', 'disabled');
        }
    </script>
    <?php
    // LISTAGEM
    if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
        // consulta no banco
        $params = array('codigo' => dcrip($_GET["codigo"]));
        $res = $avaliacao->listRegistros($params);
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

    <div id="html5form" class="main">
        <form id="form_padrao">
            <center>
                <h2>Cadastro de Avalia&ccedil;&atilde;o</h2>
                <table>
                    <tr><td align="right">Data: </td><td><input type="text" readonly size="10" id="data" name="data" value="<?php echo $data; ?>" />
                            <a href='#' id="unlock" title='Perdeu o prazo? Clique aqui e solicite ao coordenador a libera&ccedil;&atilde;o do di&aacute;rio.'><img style="width: 20px;" src="<?= ICONS ?>/unlock.png"></a>
                        </td></tr></td></tr>
                    <tr><td align="right">Nome: </td><td><input style="width: 350px" type="text" id="nome" maxlength="145" name="nome" value="<?php echo $nome; ?>"/></td></tr>
                    <tr><td align="right">Sigla: </td><td><input type="text" id="sigla" size="2" maxlength="2" name="sigla" value="<?php echo $sigla; ?>"/> <spam id="Siglas"></spam></td></tr>
                    <tr><td align="right">Tipo: </td><td>
                            <select name="tipo" id="tipo" value="<?php echo $tipo; ?>">
                                <?php
                                require CONTROLLER . "/tipoAvaliacao.class.php";
                                $tipoAvaliacao = new TiposAvaliacoes();

                                if ($tipoAval == 'substitutiva') {
                                    $res1 = $avaliacao->listAvaliacoes($atribuicao, 'substitutiva');

                                    $tipo = $tipoAvaliacao->listTiposAvaliacoes($atribuicao, $calculo, $PONTO, $pontos, $tipoAval);
                                } else {
                                    $res1 = $tipoAvaliacao->listTiposAvaliacoes($atribuicao, $calculo, $PONTO, $pontos, $tipoAval, dcrip($_GET['final']));
                                }
                                foreach ($res1 as $reg) {
                                    $selected = "";
                                    if ($reg['codigo'] == $tipo)
                                        $selected = "selected";
                                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                                    if ($reg['tipo'] == 'recuperacao')
                                        $tipoAvalRec = $reg['tipo'];
                                }
                                ?>
                            </select>
                        </td></tr>
                    <?php
                    if (($calculo == 'peso' || $calculo == 'soma' || $tipoAval == 'pontoextra') && ($tipoAval != 'substitutiva' && $tipoAvalRec != 'recuperacao')) {
                        if ($maxPontos <= 0)
                            $enabled = 'disabled';

                        $peso = number_format($res[0]['peso'], 2);
                        $peso = str_pad($peso, 5, "0", STR_PAD_LEFT);
                        ?>
                        <tr><td align="right">Valor</td><td><input type="text" id="valor" style="width: 50px" <?php echo $enabled; ?> name="peso" value="<?= $peso ?>"/> (m&aacute;ximo <?= $maxPontos ?>)</td></tr>
                        <?php
                    }
                    if ($tipoAval == 'substitutiva') {
                        $codigo = key($tipo);
                        ?>
                        <input type="hidden" name="substitutiva" value="1" />
                        <?php
                    }
                    ?>

                    <tr><td></td><td>
                            <input type="hidden" name="atribuicao" value="<?php echo crip($atribuicao); ?>" />
                            <input type="hidden" name="codigo" value="<?php echo crip($codigo); ?>" />
                            <input type="hidden" name="opcao" value="InsertOrUpdate" />
                            <input type="submit" disabled value="Salvar" id="salvar" />
                        </td></tr>
                </table>
        </form>
    </div>
    <br><div style='margin: auto'><a href="javascript:$('#professor').load('<?= $SITE ?>?atribuicao=<?= crip($atribuicao) ?>'); void(0);" class='voltar' title='Voltar' ><img class='botao' src='<?= ICONS ?>/left.png'/></a></div>
    <?php
}


if ($_GET['opcao'] == '') {

    // INSERINDO O CALCULO PESO POR PADRAO, CASO ESTEJA VAZIO
    if (!$calculo && !$_POST) {
        $att->insertIfNotCalculo($atribuicao);
        $calculo = 'peso';
    }

    $res = $avaliacao->listAvaliacoes($atribuicao);
    if ($res[0]['nome'])
        $disabled = 'disabled';
    ?>
    <div id="etiqueta" align="center">
        <b>Turma: </b><?= $res[0]['numero'] ?><br />
        <b>Disciplina: </b><?= $res[0]['disciplina'] ?><br />
        <b>M&eacute;todo de C&aacute;lculo: </b>
        <select name="campoCalculo" <?= $disabled ?> id="campoCalculo" value="<?= $calculo ?>" onChange="$('#professor').load('<?= $SITE ?>?opcao=calculo&atribuicao=<?= crip($atribuicao) ?>&calculo=' + this.value);">
            <?php
            $MC = array('soma', 'media', 'peso', 'formula');
            foreach ($MC as $c) {
                $selected = null;
                if ($c == $calculo)
                    $selected = 'selected';
                $n = strtoupper($c);
                ?>
                <option <?= $selected ?> value='<?= $c ?>'><?= $$n ?></option>
                <?php
            }
            ?>
        </select>
        <?php if ($calculo == 'peso' || $calculo == 'soma') { ?>
            <br><b>Pontos atribu&iacute;dos: </b><?= round($res[0]['totalPeso'], 2) ?>
            <?php
        }
        if ($calculo == 'formula') {
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
            <div id="html5form" class="main">
                <form id="form_padrao">
                    <font size="2">M&eacute;dia: </font><input type="text" size="25" maxlength="100" name="formula" value="<?= $formula ?>" onchange="validaItem(this)" />
                    <font size="1">(Exemplo: <b>($A1+$A2)/2</b>  --> onde A1 &eacute; a sigla da avalia&ccedil;&atilde;o precedida de $)</font>
                    <input type="hidden" name="opcao" value="InsertFormula" />
                    <input type="hidden" name="codigo" value=<?= crip($atribuicao) ?> />
                    &nbsp;&nbsp;<input type="submit" value="Salvar" />
                </form>
            </div>
            <?php
        }
        ?>
    </div>
    <hr><br>
    <?php if ($res[0]['nome']) { ?>
        <table id="listagem" border="0" align="center">
            <tr>
                <th width="40">#</th>
                <th width="100">Data</th>
                <th>Avalia&ccedil;&atilde;o</th>
                <th>Sigla</th>
                <th>Tipo</th>
                <th width="150">Valor</th>
                <th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
                    <a href="#" class='item-excluir'>
                        <img class='botao' src='<?php print ICONS; ?>/delete.png' />
                    </a>
                </th>
            </tr>
            <?php
            $i = count($res);
            foreach ($res as $reg) {
                $bimestre = $reg['bimestre'];
                $recFinal .= $reg['final'];
                $recuperacao .= $reg['recuperacao'];
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";

                $totalPesoOrPonto = $reg['totalPeso'];
                if (($calculo == 'media' || $calculo == 'formula') && $reg['tipo'] != 'pontoExtra')
                    $reg['peso'] = '';

                if ($reg['recuperacao'])
                    $reg['peso'] = $reg['recuperacao'];

                $sub = null;
                if ($reg['tipo'] == 'substitutiva')
                    $sub = ' de ' . $reg['substitutiva'];

                if ($reg['tipo'] == 'pontoExtra')
                    $totalPesoOrPonto = $reg['totalPonto'];

                if ($reg['tipo'] == 'recuperacao')
                    $final = 1;

                if ($reg['tipo'] == 'avaliacao')
                    $tipoIns = 'avaliacao';

                $reg['tipo'] = strtoupper($reg['tipo']);
                $titleAval = strtoupper($reg['recuperacao']);
                ?>
                <tr <?= $cdif ?>><td><?= $i-- ?></td>
                    <td><a class='nav' title='Clique aqui para lan&ccedil;ar as notas.' href="javascript:$('#professor').load('<?= VIEW ?>/professor/nota.php?atribuicao=<?= crip($atribuicao) ?>&avaliacao=<?= crip($reg['codigo']) ?>'); void(0);"><?= $reg['dataFormatada'] ?></a></td>
                    <td><?= $reg['nome'] ?></td>
                    <td><?= $reg['sigla'] ?></td>
                    <td><?= $$reg['tipo'] . $sub ?> </td>
                    <td><a title='<?= $$titleAval ?>' href='#'><?= $reg['peso'] ?></a></td>
                    <?php
                    if ($_SESSION['dataExpirou']) {
                        ?>
                        <td align='center'><a href='#' title='Di&aacute;rio Fechado'>Fechado</a></td>
                        <?php
                    } else {
                        ?>
                        <td align='center' width="20"><input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>'>
                            <a href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&codigo=<?= crip($reg['codigo']) ?>&pontos=<?= crip(round($totalPesoOrPonto - $reg['peso'], 2)) ?>&atribuicao=<?= crip($atribuicao) ?>&tipo=<?= crip($reg['tipo']) ?>&final=<?= crip($reg['final']) ?>'); void(0);" class='nav' title='Alterar'>
                                <img class='botao' src='<?= ICONS ?>/config.png' /></a>
                        </td>
                        <?php
                    }
                }
                ?>
        </table>
    <?php } ?>
    <center>
        <br />
        <?php if ((($calculo == 'media' || $calculo == 'formula') && ($res[0]['totalPeso'] < $PONTO) || !$recuperacao || (!$recFinal && $bimestre == 4))) { ?>
            <?php if ($_SESSION['dataExpirou'] == 0) {
                ?>
                <a class="nav" href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&atribuicao=<?= crip($atribuicao) ?>&pontos=<?= crip(round($reg['totalPeso'], 2)) ?>&final=<?= crip($final) ?>&tipo=<?= crip($tipoIns) ?>'); void(0);" title="Cadastrar Nova Avalia&ccedil;&atilde;o"><img class='botao' src='<?= ICONS ?>/avaliacao.png' /></a>
                &nbsp;&nbsp;<a class="nav" href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&atribuicao=<?= crip($atribuicao) ?>&tipo=<?= crip('pontoExtra') ?>&pontos=<?= crip(round($reg['totalPonto'], 2)) ?>'); void(0);" title="Cadastrar Ponto Extra"><img class='botao' src='<?= ICONS ?>/add.png' /></a>
                &nbsp;&nbsp;<a class="nav" href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&atribuicao=<?= crip($atribuicao) ?>&tipo=<?= crip('substitutiva') ?>'); void(0);" title="Cadastrar Prova Substitutiva"><img class='botao' src='<?= ICONS ?>/change.png' /></a>
                <?php
            } else {
                ?>
                <p style='text-align: center; font-weight: bold; color: red'>Di&aacute;rio Fechado.</p>
                <a href='#' id="unlock" title='Clique aqui para solicitar a liberação do diário.'><img src="<?= ICONS ?>/unlock.png"></a>
                <?php
            }
        } else if ($status == 0) {
            ?>
            <p style='text-align: center; font-weight: bold; color: red'>Não é possível cadastrar mais avaliações, pois a soma dos pontos distribuídos é igual a <?= $PONTO ?><br />Exclua ou altere o peso de alguma avaliação para adicionar uma nova.</p>
                <?php
            }
            ?>
    </center>
    <?php
}

if ($LIMITE_DIARIO_PROF != 0) {
    // DATA DE INICIO E FIM DA ATRIBUICAO PARA RESTRINGIR O CALENDARIO
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

    function validaItem(item) {
        item.value = item.value.replace(",", ".");
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
            'title': '<?php print $TITLE; ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption, valor) {
                if (caption == 'Sim') {
                    $('#professor').load('<?= $SITE ?>?motivo=' + encodeURIComponent(valor) + '&atribuicao=' + '<?= crip($atribuicao) ?>');
                }
            }
        });
    });
</script>