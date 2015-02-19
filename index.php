<?php
include_once "inc/config.inc.php";
?>
<!DOCTYPE html>
<html lang="pt-br" manifest="manifest.appcache">
    <head>
        <title>&nbsp;</title>
        <meta charset=utf-8 />
        <link rel="shortcut icon" type="image/x-icon" href="<?= ICONS ?>/favicon.ico" />
        <!-- CSS SITE -->        
        <link type="text/css" rel="stylesheet" href="<?= VIEW ?>/css/estilo.css" media="screen" />
        <!-- CSS MENU -->
        <link type="text/css" rel="stylesheet" href="<?= VIEW ?>/css/menu/styles.css" media="screen" />
        <!-- CSS ALERT -->
        <link type="text/css" rel="stylesheet" href="<?= LIB ?>/Zebra_Dialog/public/css/flat/zebra_dialog.css" />
        <!-- CSS CALENDAR -->
        <link type="text/css" href="<?= VIEW ?>/js/jquery-ui/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" media="screen" />

        <!-- JS PADRAO -->
        <script type="text/javascript">!window.jQuery && document.write('<script type="text/javascript" src="<?= VIEW ?>/js/jquery-2.1.3.min.js"><\/script>')</script>
        <script type="text/javascript" src="<?= VIEW ?>/js/jquery.html5form-1.5-min.js"></script>
        <!-- JS TOOLTIP -->
        <script type="text/javascript" src="<?= VIEW ?>/js/tooltip.js"></script>
        <!-- JS MENU -->
        <script type="text/javascript" src="<?= VIEW ?>/js/menu/script.js"></script>
        <!-- JS MASKINPUT -->
        <script type="text/javascript" src="<?= VIEW ?>/js/maskinput/jquery.maskedinput-1.3.js"></script>
        <script type="text/javascript" src="<?= VIEW ?>/js/maskinput/jquery-maxlength-min.js"></script>
        <!-- JS ALERT -->
        <script type="text/javascript" src="<?= LIB ?>/Zebra_Dialog/public/javascript/zebra_dialog.js"></script>
        <!-- JS CALENDAR -->
        <script type="text/javascript" src="<?= VIEW ?>/js/jquery-ui/jquery-ui-1.10.4.custom.min.js"></script>
        <!-- JS GRAFICS -->
        <script type="text/javascript" src="<?= VIEW ?>/js/highcharts/highcharts.js"></script>
        <script type="text/javascript" src="<?= VIEW ?>/js/highcharts/exporting.js"></script>
        <!-- JS AJAX -->
        <script type="text/javascript" src="<?= VIEW ?>/js/jquery.form.min.js"></script>

        <script type="text/javascript">
            $(document).ajaxStart(function () {
                $('#loading').show();
            }).ajaxStop(function () {
                $('#loading').hide();
            });

            var first = 0;
            function display_c(start) {
                window.start = parseFloat(start);
                var end = 0 // change this to stop the counter at a higher value
                var refresh = 1000; // Refresh rate in milli seconds
                if (window.start >= end) {
                    mytime = setTimeout('display_ct()', refresh)
                }
                else {
                    $('#index').load('<?= VIEW ?>/system//logoff.php');
                }
            }

            function display_ct() {
                var days = Math.floor(window.start / 86400);
                var hours = Math.floor((window.start - (days * 86400)) / 3600)
                var minutes = Math.floor((window.start - (days * 86400) - (hours * 3600)) / 60)
                var secs = Math.floor((window.start - (days * 86400) - (hours * 3600) - (minutes * 60)))
                var x = "Sessão expira em " + minutes + "min" + secs;
                document.getElementById('sessaoTime').innerHTML = x;

                var x1 = minutes + "min" + secs + ". Deseja renovar o tempo?";
                if (!first && days <= 0 && hours <= 0 && minutes <= 2) {
                    time_over();
                    first = 1;
                }

                if (first) {
                    document.title = 'SESSÃO: ' + minutes + "min" + secs;
                }

                try {
                    document.getElementById('sessaoTimeOver').innerHTML = x1;
                } catch (e) {
                }
                window.start = window.start - 1;
                tt = display_c(window.start);
            }

            $(document).ready(function () {
                $.ajaxSetup({
                    cache: false
                });

                function show_popup() {
                    window.location.reload();
                }

                $('#setTroca').click(function () {
                    var ano = $('#campoAnoIndex').val();
                    var semestre = $('#campoSemestreIndex').val();
                    $('#sessaoTime').load('<?= VIEW ?>/system/home.php?ano=' + ano + '&semestre=' + semestre);
                    $.Zebra_Dialog('<strong>Trocando período...</strong><br>Aguarde enquanto o sistema faz a troca...');
                    window.setTimeout(show_popup, 2000);
                });

                $(window).scroll(function () {
                    if ($(this).scrollTop() > 100) {
                        $('.scrollup').fadeIn();
                    } else {
                        $('.scrollup').fadeOut();
                    }
                });

                $('.scrollup').click(function () {
                    $("html, body").animate({
                        scrollTop: 0
                    }, 600);
                    return false;
                });
            });
        </script>
    </head>
    <?php
    require FUNCOES;
    require VARIAVEIS;

    // Verifica e redireciona para HTTPS
    // Verifica se está tentando acessar diretamente
    if ((!isset($_SERVER['HTTPS']) && $_SERVER['HTTP_HOST'] != 'localhost') || ($_SERVER["PHP_SELF"] != LOCATION . "/index.php")) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
    }

    //VERIFICANDO SE AS BIBLIOTECAS NECESSÁRIAS FORAM INSTALADAS
    foreach ($EXTENSIONS as $ext) {
        if (!extension_loaded($ext)) {
            print "<center><font size=\"2\" color=\"red\">Aten&ccedil;&atilde;o, a biblioteca: $ext (PHP) não foi instalada.</b></font></center>";
        }
    }

    $prontuario = (isset($_SESSION["loginProntuario"])) ? $_SESSION["loginProntuario"] : null;
    $nome = (isset($_SESSION["loginNome"])) ? $_SESSION["loginNome"] : null;
    ?>
    <body id="body">
        <div id="loading" class="loading"></div>
        <div id="mask"></div>
        <div id="wrap">
            <?php
            if (isset($nome)) {
                ?>
                <script type="text/javascript">
                    display_c(<?= $TIMEOUT * 60 ?>);
                </script>    
                <div id="header" style='height: 80px;'></div>
                <div id="menu">
                    <div id='sessaoTime'></div>
                    <div id='barra_topo'>
                        <?php
                        if ($_SESSION['loginAlteraAno']) {
                            ?>
                            <div id='ano_semestre'>
                                <span style="color: white">Ano:<input type="text" maxlength="4" style="width: 50px" value="<?= $_SESSION['ano'] ?>" name="campoAnoIndex" id="campoAnoIndex" /></span>
                                <span style="color: white">Sem:<input type="text" maxlength="1" style="width: 50px" value="<?= $_SESSION['semestre'] ?>" name="campoSemestreIndex" id="campoSemestreIndex" /></span>
                                <div id='botao_ano_semestre'><a href="#" id="setTroca"><img src="<?= ICONS ?>/change.png" /></a></div>&nbsp;
                            </div>
                            <?php
                        }
                        ?>
                        <span style="color: white">Ol&aacute; <a id="senhaTip" title="Para sua segurança, altere sua senha periodicamente (clique no seu nome)." href="javascript:$('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');void(0);" style="color: white"><?= $nome ?></a></span>
                        <a href="javascript:$('#index').load('<?= VIEW ?>/system/logoff.php');void(0);" style="color: white">(Sair)</a>
                    </div>
                </div>
                <?php
            }
            if (isset($nome)) {
                ?>
                <div id='logo_topo'></div>
            <?php }
            ?>
            <div id="content">
                <?php
                // Janela de Login, caso o usuário não tenha sessão.
                if (!isset($_SESSION["loginTipo"]) && !isset($_POST['campoLogin'])) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('#index').load('<?= VIEW ?>/system/login.php?key=<?= $_GET['key'] ?>&prt=<?= $_GET['prt'] ?>');
                                });
                    </script>
                    <?php
                }

                // USADO PARA MENUS PERSONALIZADOS
                $_SESSION["menu"] = '';
                if (isset($_SESSION["loginTipo"])) {
                    // CHECA SE O USER TROCOU DE SENHA
                    require CONTROLLER . "/login.class.php";
                    $login = new login();
                    if (!$LDAP_ATIVADO && $login->usuarioTrocouSenha($_SESSION["loginCodigo"])) {
                        ?>
                        <script type="text/javascript">
                            $(document).ready(function () {
                                $('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');
                            });
                        </script>
                        <?php
                    } else {
                        require('view/system/menu.php');
                        ?>
                        <script type="text/javascript">
                            $(document).ready(function () {
                                $('#index').load('<?= VIEW ?>/system/home.php');
                            });
                        </script>
                        <?php
                    }
                }
                ?>
                <div class="right">
                    <div id="index"></div>
                </div>
            </div>
        </div>
        <div class="footer">
            &nbsp;
            <hr class="footer-hr" />
            <a class="link" href="javascript:$('#index').load('view/system/creditos.php');void(0);">Grupo de Trabalho WebDi&aacute;rio IFSP</a>
            <br />
            <span>Resolu&ccedil;&atilde;o m&iacute;nima 1024x768</span>
        </div>
        <a href="#" class="scrollup">Scroll</a>

        <script type="text/javascript">
            document.title = '<?= $SITE_TITLE ?>';
            function time_over() {
                $.Zebra_Dialog('<strong>Aten&ccedil;&atilde;o, sua sess&atilde;o vai expirar em <div id="sessaoTimeOver"><\/div><\/strong>', {
                    'type': 'question',
                    'title': '<?= $TITLE ?>',
                    'buttons': ['Sim', 'Não'],
                    'onClose': function (caption) {
                        document.title = '<?= $SITE_TITLE ?>';
                        first = 0;
                        if (caption == 'Sim') {
                            $('#sessaoTime').load('<?= VIEW ?>/system/home.php?time_over=1');
                        } else {
                            $('#index').load('<?= VIEW ?>/system/logoff.php');
                        }
                    }
                });
            }
        </script>
    </body>
</html>