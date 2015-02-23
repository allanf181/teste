<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Menu que aparecerá em todas as telas da enquete/questionario.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0
?>

<?php
if (!$_GET["questionario"]) {
    ?>
    <a data-placement="top" title="Quest&otilde;es" data-content="Clique aqui para ver e adicionar quest&otilde;es." class = 'nav questionario_item' href = "javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioQuestao.php?questionario=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>');void(0)">
        <img src = "<?= IMAGES . '/questionariosQuestoes.png' ?>" class = 'botao' />
    </a>
    <a data-placement="top" title="Resultados" data-content="Clique aqui para ver as questões respondidas." class = 'nav questionario_item' href = "javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioResultado.php?questionario=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>');void(0)">
        <img src = "<?= IMAGES . '/questionariosResultados.png' ?>" class = 'botao' />
    </a>
    <a data-placement="top" title="Question&aacute;rio <?= (!$reg['situacao']) ? 'Desativado':'Ativado' ?>" data-content="Clique para <?= (!$reg['situacao']) ? 'ativar':'desativar' ?> o question&aacute;rio." class = 'nav questionario_item' href = "javascript:$('#index').load('<?= $_SESSION['SITE_RAIZ'] ?>?questionario=<?= crip($reg['codigo']) ?>&questionarioNome=<?= crip($reg['nome']) ?>&situacao=<?= crip($reg['situacao']) ?>&opcao=mudarSituacao');void(0)">
        <img src = "<?= ($reg['situacao']) ? IMAGES . '/questionarioStart.png':IMAGES . '/questionarioStop.png'?>" class = 'botao' />
    </a>
    <?php
    if (!$reg['situacao']) {
        ?>
        <a data-placement="top" title="Alterar Question&aacute;rio" class = 'nav questionario_item' href = "javascript:$('#index').load('<?= $_SESSION['SITE_RAIZ'] ?>?codigo=<?= crip($reg['codigo']) ?>');void(0)">
            <img src = "<?= ICONS . '/config.png' ?>" class = 'botao' />
        </a>
        <?php
    }
} else {
    ?>
    <table border = '0' width = '100%' id="form">
        <tr align = 'center' valign = 'top'>
            <td width = '33%' valign = 'top'>
                <a class = 'nav questionario_item' href = "javascript:$('#index').load('<?= $_SESSION['SITE_RAIZ'] ?>');void(0)">
                    <img width = '48' src = "<?= IMAGES . '/questionario.png' ?>" title = 'Question&aacute;rios' class = 'menuQuestionario'/>
                    <br />Question&aacute;rios
                </a>
            </td>
            <td width = '33%' valign = 'top'>
                <a class = 'nav questionario_item' href = "javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioQuestao.php?questionario=<?= $_GET['questionario'] ?>&questionarioNome=<?= $_GET['questionarioNome'] ?>');void(0)">
                    <img width = '48' src = "<?= IMAGES . '/questionariosQuestoes.png' ?>" title = 'Quest&otilde;es' class = 'menuQuestionario' />
                    <br />Quest&otilde;es
                </a>
            </td>
            <td width = '33%' valign = 'top'>
                <a class = 'nav questionario_item' href = "javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioResultado.php?questionario=<?= $_GET['questionario'] ?>&questionarioNome=<?= $_GET['questionarioNome'] ?>');void(0)">
                    <img width = '48' src = "<?= IMAGES . '/questionariosResultados.png' ?>" title = 'Resultados e Estat&iacute;sticas' class = 'menuQuestionario' />
                    <br />Resultados e Estat&iacute;sticas
                </a>
            </td>
        </tr>
    </table>
    <?php
}
?>
