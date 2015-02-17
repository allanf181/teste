<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite trocar a foto no sistema.
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

if ($tmp = $_GET['foto']) {
    $fp = fopen($tmp, "rb");
    $conteudo = fread($fp, filesize($tmp));
    fclose($fp);

    header('Content-type: image/png');
    echo $conteudo;
    die;
}

require '../inc/config.inc.php';

require VARIAVEIS;
require FUNCOES;

// PARA NAO ACESSAR DIRETAMENTE...
if (strpos($_SERVER["HTTP_REFERER"], "/$LOCATION/") == false) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}
?>
<link rel="stylesheet" type="text/css" href="<?= VIEW ?>/js/croppic/imgareaselect-default.css" />
<script type="text/javascript" src="<?= VIEW ?>/js/croppic/jquery.min.js"></script>
<script type="text/javascript" src="<?= VIEW ?>/js/croppic/jquery.imgareaselect.pack.js"></script>
<?php
$path = sys_get_temp_dir() . '/';

if ($_POST['submit'] == 'Carregar') {
    $name = $_FILES['ImageFile']['name'];
    $size = $_FILES['ImageFile']['size'];
    $type = $_FILES['ImageFile']['type'];

    if (strlen($name)) {
        list($txt, $ext) = explode(".", $name);
        $actual_image_name = time() . substr($txt, 5) . "." . $ext;
        $tmp = $_FILES['ImageFile']['tmp_name'];
        if (move_uploaded_file($tmp, $path . $actual_image_name)) {
            
        } else {
            echo "Foto n&atilde;o suportada, verifique a extens&atilde;o ou o tamanho (Tamanho M&aacute;ximo: ".ini_get(upload_max_filesize).")";
            $name = '';
            $tmp = '';
        }
    }
}

$foto = $path . $actual_image_name;
print "<hr>\n";
print "<font size=\"2\">- Use fotos profissionais e apenas de rosto.\n";
print "<br>- N&atilde;o coloque foto com outras pessoas.\n";
print "<br>- Caso a foto tenha outras pessoas, selecione o seu rosto com o mouse.</font>\n";
print "<hr>\n";

print "<div id='ret'></div>";
if ($name) {
    $filePath = $_POST['filePath'];
    print "<form id=\"cropimage\" method=\"post\" action=\"" . INC . "/processupload.inc.php\" enctype=\"multipart/form-data\">\n";
    print "<input type=\"hidden\" name=\"codigo\" value=\"" . $_SESSION['loginCodigo'] . "\"/>\n";
    print "<input type=\"hidden\" name=\"x_axis\" id=\"x_axis\" value=\"\" />\n";
    print "<input type=\"hidden\" name=\"x2_axis\" id=\"x2_axis\" value=\"\" />\n";
    print "<input type=\"hidden\" name=\"y_axis\" id=\"y_axis\" value=\"\" />\n";
    print "<input type=\"hidden\" name=\"y2_axis\" id=\"y2_axis\" value=\"\" />\n";
    print "<input type=\"hidden\" name=\"filePath\" id=\"filePath\" value=\"$foto\" />\n";
    print "<input type=\"hidden\" name=\"fileName\" id=\"filePath\" value=\"$name\" />\n";
    print "<input type=\"hidden\" name=\"fileType\" id=\"filePath\" value=\"$type\" />\n";
    print "<input type=\"submit\" name=\"submit\" id='1' value=\"Salvar\" />\n";
    if ($tmp)
        print "<br><img src=\"" . VIEW . "/trocaFoto.php?foto=$foto\" id=\"photo\" style='max-width:300px'>";
} else {
    print "<form id=\"cropimage\" method=\"post\" enctype=\"multipart/form-data\">\n";
    print "<input type=\"file\" name=\"ImageFile\" id=\"ImageFile\" value=\"\" />\n";
    print "<br><input type=\"submit\" name=\"submit\" id='1' value=\"Carregar\" />\n";
}
print "</form>\n";
?>

<script type="text/javascript">
    function getSizes(im, obj)
    {
        var x_axis = obj.x1;
        var x2_axis = obj.x2;
        var y_axis = obj.y1;
        var y2_axis = obj.y2;
        var thumb_width = obj.width;

        if (thumb_width > 0)
        {
            $("#x_axis").val(x_axis);
            $("#y_axis").val(y_axis);
            $("#x2_axis").val(x2_axis);
            $("#y2_axis").val(y2_axis);
        }
    }

    $(document).ready(function() {
        $('img#photo').imgAreaSelect({
            aspectRatio: '1:1',
            onSelectEnd: getSizes
        });

        $('#ImageFile').change(function() {

            $("#ret").html("<progress id='barraprogresso' value='0' max='100'></progress>");
            var value = 0;
            var loading = function() {
                value += 1;
                addValue = $('#barraprogresso').val(value);
                if (value == 100)
                    value = 0;
            };

            var animate = setInterval(function() {
                loading();
            }, 100);
            $('#1').click();
        });
    });
</script>