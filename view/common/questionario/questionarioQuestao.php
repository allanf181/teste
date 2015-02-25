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
$PHP_SELF = substr(end(explode('/', $_SERVER['PHP_SELF'])), 0, strlen(end($SITE_RAIZ)) - 4) . '.php';

if (!$SITE_RAIZ || $SITE_RAIZ = !$PHP_SELF || in_array($ALUNO, $_SESSION["loginTipo"])) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
} else {
    $SITE = 'view/common/questionario/questionarioQuestao.php';
    $TITLE = 'Quest&otilde;es';
    $TITLE_DESCRICAO = "<span class=\"help\"><a title='Sobre esse m&oacute;dulo' data-content=\"Permite a inser&ccedil;&atilde;o de quest&otilde;es a questionarios criados anteriormente.\" href=\"#\"><img src=\"" . ICONS . "/help.png\"></a></span>";
}

//DEFININDO OS LINKS E O INDEX
if (!$_GET['index'])
    $_GET['index'] = 'index';
$BASE = '?atribuicao='.$_GET['atribuicao'].'&index='.$_GET['index'];
$SITE .= $BASE;

require CONTROLLER . "/questionarioQuestao.class.php";
$questoes = new QuestionariosQuestoes();

require CONTROLLER . "/questionarioCategoria.class.php";
$categorias = new QuestionariosCategorias();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $_POST['obrigatorio'] = (!dcrip($_POST['obrigatorio'])) ? 0 : 1;

    $ret = $questoes->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    $_GET['questionario'] = $_POST['questionario'];
    $_GET['codigo'] = null;
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $questoes->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}


// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => dcrip($_GET["codigo"]));
    $res = $questoes->listRegistros($params);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
    unset($params);
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>

<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?><?= ': ' . dcrip($_GET['questionarioNome']) ?></h2>
<?php require_once PATH . VIEW . "/common/questionario/questionarioMenu.php" ?>

<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE ?>',
        responseDiv: '<?= '#'.$_GET['index'] ?>',
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
                <td align="right">Nome da quest&atilde;o: </td>
                <td><input size="45" maxlength="45" id="campoNome" name="nome" value="<?= $nome ?>" /></td>
            </tr>
            <tr>
                <td align="right">Quest&atilde;o obrigat&oacute;ria?: </td>
                <td><input type="checkbox" id="campoObrigatorio" name="obrigatorio" <?= ($obrigatorio) ? 'checked = checked' : ''; ?> />
                </td>
            </tr>
            <tr>
                <td align="right">Categoria: </td>
                <td>
                    <select  name = "categoria" id = "categoria">
                        <?php
                        $resCat = $categorias->listRegistros();

                        foreach ($resCat AS $cat) {
                            if ($categoria == $cat['codigo'])
                                $selected = 'selected = selected';
                            else
                                $selected = '';
                            print '<option title = "' . $cat['descricao'] . '" value = "' . $cat['codigo'] . '" ' . $selected . '>' . $cat['nome'] . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                     * Mantenha o cursor do mouse sobre o item para dicas de funcionamento.
                    <br> * Aten&ccedil;&atilde;o, Texto, Par&aacute;grafo e data n&atilde;o entram na contabiliza&ccedil;&atilde;o para pontua&ccedil;&atilde;o. 
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <input type="hidden" name="questionario" value="<?= $_GET['questionario'] ?>" />
                    <table width="100%">
                        <tr>
                            <td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><a href="javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarioQuestao.php<?= $BASE ?>&questionario=<?= $_GET['questionario'] ?>&questionarioNome=<?= $_GET['questionarioNome'] ?>');void(0)">Novo/Limpar</a></td> 
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

$params['questionario'] = dcrip($_GET['questionario']);
$sqlAdicional = ' ORDER BY qq.codigo ';
$res = $questoes->listQuestoes($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($questoes->listQuestoes($params, $sqlAdicional, $item, $itensPorPagina));

$DIV_SITE = '#'.$_GET['index'];
$SITENAV = $SITE . '&questionario=' . $_GET['questionario'] . '&questionarioNome=' . $_GET['questionarioNome'];
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="40">#</th>
        <th>Nome</th>
        <th>Obrigat&oacute;ria</th>
        <th>Categoria</th>
        <th>A&ccedil;&otilde;es</th>
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
        <tr <?= $cdif ?>>
            <td align='left'><?= $i ?></td>
            <td><a href='#' data-placement="top" data-content='<?= $reg['questaoNome'] ?>' title='Nome'><?= abreviar($reg['questaoNome'], 30) ?></a></td>
            <td><?= ($reg['obrigatorio']) ? 'Sim' : 'N&atilde;o' ?></td>
            <td><a href='#' data-placement="top" data-content='<?= $reg['categoria'] ?>' title='Categoria'><?= abreviar($reg['categoria'], 30) ?></a></td>
            <td>
                <?php
                //verifica se a categoria está entre as que necessitam de itens
                if ($reg['codCategoria'] <= 3) {
                    ?>
                    <a data-placement="top" title="Adicionar Escolhas" data-content="Clique para adicionar escolhas &agrave; sua quest&atilde;o." href = "javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarioQuestaoItem.php<?= $BASE ?>&questao=<?= crip($reg['codigo']) ?>&questionario=<?= $_GET['questionario'] ?>&questionarioNome=<?= $_GET['questionarioNome'] ?>&questaoNome=<?= crip($reg['questaoNome']) ?>'); void(0)">
                        <img src = "<?= ICONS ?>/add.png" class="botao" /></a>
                    <?php
                }//fim da verificação se necessita de itens
                ?>
            </td>	
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                <a class="item-alterar" href="javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarioQuestao.php<?= $BASE ?>&codigo=<?= crip($reg['codigo']) ?>&questionario=<?= $_GET['questionario'] ?>&questionarioNome=<?= $_GET['questionarioNome'] ?>'); void(0)" title = "Alterar">
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

                        $('<?= '#'.$_GET['index'] ?>').load('<?= $SITE ?>&opcao=delete&codigo=' + selected + '&item=<?= $item ?>&questionario=<?= $_GET['questionario'] ?>');
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
