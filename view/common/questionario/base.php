<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a visualização de questionário por alunos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

//DEFININDO OS LINKS E O INDEX
if (!$_GET['index'])
    $_GET['index'] = 'index';
$BASE = '?atribuicao='.$_GET['atribuicao'].'&index='.$_GET['index'];
$SITE .= $BASE;

require_once CONTROLLER . "/questionario.class.php";
$questionario = new Questionarios();

// PAGINACAO
$itensPorPagina = 20;
$item = 1;
$ordem = '';

if (dcrip($_GET['atribuicao'])) {
    $res = $questionario->getQuestionariosAtribuicao($_SESSION['loginCodigo'], dcrip($_GET['atribuicao']));
} else {
    $res = $questionario->getAvisoQuestionarios($_SESSION['loginCodigo']);
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
	
<br /><table id="listagem" border="0" align="center" cellpadding = "5px">
    <tr><th align="left" width="40">#</th>
        <th>Criado em</th>
        <th>Nome</th>
        <th>Descri&ccedil;&atilde;o</th>
        <th>Fechamento</th>
        <th>Valor Total</th>
        <th width="150">A&ccedil;&otilde;es</th>

        <?php
        $i = $item;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>><td align='left'><?= $i ?></td>
            <td><?= $reg['dataCriacao'] ?></td>
            <td><a href="#" data-placement="top" title="Nome" data-content="<?= $reg['nome'] ?>"><?= abreviar($reg['nome'], 20) ?></a></td>
            <td><a href="#" data-placement="top" title="Descri&ccedil;&atilde;o" data-content="<?= $reg['descricao'] ?>"><?= abreviar($reg['descricao'], 20) ?></a></td>
            <td><?= $reg['dataFechamento'] ?></td>
            <td><?= $reg['valorTotal'] ?></td>
            <td>
                <a data-placement="top" title="Visualizar Question&aacute;rio" data-content="Clique para visualizar ou responder o question&aacute;rio se aberto." href="javascript:$('#<?= $_GET['index'] ?>').load('<?= VIEW ?>/common/questionario/questionarioVisualiza.php<?= $BASE ?>&questionario=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>&preview=aluno&base=<?=$_GET['base']?>');void(0)">
                    <img src = "<?= IMAGES . '/questionarioPreview.png' ?>" class='botao'/>
                </a>
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>