<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe um quadro estatístico de acessos e ações realizadas no sistema pelos docentes dos Campus.
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
<script src="<?= VIEW ?>/js/highcharts/highcharts.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/highcharts/exporting.js" type="text/javascript"></script>

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
require CONTROLLER . "/atribuicao.class.php";
$atribuicao = new Atribuicoes();

$params = array('ano' => $ANO, 'semestre' => $SEMESTRE);

$res = $atribuicao->getDadosUsoSistema($params);
?>
<div id="container" style="position: static; min-width: 310px; height: 300px; margin: 0 auto"></div>
<script>
$(function () {
    $('#container').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 1,//null,
            plotShadow: false
        },
        title: {
            text: 'Utilização do Sistema'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Percentual',
            data: [
                {
                    name: 'Utilizado',
                    y: <?= $res[0]['uso'] ?>,
                    sliced: true,
                    selected: true
                },
                ['Não utilizado', <?= (100 - $res[0]['uso']) ?>]
            ]
        }]
    });
});
</script>
<?php
// PAGINACAO
$itensPorPagina = 60;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$res = $atribuicao->getDadosUsoSistema($params, null, $item, $itensPorPagina);
$totalRegistros = count($atribuicao->getDadosUsoSistema($params, null, null, null));

$SITENAV = $SITE . '?';
require PATH . VIEW . '/paginacao.php';
?>
<table id="listagem" border="0" align="center">
    <tr>
        <th width='20'>#</th>
        <th width='220'>Nome</th>
        <th align='center' width='80'>Aulas Lan&ccedil;adas</th>
        <th align='center' width='90'>Frequ&ecirc;ncias Cadastradas</th>
        <th align='center' width='80'>Avalia&ccedil;&otilde;es</th>
        <th align='center' width='30'>Notas Lan&ccedil;adas</th>
        <th align='center' width='70'>&Uacute;ltimo Registro de Aula</th>
    </tr>
    <?php
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>>
            <td><?= $i ?></td>
            <td><?= $reg['nome'] ?></td>
            <td><?= $reg['aula'] ?></td>
            <td align='center'><?= $reg['frequencia'] ?></td>
            <td align='center'><?= $reg['avaliacao'] ?></td>
            <td align='center'><?= $reg['nota'] ?></td>
            <td align='center'><?= $reg['ultAula'] ?></td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>