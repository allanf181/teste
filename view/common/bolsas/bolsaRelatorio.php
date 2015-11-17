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

if (!$SITE_RAIZ || $SITE_RAIZ = !$PHP_SELF) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
} else {
    $SITE = 'view/common/bolsas/bolsaRelatorio.php';
    $TITLE = 'Relat&oacute;rios';
    $TITLE_DESCRICAO = "<span class=\"help\"><a title='Sobre esse m&oacute;dulo' data-content=\"Cadastro de relat&oacute;rios de atividades de bolsas de ensino.\" href=\"#\"><img src=\"" . ICONS . "/help.png\"></a></span>";
}

require CONTROLLER . "/bolsa.class.php";
$bolsas = new Bolsas();

require CONTROLLER . "/bolsaRelatorio.class.php";
$bolsaRelatorio = new BolsasRelatorios();

require CONTROLLER . "/bolsaAluno.class.php";
$bolsaAluno = new BolsasAlunos();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);
    $_POST['data'] = dataMysql($_POST['data']);

    $ret = $bolsaRelatorio->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);

    $_GET["bolsa"] = $_POST['bolsa'];
    $_GET["aluno"] = $_POST['aluno'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $bolsaRelatorio->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if (dcrip($_GET["bolsa"]) != "") {
    $params['bolsa'] = dcrip($_GET["bolsa"]);
    $sqlAdicional .= " AND b.codigo = :bolsa ";
    $bolsa = dcrip($_GET["bolsa"]);

    $sqlAdicionalAluno = $sqlAdicional;
    $paramsAluno = $params;
}

if (dcrip($_GET["aluno"]) != "") {
    $params['aluno'] = dcrip($_GET["aluno"]);
    $sqlAdicional .= " AND br.aluno = :aluno ";
    $aluno = dcrip($_GET["aluno"]);
}

if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
    $paramsProf['professor'] = $_SESSION["loginCodigo"];
    $sqlAdicionalProf = " AND p.codigo = :professor ";

    $params['professor'] = $_SESSION["loginCodigo"];
    $sqlAdicional .= " AND b.professor = :professor ";

    $paramsRestrict['professor'] = $_SESSION["loginCodigo"];
    $sqlAdicionalRestrict = " AND b.professor = :professor ";
}

if (in_array($ALUNO, $_SESSION["loginTipo"])) {
    $paramsRestrict['aluno'] = $_SESSION["loginCodigo"];
    $sqlAdicionalRestrict = " AND b.codigo IN (SELECT ba.bolsa FROM BolsasAlunos ba WHERE ba.aluno = :aluno) ";

    $sqlAdicionalAluno .= " AND p.codigo = :aluno ";
    $paramsAluno['aluno'] = $_SESSION["loginCodigo"];

    $sqlAdicional .= " AND p.codigo = :aluno ";
    $params['aluno'] = $_SESSION["loginCodigo"];
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params1 = array('codigo' => dcrip($_GET["codigo"]));
    $res = $bolsaRelatorio->listRegistros($params1);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
    $data = dataPTBR($data);
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
    <?php include("base.php"); ?>
    <form id="form_padrao" class="form_bolsas">
        <table align="center" width="100%" id="form">
            <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
            <tr>
                <td align="right">Bolsa: </td>
                <td>
                    <select name="bolsa" id="bolsa" value="<?= $bolsa ?>">
                        <option></option>
                        <?php
                        foreach ($bolsas->listBolsas($paramsRestrict, $sqlAdicionalRestrict) as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $bolsa) {
                                $selected = "selected";
                                $inicioCalendar = dataPTBR($reg['dataInicio']);
                                $fimCalendar = dataPTBR($reg['dataFim']);
                            }
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['titulo'] . " [" . $reg['professor'] . "]</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Aluno: </td>
                <td>
                    <select name="aluno" id="aluno">
                        <option></option>
                        <?php
                        if ($paramsAluno || $aluno) {
                            $sqlAdicionalAluno .= ' ORDER BY p.nome ';
                            $res = $bolsaAluno->listAlunos($paramsAluno, $sqlAdicionalAluno);
                            foreach ($res as $reg) {
                                $selected = "";
                                if ($reg['codAluno'] == $aluno)
                                    $selected = "selected";
                                print "<option $selected value='" . crip($reg['codAluno']) . "'>[" . $reg['prontuario'] . "] " . $reg['aluno'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Assunto: </td>
                <td>
                    <input type="text" maxlength="100" name="assunto" id="assunto" value="<?= $assunto ?>"/>
                </td>
            </tr>
            <tr>
                <td align="right">Data: </td>
                <td><input type="text" readonly class="data" id="data" name="data" value="<?= $data ?>" />
                </td>
            </tr>
            <tr>
                <td align="right">Descri&ccedil;&atilde;o: </td>
                <td><textarea maxlength="1000" rows="5" id="descricao" name="descricao"><?= $descricao ?></textarea>
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
                                <input style="width: 120px" type='button' id="limpar" value='Novo/Limpar' />
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

if (isset($_GET['item']))
    $item = $_GET["item"];

$sqlAdicional .= ' ORDER BY b.dataInicio DESC, b.titulo ASC ';

$res = $bolsaRelatorio->listRelatorios($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($bolsaRelatorio->listRelatorios($params, $sqlAdicional, null, null));

$paramsPesquisa['bolsa'] = crip($params['bolsa']);
$paramsPesquisa['aluno'] = crip($params['aluno']);

$SITENAV = $SITE . "?" . mapURL($paramsPesquisa);
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">C&oacute;digo</th>
        <th align="left">Bolsa</th>
        <th align="left">Aluno</th>
        <th align="left">Data</th>
        <th align="left">Assunto</th>
        <th align="left">Descri&ccedil;&atilde;o</th>
        <th align="center" width="45">&nbsp;
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
        <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
            <td><a href="#" data-placement="top" data-content='<?= $reg['titulo'] ?>' title="Bolsa"><?= abreviar(mostraTexto($reg['titulo']), 25) ?></a></td>
            <td><a href="#" data-placement="top" data-content='<?= $reg['aluno'] ?>' title="Aluno"><?= abreviar(mostraTexto($reg['aluno']), 25) ?></a></td>
            <td><?= $reg['data'] ?></td>
            <td><a href="#" data-placement="top" data-content='<?= $reg['assunto'] ?>' title="Assunto"><?= abreviar(mostraTexto($reg['assunto']), 25) ?></a></td>
            <td><a href="#" data-placement="top" data-content='<?= $reg['descricao'] ?>' title="Descri&ccedil;&atilde;o"><?= abreviar(mostraTexto($reg['descricao']), 25) ?></a></td>
            <td align='left'>
                &nbsp;<input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
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
    $('#limpar').click(function(){
        $('#index').load('<?= $SITE ?>');
    });
    function atualizar(getLink) {
        var bolsa = $('#bolsa').val();
        var aluno = $('#aluno').val();
        var URLS = '<?= $SITE ?>?bolsa=' + bolsa + '&aluno=' + aluno;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    $('#bolsa,#aluno').change(function () {
        atualizar();
    });

    $('#descricao').maxlength({
        events: [], // Array of events to be triggerd    
        maxCharacters: 1000, // Characters limit   
        status: true, // True to show status indicator bewlow the element    
        statusClass: "status", // The class on the status div  
        statusText: "caracteres restando", // The status text  
        notificationClass: "notification", // Will be added when maxlength is reached  
        showAlert: false, // True to show a regular alert message    
        alertText: "Limite de caracteres excedido!", // Text in alert message   
        slider: true // True Use counter slider    
    });

    $("#data").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior',
            minDate: '<?= $inicioCalendar ?>',
            maxDate: '<?= $fimCalendar ?>'
    });

    function valida() {
        if ($('#bolsa').val() == "" || $('#aluno').val() == ""
                || $('#assunto').val() == "" || $('#data').val() == ""
                || $('#descricao').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    $(document).ready(function () {
        valida();
        $('#bolsa,#aluno,#data').change(function () {
            valida();
        });
        $('#assunto,#descricao').keypress(function () {
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