<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita associar um ou mais coordenadores a um ou mais cursos.
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
$prof = new Professores();

require CONTROLLER . "/coordenador.class.php";
$coord = new Coordenadores();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $ret = $coord->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
    
    $_GET["curso"] = $_POST['curso'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $coord->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params1 = array('codigo' => dcrip($_GET["codigo"]));
    $res = $coord->listRegistros($params1);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
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
                <td align="right">Curso: </td>
                <td>
                    <select name="curso" id="curso" value="<?= $curso ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . '/curso.class.php';
                        $cursos = new Cursos();
                        foreach ($cursos->listCursos(null) as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $curso)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>[" . $reg['codigo'] . "] " . $reg['curso'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Coordenador: </td>
                <td>
                    <select name="coordenador" id="coordenador" value="<?= $coordenador ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . '/pessoa.class.php';
                        $pessoa = new Pessoas();
                        $sqlAdicionalCoord = ' AND pt.tipo = :coord ';
                        $paramsCoord = array('coord' => $COORD);
                        $resCoord = $pessoa->listPessoasTipos($paramsCoord, $sqlAdicionalCoord, null, null);
                        foreach ($resCoord as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $coordenador)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        if (!$resCoord) {
                            ?>
                            <option selected>Nenhuma pessoa cont&eacute;m o tipo Coordenador</option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Área: </td>
                <td>
                    <select name="area" id="area" value="<?= $area ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . "/area.class.php";
                        $areas = new Areas();
                        foreach ($areas->listRegistros() as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $area)
                                $selected = "selected";
                            print "<option $selected value='" . ($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
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

if ($params['curso'] = $curso)
    $sqlAdicional = ' AND c.codigo = :curso ';

$res = $coord->listCoordenadores($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($coord->listCoordenadores($params, $sqlAdicional, null, null));

$SITENAV = $SITE . "?" . mapURL($params);
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">C&oacute;digo</th>
        <th align="left">Curso</th>
        <th align="left">Coordenador</th>
        <th align="left">Área</th>
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
        <tr <?= $cdif ?>><td align='center'><?= $reg['codCurso'] ?></td>
            <td><?= mostraTexto($reg['curso']) ?></td>
            <td><?= mostraTexto($reg['coordenador']) ?></td>
            <td><?= mostraTexto($reg['area']) ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
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
        if ($('#curso').val() == "" || $('#coordenador').val() == "" || $('#area').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    function atualiza() {
        curso = $('#curso').val();
        $('#index').load('<?= $SITE ?>?curso=' + curso);
    }

    $('#curso').change(function() {
        atualiza();
    });

    $(document).ready(function() {
        valida();
        $('#curso, #coordenador, #area').change(function() {
            valida();
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
                        $('#index').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
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

        $(".item-alterar").click(function() {
            var codigo = $(this).attr('id');
            $('#index').load('<?= $SITE ?>?codigo=' + codigo);
        });
    });
</script>