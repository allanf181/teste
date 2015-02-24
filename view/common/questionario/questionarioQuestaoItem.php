<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require '../../../inc/config.inc.php';

require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;

$SITE_RAIZ = end(explode('/', $_SESSION['SITE_RAIZ']));
$PHP_SELF = substr(end(explode('/', $_SERVER['PHP_SELF'])), 0, strlen(end($SITE_RAIZ))-4).'.php';

if (!$SITE_RAIZ || $SITE_RAIZ = !$PHP_SELF || in_array($ALUNO, $_SESSION["loginTipo"])) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
} else {
    $SITE = 'view/common/questionario/questionarioQuestaoItem.php';
    $TITLE = 'Itens de Quest&otilde;es';
    $TITLE_DESCRICAO = "<span class=\"help\"><a title='Sobre esse m&oacute;dulo' data-content=\"Permite a inser&ccedil;&atilde;o itens de quest&otilde;es a questionarios criados anteriormente.\" href=\"#\"><img src=\"" . ICONS . "/help.png\"></a></span>";
}

require CONTROLLER . "/questionarioQuestao.class.php";
$questoes = new QuestionariosQuestoes();

require CONTROLLER . "/questionarioQuestaoItem.class.php";
$questoesItens = new QuestionariosQuestoesItens();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_GET['questionario'] = $_POST['questionario'];
    $_GET['questionarioNome'] = $_POST['questionarioNome'];
    $_GET['questaoNome'] = $_POST['questaoNome'];

    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    unset($_POST['questionario']);
    unset($_POST['questionarioNome']);
    unset($_POST['questaoNome']);
    unset($_POST['opcao']);

    $ret = $questoesItens->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    $_GET['questao'] = $_POST['questao'];
    $_GET['codigo'] = null;
    $nome = '';
    $valor = '';
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $questoesItens->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => dcrip($_GET["codigo"]));
    $params['questao'] = dcrip($_GET['questao']);
    $res = $questoesItens->listQuestoesItens($params, " AND codigo = :codigo ", null, null);
    $nome = $res[0]['nome'];
    $valor = $res[0]['valor'];
    unset($params);
}
?>

<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= dcrip($_GET['questionarioNome']) . '<br />' ?><?= $TITLE ?><?= ': ' . dcrip($_GET['questaoNome']) ?></h2>

<?php require_once PATH . VIEW . "/common/questionario/questionarioMenu.php" ?>

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
            <input type="hidden" name="codigo" value="<?= $_GET['codigo'] ?>" />
            <tr>
                <td align="right">Nome: </td>
                <td><input size="60" maxlength="145" id="campoNome" name="nome" value="<?= $nome ?>" /></td>
            </tr>
            <tr>
                <td align="right">Valor: </td>
                <td><input type = "text" id="campoValor" name="valor" value = "<?= $valor ?>"/></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <input type="hidden" name="questao" value="<?= $_GET['questao'] ?>" />
                    <input type="hidden" name="questaoNome" value="<?= $_GET['questaoNome'] ?>" />
                    <input type="hidden" name="questionario" value="<?= $_GET['questionario'] ?>" />
                    <input type="hidden" name="questionarioNome" value="<?= $_GET['questionarioNome'] ?>" />
                    <table width="100%">
                        <tr>
                            <td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td>
                                <a href="javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioQuestaoItem.php?questao=<?= $_GET['questao'] ?>&questionario=<?= $_GET['questionario'] ?>&questionarioNome=<?= $_GET['questionarioNome'] ?>&questaoNome=<?= $_GET['questaoNome'] ?>'); void(0)">Novo/Limpar</a>
                            </td> 
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

$params['questao'] = dcrip($_GET['questao']);

$res = $questoesItens->listQuestoesItens($params, null, $item, $itensPorPagina);

$totalRegistros = count($questoesItens->listQuestoesItens($params));
$SITENAV = $SITE . '?questao='.$_GET['questao'].'&questaoNome='.$_GET['questaoNome'].'&questionario='.$_GET['questionario'].'&questionarioNome='.$_GET['questionarioNome'];
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="40">#</th>
        <th>Nome</th>
        <th>Valor</th>
        <th align="center" width="50">&nbsp;&nbsp;
            <input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?= ICONS ?>/delete.png' />
            </a>
        </th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>><td align='left'><?= $i ?></td>
            <td><?= $reg['nome'] ?></td>
            <td><?= $reg['valor'] ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                <a class="item-alterar" href="javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioQuestaoItem.php?codigo=<?= crip($reg['codigo']) ?>&questionario=<?= $_GET['questionario'] ?>&questionarioNome=<?= $_GET['questionarioNome'] ?>&questao=<?= $_GET['questao'] ?>&questaoNome=<?= $_GET['questaoNome'] ?>'); void(0)" title = "Alterar">
                    <img class="botao" src="<?= ICONS . '/config.png' ?>" />
                </a>
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>
<script>
    function valida() {
        if ($('#campoNome').val() == "" || $('#campoDescricao').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
        }
    }

    $(document).ready(function () {
        valida();

        $('#campoNome').keyup(function () {
            valida();
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

                        $('#index').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&item=<?= $item ?>&questao=<?= $_GET['questao'] ?>');
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