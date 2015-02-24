<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require '../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

$SITE_RAIZ = end(explode('/', $_SESSION['SITE_RAIZ']));
$PHP_SELF = substr(end(explode('/', $_SERVER['PHP_SELF'])), 0, strlen(end($SITE_RAIZ)) - 4) . '.php';

if (!$SITE_RAIZ || $SITE_RAIZ = !$PHP_SELF || in_array($ALUNO, $_SESSION["loginTipo"])) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
} else {
    $SITE = 'view/common/questionario/questionarioQuestaoItem.php';
    $TITLE = 'Itens de Quest&otilde;es';
    $TITLE_DESCRICAO = "<span class=\"help\"><a title='Sobre esse m&oacute;dulo' data-content=\"Permite a inser&ccedil;&atilde;o itens de quest&otilde;es a questionarios criados anteriormente.\" href=\"#\"><img src=\"" . ICONS . "/help.png\"></a></span>";
}

require CONTROLLER . "/questionarioResposta.class.php";
$resposta = new QuestionariosRespostas();

require CONTROLLER . "/questionarioQuestao.class.php";
$questao = new QuestionariosQuestoes();

$questionarioNome = dcrip($_GET['questionarioNome']);
$questionarioCodigo = dcrip($_GET['questionario']);

$params['questionario'] = $questionarioCodigo;
$sqlAdicional = ' AND q.codigo = :questionario ';
$res = $resposta->listRespostas($params, $sqlAdicional . ' ORDER BY p.nome ASC ');

if ($_GET['export']) {
    $orientacao = 'L'; // PORTRAIT

    $linha2 = $res;

    $titulo = $questionarioNome;
    $titulo2 = '';

    $titulosColunas = array("Nome", "Questão", "Resposta");
    $colunas = array("pessoa", "questao", "resposta");
    $largura = array(20, 55, 30);


    // gera o relatório em XLS
    include PATH . LIB . '/relatorio_planilha.php';
    die;
}

require MENSAGENS;
require SESSAO;

$SITE_RAIZ = end(explode('/', $_SESSION['SITE_RAIZ']));
$PHP_SELF = substr(end(explode('/', $_SERVER['PHP_SELF'])), 0, strlen(end($SITE_RAIZ))-4).'.php';

if (!$SITE_RAIZ || $SITE_RAIZ = !$PHP_SELF) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
} else {
    $SITE = 'view/common/questionario/questionarioResultado.php';
    $TITLE = 'Resultados';
    $TITLE_DESCRICAO = "<span class=\"help\"><a title='Sobre esse m&oacute;dulo' data-content=\"Permite visualizar e exportar as respostas dos question&aacute;rios.\" href=\"#\"><img src=\"" . ICONS . "/help.png\"></a></span>";
}

?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?><?= ': ' . dcrip($_GET['questionarioNome']) ?></h2>
<?php
require_once PATH . VIEW . "/common/questionario/questionarioMenu.php";

$pessoa = dcrip($_GET["pessoa"]);
?>
<table id="form" border="0" align="center" width="100%">
    <tr>
        <td style="text-align: center">
            <a class = 'nav questionario_item' href = "#" id="item-listagem">
                <img src = '<?= ICONS ?>/list.png' title = "Exportar question&aacute;rio" class = 'menuQuestionario' />
                <br />Listagem dos Dados
            </a>
        </td>
        <td style="text-align: center">
            <a class = 'nav questionario_item' href = "#" id="item-agrupamento">
                <img src = '<?= ICONS ?>/group.png' title = "Exportar question&aacute;rio" class = 'menuQuestionario' />
                <br />Agrupamento dos Dados
            </a>
        </td>
        <td style="text-align: center">
            <a class = 'nav questionario_item' href = "#" id="item-export">
                <img src = '<?= ICONS ?>/files/xls.png' title = "Exportar question&aacute;rio" class = 'menuQuestionario' />
                <br />Exportar Question&aacute;rio
            </a>
        </td>
    </tr>
    <tr><td colspan="3"><br /><hr /></td></tr>
    <?php
    if ($_GET['relatorio']) {
        ?>
        <tr>
            <td align="left" colspan="3">FILTRO: 
                <select name="pessoa" id="pessoa" style="width: 350px">
                    <option></option>
                    <?php
                    $sqlAdicionalFiltro .= $sqlAdicional . ' GROUP BY p.codigo ORDER BY p.nome ASC ';
                    $resFiltro = $resposta->listRespostas($params, $sqlAdicionalFiltro);

                    foreach ($resFiltro as $reg) {
                        $selected = "";
                        if ($reg['codPessoa'] == $pessoa)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['codPessoa']) . "'>" . $reg['pessoa'] . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }
    ?>
</table>
<br />
<?php
$paramsList = $params;
$sqlAdicionalList = $sqlAdicional;

$paramsGroup = $params;

if ($pessoa != "") {
    $paramsPessoa['pessoa'] = $pessoa;
    $sqlAdicionalPessoa .= " AND p.codigo = :pessoa ";

    $paramsGroup += $paramsPessoa;
    $sqlAdicionalGroup .= $sqlAdicionalPessoa;
    
    $paramsList += $paramsPessoa;
    $sqlAdicionalList .= $sqlAdicional . $sqlAdicionalPessoa;
}

$SITENAV = $SITE . '?&relatorio=' . $_GET['relatorio'] . '&questionario=' . $_GET['questionario'] . '&questionarioNome=' . $_GET['questionarioNome'];

if ($_GET['relatorio'] == 'agrupamento') {
    $dados = $questao->listQuestoes($params);
    foreach ($dados as $reg) {
        ?>
        <table class="socioeconomico" align="center">
            <tr>
                <th align="center" style='width: 50%' colspan='4'><?= $reg['questaoNome'] ?></th>
            </tr>
            <?php
            $total = 0;
            $paramsGroup['questao'] = $reg['codigo'];
            print $sqlAdicional2;
            foreach ($resposta->dadosTabela($paramsGroup, $sqlAdicionalGroup) as $reg2) {
                ?>
                <tr class='<?= (( ++$i % 2 == 0) ? "cdif" : "") ?>'>
                    <td align='center' style='width: 300px'><?= mostraTexto($reg2['resposta']) ?></td>
                    <td align='center' ><?= $reg2['total'] ?></td>
                    <td align='center' ><?= percentual($reg2['total'], $reg2['geral']) ?></td>
                </tr>
                <?php
            }
            ?>
            <tr style="background: #E0E0E0; font-weight: bold;  ">
                <td align='center'>TOTAL</td>
                <td align='center'><?= $reg2['geral'] ?></td>
                <td align='center'><?= percentual($reg2['geral'], $reg2['geral']) ?></td>
            </tr>
        </table>
        <?php
    }
}

if ($_GET['relatorio'] == 'listagem') {
    // PAGINACAO
    $itensPorPagina = 100;

    if (isset($_GET['item']))
        $item = $_GET["item"];

    $res = $resposta->listRespostas($paramsList, $sqlAdicionalList, $item, $itensPorPagina);
    $totalRegistros = count($resposta->listRespostas($params, $sqlAdicional));

    require PATH . VIEW . '/system/paginacao.php';
    ?>
    <table id="listagem" border="0" align="center">
        <tr>
            <th colspan = "4">
        <center><?= $questionarioNome ?></center>
    </th>
    </tr>
    <tr>
        <th align="left" width="40">#</th>
        <th>Pessoa</th>
        <th>Questao</th>
        <th>Resposta</th>
    </tr>
    <?php
    foreach ($res as $reg) {//pessoas 
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>>
            <td align='left'><?= $i ?></td>

            <td width='120'><a href='#' data-placement="top" data-content='<?= $reg['pessoa'] ?>' title='Pessoa'><?= abreviar($reg['pessoa'], 30) ?></a></td>
            <td width='120'><a href='#' data-placement="top" data-content='<?= $reg['questao'] ?>' title='Questão'><?= abreviar($reg['questao'], 30) ?></a></td>
            <td width='120'><a href='#' data-placement="top" data-content='<?= $reg['resposta'] ?>' title='Resposta'><?= abreviar($reg['resposta'], 30) ?></a></td>
        </tr>
        <?php
        $i++;
    }
    ?>
    </table>
    <?php
}
?>
<script>
    $('#pessoa').change(function () {
        atualizar();
    });

    function atualizar(getLink) {
        var pessoa = $('#pessoa').val();
        var URLS = '<?= $SITENAV ?>&pessoa=' + pessoa;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    $("#item-export").click(function () {
        window.open('<?= $SITENAV ?>&export=1');
    });

    $("#item-listagem").click(function () {
        $('#index').load('<?= $SITENAV ?>&relatorio=listagem');
    });

    $("#item-agrupamento").click(function () {
        $('#index').load('<?= $SITENAV ?>&relatorio=agrupamento');
    });
</script>
