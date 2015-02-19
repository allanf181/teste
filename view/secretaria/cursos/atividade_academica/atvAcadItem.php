<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita associar um ou mais itens em uma atividade previamente cadastrada.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atvAcademica.class.php";
$atv = new AtvAcademicas();

require CONTROLLER . "/atvAcadItem.class.php";
$atvItem = new AtvAcadItens();

// IMPORTAÇÃO DE ITENS PRÉ-CADASTRADOS
if ($_GET["opcao"] == 'import') {
    if (!dcrip($_GET['atvAcademica'])) {
        $ret['STATUS'] = 'INFO';
        $ret['TIPO'] = 'ATV_EMPTY';
        $_GET['atvAcademica'] = null;
    } else {
        $ret = $atvItem->import($_GET['atvAcademica']);
    }
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $ret = $atvItem->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);

    $_GET["curso"] = $_POST['curso'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $atvItem->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if (dcrip($_GET["atvAcademica"])) {
    $atvAcademica = dcrip($_GET["atvAcademica"]);
    $params['atvAcademica'] = $atvAcademica;
    $sqlAdicional .= ' AND atvAcademica = :atvAcademica ';
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params1 = array('codigo' => dcrip($_GET["codigo"]));
    $res = $atvItem->listRegistros($params1);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsCurso['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCurso = " curso IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
    $sqlAdicionalCurso2 = ' WHERE ' . $sqlAdicionalCurso;
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
            <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
            <tr>
                <td align="right">Atividade: </td>
                <td>
                    <select name="atvAcademica" id="atvAcademica" value="<?= $atvAcademica ?>" style="width: 350px">
                        <option></option>
                        <?php
                        foreach ($atv->listRegistros($paramsCurso, $sqlAdicionalCurso2) as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $atvAcademica)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Tipo: </td>
                <td>
                    <select name="tipo" id="tipo" value="<?= $tipo ?>" style="width: 350px">
                        <option value="Científica">Cient&iacute;fica</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Acadêmica">Acad&ecirc;mica</option>
                    </select>
                </td>
            </tr>            
            <tr>
                <td align="right">Descri&ccedil;&atilde;o da atividade: </td>
                <td>
                    <textarea maxlength="300" rows="5" cols="80" id="atividade" name="atividade" style="width: 600px; height: 40px"><?= $atividade ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">Comprova&ccedil;&atilde;o: </td>
                <td>
                    <textarea maxlength="300" rows="5" cols="80" id="comprovacao" name="comprovacao" style="width: 600px; height: 40px"><?= $comprovacao ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">Carga hor&aacute;ria: </td>
                <td>
                    <textarea maxlength="300" rows="5" cols="80" id="CH" name="CH" style="width: 600px; height: 40px"><?= $CH ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">Limite da CH ao longo do curso: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CHLimite" id="CHLimite" value="<?= $CHLimite ?>"/>
                </td>
            </tr>             
            <tr><td>&nbsp;</td>
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
                            <td>
                                <a href='#' title='Importar itens pré-cadastrados para a atividade selecionada.' class='item-importar' id='<?= crip($atvAcademica) ?>'>
                                    <img class='botao' src='<?= ICONS ?>/config.png' />
                                </a>
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
$itensPorPagina = 50;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params = $paramsCurso;
    $sqlAdicional .= ' AND ' . $sqlAdicionalCurso;
}

if ($atvAcademica) {
    $params['atvAcademica'] = $atvAcademica;
    $sqlAdicional .= ' AND atvAcademica = :atvAcademica ';
}

if ($codigo) {
    $params['codigo'] = $codigo;
    $sqlAdicional .= ' AND ai.codigo = :codigo ';
}

$res = $atvItem->listItens($params, $sqlAdicional);
$totalRegistros = count($atvItem->listItens($params, $sqlAdicional, null, null));

$params['atvAcademica'] = crip($atvAcademica);
$SITENAV = $SITE . "?" . mapURL($params);
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">C&oacute;digo</th>
        <th align="left">Nome</th>
        <th align="left" width="100">Tipo</th>
        <th align="left">Atividade</th>
        <th align="left">Comprova&ccedil;&atilde;o</th>
        <th align="left">Carga horária</th>
        <th align="left" width="100">Limite de CH ao longo do curso</th>
        <th align="center" width="45">&nbsp;
            <input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?= ICONS ?>/delete.png' />
            </a>
        </th>
        <th align="center" width="20"></th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
            <td><?= mostraTexto($reg['nome']) ?></td>
            <td><?= $reg['tipo'] ?></td>
            <td><a href="#" title="<?= $reg['atividade'] ?>"><?= abreviar($reg['atividade'], 20) ?></a></td>
            <td><a href="#" title="<?= $reg['comprovacao'] ?>"><?= abreviar($reg['comprovacao'], 20) ?></a></td>
            <td><a href="#" title="<?= $reg['CH'] ?>"><?= abreviar($reg['CH'], 20) ?></a></td>
            <td><?= $reg['CHLimite'] ?>h</td>
            <td align='left'>
                &nbsp;<input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
            </td>
            <td>
                <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                    <img class='botao' src='<?= ICONS ?>/config.png' />
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
        if ($('#atvAcademica').val() == "" || $('#atividade').val() == ""
                || $('#comprovacao').val() == "" || $('#CH').val() == ""
                || $('#CHLimite').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    function atualiza() {
        atvAcademica = $('#atvAcademica').val();
        $('#index').load('<?= $SITE ?>?atvAcademica=' + atvAcademica);
    }

    $('#atvAcademica').change(function () {
        atualiza();
    });

    $(document).ready(function () {
        valida();

        $('#atvAcademica, #atividade, #comprovacao, #CH, #CHLimite').keyup(function () {
            valida();
        });

        $(".item-importar").click(function () {
            var codigo = $(this).attr('id');
            $.Zebra_Dialog('<strong>Deseja importar itens pré-cadastrados para a atividade selecionada?</strong>', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        $('#index').load('<?= $SITE ?>?opcao=import&atvAcademica=' + codigo);
                    }
                }
            });
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

        $('#atividade, #comprovacao, #CH').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 300, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
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