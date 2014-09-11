<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar os dados das cidades importadas do Nambei.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

require CONTROLLER . "/cidade.class.php";
$cidade = new Cidades();

// PARA QUALQUER APLICAÇÃO UTILIZANDO AJAX PESQUISANDO CIDADES
if ($_GET["ajaxCidade"]) {
    $arr = array();
    header('Cache-Control: no-cache');
    header('Content-type: application/xml; charset="utf-8"', true);

    $cidades = $cidade->listCidadesToJSON(dcrip($_GET["codigo"]));

    echo( json_encode($cidades) );
    die;
}

require SESSAO;

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $ret = $cidade->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = crip($_POST['codigo']);
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $cidade->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if ($_GET['estado']) {
    $estado = dcrip($_GET['estado']);
    $params['estado'] = $estado;
    $sqlAdicional = ' AND e.codigo = :estado ';
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => dcrip($_GET["codigo"]));
    $res = $cidade->listRegistros($params);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
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
    <form id="form_padrao">     
        <table align="center" width="100%" id="form">
            <tr><td align="right">Estado: </td><td>
                    <select name="estado" id="estado" value="<?php echo $estado; ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . '/estado.class.php';
                        $e = new Estados();
                        $res = $e->listRegistros(null, null, null, ' ORDER BY nome ');
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $estado)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td></tr>
            <tr><td align="right">Nome: </td><td><input type="text" maxlength="145" name="nome" id="nome" value="<?php echo $nome; ?>"/></td></tr>
            <tr><td></td><td>
                    <input type="hidden" value="<?php echo $codigo; ?>" name="codigo" />
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><input type="reset" value="Novo/Limpar" id="salvar" class="submit" onclick="javascript:$('#index').load('<?php print $SITE; ?>');
                                            void(0);" /></td>
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

if ($params['codigo'])
    $sqlAdicional = ' AND c.codigo = :codigo ';

$res = $cidade->listCidades($params, $item, $itensPorPagina, $sqlAdicional);
$totalRegistros = count($cidade->listCidades($params, null, null, $sqlAdicional));

$params['estado'] = crip($params['estado']);
$SITENAV = $SITE . '?' . mapURL($params);
require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="center" width="40">#</th>
        <th align="left">Cidade</th>
        <th>Estado</th>
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
        ?>
        <tr <?= $cdif ?>>
            <td align='center'><?= $i ?></td>
            <td><?= $reg['cidade'] ?></td>
            <td align='left'><?= $reg['estado'] ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                    <img class='botao' src='<?php print ICONS; ?>/config.png' />
                </a>
            </td>
            <?php
            $i++;
        }
        ?>
</table>

<script>
    function atualizar(getLink) {
        var estado = $('#estado').val();
        var URLS = '<?php print $SITE; ?>?estado=' + estado;
        if (!getLink)
            $('#index').load(URLS + '&item=<?php print $item; ?>');
        else
            return URLS;
    }

    function valida() {
        if ($('#estado').val() == "" || $('#nome').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
        }
    }

    $(document).ready(function() {
        valida();
        $('#estado, #nome').keyup(function() {
            valida();
        });

        $(".item-excluir").click(function() {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?php print $TITLE; ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function(caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function() {
                            selected.push($(this).val());
                        });

                        $('#index').load(atualizar(1) + '&opcao=delete&codigo=' + selected + '&item=<?php print $item; ?>');
                    }
                }
            });
        });

        $('#select-all').click(function(event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        $(".item-alterar").click(function() {
            var codigo = $(this).attr('id');
            $('#index').load(atualizar(1) + '&codigo=' + codigo);
        });

        $('#estado').change(function() {
            atualizar();
        });
    });
</script>