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
    $res = $chat->insertOrUpdate($_POST);
    if ($res['STATUS'] == 'OK')
        print '<font size="1"><b>' . $_SESSION['loginNome'] . ' diz...</b></font><br>' . $_POST['mensagem'] . '<br><br>';
    die;
}

if ($_POST["opcao"] == 'loadMessages') {
    $res = $chat->getMessage($_SESSION['loginProntuario'], dcrip($_POST['atribuicao']), $_POST['para'], $_POST['first']);
    print $res;
    die;
}

if ($_GET["opcao"] == 'alunos') {
    $res = $chat->haveMessage($_SESSION['loginProntuario'], dcrip($_GET['atribuicao']));
    print json_encode($res);
    die;
}

require SESSAO;
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

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
                    foreach ($professor->listProfessores($params, $sqlAdicional) as $reg) {
                        if ($_SESSION['loginProntuario'] != $reg['prontuario']) {
                            print '<b>Professor: </b><div style="cursor: pointer; cursor: hand;" class="aluno" id="' . $reg["prontuario"] . '">
                                [' . $reg["prontuario"] . '] ' . $reg["nome"] . '
                               </div>
                               ';
                            print '<p id="T' . $reg["prontuario"] . '"></p>';
                            print "<hr>";
                        }
                    }

                    print '<b>Alunos: </b>';
                    require CONTROLLER . "/aula.class.php";
                    $aulaFreq = new Aulas();
                    $params = array('atribuicao' => dcrip($_GET["atribuicao"]));
                    $sqlAdicional = ' WHERE a.codigo=:atribuicao GROUP BY al.codigo ORDER BY al.nome ';
                    foreach ($aulaFreq->listAlunosByAula($params, $sqlAdicional) as $reg) {
                        if ($_SESSION['loginProntuario'] != $reg['prontuario']) {
                            print '<div style="cursor: pointer; cursor: hand;" class="aluno" id="' . $reg["prontuario"] . '">
                                [' . $reg["prontuario"] . '] ' . $reg["aluno"] . '
                               </div>
                               ';
                            print '<p id="T' . $reg["prontuario"] . '"></p>';
                            print "<hr>";
                        }
                    }
                    ?>
                </td>
                <td>&nbsp;</td>
                <td width="450">
                    <div class="message_box" style="width: 450px; height: 300px; overflow-y: scroll;"></div> 
                    <textarea rows="2" cols="30" maxlength='500' id='conteudo' name='conteudo'><?= $mensagem ?></textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>&nbsp;</td>
                <td>
                    <input type="hidden" name="first" id="first" value="0" />
                    <table width="100%">
                        <tr>
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

<script>
    var load_data = null;
    clearInterval(interval1);
    var hasMessage = 0;
    var stopNotify = 1;

    interval1 = setInterval("haveMessage();", 5000);

    function haveMessage() {
        $.ajax({
            url: '<?= $SITE ?>',
            data: {'opcao': 'alunos', 'atribuicao': '<?= $_GET['atribuicao'] ?>'},
            dataType: 'json',
            success: function (data)
            {
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
        if (ipara != 0 && imessage != 0) {
            var iatribuicao = '<?= $_GET['atribuicao'] ?>';
            post_data = {'opcao': 'insertMessage', 'mensagem': imessage, 'atribuicao': iatribuicao, 'para': ipara};
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
    });

    function monitor() {
        load_data.para = $('#first').val();
        load_data.first = 0;
        $.post('<?= $SITE ?>', load_data, function (data) {
            if (data) {
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
            $('.message_box').html('');
            var iatribuicao = '<?= $_GET['atribuicao'] ?>';
            var ipara = $(this).attr('id');
            $('#first').val(ipara);
            $('#' + ipara).css("font-weight", "normal");
            $('#T' + ipara).html('');
            $('#nomeAluno').html($('#' + ipara).html());
            $('#nomeAluno').css("font-weight", "bold");
            load_data = {'opcao': 'loadMessages', 'atribuicao': iatribuicao, 'para': ipara, 'first': 1};
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

        });
    });
</script>