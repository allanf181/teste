<?php
//Esse arquivo é fixo para o professor.
//Permite a impressão do diário e inserção de competências e observações.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;
require MENSAGENS;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_GET['atribuicao'] = $_POST['atribuicao'];

    $_POST['codigo'] = $_POST['atribuicao'];
    unset($_POST['atribuicao']);
    unset($_POST['opcao']);

    $ret = $att->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

$res = $att->getAtribuicao(dcrip($_GET['atribuicao']), $LIMITE_DIARIO_PROF);
extract(array_map("htmlspecialchars", $res), EXTR_OVERWRITE);

?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<br />
<a class='nav professores_item' id='diario' target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/diario.php?atribuicao=<?= $_GET['atribuicao'] ?>'>
    <img src='<?=ICONS?>/icon-printer.gif'><h3>Clique aqui para imprimir o di&aacute;rio.</h3>
</a>
<br />
<?php if (!$status) {
    ?>
    <font size='3' color='red'>Aten&ccedil;&atilde;o, finalizar o di&aacute;rio antes de imprimir a vers&atilde;o final.
    <br />Somente ap&oacute;s a finaliza&ccedil;&atilde;o, a marca d'&aacute;gua deixar&aacute; de ser impressa.</font><br /><br />
    <?php
}
?>
<p style="margin-bottom: 10px; margin-top: 10px;">Professor, utilize os campos abaixo para inserir as compet&ecirc;ncias e observa&ccedil;&otilde;es no di&aacute;rio.</p>
<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE; ?>',
        responseDiv: '#professor',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <h2>Competências Desenvolvidas:</h2>
        <div class='fundo_listagem'>
            <textarea <?php if($_SESSION['dataExpirou']) print 'disabled'; ?> style="min-width: 930px" maxlength='1500' id='competencias' name='competencias'><?= $competencias ?></textarea>
        </div>
        <h2>Observações a serem incluídas no diário da disciplina:</h2>
        <div class='fundo_listagem'>
            <textarea <?php if($_SESSION['dataExpirou']) print 'disabled'; ?> style="min-width: 930px" maxlength='1500' id='observacoes' name='observacoes'><?= $observacoes ?></textarea>
        </div>
        <input type='hidden' value='<?= $_GET['atribuicao'] ?>' name='atribuicao' id='atribuicao' />
        <input type='hidden' name='opcao' value='InsertOrUpdate' />
        <p style="text-align: center; margin-top: 10px;"><input id='professores_botao' <?php if($_SESSION['dataExpirou']) print 'disabled'; ?> type='submit' value='Salvar' /></p>
    </form>
</div>

<script>

    $(document).ready(function() {
        $('#competencias, #observacoes').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 1500, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
        });
    });

</script>