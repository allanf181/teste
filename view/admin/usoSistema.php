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
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<style>
    progress::-webkit-progress-bar { /* Estilizando barra de progresso */ } progress::-webkit-progress-value { /* Estilizando apenas valor do progresso */ }
    progress { /* Estilizando barra de progresso */ } progress::-webkit-progress-value { /* Estilizando apenas valor do progresso */ }
</style>

<?php
require CONTROLLER . "/atribuicao.class.php";
$atribuicao = new Atribuicoes();

$params = array('ano' => $ANO, 'semestre' => $SEMESTRE);

$res = $atribuicao->getDadosUsoSistema($params);
foreach ($res as $reg) {
    if ($reg['aula'] || $reg['frequencia'] || $reg['avaliacao'] || $reg['nota'])
        $uso++;
    $count++;
}
$uso = round(($uso * 100) / $count);
$width1 = "$uso";
$width2 = (100 - $uso);
?>
<div class='fundo_listagem'>
    <table class="listagem" align="center">
        <tr>
            <td>Utilizando: </td>
            <td><progress max="100" value="<?= $width1 ?>"></progress><?= $width1 ?>%</td>
        </tr>
        <tr>
            <td>N&atilde;o utilizado: </td><td><progress max="100" value="<?= $width2 ?>"></progress><?= $width2 ?>%</td>
        </tr>
    </table>
</div>
<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$res = $atribuicao->getDadosUsoSistema($params, null, $item, $itensPorPagina);
$totalRegistros = count($atribuicao->getDadosUsoSistema($params, null, $item, $itensPorPagina));

$SITENAV = $SITE . '?';
require PATH . VIEW . '/paginacao.php';
?>
<table id="listagem" border="0" align="center">
    <tr>
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