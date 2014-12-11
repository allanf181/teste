<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita a visualização da atribuição das disciplinas aos seus respectivos docentes e o código das turmas dessas disciplinas.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/professor.class.php";
$professor = new Professores();

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $professor->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
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
        <table align="center" align="left" id="form" width="100%" >
            <tr>
                <td align="right" style="width: 100px">Turma: </td>
                <td>
                    <select name="turma" id="turma" value="<?= $turma ?>" style="width: 650px">
                        <option></option>
                        <?php
                        require CONTROLLER . '/turma.class.php';
                        $turmas = new Turmas();
                        $paramsTurma = array(':ano' => $ANO, ':semestre' => $SEMESTRE);
                        foreach ($turmas->listTurmas($paramsTurma) as $reg) {
                            $selected = "";
                            if ($reg['codTurma'] == $turma)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td>&nbsp;</td>
                            <td align="right">
                                <a href="javascript:$('#index').load('<?= $SITE ?>'); void(0);">Limpar</a>
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

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;

$res = $professor->getProfessoresByTurma($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($professor->getProfessoresByTurma($params, $sqlAdicional, null, null));

$params['turma'] = crip($params['turma']);

$SITENAV = $SITE . '?' . mapURL($params);
require PATH . VIEW . '/paginacao.php';
?>
<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="300" colspan="2">Professor</th>
        <th align="left">Disciplina</th>
        <th>Turma</th>
        <th align="center" width="50">&nbsp;&nbsp;
            <input type="checkbox" id="select-all" value="">
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
        ?>
        <tr <?= $cdif ?>>
            <td width="25" align='left'>
                <a href='#' rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codProfessor']) ?>&timestamp=<?= time() ?>' class='screenshot' title='<?= mostraTexto($reg['professor']) ?>'>
                    <img style='width: 25px; height: 25px' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codProfessor']) ?>&timestamp=<?= time() ?>' />
                </a>
            </td>
            <td align='left'>
                <?= $reg['professor'] ?>
            </td>
            <td><?= $reg['disciplina'] ?><?= $reg['bimestre'] ?><?= $reg['subturma'] ?></td>
            <td align=left><?= $reg['turma'] ?></td>
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

    $(document).ready(function () {
        $(".item-excluir").click(function () {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?php print $TITLE; ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function () {
                            selected.push($(this).val());
                        });

                        $('#index').load(atualizar(1) + '&opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
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


        $('#turma').change(function () {
            atualizar();
        });
    });
</script>