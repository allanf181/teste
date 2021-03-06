<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Lista os tipos de avaliações cadastrados para as modalidades do sistema.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/tipoAvaliacao.class.php";
$tipoAval = new TiposAvaliacoes();

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $tipoAval->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
// PAGINACAO
$itensPorPagina = 50;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$res = $tipoAval->listAvaliacoesModalidades($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = $tipoAval->count();

$SITENAV = $SITE . '?';
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="1" align="center">
    <tr>
        <th align="center" width="40">#</th>
        <th align="left">Nome</th>
        <th>Tipo</th>
        <th>Modalidade</th>
        <th>Arredondar</th>
        <th>C&aacute;lculo</th>
        <th>Mostrar Avalia&ccedil;&atilde;o se</th>
        <th>Nota &Uacute;ltimo Bimestre</th>
        <th>Qde. M&iacute;nima</th>
        <th>Nota M&aacute;xima</th>
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
        <tr <?= $cdif ?>>
            <td align='left'><?= $i ?></td>
            <td><?= abreviar(mostraTexto($reg['nome']),20) ?></td>
            <td align='left'><?= $reg['tipo'] ?> <?= $reg['final'] ?></td>
            <td align='left'><?= mostraTexto($reg['modalidade']) ?></td>
            <td align='left'><?= $reg['arredondar'] ?></td>
            <td align='left'><a href="#" title="<?= $$reg['calculo'] ?>"><?= $reg['calculo'] ?></a></td>
            <td align='left'><?= $reg['notaMaior'] ?> < Nota > <?= $reg['notaMenor'] ?></td>
            <td align='left'><?= $reg['notaUltimBimestre'] ?></td>
            <td align='left'><?= $reg['qdeMinima'] ?></td>
            <td align='left'><?= $reg['notaMaxima'] ?></td>
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
    $(document).ready(function() {
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
    });
</script>