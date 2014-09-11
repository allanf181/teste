<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Visualização da alocação das salas dos respectivos professores e das disciplinas dadas nesta sala em determinado horário.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/ensalamento.class.php";
$ensalamento = new Ensalamentos();

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $ensalamento->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (dcrip($_GET["turma"])) {
    $params['turma'] = dcrip($_GET["turma"]);
    $sqlAdicional = " and t.codigo=:turma";
    $turma = dcrip($_GET["turma"]);
}
?>
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
            <input type="hidden" name="codigo" value="<?= crip($codigo); ?>" />
            <tr>
                <td align="right" style="width: 100px">Turma: </td>
                <td>
                    <select name="turma" id="turma" value="<?= $turma ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . '/turma.class.php';
                        $turmas = new Turmas();
                        $paramsTurma = array(':ano' => $ANO, ':semestre' => $SEMESTRE);
                        $res = $turmas->listTurmas($paramsTurma);
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codTurma'] == $turma)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                        }
                        ?>
                    </select>
                </td>
                <?php if ($turma) { ?>
                    <td><a href="#" class='ensalamento'><img src="<?= VIEW ?>/css/images/horario.png" width="50%"></a></td>
                <?php } ?>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%">
                        <tr>
                            <td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </form>
</div>

<div id='showEnsalamento'>
    <?php
    // COPIA DE:
    if ($turma) {
        $tipo = 'turma';
        $codigo = $turma;
        require PATH . VIEW . '/common/ensalamento.php';
    }
    ?>
</div>
<?php
// PAGINACAO
$item = 1;
$itensPorPagina = 50;

if (isset($_GET['item']))
    $item = $_GET["item"];

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;

$res = $ensalamento->listEnsalamentos($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($ensalamento->listEnsalamentos($params, $sqlAdicional));

$params['turma'] = crip($turma);

$SITENAV = $SITE . "?" . mapURL($params);

require(PATH . VIEW . '/paginacao.php');
?>	
<table id="listagem" border="0" align="center">
    <tr>
        <th align="center" width="40">#</th>
        <th>Atribui&ccedil;&atilde;o</th>
        <th>Sala</th>
        <th width="180">Hor&aacute;rio</th>
        <th align="center" width="40">
            <input type='checkbox' id="select-all" value="" />
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?php print ICONS; ?>/delete.png' />
            </a>
        </th>
    </tr>
    <?php
    $i = $item;
    $dias = diasDaSemana();
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $codigo = crip($reg['codigo']);
        $reg['diaSemana'] = $dias[$reg['diaSemana']];
        ?>
        <tr <?= $cdif ?>><td align='left'><?= $i ?></td>
            <td><?= $reg['professor'] . ' [' . $reg['discNumero'] . '][' . $reg['turma'] . ']' ?></td>
            <td><?= $reg['sala'] ?></td>
            <td><?= $reg['horario'] . ' [' . $reg['inicio'] . ' - ' . $reg['fim'] . ']' ?></td>
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
    function atualizar(getLink) {
        var turma = $('#turma').val();
        var URLS = '<?= $SITE ?>?turma=' + turma;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    document.onkeydown = function(evt) {
        evt = evt || window.event;
        if (evt.keyCode == 27) {
            $('#showEnsalamento').hide();
        }
    };

    $(document).ready(function() {
        $('#showEnsalamento').hide();

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

        $(".ensalamento").click(function() {
            $('#showEnsalamento').show();
        });

        $('#turma').change(function() {
            atualizar();
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
                        $('#index').load('<?php print $SITE; ?>?opcao=delete&codigo=' + selected + '&item=<?php print $item; ?>');
                    }
                }
            });
        });
    });
</script>