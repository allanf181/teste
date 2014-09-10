<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe uma lista com os códigos, nomes e modalidades de todos os cursos ministrados pelo Campus.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/curso.class.php";
$cursos = new Cursos();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $ret = $cursos->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if ($_POST['codigo'])
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $cursos->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => dcrip($_GET["codigo"]));
    $res = $cursos->listRegistros($params);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

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
        <table align="center" width="100%" id="form">
            <input type="hidden" name="codigo" value="<?php echo crip($codigo); ?>" />
            <tr>
                <td align="right">Nome: </td>
                <td><input type="text" name="nome" id="nome" maxlength="145" disabled value="<?php echo $nome; ?>"/></td>
            </tr>
            <tr>
                <td align="right">Nome Alternativo: </td>
                <td><input type="text" maxlength="145" style="width: 400px" title="Nome alternativo utilizado no atestado de matrícula." name="nomeAlternativo" id="nomeAlternativo" value="<?php echo $nomeAlternativo; ?>"/></td>
            </tr>            
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%">
                        <tr>
                            <td><input type="submit" value="Salvar" id="salvar" class="submit" /></td>
                            <td><input type="reset" value="Limpar" id="salvar" class="submit" onclick="javascript:$('#index').load('<?php print $SITE; ?>');
                                            void(0);"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;
$ordem = '';

if (isset($_GET['item']))
    $item = $_GET["item"];

$res = $cursos->listRegistros($params, $item, $itensPorPagina);

$totalRegistros = $cursos->count();
$SITENAV = $SITE . '?';
require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="center" width="40">#</th>
        <th align="left">Curso</th>
        <th align="left">Modalidade</th>
        <th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
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
        $codigo = crip($reg['codigo']);
        ?>
        <tr <?php print $cdif; ?>>
            <td align='center'><?php print $i; ?></td>
            <td><?php print $reg['nome']; ?></td>
            <td><?php print $reg['modalidade']; ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= $codigo ?>' />
                <a href='#' title='Alterar' class='item-alterar' id='<?= $codigo ?>'>
                    <img class='botao' src='<?php print ICONS; ?>/config.png' />
                </a>
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>
<br />

<script>
    $(document).ready(function() {
        $(".item-excluir").click(function() {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?', {
                'type': 'question',
                'title': '<?php print $TITLE; ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function(caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function() {
                            selected.push($(this).val());
                        });

                        $('#index').load('<?php print $SITE; ?>?opcao=delete&codigo=' + selected + '&item=<?php print $item; ?>');
                    }
                }
            });
        });

        $(".item-alterar").click(function() {
            var codigo = $(this).attr('id');
            $('#index').load('<?php print $SITE; ?>?codigo=' + codigo);
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
    });
</script>