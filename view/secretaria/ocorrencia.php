<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita o registro de ocorrência de alunos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

if ($_GET['dados']) {
    $arr = array();

    $paramPessoa = array('tipo' => $ALUNO, 'ano' => $ANO, 'semestre' => $SEMESTRE, ':s' => '%' . $_GET["q"] . '%');
    $sqlAdicionalTipo = " AND pt.tipo = :tipo AND p.codigo IN
            (SELECT m.aluno FROM Atribuicoes a, Matriculas m, Turmas t
            WHERE a.codigo = m.atribuicao AND a.turma = t.codigo
            AND t.ano = :ano AND (t.semestre = :semestre OR t.semestre = 0)) ";
    foreach ($pessoa->listPessoasTiposToJSON($paramPessoa, $sqlAdicionalTipo) as $reg)
        $arr[] = $reg;

    $json_response = json_encode($arr);

    if ($_GET["callback"])
        $json_response = $_GET["callback"] . "(" . $json_response . ")";

    echo $json_response;
    die;
}

require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/ocorrencia.class.php";
$ocorrencia = new Ocorrencias();

require CONTROLLER . "/ocorrenciaInteracao.class.php";
$interacao = new OcorrenciasInteracoes();

$remover = 1;
if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
    $remover = 0;
}

if ($_GET["opcao"] == 'historico') {
    //REMOVENDO INTERACAO
    if (isset($_GET["remover"]) && $remover) {
        $interacao->delete($_GET["remover"]);
    }
    ?>
    <table border="1" style="border-collapse:collapse; font-family: verdana; font-size: 10px;" align="center" width="100%">
        <tr style="background-color: #ccc">
            <th align="center" width="40">Data</th>
            <th>Registro por</th>
            <th>Descri&ccedil;&atilde;o</th>
            <?php if ($remover) print '<th>&nbsp;</th>'; ?>
        </tr>
        <?php
        $params = array('codigo' => dcrip($_GET["codigo"]));
        $sqlAdicional = ' AND o.codigo = :codigo ORDER BY data DESC, codigo DESC ';
        $res = $ocorrencia->listOcorrencias($params, $sqlAdicional);
        ?>
        <tr>
            <th><?= $res[0]['dataFormat'] ?></th>
            <th><?= $res[0]['registroPor'] ?></th>
            <th><?= $res[0]['descricao'] ?></th>
            <?php if ($remover) print '<th>&nbsp;</th>'; ?>
        </tr>
        <?php
        $sqlAdicional = ' AND i.ocorrencia = :codigo ORDER BY i.data DESC ';
        foreach ($interacao->listInteracoes($params, $sqlAdicional) as $l) {
            ?>
            <tr>
                <th><?= $l['dataFormat'] ?></th>
                <th><?= $l['registroPor'] ?></th>
                <th><?= $l['descricao'] ?></th>
                <?php if ($remover) { ?>
                    <td><a href="ocorrencia.php?opcao=historico&codigo=<?= $_GET['codigo'] ?>&remover=<?= crip($l['codigo']) ?>">remover</a></td>
                <?php } ?>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    die;
}

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['data'] = date('Y-m-d H:i:s');
    $_POST['registroPor'] = $_SESSION['loginCodigo'];
    unset($_POST['opcao']);
    unset($_POST['pesquisa']);

    if (dcrip($_POST['codigo'])) {
        unset($_POST['to']);
        $_POST['ocorrencia'] = $_POST['codigo'];
        unset($_POST['codigo']);
        $ret = $interacao->insertOrUpdate($_POST);
        $_GET["codigo"] = $_POST['ocorrencia'];
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    } else {
        $ret = $ocorrencia->insertOrUpdateOcorrencias($_POST);
        mensagem('OK', 'OCORRENCIA', $ret);
    }
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $ocorrencia->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
// inicializando as variáveis do formulário

if ($_GET["pesquisa"]) {
    $params['nome'] = '%' . $_GET["pesquisa"] . '%';
    $pesquisa = $_GET["pesquisa"];
    $sqlAdicional .= ' AND p.nome like :nome ';
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $res = $ocorrencia->listRegistros(array('codigo' => dcrip($_GET["codigo"])));
    $codOcorr = $res[0]['codigo'];
    $res = $pessoa->listRegistros(array('codigo' => $res[0]['aluno']));
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
    $sqlAdicional .= " AND o.codigo = :codigo ";
    $params['codigo'] = dcrip($_GET["codigo"]);
    $descricao = null;
}
?>

<script type="text/javascript" src="<?= VIEW ?>/js/AutocompleteList/src/jquery.tokeninput.js"></script>
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input-facebook.css" type="text/css" />

<script type="text/javascript">
    $(document).ready(function () {
        $("#to").tokenInput("<?= $SITE ?>?dados=1", {
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
            <input type="hidden" value="<?= crip($codOcorr) ?>" name="codigo" id="codigo" />
            <tr>
                <td align="right">Para: </td>
                <td>
                    <?php
                    if ($codigo) {
                        ?>
                        <b><?= $nome ?></b>
                        <?php
                    } else {
                        ?>
                        <input type="text" id="to" name="to" />
                        <?php
                    }
                    ?>
                </td>
                <td>
                    Pesquisa: <input type="text" name="pesquisa" id="pesquisa" value="<?= $pesquisa ?>">
                    &nbsp;
                    <a href="#" id="botaoPesquisa" title="Buscar">
                        <img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png' />
                    </a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td align="left" colspan="2">
                    <font size='1'><?= $para ?> Deixe em branco para enviar para todos.</font>
                </td>
            </tr>
            <tr>
                <td align="right">Descri&ccedil;&atilde;o:</td>
                <td colspan="2">
                    <textarea maxlength="500" rows="5" cols="80" id="descricao" name="descricao" style="width: 400px; height: 60px"><?= $descricao ?></textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%">
                        <tr>
                            <td>
                                <input type="submit" value="Salvar" id="salvar" />
                                <input type="button" id="limpar" onclick="$('#form_padrao').attr('id','')" value=" Novo/Limpar " />
                            </td>                            
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <font color="red"><br />1. Depois de cadastrada, uma ocorr&ecirc;ncia n&atilde;o pode ser alterada, somente apagada.
                    <br />2. Somente a CRE/ADM/GED pode remover uma ocorr&ecirc;ncia ou intera&ccedil;&atilde;o.
                    <br />3. Somente TAEs e Docentes possuem acesso, alunos não conseguem visualizar ocorrências.</font>
                </td>
            </tr>            
        </table>
    </form>
</div>
<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$sqlAdicional .= ' ORDER BY data DESC, p.nome ASC, codigo DESC ';
$res = $ocorrencia->listOcorrencias($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($ocorrencia->listOcorrencias($params, $sqlAdicional));

$params = array('aluno' => crip($params['aluno']));
$SITENAV = $SITE . "?" . mapURL($params);

require PATH . VIEW . '/system/paginacao.php';
?>
<table id="listagem" border="0" align="center">
    <tr>
        <th width="250">&nbsp;Aluno</th>
        <th width="140">Data</th>
        <th width="250">Registro por</th>
        <th>Descri&ccedil;&atilde;o</th>
        <th width="90">Intera&ccedil;&otilde;es</th>
        <th width="50">A&ccedil;&otilde;es</th>
        <?php if ($remover) {
            ?>
            <th width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
                <a href="#" class='item-excluir'><img class='botao' src='<?= ICONS ?>/delete.png' /></a>
            </th>
        <?php } ?>
    </tr>
    <?php
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>>
            <td>&nbsp;<a href="#" data-placement="top" title="Aluno" data-content="<?= $reg['aluno'] ?>"><?= abreviar($reg['aluno'], 30) ?></a></td>
            <td><?= $reg['dataFormat'] ?></td>
            <td>&nbsp;<a href="#" data-placement="top" title="Regitro por" data-content="<?= $reg['registroPor'] ?>"><?= abreviar($reg['registroPor'], 30) ?></a></td>
            <td>&nbsp;<a href="#" data-placement="top" title="Descri&ccedil;&atilde;o" data-content="<?= $reg['descricao'] ?>"><?= abreviar($reg['descricao'], 25) ?></a></td>
            <td align='center'><?= $reg['interacao'] ?></td>
            <td align='center'>
                <a href='#' data-placement="top" title='Ver hist&oacute;rico de ocorr&ecirc;ncias e intera&ccedil;&otilde;es'>
                    <img class='botao search' id='<?= crip($reg['codigo']) ?>' src='<?= ICONS ?>/search.png' />
                </a>
                <a href="#" data-placement="top" class='item-add' id='<?= crip($reg['codigo']) ?>' title='Adicionar uma intera&ccedil;&atilde;o nesta ocorr&ecirc;ncia.'>
                    <img class='botao' src='<?= ICONS ?>/add.png' />
                </a>                
            </td>
            <?php if ($remover) { ?>
                <td align='center'>
                    <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                </td>
            <?php } ?>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>

<script>
    $('#limpar').click(function(){
        $('#index').load('<?= $SITE ?>');
    });
    $(".search").click(function () {
        var codigo = $(this).attr('id');
        new $.Zebra_Dialog('<strong>Hist&oacute;rico de Ocorr&ecirc;ncias e Intera&ccedil;&otilde;es</strong>', {
            source: {'iframe': {
                    'src': 'view/secretaria/ocorrencia.php?opcao=historico&codigo=' + codigo,
                    'height': 300
                }},
            width: 600,
            title: '<?= $tabela ?>',
            onClose: function () {
                atualizar();
            }
        });
    });

    function atualizar(getLink) {
        var URLS = '<?= $SITE ?>?';
        var pesquisa = encodeURIComponent($('#pesquisa').val());
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>&pesquisa='+pesquisa);
        else
            return URLS;
    }

    function valida() {
        if ($('#to').val() != "" && ($('#descricao').val() != "")) {
            $('#salvar').removeAttr('disabled');
        } else {
            $('#salvar').attr('disabled', 'disabled');
        }
    }

    $(document).ready(function () {
        valida();
        $('#to').change(function () {
            valida();
        });

        $('#descricao').keyup(function () {
            valida();
        });

        $('#descricao').maxlength({
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

        $('#botaoPesquisa').click(function () {
            atualizar();
        });
        
        $(".item-add").click(function () {
            var codigo = $(this).attr('id');
            $('#index').load(atualizar(1) + '&codigo=' + codigo);
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
    });
</script>