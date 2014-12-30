<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita associar uma ou mais atividades acadêmicas aos cursos.
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

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $ret = $atv->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    
    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
    
    $_GET["curso"] = $_POST['curso'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $atv->delete($_GET["codigo"]);
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
    $res = $atv->listRegistros($params1);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsCurso['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCurso = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord)";
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
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
                        foreach ($cursos->listCursos($paramsCurso, $sqlAdicionalCurso) as $reg) {
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
                <td align="right">Nome da Atividade: </td>
                <td>
                    <input type="text" maxlength="200" size="60" name="nome" id="nome" value="<?= $nome ?>"/>
                </td>
            </tr>            
            <tr>
                <td align="right">CH total do curso: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CHTotal" id="CHTotal" value="<?= $CHTotal ?>"/>
                </td>
            </tr>
            <tr>
                <td align="right">CH m&iacute;nima no semestre: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CHminSem" id="CHminSem" value="<?= $CHminSem ?>"/>
                </td>
            </tr>
            <tr>
                <td align="right">CH m&aacute;xima no semestre: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CHmaxSem" id="CHmaxSem" value="<?= $CHmaxSem ?>"/>
                </td>
            </tr>
            <tr>
                <td align="right">CH em eventos cient&iacute;ficos: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CHminCientifica" id="CHminCientifica" value="<?= $CHminCientifica ?>"/>
                </td>
            </tr>
            <tr>
                <td align="right">CH m&iacute;nima em eventos culturais: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CHminCultural" id="CHminCultural" value="<?= $CHminCultural ?>"/>
                </td>
            </tr>
            <tr>
                <td align="right">CH m&iacute;nima em eventos acad&ecirc;micos: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CHminAcademica" id="CHminAcademica" value="<?= $CHminAcademica ?>"/>
                </td>
            </tr>
            <tr><td colspan="2"><font size="1">* deixar vazio o campo que desejar desabilitar a verificação de carga hor&aacute;ria.</font></td></tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />    
                    <table width="100%">
                        <tr>
                            <td>
                                <input type="submit" value="Salvar" id="salvar" />
                            </td>
                            <td>
                                <a href="javascript:$('#index').load('<?= $SITE ?>'); void(0);">Novo/Limpar</a>
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

$params = $paramsCurso;
$sqlAdicional .= $sqlAdicionalCurso;

if ($curso) {
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';
}

if ($codigo) {
    $params['codigo'] = $codigo;
    $sqlAdicional .= ' AND aa.codigo = :codigo ';
}

$res = $atv->listAtividades($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($atv->listAtividades($params, $sqlAdicional, null, null));

$params['curso'] = crip($curso);
$SITENAV = $SITE . "?" . mapURL($params);
require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">C&oacute;digo</th>
        <th align="left">Nome</th>
        <th align="left">Curso</th>
        <th align="left">CH total do curso</th>
        <th align="left" width="100px">CH semestre [min-max]</th>
        <th align="left">CH curso</th>
        <th align="left" width="100px">CH Cient&iacute;fica / Cultural / Acad&ecirc;mica</th>
        <th align="center" width="45">&nbsp;
            <input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?= ICONS ?>/delete.png' />
            </a>
        </th>
        <th align="center" width="50"></th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
            <td><a href="#" title="<?= $reg['nome'] ?>"><?= abreviar(mostraTexto($reg['nome']), 30) ?></a></td>
            <td><a href="#" title="<?= $reg['curso'] ?>"><?= abreviar(mostraTexto($reg['curso']), 30) ?></a></td>
            <td><?= $reg['CHTotal'] ?>h</td>
            <td>[<?= $reg['CHminSem'].'h/'.$reg['CHmaxSem'] ?>h]</td>
            <td><?= $reg['CHTotal'] ?>h</td>
            <td><?= $reg['CHminCientifica'].'h/'.$reg['CHminCultural'].'h/'.$reg['CHminAcademica'] ?>h</td>
            <td align='left'>
                &nbsp;<input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
            </td>
            <td>
                <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                    <img class='botao' src='<?= ICONS ?>/config.png' />
                </a>
                <a href="#" class='item-add' title='Adicionar Itens nessa Atividade' id='<?= crip($reg['codigo']) ?>'>
                    <img class='botao' src='<?= ICONS ?>/add.png' />
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
        if ($('#curso').val() == "" || $('#CHTotal').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
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
        $('#curso, #CHTotal').change(function() {
            valida();
        });

        $(".item-add").click(function() {
            var codigo = $(this).attr('id');
            $('#index').load('<?= VIEW ?>/secretaria/cursos/atividade_academica/atvAcadItem.php?atvAcademica=' + codigo);
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

        $('#select-all').click(function() {
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