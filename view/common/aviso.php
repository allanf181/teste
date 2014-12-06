<?php
// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
}


require CONTROLLER . "/aviso.class.php";
$aviso = new Avisos();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $_POST['pessoa'] = crip($_SESSION['loginCodigo']);
    $ret = $aviso->insertOrUpdateAvisos($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $aviso->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<script type="text/javascript" src="<?= VIEW ?>/js/AutocompleteList/src/jquery.tokeninput.js"></script>
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input-facebook.css" type="text/css" />

<script type="text/javascript">
    $(document).ready(function () {
        $("#to").tokenInput("<?= $SITE ?>&dados=1", {
            theme: "facebook",
            searchingText: "Procurando...",
            noResultsText: "Sem resultados para esse termo!",
            preventDuplicates: true
        });
    });
</script>

<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?php print $SITE; ?>',
        responseDiv: '<?= $DIV ?>',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <table align="center" width="100%" id="form">
            <input type="hidden" name="codigo" value="<?php echo crip($codigo); ?>" />
            <tr><td align="right">Para: </td><td><input type="text" id="to" name="to" /></td></tr>
            <tr><td></td><td align="left"><font size='1'><?= $para ?> Deixe em branco para enviar para todos.</font></td></tr>
            <tr><td align="right" style="width: 120px">Aviso: </td> 
                <td><textarea rows="5" cols="60" maxlength='500' id='conteudo' name='conteudo'><?php print $conteudo; ?></textarea></tr>
            <tr><td></td><td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><a href="javascript:$('<?= $DIV ?>').load('<?= $SITE ?>'); void(0);">Novo/Limpar</a></td> 
                        </tr></table> 
                </td></tr> 
        </table>
    </form>
</div>
<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$params['pessoa'] = $_SESSION['loginCodigo'];
$res = $aviso->listAvisos($params, $item, $itensPorPagina);

$totalRegistros = count($aviso->listAvisos($params));
$SITENAV = $SITE . '?';
require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="40">#</th>
        <th>Data</th>
        <th>Aviso</th>
        <th>Para</th>
        <th align="center" width="50">&nbsp;&nbsp;
            <input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?php print ICONS; ?>/delete.png' />
            </a>
        </th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $para = '';
        if ($reg['curso'])
            $para = $reg['curso'];
        if ($reg['turma'])
            $para = $reg['turma'];
        if ($reg['destinatario'])
            $para = $reg['destinatario'];

        if (!$para)
            $para = 'Todos';
        ?>
        <tr <?= $cdif ?>><td align='left'><?= $i ?></td>
            <td><?= $reg['data'] ?></td>
            <td><?= mostraTexto($reg['conteudo']) ?></td>
            <td><?= mostraTexto($para) ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>

<script>
    function valida() {
        if ($('#conteudo').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
        }
    }

    $(document).ready(function () {
        valida();

        $('#conteudo').keyup(function () {
            valida();
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

                        $('<?= $DIV ?>').load('<?php print $SITE; ?>&opcao=delete&codigo=' + selected + '&item=<?php print $item; ?>');
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