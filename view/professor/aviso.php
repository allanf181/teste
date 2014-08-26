<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de avisos para os alunos.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/aviso.class.php";
$aviso = new Avisos();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    unset($_POST['opcao']);
    $_POST["pessoa"] = $_SESSION['loginCodigo'];
    $_POST["turma"] = 0;
    $_POST["curso"] = 0;       
    $_POST["destinatario"] = (dcrip($_POST["destinatario"]) == 'Todos') ? '' : $_POST["destinatario"];
    $ret = $aviso->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['atribuicao'] = $_POST["atribuicao"];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $aviso->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?php print $SITE; ?>',
        responseDiv: '#professor',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <table align="center" width="100%" id="form"> <input type="hidden" name="codigo" value="<?php echo $codigo; ?>" /> 
            <tr><td align="right">Aluno: </td><td><select name="destinatario" id="destinatario" style="width: 350px"> 
                        <?php
                        require CONTROLLER . "/aluno.class.php";
                        $aluno = new Alunos();
                        $selected = "";
                        $res = $aluno->getAlunosFromAtribuicao(dcrip($_GET["atribuicao"]));
                        print "<option value='" . crip("Todos") . "'>Todos da Turma</option>";
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $tipo)
                                $selected = "selected";
                            print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td></tr>
            <tr><td align="right" style="width: 120px">Aviso: </td> 
                <td><textarea rows="5" cols="60" maxlength='500' id='conteudo' name='conteudo'><?php print $conteudo; ?></textarea></tr>
            <tr><td></td><td>
                    <input type="hidden" name="atribuicao" value="<?php echo $_GET['atribuicao']; ?>" /> 
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><a href="javascript:$('#professor').load('<?php print $SITE . "?atribuicao=" . $_GET['atribuicao']; ?>'); void(0);">Novo/Limpar</a></td> 
                        </tr></table> 
                </td></tr> 
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

$params['pessoa'] = $_SESSION['loginCodigo'];
$params['atribuicao'] = dcrip($_GET['atribuicao']);

$res = $aviso->listAvisos($params, $item, $itensPorPagina);

$totalRegistros = count($aviso->listRegistros($params, $item, $itensPorPagina));
$SITENAV = $SITE . '?atribuicao='.$_GET['atribuicao'];
$DIV_SITE = '#professor';
require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr><th align="left" width="40">#</th>
        <th>Data</th><th>Aviso</th>
        <th>Para</th>
        <th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'><img class='botao' src='<?php print ICONS; ?>/delete.png' /></a>
        </th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $codigo = crip($reg['codigo']);
        ?>
        <tr <?php print $cdif; ?>><td align='center'><?php print $i; ?></td>
            <td><?php print $reg['data']; ?>
            </td><td><a href='#' title='<?= $reg['conteudo'] ?>'><?= abreviar($reg['conteudo'], 50) ?></a></td>
            </td><td><?= ($reg['destinatario']) ? $reg['destinatario'] : 'Todos' ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= $codigo ?>' />
                <a href='#' title='Alterar' class='item-alterar' id='<?= $codigo ?>'><img class='botao' src='<?php print ICONS; ?>/config.png' /></a>
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>
<br />

<?php
$atribuicao = $_GET["atribuicao"];
?>
<script>
    function valida() {
        if ($('#conteudo').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
        }
    }

    $(document).ready(function() {
        valida();

        $('#conteudo').keyup(function() {
            valida();
        });

        $(".item-excluir").click(function() {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?', {
                'type': 'question',
                'title': '<?php print $TITLE; ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function(caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function() {
                            selected.push($(this).val());
                        });
                        $('#professor').load('<?php print $SITE . "?atribuicao=$atribuicao"; ?>&opcao=delete&codigo=' + selected + '&item=<?php print $item; ?>');
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
    });
</script>