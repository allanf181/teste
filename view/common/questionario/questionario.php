<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require MENSAGENS;
require SESSAO;

$SITE_RAIZ = end(explode('/', $_SESSION['SITE_RAIZ']));
$PHP_SELF = substr(end(explode('/', $_SERVER['PHP_SELF'])), 0, strlen(end($SITE_RAIZ)) - 4) . '.php';

if (!$SITE_RAIZ || $SITE_RAIZ = !$PHP_SELF || in_array($ALUNO, $_SESSION["loginTipo"])) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
}

//DEFININDO OS LINKS E O INDEX
if (!$_GET['index'])
    $_GET['index'] = 'index';
$BASE = '?atribuicao='.$_GET['atribuicao'].'&index='.$_GET['index'];
$SITE .= $BASE;


require_once CONTROLLER . "/questionario.class.php";
$questionario = new Questionarios();

require_once CONTROLLER . "/questionarioQuestao.class.php";
$questoes = new QuestionariosQuestoes();

require_once CONTROLLER . "/questionarioQuestaoItem.class.php";
$questoesItens = new QuestionariosQuestoesItens();

require_once CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

require_once CONTROLLER . "/questionarioPessoa.class.php";
$questionariosPessoas = new QuestionariosPessoas();

//Mudar Situacao
if ($_GET['opcao'] == 'mudarSituacao') {
    $paramsSituacao['codigo'] = $_GET['questionario'];
    $paramsSituacao['situacao'] = (!dcrip($_GET['situacao'])) ? 1 : 0;
    $ret = $questionario->insertOrUpdate($paramsSituacao);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    unset($_GET['questionarioNome']);
    unset($_GET['situacao']);
    unset($_GET['opcao']);
    unset($_GET['questionario']);
}

// INSERT E UPDATE DE COPIA
if (dcrip($_POST["codCopy"])) {
    unset($_POST['opcao']);

    $questionarioAntigo = dcrip($_POST['codCopy']);
    unset($_POST['questionario']);

    $_POST['criador'] = crip($_SESSION['loginCodigo']);
    $_POST['dataCriacao'] = dataMysql($_POST['dataCriacao']);
    $_POST['dataFechamento'] = dataMysql($_POST['dataFechamento']);

    $retQ = $questionario->insertOrUpdateQuestionarios($_POST);
    $questionarioNovo = $retQ['RESULTADO'];

    $params['questionario'] = $questionarioAntigo;
    $sqlAdicional = ' WHERE questionario = :questionario ORDER BY codigo ';
    $resQuestoes = $questoes->listRegistros($params, $sqlAdicional);

    foreach ($resQuestoes as $regQuestoes) {
        unset($params);
        $params['nome'] = $regQuestoes['nome'];
        $params['categoria'] = $regQuestoes['categoria'];
        $params['obrigatorio'] = $regQuestoes['obrigatorio'];
        $params['questionario'] = $questionarioNovo;


        $ret = $questoes->insertOrUpdate($params);
        $questaoNova = $ret['RESULTADO'];

        unset($params);
        $params['questao'] = $regQuestoes['codigo'];
        $resItens = $questoesItens->listQuestoesItens($params);

        foreach ($resItens as $regItens) {
            unset($params);
            $params['nome'] = $regItens['nome'];
            $params['valor'] = $regItens['valor'];
            $params['questao'] = $questaoNova;

            $retItens = $questoesItens->insertOrUpdate($params);
        }
    }
    mensagem($retQ['STATUS'], $retQ['TIPO'], $retQ['RESULTADO']);
    $_GET["codigo"] = crip($retQ['RESULTADO']);
}

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);
    if (dcrip($_POST['atribuicao']))
        $_POST['atribuicao'] = dcrip($_POST['atribuicao']);
    $_POST['criador'] = crip($_SESSION['loginCodigo']);
    $_POST['dataCriacao'] = dataMysql($_POST['dataCriacao']);
    $_POST['dataFechamento'] = ($_POST['dataFechamento']) ? dataMysql($_POST['dataFechamento']) : 'NULL';
    $ret = $questionario->insertOrUpdateQuestionarios($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if ($_POST['codigo'])
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $questionario->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

//DELETAR PESSOA DO QUESTIONARIO
if ($_GET['opcao'] == 'deletePessoa') {
    $ret = $questionariosPessoas->delete($_GET["pessoaCodigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["pessoaCodigo"] = null;
}

$questionarioNome = dcrip($_GET['questionarioNome']);
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>

<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?><?= ($_GET['codCopy']) ? ": Copiando de $questionarioNome" : "" ?></h2>

<script type="text/javascript" src="<?= VIEW ?>/js/AutocompleteList/src/jquery.tokeninput.js"></script>
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input-facebook.css" type="text/css" />

<script type="text/javascript">
    $(document).ready(function () {
        $("#to").tokenInput("<?= $SITE ?>&dados=1", {
            theme: "facebook"
        });
    });
</script>

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
<?php
if ($_GET['codigo'] && !dcrip($_GET['codCopy'])) {
    $paramsCodigo['codigo'] = dcrip($_GET['codigo']);
    $res = $questionario->listQuestionarios($paramsCodigo);
    $dataCriacao = explode('-', $res[0]['dataCriacao']);
    $dataCriacao = substr($dataCriacao[2], 0, 2) . '/' . $dataCriacao[1] . '/' . $dataCriacao[0];

    $dataFechamento = explode('-', $res[0]['dataFechamento']);
    $dataFechamento = substr($dataFechamento[2], 0, 2) . '/' . $dataFechamento[1] . '/' . $dataFechamento[0];
    $nome = $res[0]['nome'];
    $descricao = $res[0]['descricao'];
    $valorTotal = $res[0]['valorTotal'];

    unset($paramsCodigo['codigo']);

    $paramsCodigo['questionario'] = dcrip($_GET['codigo']);
    $resQuestionariosPessoas = $questionariosPessoas->listQuestionariosPessoas($paramsCodigo);
    if ($resQuestionariosPessoas) {
        $html = "<b>Destinat&aacute;rios do question&aacute;rio</b><br />Clique abaixo nos destinat&aacute;rios que deseja excluir<ul class = 'token-input-list-facebook'>";
        foreach ($resQuestionariosPessoas as $regQPessoas) {
            $pessoaQuestionario = '';

            if ($regQPessoas['pessoa'] == null && $regQPessoas['disciplina'] == null && $regQPessoas['tipo'] == null && $regQPessoas['curso'] == null && $regQPessoas['turma'] == null)
                $pessoaQuestionario = 'Todos';
            else if ($regQPessoas['pessoa'] != null && $regQPessoas['disciplina'] == null && $regQPessoas['tipo'] == null && $regQPessoas['curso'] == null && $regQPessoas['turma'] == null)
                $pessoaQuestionario = $regQPessoas['pessoa'];
            else if ($regQPessoas['pessoa'] == null && $regQPessoas['disciplina'] != null && $regQPessoas['tipo'] == null && $regQPessoas['curso'] == null && $regQPessoas['turma'] == null)
                $pessoaQuestionario = $regQPessoas['disciplina'];
            else if ($regQPessoas['pessoa'] == null && $regQPessoas['disciplina'] == null && $regQPessoas['tipo'] != null && $regQPessoas['curso'] == null && $regQPessoas['turma'] == null)
                $pessoaQuestionario = $regQPessoas['tipo'];
            else if ($regQPessoas['pessoa'] == null && $regQPessoas['disciplina'] == null && $regQPessoas['tipo'] == null && $regQPessoas['curso'] != null && $regQPessoas['turma'] == null)
                $pessoaQuestionario = $regQPessoas['curso'] . "[" . $regQPessoas['codCurso'] . "]";
            else if ($regQPessoas['pessoa'] == null && $regQPessoas['disciplina'] == null && $regQPessoas['tipo'] == null && $regQPessoas['curso'] == null && $regQPessoas['turma'] != null)
                $pessoaQuestionario = $regQPessoas['turma'];
            $html .= "<li class = 'token-input-token-facebook'><p>" . $pessoaQuestionario . "</p><span class = 'pessoa-excluir' id = '" . crip($regQPessoas['codigo']) . "'>x</span></li>";
        }
        $html.= "</ul>";
    }
}//fim se estiver setado $_GET['codigo']

if (!dcrip($_GET['atribuicao'])) {
?>
<table border = '0' width = '100%' id="form">
    <tr align = 'center' valign = 'top'>
        <td width = '33%' valign = 'top'>
            <a class = 'nav questionario_item' href = "javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= $SITE ?>');void(0)">
                <img width = '48' src = "<?= IMAGES . '/questionario.png' ?>" title = 'Question&aacute;rios' class = 'menuQuestionario'/>
                <br />Question&aacute;rios
            </a>
        </td>
        <td width = '33%' valign = 'top'>
            <a class = 'nav questionario_item' href = "javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= $SITE ?>&base=view');void(0)">
                <img width = '48' src = "<?= IMAGES . '/boletim.png' ?>" class = 'menuQuestionario' />
                <br />Question&aacute;rios endere&ccedil;ados para voc&ecirc;
            </a>
        </td>
    </tr>
</table>
<?php
}

if ($_GET['base']) {
    // COPIA DE:
    require PATH . VIEW . '/common/questionario/base.php';
    die;
}
?>
<div id="html5form" class="main">
    <form id="form_padrao">
        <table align="center" width="100%" id="form" border="0">
            <input type = 'hidden' name = 'codigo' value = '<?= $_GET['codigo'] ?>'>
            <input type = 'hidden' name = 'codCopy' value = '<?= $_GET['codCopy'] ?>'>
            <input type = 'hidden' name = 'atribuicao' value = '<?= $_GET['atribuicao'] ?>'>
            <tr>
                <td align="right">Data de cria&ccedil;&atilde;o: </td>
                <td><input type="text" readonly value = "<?= $dataCriacao ?>" id="campoDataCriacao" name="dataCriacao" /></td>
                <td><?= $html ?></td>
            </tr>
            <tr>
                <td align="right">Nome do question&aacute;rio: </td>
                <td><input size="45" maxlength="45" id="campoNome" name="nome" value="<?= $nome ?>"></td>
            </tr>
            <tr>
                <td align="right">Descri&ccedil;&atilde;o: </td>
                <td><input size="60" maxlength="145" id="campoDescricao" name="descricao" value="<?= $descricao ?>"></td>
            </tr>
            <tr>
                <td align="right">Data de fechamento: </td>
                <td><input type="text" value = "<?= $dataFechamento ?>" id="campoDataFechamento" name="dataFechamento" /></td>
            </tr>
            <tr>
                <td align="right">Valor Total: </td>
                <td><input type="text" value = "<?= $valorTotal ?>" id="campoValorTotal" name="valorTotal" /></td>
            </tr>
            <tr>
                <td align="right">Para: </td>
                <td><input type="text" id="to" name="to" />
                    <?php if ($_GET['codigo']) { ?> 
                        <input type = "checkbox" name="alteraPessoa" />&nbsp;Selecione para inserir novos destinat&aacute;rios
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td align="left"><font size='1'><?= $para ?> Deixe em branco para enviar para todos.</font></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%">
                        <tr>
                            <td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><a href="javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= $SITE ?>'); void(0);">Novo/Limpar</a></td> 
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
if (in_array($ADM, $_SESSION['loginTipo'])) {
    $res = $questionario->listQuestionarios(null, null, $item, $itensPorPagina);
    $totalRegistros = count($questionario->listQuestionarios());
} else {
    if (dcrip($_GET['atribuicao'])) {
        $params['atribuicao'] = dcrip($_GET['atribuicao']);
        $sqlAdicional = ' AND q.codigo IN (SELECT p.questionario FROM QuestionariosPessoas p WHERE p.atribuicao = :atribuicao) ';
    }
    $params['criador'] = $_SESSION['loginCodigo'];
    $res = $questionario->listQuestionarios($params, $sqlAdicional, $item, $itensPorPagina);
    $totalRegistros = count($questionario->listQuestionarios($params, $sqlAdicional));
}

$SITENAV = $SITE;
$DIV_SITE = '#'.$_GET['index'];
require PATH . VIEW . '/system/paginacao.php';
?>	
<table id="listagem" border="0" align="center" cellpadding = "5px">
    <tr><th align="left" width="40">#</th>
        <?php
        if (in_array($ADM, $_SESSION['loginTipo'])) {
            ?>
            <th>Criador</th>
            <?php
        }
        ?>
        <th>Criado em</th>
        <th>Nome</th>
        <th>Descri&ccedil;&atilde;o</th>
        <th>Fechamento</th>
        <th>Valor Total</th>
        <th width="150">A&ccedil;&otilde;es</th>
        <th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'><img class='botao' src='<?= ICONS ?>/delete.png' /></a>
        </th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $dataCriacao = explode('-', $reg['dataCriacao']);
        $dataFechamento = explode('-', $reg['dataFechamento']);
        $dataCriacao = substr($dataCriacao[2], 0, 2) . "/" . $dataCriacao[1] . "/" . $dataCriacao[0];
        $dataFechamento = substr($dataFechamento[2], 0, 2) . "/" . $dataFechamento[1] . "/" . $dataFechamento[0];
        ?>
        <tr <?= $cdif ?>><td align='left'><?= $i ?></td>
            <?php
            if (in_array($ADM, $_SESSION['loginTipo'])) {
                ?>
                <td>
                    <?php
                    $paramP['codigo'] = $reg['criador'];
                    $Pessoa = $pessoa->listPessoasTipos($paramP, " AND p.codigo = :codigo");

                    print $Pessoa[0]['nome'];
                    ?>
                </td>
                <?php
            }
            ?>
            <td><?= $dataCriacao ?></td>
            <td><a href="javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarioQuestao.php<?= $BASE ?>&questionario=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>');void(0)" data-placement="top" data-content="Clique para visualizar as quest&otilde;es" title="<?= $reg['nome'] ?>"><?= abreviar($reg['nome'], 20) ?></a></td>
            <td><a href="#" data-placement="top" title="Descri&ccedil;&atilde;o" data-content="<?= $reg['descricao'] ?>"><?= abreviar($reg['descricao'], 20) ?></a></td>
            <td><?= $dataFechamento ?></td>
            <td><?= $reg['valorTotal'] ?></td>
            <td>
                <?php require PATH . VIEW . "/common/questionario/questionarioMenu.php" ?>
                <a data-placement="top" title="Pr&eacute;-visualiza&ccedil;&atilde;o" data-content="Clique para ver como o question&aacute;rio será visualizado." href="javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarioVisualiza.php<?= $BASE ?>&questionario=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>&preview=preview');void(0)">
                    <img src = "<?= IMAGES . '/questionarioPreview.png' ?>" class='botao'/>
                </a>
                <a data-placement="top" title="Copiar Question&aacute;rio" data-content="Clique para copiar esse question&aacute;rio para outro curso ou pessoa." href="javascript:$('<?= '#'.$_GET['index'] ?>').load('<?= $SITE ?>&codCopy=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>');void(0)">
                    <img src = "<?= IMAGES . '/questionarioCopia.png' ?>" class='botao'/>
                </a>
            </td>

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
        if ($('#campoNome').val() == "" || $('#campoDescricao').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
        }
    }

    $(document).ready(function () {
        valida();


        if ($('#campoDataCriacao').val() == '')
        {
            $('#campoDataCriacao').val('<?= date("d/m/Y") ?>');
        }

        $("#campoDataCriacao, #campoDataFechamento").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior'
        });

        $('#campoNome, #campoDescricao').keyup(function () {
            valida();
        });

        $("#questoes, #resultados,#preview").click(function () {
            if ($('input:checkbox:checked').val())
            {
                id = $(this).attr('id');
                id = id.substr(0, 1).toUpperCase() + id.substr(1);
                if ($(this).attr('id') == 'preview')
                    $('<?= '#'.$_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarioVisualiza.php<?= $BASE ?>&questionario=' + $('input:checkbox:checked').val() + '&preview=preview');
                else
                    $('<?= '#'.$_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarios' + id + '.php<?= $BASE ?>&questionario=' + $('input:checkbox:checked').val());
            }
            else
            {
                $.Zebra_Dialog('<strong>Selecione o question&aacute;rio'), {
                    'type': 'alert',
                    'title': '<?= $TITLE ?>',
                    'buttons': ['OK']
                }
            }
        })
        $(".item-excluir").click(function () {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>\n\
                            <br><br>Aten&ccedil;&atilde;o TODAS as quest&otilde;es e respostas j&aacute; informadas ser&atilde;o apagadas.\n\
                            <br><br>Caso n&atilde;o queira apagar, existe a op&ccedil;&atilde;o de Desativar o Question&aacute;rio.', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function () {
                            selected.push($(this).val());
                        });

                        $('<?= '#'.$_GET['index'] ?>').load('<?= $SITE ?>&opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                    }
                }
            });
        });

        $(".pessoa-excluir").click(function () {
            id = $(this).attr('id');
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        $('<?= '#'.$_GET['index'] ?>').load('<?= $SITE ?>&opcao=deletePessoa&pessoaCodigo=' + id + '&codigo=<?= $_GET["codigo"] ?>&item=<?= $item ?>');
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