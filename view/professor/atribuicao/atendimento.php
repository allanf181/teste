<?php
//Esse arquivo é fixo para o professor.
//Permite que o professor cadastre o seu horário de atendimento ao aluno.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;
require MENSAGENS;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atendimento.class.php";
$atendimento = new Atendimento();

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['ano'] = $ANO;
    $_POST['semestre'] = $SEMESTRE;
    $_POST['pessoa'] = $_SESSION['loginCodigo'];

    unset($_POST['opcao']);

    $ret = $atendimento->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

$sqlAdicional = ' WHERE pessoa = :pessoa AND ano = :ano AND semestre = :semestre ';
$params = array('pessoa' => $_SESSION['loginCodigo'], 'ano' => $ANO, 'semestre' => $SEMESTRE);
$res = $atendimento->listRegistros($params, $sqlAdicional, null, null);
extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE; ?>',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">     
        <table align="center" width="100%" id="form" border="0">
            <tr>
                <td align="left">Digite o horário de atendimento, referente ao <?= $SEMESTRE.'º semestre de '.$ANO?>, que será disponibilizado para o aluno: </td>
            </tr>
            <tr>
                <td>
                    <textarea maxlength='2000' cols='100' rows='5' id='horario' name='horario'><?= $horario ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="hidden" value="<?= $codigo ?>" name="codigo" />
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%">
                        <tr>
                            <td>
                                <input type="submit" value="Salvar" id="salvar" />
                            </td>
                        </tr>
                    </table>
                </td></tr>
        </table>
    </form>
</div>

<script>
    $(document).ready(function () {
        $('#horario').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 2000, // Characters limit   
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