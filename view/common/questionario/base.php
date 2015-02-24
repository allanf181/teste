<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a visualização de questionário por alunos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1


// PAGINACAO
$itensPorPagina = 20;
$item = 1;
$ordem = '';

$res = $questionario->getAvisoQuestionarios($_SESSION['loginCodigo'], $sqlAdicional);
$totalRegistros = count($questionario->getAvisoQuestionarios());
?>	
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
                <a data-placement="top" title="Visualizar Question&aacute;rio" data-content="Clique para visulizar ou responder o question&aacute;io se aberto." href="javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioVisualiza.php?questionario=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>&preview=aluno');void(0)">
                    <img src = "<?= IMAGES . '/questionarioPreview.png' ?>" class='botao'/>
                </a>
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>