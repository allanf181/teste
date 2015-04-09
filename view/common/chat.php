<?php
// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
}

require CONTROLLER . "/chat.class.php";
$chat = new Chat();

// INSERT
if ($_POST["opcao"] == 'insertMessage') {
    unset($_POST['opcao']);
    $_POST['prontuario'] = $_SESSION['loginProntuario'];
    $_POST['data'] = date('Y-m-d H:m:i');
    $_POST['mensagem'] = xss_clean($_POST['mensagem']);
    
    if ($_POST['origem'])
        unset($_POST['atribuicao']);

    $res = $chat->insertOrUpdate($_POST);
    if ($res['STATUS'] == 'OK')
        print '<font size="1"><b>' . $_SESSION['loginNome'] . ' diz...</b></font><br>' . $_POST['mensagem'] . '<br><br>';
    die;
}

if ($_POST["opcao"] == 'loadMessages') {
    $res = $chat->getMessage($_SESSION['loginProntuario'], dcrip($_POST['atribuicao']), $_POST['para'], $_POST['first'], $_POST['origem']);
    print $res;
    die;
}

if ($_GET["opcao"] == 'alunos') {
    $res = $chat->haveMessage($_SESSION['loginProntuario'], dcrip($_GET['atribuicao']), dcrip($_GET['origem']));
    print json_encode($res);
    die;
}

require SESSAO;
?>

<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE ?>',
        responseDiv: '<?= $DIV ?>',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<div id="loaderChat">&nbsp;Inicializando o Chat... aguarde...</div>
<div id="html5form" class="main">
    <form id="form_padrao">
        <table align="center" width="100%" id="form" border="0">
            <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
            <tr style="background-color: #DEDEDE">
                <td align="left" width="400"><b>Membros</b></td>
                <td>&nbsp;</td>
                <td align="left"><div id="nomeAluno"></div></td>
            </tr>
            <tr>
                <td valign="top">
                    <?php
                    require CONTROLLER . "/professor.class.php";
                    $professor = new Professores();
                    $params = array('atribuicao' => dcrip($_GET["atribuicao"]), 'tipo' => $PROFESSOR);
                    $sqlAdicional = ' AND pr.atribuicao = :atribuicao ';
                    $k = 0;
                    if ($resProf = $professor->listProfessores($params, $sqlAdicional)) {
                        foreach ($resProf as $reg) {
                            if ($_SESSION['loginProntuario'] != $reg['prontuario']) {
                                if ($k == 0) {
                                    print '<br><b>Professor(es): </b>';
                                    $k = 1;
                                }
                                print printUser($reg['codigo'], $reg['nome'], $reg['prontuario'], 'aluno');
                            }
                        }
                    }

                    require CONTROLLER . "/aula.class.php";
                    $aulaFreq = new Aulas();
                    $params = array('atribuicao' => dcrip($_GET["atribuicao"]));
                    $sqlAdicional = ' WHERE a.codigo=:atribuicao GROUP BY al.codigo ORDER BY al.nome ';
                    $k = 0;
                    if ($resAluno = $aulaFreq->listAlunosByAula($params, $sqlAdicional)) {
                        print '<br><b>Aluno(s): </b>';
                        foreach ($resAluno as $reg) {
                            $alunos[] = $reg['prontuario'];
                            if ($_SESSION['loginProntuario'] != $reg['prontuario']) {
                                print printUser($reg['codAluno'], $reg['aluno'], $reg['prontuario'], 'aluno');
                            }
                        }
                    }

                    require CONTROLLER . "/bolsa.class.php";
                    $bolsa = new Bolsas();
                    if ($resBolsista = $bolsa->checkBolsista(dcrip($_GET['codDisciplina']), null)) {
                        foreach ($resBolsista as $reg) {
                            $bolsistas[] = $reg['prontuario'];
                            $k = 0;
                            if ($_SESSION['loginProntuario'] != $reg['prontuario'] && !in_array($reg['prontuario'], $alunos)) {
                                if ($k == 0) {
                                    print '<br><b>Bolsista(s): </b>';
                                    $k = 1;
                                }
                                print printUser($reg['codigo'], $reg['nome'], $reg['prontuario'], 'bolsista');
                            }
                        }
                    }

                    if ($resBolsa = $chat->listMessageBolsa($_SESSION['loginCodigo'], dcrip($_GET['origem']))) {
                        foreach ($resBolsa as $reg) {
                            $k = 0;
                            if ($_SESSION['loginProntuario'] != $reg['prontuario'] && !in_array($reg['prontuario'], $bolsistas)) {
                                if ($k == 0) {
                                    print '<br><b>Contatos Bolsa: </b>';
                                    $k = 1;
                                }
                                print printUser($reg['codigo'], $reg['nome'], $reg['prontuario'], 'bolsista');
                            }
                        }
                    }
                    ?>
                </td>
                <td>&nbsp;</td>
                <td width="450" valign="top">
                    <input type="hidden" name="first" id="first" value="0" />
                    <input type="hidden" name="origem" id="origem" value="" />
                    <table width="100%" border="0">
                        <tr>
                            <td colspan="2">
                                <div class="message_box" style="width: 450px; height: 300px; overflow-y: scroll;"></div> 
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <textarea rows="2" cols="40" maxlength='500' id='conteudo' name='conteudo'><?= $mensagem ?></textarea>
                            </td>
                            <td>
                                <input type="submit" value="Enviar" id="salvar" />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php

function printUser($codigo, $nome, $prontuario, $tipo) {
    $linha = '<table width="100%" border="0">
                <tr>
                    <td width="25px">
                        <a href="#" rel=' . INC . '/file.inc.php?type=pic&id=' . crip($codigo) . '&timestamp=' . time() . ' class="screenshot" title=' . $nome . '">
                            <img style="width: 25px; height: 25px" alt="Embedded Image" src=' . INC . '/file.inc.php?type=pic&id=' . crip($codigo) . '&timestamp=' . time() . ' />
                        </a>
                    </td>
                    <td>
                        <div style="cursor: pointer; cursor: hand;" class="' . $tipo . '" id="' . $prontuario . '">[' . $prontuario . '] ' . $nome . '</div>
                    </td>
                    <td>
                        <div id="T' . $prontuario . '"></div>
                    </td>
                </tr>
             </table>';
    $linha .= '';
    $linha .= "<hr>";
    return $linha;
}
?>
<script>
    var load_data = null;
    clearInterval(interval1);
    var hasMessage = 0;
    var stopNotify = 1;

    interval1 = setInterval("haveMessage();", 5000);

    function haveMessage() {
        $.ajax({
            url: '<?= $SITE ?>',
            data: {'opcao': 'alunos', 'atribuicao': '<?= $_GET['atribuicao'] ?>', 'origem': '<?= $_GET['origem'] ?>'},
            dataType: 'json',
            success: function (data)
            {
                $('#loaderChat').hide();
                for (var i in data) {
                    if (data[i].prontuario != $('#first').val()) {
                        $('#' + data[i].prontuario).html(data[i].nome);
                        $('#T' + data[i].prontuario).html(data[i].total);
                        $('#' + data[i].prontuario).css("font-weight", "bold");
                        hasMessage = 1;
                    }
                }
                if (hasMessage && stopNotify) {
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '<?= VIEW ?>/css/som/notify.wav');
                    audioElement.setAttribute('autoplay', 'autoplay');
                    stopNotify = 0;
                }
            }
        });
    }

    $("#salvar").click(function () {
        var imessage = $('#conteudo').val();
        var ipara = $('#first').val();
        var iorigem = $('#origem').val();
        if (ipara != 0 && imessage != 0) {
            var iatribuicao = '<?= $_GET['atribuicao'] ?>';
            post_data = {'opcao': 'insertMessage', 'mensagem': imessage, 'atribuicao': iatribuicao, 'para': ipara, 'origem': iorigem};
            $.post('<?= $SITE ?>', post_data, function (data) {
                var audioElement = document.createElement('audio');
                audioElement.setAttribute('src', '<?= VIEW ?>/css/som/msgSent.wav');
                audioElement.setAttribute('autoplay', 'autoplay');
                $('.message_box').append(data);
                var scrolltoh = $('.message_box')[0].scrollHeight;
                $('.message_box').scrollTop(scrolltoh);
                $('#conteudo').val('');
            }).fail(function (err) {
                alert(err.statusText);
            });
        }
        document.getElementById("conteudo").focus();

    });

    function monitor() {
        load_data.para = $('#first').val();
        load_data.first = 0;
        $.post('<?= $SITE ?>', load_data, function (data) {
            if (data && data.length > 5) {
                $(data).hide().appendTo('.message_box').fadeIn();
                var scrolltoh = $('.message_box')[0].scrollHeight;
                $('.message_box').scrollTop(scrolltoh);

                var audioElement = document.createElement('audio');
                audioElement.setAttribute('src', '<?= VIEW ?>/css/som/notify.wav');
                audioElement.setAttribute('autoplay', 'autoplay');
            }
        });
    }

    $(document).ready(function () {
        $(".aluno").click(function () {
            var ipara = $(this).attr('id');
            loadMessages(ipara, '', '<?= $_GET['atribuicao'] ?>');
        });

        $(".bolsista").click(function () {
            var ipara = $(this).attr('id');
            $('#origem').val('bolsista');
            loadMessages(ipara, 'bolsista', '');
        });
    });

    function loadMessages(ipara, origem, atribuicao) {
        $('.message_box').html('');
        $('#first').val(ipara);
        $('#' + ipara).css("font-weight", "normal");
        $('#T' + ipara).html('');
        $('#nomeAluno').html($('#' + ipara).html());
        $('#nomeAluno').css("font-weight", "bold");
        load_data = {'opcao': 'loadMessages', 'atribuicao': atribuicao, 'para': ipara, 'first': 1, 'origem': origem};
        $.post('<?= $SITE ?>', load_data, function (data) {
            if (data) {
                $(data).hide().appendTo('.message_box').fadeIn();
                var scrolltoh = $('.message_box')[0].scrollHeight;
                $('.message_box').scrollTop(scrolltoh);
            }
        });

        clearInterval(interval);
        interval = setInterval("monitor();", 5000);

        $('#imageChat').html("<img style='width: 70px' src='<?= INC ?>/file.inc.php?type=chat&atribuicao=<?= $_GET['atribuicao'] ?>' />");

    }
</script>