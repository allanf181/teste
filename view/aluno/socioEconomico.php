<?php
//Esse arquivo é fixo para o aluno.
//Permite responder o questionário socioeconômico.
//Link visível no menu: PADRÃO SIM.
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
$codigo = $_SESSION["loginCodigo"];

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['codigo'] = $codigo;
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    unset($_POST['opcao']);

    $ret = $pessoa->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

// LISTAGEM
if ($codigo) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => $codigo);
    $res = $pessoa->listRegistros($params);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
}
?>

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
        <?php
        //COPIA DE:
        require PATH . VIEW . '/common/socioEconomico.php';
        ?>
    </form>
</div>
