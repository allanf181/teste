<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Visualização de Registros de acessos ao sistema e importação de dados.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php

require CONTROLLER . "/log.class.php";
$log = new Logs();

if ($_GET["data"]) {
    $data = $_GET["data"];
    $params['data'] = dataMysql($data);
    $sqlAdicional .= " AND STR_TO_DATE( l.data, '%Y-%m-%d' ) = :data ";
}

if ($_GET["filtro"]) {
    $filtro = $_GET["filtro"];
    $params['filtro'] = '%'.$filtro.'%';
    $sqlAdicional .= " AND ((l.url LIKE :filtro) OR (p.nome LIKE :filtro)) ";
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
            <tr>
                <td align="right" style="width: 100px">Data: </td>
                <td>
                    <input value="<?php echo $data; ?>" type="text" name="data" id="data" onChange="$('#index').load('<?php print $SITE; ?>?filtro=<?php echo $filtro; ?>&data=' + this.value);">
                </td>
            </tr>
            <tr>
                <td align="right">Filtro: </td>
                <td>
                    <input value="<?php echo $filtro; ?>" type="text" value="<?php echo $filtro; ?>" name=filtro" id=filtro" onblur="$('#index').load('<?php print $SITE; ?>?data=<?php echo $data; ?>&filtro=' + encodeURIComponent(this.value));" />
                    <a href="#" title="Buscar"><img class="botao" style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' id="atualizaData" /></a>
                    &nbsp;&nbsp;<a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a>
                </td>
            </tr>
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

$sqlAdicional .= ' ORDER BY data DESC ';

$res = $log->listLogs($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($log->listLogs($params, $sqlAdicional));

if ($params['data'])
    $params['data'] = dataPTBR($params['data']);

$params['filtro'] = urlencode($filtro);

$SITENAV = $SITE . "?" . mapURL($params);

require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="center">URL</th>
        <th width="100">Data</th>
        <th width="100">Origem</th>
        <th width="100">Pessoa</th>
    </tr>
            <?php
            $i = 0;
            foreach($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?=$cdif?>>
                    <td align='left'><?= utf8_decode(str_replace("##", "<br>", $reg['url'])) ?></td>
                    <td align='left'><?= $reg['data'] ?></td>
                    <td align='left'><?= $reg['origem'] ?></td>
                    <td align='left'><?= $reg['pessoa'] ?></td>
                </tr>
                <?php
                $i++;
            }
            ?>
</table>

<script>
    $(document).ready(function() {
        $("#data").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior'
        });
    });
</script>