<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a inserção de avisos para uma pessoa, disciplina, turma, curso ou para todos do sistema.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;

if ($_GET['dados']) {
    require CONTROLLER . "/turma.class.php";
    $turma = new Turmas();

    require CONTROLLER . "/curso.class.php";
    $curso = new Cursos();
    
    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new Pessoas();

    $arr = array();

    foreach($pessoa->listPessoasToJSON($_GET["q"]) as $reg)
        $arr[] = $reg;

    foreach($curso->listCursosToJSON($_GET["q"], $ANO, $SEMESTRE) as $reg)
        $arr[] = $reg;    

    foreach($turma->listTurmasToJSON($_GET["q"], $ANO, $SEMESTRE) as $reg)
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

require CONTROLLER . "/aviso.class.php";
$aviso = new Avisos();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    unset($_POST['opcao']);

    $_POST['pessoa'] = crip($_SESSION['loginCodigo']);
    $ret = $aviso->insertOrUpdateAvisos($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if ($_POST['codigo'])
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $aviso->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => dcrip($_GET["codigo"]));
    $res = $aviso->listRegistros($params);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
    $params = null;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<script type="text/javascript" src="<?= VIEW ?>/js/AutocompleteList/src/jquery.tokeninput.js"></script>
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="<?= VIEW ?>/js/AutocompleteList/styles/token-input-facebook.css" type="text/css" />

<script type="text/javascript">
    $(document).ready(function() {
        $("#to").tokenInput("<?= VIEW ?>/secretaria/aviso.php?dados=1", {
            theme: "facebook"
        });
    });
</script>

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
            <input type="hidden" name="codigo" value="<?php echo crip($codigo); ?>" />
            <tr><td align="right">Para: </td><td><input type="text" id="to" name="to" /></td></tr>
            <tr><td></td><td align="left"><font size='1'>Digite a pessoa, curso ou turma para enviar a mensagem. Deixe em branco para enviar para todos.</font></td></tr>
            <tr><td align="right" style="width: 120px">Aviso: </td> 
                <td><textarea rows="5" cols="60" maxlength='500' id='conteudo' name='conteudo'><?php print $conteudo; ?></textarea></tr>
            <tr><td></td><td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><a href="javascript:$('#index').load('<?= $SITE ?>'); void(0);">Novo/Limpar</a></td> 
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
$res = $aviso->listAvisos($params, $item, $itensPorPagina);

$totalRegistros = count($aviso->listAvisos($params));
$SITENAV = $SITE . '?';
require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr><th align="left" width="40">#</th><th>Data</th><th>Aviso</th><th>Para</th><th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value=""><a href="#" class='item-excluir'><img class='botao' src='<?php print ICONS; ?>/delete.png' /></a></th></tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $para = '';
        if ($reg['curso'])
            $para = $reg['curso'];
        if ($reg['turma'])
            $para = $reg['turma'];
        if ($reg['destinatario'])
            $para = $reg['destinatario'];

        if (!$para)
            $para = 'Todos';
        ?>
        <tr <?= $cdif ?>><td align='left'><?= $i ?></td>
            <td><?= $reg['data'] ?></td>
            <td><?= mostraTexto($reg['conteudo']) ?></td>
            <td><?= mostraTexto($para) ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?=crip($reg['codigo'])?>' />
            </td>
        </tr>
        <?php
        $i++;
    }
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

                            $('#index').load('<?php print $SITE; ?>?opcao=delete&codigo=' + selected + '&item=<?php print $item; ?>');
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