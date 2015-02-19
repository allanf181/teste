<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita a visualização do calendário do ano letivo corrente do Campus.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/calendario.class.php";
$calendario = new Calendarios();

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['dataInicio'] = dataMysql($_POST['dataInicio']);
    $_POST['dataFim'] = dataMysql($_POST['dataFim']);
    unset($_POST['opcao']);

    if ($_POST['diaLetivo'])
        $_POST['diaLetivo'] = 1;
    else
        $_POST['diaLetivo'] = 0;

    $ret = $calendario->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if (!dcrip($_POST['codigo']))
        $_GET["codigo"] = crip($ret['RESULTADO']);
    else
        $_GET["codigo"] = $_POST['codigo'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $calendario->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
// inicializando as variáveis do formulário
$params['ano'] = $ANO;
$sqlAdicional .= " AND date_format(c.dataInicio, '%Y') = :ano ";

if (dcrip($_GET["tipo"]) != "") {
    $params['tipo'] = dcrip($_GET["tipo"]);
    $sqlAdicional .= " AND c.tipo = :tipo ";
    $tipo = dcrip($_GET["tipo"]);
}
if (dcrip($_GET["curso"]) != "") {
    $params['curso'] = dcrip($_GET["curso"]);
    $sqlAdicional .= " AND c.curso = :curso ";
    $curso = dcrip($_GET["curso"]);
}

$dataInicio = $_GET['dataInicio'];
$dataFim = $_GET['dataFim'];

if ($dataFim && $dataInicio) {
    $params['dataInicio'] = dataMysql($dataInicio);
    $params['dataFim'] = dataMysql($dataFim);
    $sqlAdicional .= " AND c.dataInicio >= :dataInicio AND c.dataFim <= :dataFim ";
} else {
    if (!empty($dataInicio)) {
        $params['dataInicio'] = dataMysql($dataInicio);
        $sqlAdicional .= " AND c.dataInicio = :dataInicio ";
    }
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => dcrip($_GET["codigo"]), 'ano' => $ANO);
    $sqlAdicional = ' AND c.codigo = :codigo';
    $res = $calendario->listCalendario($params, $sqlAdicional);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
}
?>

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
            <input type="hidden" value="<?= crip($codigo) ?>" name="codigo" id="codigo" />
            <tr>
                <td align="right">Curso: </td>
                <td>
                    <select name="curso" id="curso" value="<?= $curso ?>">
                        <option>Todos</option>
                        <?php
                        require CONTROLLER . '/curso.class.php';
                        $cursos = new Cursos();
                        foreach ($cursos->listCursos(null) as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $curso)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['curso'] . " [" . $reg['codigo'] . "]</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>            
            <tr><td  style="width: 100px" align="right">Tipo: </td>
                <td colspan="2" align="left" style="width: 100px"> 
                    <select name="tipo" id="tipo">
                        <option>Todos</option>
                        <?php
                        require CONTROLLER . '/tipo.class.php';
                        $tp = new Tipos();
                        $res = $tp->listRegistros(null, 'ORDER BY nome', null, null);
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $tipo)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>            
            <tr><td  style="width: 100px" align="right">Data: </td><td>
                    <input type="text" size="10" value="<?= $dataInicio ?>" name="dataInicio" id="dataInicio" />
                    a <input type="text" size="10" value="<?= $dataFim ?>" name="dataFim" id="dataFim" />
                </td>
            </tr>
            <tr><td align="right">Ocorr&ecirc;ncia:</td><td><input type="text" size="50" maxlength="255" name="ocorrencia" id="ocorrencia" value="<?= $ocorrencia ?>" /></td>
                <td rowspan="3"><a href="#" class='calendario'><img src="<?= VIEW ?>/css/images/horario.png" width="50%"></a></td>                
            </tr>
            <tr><td align="right"></td><td>
                    <?php if ($diaLetivo) $checked = 'checked'; ?>
                    <input type="checkbox" id="diaLetivo" name="diaLetivo" <?= $checked ?> /> Dia letivo
                </td>
            </tr>
            <tr><td></td><td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Novo/Limpar</a></td>
                        </tr></table>
                </td></tr>
        </table>
    </form>
</div>
<div id='showCalendar'>
<?php
// COPIA DE:
require PATH.VIEW.'/common/calendario.php';
?>
    
</div>
<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$res = $calendario->listCalendario($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($calendario->listCalendario($params, $sqlAdicional));

if ($params['dataFim'])
    $params['dataFim'] = dataPTBR($params['dataFim']);
if ($params['dataInicio'])
    $params['dataInicio'] = dataPTBR($params['dataInicio']);
$SITENAV = $SITE . "?" . mapURL($params);

require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th>Data</th>
        <th>Ocorr&ecirc;ncia</th>
        <th>Dia Letivo</th>
        <th>Curso</th>
        <th>Tipo</th>
        <th width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'><img class='botao' src='<?= ICONS ?>/delete.png' /></a>
        </th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        if ($reg['dataFim'] && $reg['dataFim'] != '00/00/0000')
            $reg['dataInicio'] = $reg['dataInicio'] . ' a ' . $reg['dataFim'];
        ?>
        <tr <?= $cdif ?>>
            <td><?= $reg['dataInicio'] ?></td>
            <td><?= mostraTexto($reg['ocorrencia']) ?></td>
            <td><?= $reg['diaLetivoNome'] ?></td>
            <td><?= $reg['cursoCal'] ?></td>
            <td><?= $reg['tipoCal'] ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                    <img class='botao' src='<?= ICONS ?>/config.png' /></a>
            </td></tr>
        <?php
        $i++;
    }
    ?>
</table>

<script>
    function atualizar(getLink) {
        var dataInicio = $('#dataInicio').val();
        var dataFim = $('#dataFim').val();
        var tipo = $('#tipo').val();
        var curso = $('#curso').val();
        var URLS = '<?= $SITE ?>?dataInicio=' + dataInicio + '&dataFim=' + dataFim + '&tipo=' + tipo + '&curso=' + curso;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    function valida() {
        if ($('#dataInicio').val() == "" || $('#ocorrencia').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    $(document).ready(function() {
        $('#showCalendar').hide();
        
        valida();
        $('#dataInicio, #ocorrencia').keyup(function() {
            valida();
        });

        $("#dataInicio, #dataFim").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior'
        });

        $(".item-excluir").click(function() {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function(caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function() {
                            selected.push($(this).val());
                        });
                        $('#index').load(atualizar(1) + '&opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
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

        $(".calendario").click(function() {
            $('#showCalendar').show();
        });
        
        $(".item-alterar").click(function() {
            var codigo = $(this).attr('id');
            $('#index').load(atualizar(1) + '&codigo=' + codigo);
        });

<?php if (!$_GET["codigo"]) { ?>
            $('#dataInicio, #dataFim, #curso, #tipo').change(function() {
                atualizar();
            });
<?php } ?>
    });
</script>