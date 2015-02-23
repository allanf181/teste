<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;

$SITE_RAIZ = end(explode('/', $_SESSION['SITE_RAIZ']));
$PHP_SELF = substr(end(explode('/', $_SERVER['PHP_SELF'])), 0, strlen(end($SITE_RAIZ)) - 4) . '.php';

if ((!$SITE_RAIZ || $SITE_RAIZ = !$PHP_SELF) && !$_SESSION['QUEST_VIEW']) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
} else {
    $SITE = 'view/common/questionario/questionarioVisualiza.php';
    $TITLE = 'Formul&aacute;rio do Question&aacute;rio';
    $TITLE_DESCRICAO = "<span class=\"help\"><a title='Sobre esse m&oacute;dulo' data-content=\"Permite responder o question&aacute;rio. Perguntas com * s&atilde;o obrigat&oacute;rias.\" href=\"#\"><img src=\"" . ICONS . "/help.png\"></a></span>";
}

require_once CONTROLLER . "/questionario.class.php";
$questionario = new Questionarios();

require_once CONTROLLER . "/questionarioQuestao.class.php";
$questoes = new QuestionariosQuestoes();

require_once CONTROLLER . "/questionarioQuestaoItem.class.php";
$questoesItens = new QuestionariosQuestoesItens();

require_once CONTROLLER . "/questionarioResposta.class.php";
$questoesRespostas = new QuestionariosRespostas();

$fechado = 0;
$paramsValida['codigo'] = dcrip($_GET['questionario']);
$res = $questionario->listQuestionarios($paramsValida);
if ( ($res[0]['dataFechamento']!='0000-00-00 00:00:00' && $res[0]['dataFechamento'] && round((strtotime($res[0]['dataFechamento']) - strtotime(date('Y-m-d') . ' 00:00:00')) / 86400) < 0) || !$res[0]['situacao']) {
    $fechado = 1;
    mensagem('NOK', 'QUESTIONARIO_CLOSE');
}

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate' && !$fechado) {
    $_GET['questionario'] = $_POST['questionario'];
    unset($_POST['questionario']);
    unset($_POST['opcao']);

    $contInseridos = 0;

    foreach ($_POST as $questao => $resposta) {
        unset($paramsRes);
        $paramsRes['pessoa'] = $_SESSION['loginCodigo'];
        $sqlAdicionalRes = ' AND p.codigo = :pessoa AND qq.codigo = :questao ';

        if (is_array($resposta)) {
            $paramsRes['questao'] = $questao;
            $res = $questoesRespostas->listRespostas($paramsRes, $sqlAdicionalRes);

            if ($res) {//se já houver respostas multiplas, apaga todas antes de inserir novamente
                foreach ($res as $resp) {
                    $ret = $questoesRespostas->delete(crip($resp['codigo']));
                }
            }

            foreach ($resposta as $check => $resCheck) {
                $paramsRes['resposta'] = $resCheck;
                $ret = $questoesRespostas->insertOrUpdate($paramsRes);
                //print_r($ret);

                if ($ret['STATUS'] == 'OK')
                    $contInseridos++;
            }
        } else {
            $paramsRes['questao'] = $questao;
            $res = $questoesRespostas->listRespostas($paramsRes, $sqlAdicionalRes);
            if ($res)
                $paramsRes['codigo'] = $res[0]['codigo'];

            $paramsRes['resposta'] = $resposta;
            $ret = $questoesRespostas->insertOrUpdate($paramsRes);

            if ($ret['STATUS'] == 'OK')
                $contInseridos++;
        }
    }
    if ($contInseridos)
        mensagem('OK', 'INSERT');
    else
        mensagem('INFO', 'UPDATE');
}

$params = array('codigo' => dcrip($_GET['questionario']));
$resQuestionario = $questionario->listRegistros($params);
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>

<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?><?= '<br>' . $resQuestionario[0]['nome'] ?>
    <?= ($resQuestionario[0]['descricao']) ? '<br>' . $resQuestionario[0]['descricao'] : ''; ?></h2>

<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE ?>?preview=<?= $_GET['preview'] ?>',
                responseDiv: '#index',
                colorOn: '#000',
                colorOff: '#999',
                messages: 'br'
            })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <?php
        $params = array('questionario' => dcrip($_GET['questionario']));
        $sqlAdicional = ' WHERE questionario = :questionario ORDER BY codigo ';
        $resQuestoes = $questoes->listRegistros($params, $sqlAdicional);
        ?>
        <table align="center" width="100%" id="form">
            <?php
            foreach ($resQuestoes as $regQuestoes) {
                if ($paramsRes)
                    unset($paramsRes);
                $paramsRes['pessoa'] = $_SESSION['loginCodigo'];
                $paramsRes['questao'] = $regQuestoes['codigo'];
                $sqlAdicionalRes = ' AND qq.codigo = :questao AND p.codigo = :pessoa ';
                $regRespostas = $questoesRespostas->listRespostas($paramsRes, $sqlAdicionalRes);

                if ($regQuestoes['obrigatorio']) {
                    if ($regRespostas)
                        $questoes->setTag($regQuestoes['categoria'], $regQuestoes['codigo'], 'remObrigatorio');
                    else
                        $questoes->setTag($regQuestoes['categoria'], $regQuestoes['codigo'], 'obrigatorio');
                } else
                    $questoes->setTag($regQuestoes['categoria'], $regQuestoes['codigo']);
                ?>
                <tr>
                    <th colspan = '2'>
                        <?php
                        if ($regQuestoes['obrigatorio'])
                            print '* ';
                        print $regQuestoes['nome'];
                        ?>
                    </th>
                </tr>
                <tr>
                    <td colspan = '2'>
                        <?php
                        print $questoes->getTagAbertura();

                        if ($regRespostas && $regQuestoes['categoria'] == '5') {//textarea
                            print $regRespostas[0]['resposta'];
                        }

                        $paramsItem['questao'] = $regQuestoes['codigo'];
                        $resItens = $questoesItens->listQuestoesItens($paramsItem);
                        $cont = 0;

                        foreach ($resItens as $regItens) {
                            if ($regQuestoes['obrigatorio']) {
                                if ($regRespostas)
                                    $questoesItens->setTag($regQuestoes['codigo'], $regQuestoes['categoria'], $regQuestoes['codigo'], $regItens['nome'], 'remObrigatorio');
                                else
                                    $questoesItens->setTag($regQuestoes['codigo'], $regQuestoes['categoria'], $regQuestoes['codigo'], $regItens['nome'], 'obrigatorio');
                            } else
                                $questoesItens->setTag($regQuestoes['codigo'], $regQuestoes['categoria'], $regQuestoes['codigo'], $regItens['nome']);

                            if (in_array($regQuestoes['categoria'], array('2', '3'))) {//checkbox e radio
                                if ($cont % 2 == 0)
                                    print '</tr><tr><td>';
                                else
                                    print '</td><td>';
                            }
                            print $questoesItens->getTagAbertura();

                            foreach ($regRespostas as $resposta) {
                                if (strtoupper($regItens['nome']) == strtoupper($resposta['resposta'])) {
                                    if (in_array($regQuestoes['categoria'], array('2', '3')))//checkbox e radio
                                        print " checked = 'checked'";
                                    else
                                        print " selected = 'selected'";
                                }
                            }

                            print $questoesItens->getTagFechamento();

                            $cont++;
                        }
                        if (in_array($regQuestoes['categoria'], array('4', '6')) || $regQuestoes['categoria'] > 6) {//texto e data e outros criados pelo administrador
                            print " value = '" . $regRespostas[0]['resposta'] . "'";
                        }
                        print $questoes->getTagFechamento();
                        ?>
                    </td>
                </tr>
                <?php
            }//fecha foreach questoes
            ?>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <input type="hidden" name="questionario" value="<?= $_GET['questionario'] ?>" />
                </td>
            </tr> 
        </table>
        <table width="100%">
            <tr>
                <td>
                    <?php if (!$fechado) {
                    ?>
                        <input type="submit" value="Salvar" id="salvar" /></td>
                    <?php
                    }
                    if (!$_GET['preview']) {
                        ?>
                    <td><a href="javascript:$('#index').load('<?= VIEW ?>/system/home.php'); void(0);">VOLTAR</a></td> 
                    <?php
                } else {
                    ?>	
                    <td><a href="javascript:$('#index').load('<?= $_SESSION['SITE_RAIZ'] ?>'); void(0);">VOLTAR</a></td> 
                    <?php
                }
                ?>
            </tr>
        </table> 
    </form>
</div>

<script>
    $(".dt").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'TerÃ§a', 'Quarta', 'Quinta', 'Sexta', 'SÃ¡bado'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'SÃ¡b', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'MarÃ§o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'PrÃ³ximo',
        prevText: 'Anterior'
    });

<?php
if ($_GET['preview'] && $_GET['preview'] != 'aluno') {
    ?>
        $('#salvar').attr('disabled', 'disabled');
    <?php
} else {
    ?>
        function valida()
        {
            retorno = true;

            $('input, textarea').each(function () {
                if ($(this).attr('class') == 'obrigatorio' || $(this).attr('class') == 'dt hasDatepicker obrigatorio')
                {
                    retorno = false;
                    return false;
                }
            });

            if (retorno)
                $('#salvar').removeAttr('disabled');
            else
                $('#salvar').attr('disabled', 'disabled');

            return retorno;
        }

        function obrigatorio(params, componente)
        {
            if (params == true)//se for obrigatorio
            {
                componente.addClass('obrigatorio');
                componente.removeClass('remObrigatorio');

            }
            else
            {
                componente.addClass('remObrigatorio');
                componente.removeClass('obrigatorio');
            }
        }

        $(document).ready(function () {
            valida();

            $('.obrigatorio, .remObrigatorio').keyup(function () {
                if ($(this).val() != "" || $(this).text() != "")
                    obrigatorio(false, $(this));
                else
                    obrigatorio(true, $(this));

                valida();
            }).focusout(function () {
                if (($(this).val() != "" || $(this).text() != "") && $(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio')
                    obrigatorio(false, $(this));
                else if (($(this).val() == "" || $(this).text() == "") && $(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio')
                    obrigatorio(true, $(this));

                valida();

            }).click(function () {
                nome = $(this).attr('name');

                $(':radio').each(function () {
                    if ($(this).attr('name') == nome)
                        obrigatorio(false, $(this));
                });


                if ($(this).attr('type') == 'checkbox' && $(this).is(':checked'))
                {
                    $(':checkbox').each(function () {
                        if ($(this).attr('name') == nome)
                            obrigatorio(false, $(this));
                    });
                }
                else if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked'))
                {
                    retorno = false;

                    $(':checkbox').each(function () {

                        if ($(this).attr('name') == nome && $(this).is(':checked'))
                            retorno = true;
                    });

                    if (retorno)
                    {
                        $(':checkbox').each(function () {
                            if ($(this).attr('name') == nome)
                                obrigatorio(false, $(this));
                        });
                    }
                    else
                    {
                        $(':checkbox').each(function () {
                            if ($(this).attr('name') == nome)
                                obrigatorio(true, $(this));
                        });
                    }
                }

                valida();
            });
        });
    <?php
}//fecha verificação de preview
?>
</script>

