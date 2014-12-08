<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe uma lista contendo todos os possíveis status dos discentes relativos à sua situação na disciplina ou curso que ele frequenta.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/situacao.class.php";
$situacoes = new Situacoes();
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<div id="sitelist">
    <?php
// PAGINACAO
    $itensPorPagina = 20;
    $item = 1;

    if (isset($_GET['item']))
        $item = $_GET["item"];

    $res = $situacoes->listRegistros($params, null, $item, $itensPorPagina);

    $totalRegistros = $situacoes->count();
    $SITENAV = $SITE . '?';
    require PATH . VIEW . '/paginacao.php';
    ?>

    <table id="listagem" border="0" align="center">
        <tr>
            <th align="center" width="40">#</th>
            <th align="left">Situação</th>
            <th>Sigla</th>
        </tr>
        <?php
        // efetuando a consulta para listagem
        $i = $item;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            $codigo = crip($reg['codigo']);
            ?>
            <tr <?= $cdif ?>>
                <td align='center'><?= $i ?></td>
                <td><?= $reg['nome'] ?></td>
                <td><?= $reg['sigla'] ?></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
</div>