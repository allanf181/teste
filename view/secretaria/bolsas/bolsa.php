<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Cadastro de bolsas de ensino.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/bolsa.class.php";
$bolsa = new Bolsas();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);
    $_POST['dataInicio'] = dataMysql($_POST['dataInicio']);
    $_POST['dataFim'] = dataMysql($_POST['dataFim']);

    $ret = $bolsa->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $bolsa->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params1 = array('codigo' => dcrip($_GET["codigo"]));
    $res = $bolsa->listRegistros($params1);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
    $dataInicio = dataPTBR($dataInicio);
    $dataFim = dataPTBR($dataFim);
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<script>

    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE ?>',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>
<div id="html5form" class="main">
    <?php
    include("base.php");
    if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
        $params['professor'] = $_SESSION["loginCodigo"];
        $sqlAdicional = " AND p.codigo = :professor ";
    } else if (in_array($ALUNO, $_SESSION["loginTipo"])) {
        $params['aluno'] = $_SESSION["loginCodigo"];        
        $sqlAdicional = " AND b.codigo IN (SELECT ba.bolsa FROM BolsasAlunos ba WHERE ba.aluno = :aluno) ";
    } else {
        ?>
        <form id="form_padrao">
            <table align="center" width="100%" id="form">
                <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
                <tr>
                    <td align="right">T&iacute;tulo: </td>
                    <td>
                        <input type="text" maxlength="100" size="60" name="titulo" id="titulo" value="<?= $titulo ?>"/>
                    </td>
                </tr>            
                <tr>
                    <td align="right">Professor respons&aacute;vel: </td>
                    <td>
                        <select name="professor" id="professor" value="<?= $professor ?>">
                            <option></option>
                            <?php
                            require CONTROLLER . '/pessoa.class.php';
                            $pessoa = new Pessoas();
                            $sqlAdicionalProf .= ' AND pt.tipo = :tipo ';
                            $paramsProf['tipo'] = $PROFESSOR;
                            $resProf = $pessoa->listPessoasTipos($paramsProf, $sqlAdicionalProf, null, null);
                            foreach ($resProf as $reg) {
                                $selected = "";
                                if ($reg['codigo'] == $professor)
                                    $selected = "selected";
                                print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right">Data in&iacute;cio: </td>
                    <td><input type="text" readonly class="data" size="10" id="dataInicio" name="dataInicio" value="<?= $dataInicio ?>" />
                    </td>
                </tr>
                <tr>
                    <td align="right">Data fim: </td><td><input type="text" readonly class="data" size="10" id="dataFim" name="dataFim" value="<?= $dataFim ?>" />
                    </td>
                </tr>
                <tr>
                    <td align="right">Observa&ccedil;&otilde;es: </td>
                    <td><textarea maxlength="500" rows="5" cols="80" id="observacao" name="observacao" style="width: 600px; height: 60px"><?= $observacao ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="hidden" name="opcao" value="InsertOrUpdate" />    
                        <table width="100%">
                            <tr>
                                <td>
                                    <input type="submit" value="Salvar" id="salvar" />
                                </td>
                                <td>
                                    <a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Novo/Limpar</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }
    ?>
</div>
<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$sqlAdicional .= ' ORDER BY b.dataInicio DESC ';
$res = $bolsa->listBolsas($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($bolsa->listBolsas($params, $sqlAdicional, null, null));

$SITENAV = $SITE . "?";
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">C&oacute;digo</th>
        <th align="left">T&iacute;tulo</th>
        <th align="left">Professor</th>
        <th align="left">Dura&ccedil;&atilde;o</th>
        <th align="left">Observa&ccedil;&atilde;o</th>
        <th align="left" width="90">&nbsp;
            <?php
            if (!in_array($PROFESSOR, $_SESSION["loginTipo"]) && !in_array($ALUNO, $_SESSION["loginTipo"])) {
                ?>
                <input type="checkbox" id="select-all" value="">
                <a href="#" class='item-excluir'>
                    <img class='botao' src='<?= ICONS ?>/delete.png' />
                </a>
            <?php } ?>
        </th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
            <td><a href="#" title="<?= $reg['titulo'] ?>"><?= abreviar(mostraTexto($reg['titulo']), 30) ?></a></td>
            <td><a href="#" title="<?= $reg['professor'] ?>"><?= abreviar(mostraTexto($reg['professor']), 30) ?></a></td>
            <td><?= $reg['duracao'] ?></td>
            <td><a href="#" title="<?= $reg['observacao'] ?>"><?= abreviar(mostraTexto($reg['observacao']), 30) ?></a></td>
            <td>
                &nbsp;
                <?php
                if (!in_array($PROFESSOR, $_SESSION["loginTipo"]) && !in_array($ALUNO, $_SESSION["loginTipo"])) {
                    ?>
                    <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                    <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                        <img class='botao' src='<?= ICONS ?>/config.png' />
                    </a>
                    <?php
                }
                ?>
                <a href='#' class='item-print' id='<?= crip($reg['codigo']) ?>' title='Imprimir Relat&oacute;rio'>
                    <img style="width: 40px" src='<?= ICONS ?>/icon-printer.gif' />
                </a>
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>

<script>
    $('#observacao').maxlength({
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

    $("#dataInicio, #dataFim").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'
    });

    function valida() {
        if ($('#titulo').val() == "" || $('#professor').val() == ""
                || $('#datInicio').val() == "" || $('#dataFim').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    $(document).ready(function () {
        valida();
        $('#titulo,#professor,#dataInicio,#dataFim').change(function () {
            valida();
        });

        $(".item-print").click(function () {
            var codigo = $(this).attr('id');
            window.open('<?= VIEW ?>/secretaria/relatorios/inc/bolsa.php?codigo=' + codigo);
        });

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
                        $('#index').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                    }
                }
            });
        });

        $('#select-all').click(function () {
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

        $(".item-alterar").click(function () {
            var codigo = $(this).attr('id');
            $('#index').load('<?= $SITE ?>?codigo=' + codigo);
        });
    });
</script>