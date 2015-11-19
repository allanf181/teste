<?php
include_once "inc/config.inc.php";

// Verifica e redireciona para HTTPS
// Verifica se está tentando acessar diretamente
if ((!isset($_SERVER['HTTPS']) && $_SERVER['HTTP_HOST'] != 'localhost') || ($_SERVER["PHP_SELF"] != LOCATION . "/index.php")) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>&nbsp;</title>
        <meta http-equiv="Content-Language" content="pt-br" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="1" />
        <meta http-equiv="pragma" content="no-cache" />
        <link rel="shortcut icon" type="image/x-icon" href="<?= ICONS ?>/favicon.ico" />
        <!-- CSS SITE -->        
        <link type="text/css" rel="stylesheet" href="<?= VIEW ?>/css/estilo.min.css" media="screen" />
        <!-- CSS MENU -->
        <link type="text/css" rel="stylesheet" href="<?= VIEW ?>/css/menu/styles.css" media="screen" />
        <!-- CSS ALERT -->
        <link type="text/css" rel="stylesheet" href="<?= LIB ?>/Zebra_Dialog/public/css/flat/zebra_dialog.css" />
        <!-- CSS CALENDAR -->
        <link type="text/css" href="<?= VIEW ?>/js/jquery-ui/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" media="screen" />
        <!-- CSS TOOLTIP -->
        <link rel="stylesheet" href="<?= VIEW ?>/js/webui-popover/jquery.webui-popover.min.css" />
        
        <!-- JS PADRAO -->
        <script type="text/javascript">!window.jQuery && document.write('<script type="text/javascript" src="<?= VIEW ?>/js/jquery-2.1.3.min.js"><\/script>')</script>
        <script type="text/javascript" src="<?= VIEW ?>/js/jquery.html5form-1.5-min.js"></script>
        
        <!--PROJETO DE LIMPEZA DO JS-->
        <script type="text/javascript" src="<?= VIEW ?>/js/wd.js"></script>
        
        <!-- JS TOOLTIP -->
        <script src="<?= VIEW ?>/js/webui-popover/jquery.webui-popover.min.js"></script>
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

                $('#setRetorno').click(function () {
                    $('#sessaoTime').load('<?= VIEW ?>/system/home.php?retorno');
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
                                    <?php if ($_SESSION['anoOuSemestreAlterado']){?>
                                <div id='botao_retorno'>
                                    <a href="#" id="setRetorno" data-placement="right" data-content="Não é o ano/semestre corrente, deseja ajustar para o ano/semestre atual?" title="Ajustar para ano/semestre atual"><img width="20px" src="<?= ICONS ?>/warning.png" /></a>
                                </div>&nbsp;
                                    <?php } ?>
                                <div id='botao_ano_semestre'>
                                    <a href="#" id="setTroca" data-placement="right" data-content="Clique para trocar o semestre" title="Trocar semestre"><img src="<?= ICONS ?>/change.png" /></a>
                                </div>&nbsp;
                            </div>
                            <?php
                        }
                        ?>
                        <div style="width: 800px; color: white; text-align: right">Ol&aacute; <a id="senhaTip" title="Troque sua senha" data-content="Para sua segurança, altere sua senha periodicamente." href="javascript:$('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');void(0);" style="color: white"><?= $nome ?></a>
                        <a href="javascript:$('#index').load('<?= VIEW ?>/system/logoff.php');void(0);" style="color: white">(Sair)</a></div>
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
            <a class="link" href="javascript:$('#index').load('view/system/creditos.php');void(0);">Grupo de Trabalho <b>WebDi&aacute;rio IFSP</b></a>
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
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-69732705-1', 'auto');
  ga('send', 'pageview');

</script>
    </body>
</html>