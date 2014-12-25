<?php
include_once "inc/config.inc.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
<meta http-equiv="Content-Language" content="pt-br" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="shortcut icon" type="image/x-icon" href="<?= ICONS ?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?= VIEW ?>/css/estilo.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?= VIEW ?>/css/menu/styles.css" media="screen" />

<!-- JS PADRAO -->
<script>!window.jQuery && document.write('<script src="<?= VIEW ?>/js/1.7.2.jquery.min.js"><\/script>')</script>
<script src="<?= VIEW ?>/js/jquery.loading.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/jquery.html5form-1.5-min.js"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/css/menu/script.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/jquery.form.min.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/jquery.maskedinput-1.3.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/jquery-maxlength-min.js" type="text/javascript"></script>

<!-- Alertas -->
<script type="text/javascript" src="<?= LIB ?>/Zebra_Dialog/public/javascript/zebra_dialog.js"></script>
<link rel="stylesheet" href="<?= LIB ?>/Zebra_Dialog/public/css/flat/zebra_dialog.css" type="text/css"></link>

<!-- utilizado pelo calendário -->
<script src="<?= VIEW ?>/js/jquery-ui/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
<link href="<?= VIEW ?>/js/jquery-ui/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" type="text/css" media="screen" />

<script type="text/javascript">
    var first=0;
    function display_c(start){
        window.start = parseFloat(start);
        var end = 0 // change this to stop the counter at a higher value
        var refresh=1000; // Refresh rate in milli seconds
        if(window.start >= end ){
            mytime=setTimeout('display_ct()',refresh)
        }
        else {
            $('#index').load('<?= VIEW ?>/logoff.php');
        }
    }

    function display_ct() {
        var days=Math.floor(window.start / 86400); 
        var hours = Math.floor((window.start - (days * 86400 ))/3600)
        var minutes = Math.floor((window.start - (days * 86400 ) - (hours *3600 ))/60)
        var secs = Math.floor((window.start - (days * 86400 ) - (hours *3600 ) - (minutes*60)))
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
        
        try { document.getElementById('sessaoTimeOver').innerHTML = x1; } catch (e) {}
        window.start= window.start- 1;
        tt=display_c(window.start);
    }

    $(document).ready(function() {
        $.ajaxSetup({
            cache: false
        });
        $.loading({
            onAjax: true,
            text: 'Aguarde Carregando...',
            mask: true,
            img: '<?= IMAGES ?>/loader.gif',
            align: 'center'
        });

        $('#setTroca').click(function() {
            var ano = $('#campoAnoIndex').val();
            var semestre = $('#campoSemestreIndex').val();
            $(document).ready(function() {
                $('#sessaoTime').load('home.php?ano='+ano+'&semestre='+semestre);
                $('#index').prepend('<font color="red">Conteúdo do semestre anterior, faça uma nova consulta...</font>');
            });
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
if ((!isset($_SERVER['HTTPS']) && $_SERVER['HTTP_HOST']!='localhost') 
        || ($_SERVER["PHP_SELF"] != LOCATION."/index.php") ) {
    header('Location: https://'.$_SERVER['HTTP_HOST'].LOCATION);
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
<div id="mask"></div>

<body id="body">
<div id="wrap">
<?php
if (isset($nome)) {
    ?>
    <script> 
        display_c(<?= $TIMEOUT*60 ?>); 
    </script>    
    <div id="header" style='height: 80px;'>
    </div>
    <div id="menu"><div id='sessaoTime'></div>
        <div id='barra_topo'>
    <?php

    if (!in_array($ALUNO, $_SESSION["loginTipo"]) &&
        ( !in_array($PROFESSOR, $_SESSION["loginTipo"]) ||
        (in_array($PROFESSOR, $_SESSION["loginTipo"]) && count($_SESSION["loginTipo"]) > 1)  ) ) {
    ?>
        <div id='ano_semestre'>
        <span style="color: white">Ano:<input type="text" maxlength="4" style="width: 50px" value="<?= $_SESSION['ano'] ?>" name="campoAnoIndex" id="campoAnoIndex" /></span>
        <span style="color: white">Sem:<input type="text" maxlength="1" style="width: 50px" value="<?= $_SESSION['semestre'] ?>" name="campoSemestreIndex" id="campoSemestreIndex" /></span>
        <div id='botao_ano_semestre'><a href="#" id="setTroca"><img src="<?= ICONS ?>/change.png" /></a></div>&nbsp;
	</div>
    
    <?php 
    }
    ?>
    <span style="color: white">Ol&aacute; <a id="senhaTip" title="Para sua segurança, altere sua senha periodicamente (clique no seu nome)." href="javascript:$('#index').load('<?= VIEW ?>/senha.php?opcao=alterar'); void(0);" style="color: white"><?= $nome ?></a></span>
    <a href="javascript:$('#index').load('<?= VIEW ?>/logoff.php'); void(0);" style="color: white">(Sair)</a>
    </div>
    </div>
<?php 
}

if (isset($nome)) {
    ?>
    <div id='logo_topo'></div>
    <?php
} ?>

<div id="content">

<?php

// Janela de Login, caso o usuário não tenha sessão.
if (!isset($_SESSION["loginTipo"]) && !isset($_POST['campoLogin']) && (!isset($_GET['est']) && !isset($_GET['p']))  ) {
?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#index').load('<?= VIEW ?>/login.php');
        });
    </script>
<?php
}

// Quando o usuário utiliza o link de recuperar senha.
if (isset($_GET['est']) && isset($_GET['p'])){
    $est = $_GET['est'];
    $p = $_GET['p'];
    ?>
    <script>
        $(document).ready(function() {
            $('#index').load('<?= VIEW ?>/senha.php?opcao=recuperar&est=<?= $est ?>&p=<?= $p ?>');
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
            $(document).ready(function() {
                $('#index').load('<?= VIEW ?>/senha.php?opcao=alterar');
            });
        </script>
        <?php
    } else {
        ?>
        <div id="cssmenu">
        <ul>
        <li><a href="javascript:$('#index').load('home.php'); void(0);"><span>Home</span></a></li>
        <?php

        // SE FOR ALUNO OU PROFESSOR, MOSTRA O MENU PERSONALIZADO COM AS SUAS ATRIBUICOES
        $menuDiferencial=0;
        if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
            // MENU PROFESSOR
            require CONTROLLER . "/atribuicao.class.php";
            $disc = new Atribuicoes();
            $disc = $disc->getAtribuicoesFromPapel($_SESSION['loginCodigo'], 'professor', $ANO, 'menu');
            $menuDiferencial='professor';
        }
        if (in_array($ALUNO, $_SESSION["loginTipo"])) {
          // MENU ALUNO
            require CONTROLLER . "/atribuicao.class.php";
            $disc = new Atribuicoes();
            $disc = $disc->getAtribuicoesFromPapel($_SESSION['loginCodigo'], 'aluno', $ANO, 'menu');
            $menuDiferencial='aluno';
        }
        if ($menuDiferencial) {
            foreach ($disc as $ano => $reg) {
                ?><li class='active has-sub'><a href='#'><span>Disciplinas <?= $ano ?></span></a>
                <ul>
                <?php
                if (isset($disc[$ano]['A']['A']))
                    foreach ($disc[$ano]['A']['A'] as $atribuicao => $reg) { // ANUAL
                        ?>
                        <li><a title="<?= $reg[0].'<br>'.$reg[3].'<br>'.$reg[4] ?>" href="javascript:$('#index').load('<?= VIEW."/".$menuDiferencial."/".$menuDiferencial.".php?atribuicao=".crip($reg[1]);?>'); void(0);"><span><?= $reg[2] ?></span></a></li>
                        <?php
                    }
                foreach ($disc[$ano] as $semestre => $reg) {
                    if ($semestre != 'A') { // SEMESTRAL
                        ?>
                        <li class='active has-sub'><a href='#'><span><?= $semestre ?>&ordm; SEMESTRE</span></a>
                        <ul>
                        <?php
                        if (isset($disc[$ano][$semestre]['S'])) { // DISCIPLINA SEMESTRAL
                            foreach ($disc[$ano][$semestre]['S'] as $atribuicao => $reg) {
                                ?>
                                <li><a title="<?= $reg[0].'<br>'.$reg[3].'<br>'.$reg[4] ?>" href="javascript:$('#index').load('<?= VIEW."/".$menuDiferencial."/".$menuDiferencial.".php?atribuicao=".crip($reg[1]);?>'); void(0);"><span><?= $reg[2] ?></span></a></li>
                                <?php
                            }
                        }
                        if (isset($disc[$ano][$semestre]['B'])) { // BIMESTRE
                            foreach ($disc[$ano][$semestre]['B'] as $bimestre => $reg) {
                                ?>
                                <li class='active has-sub'><a href='#'><span><?= $bimestre ?>&ordm; BIMESTRE</span></a>
                                <ul>
                                <?php
                                foreach ($disc[$ano][$semestre]['B'][$bimestre] as $atribuicao => $reg) {
                                    ?>
                                    <li><a title="<?= $reg[0].'<br>'.$reg[3].'<br>'.$reg[4] ?>" href="javascript:$('#index').load('<?= VIEW."/".$menuDiferencial."/".$menuDiferencial.".php?atribuicao=".crip($reg[1]);?>'); void(0);"><span><?= $reg[2] ?></span></a></li>
                                    <?php
                                }
                                ?></ul></li><?php
                            }
                        }
                        ?></ul></li><?php                    
                    }
                }
                ?></ul></li><?php                    
            } // FIM MENU PROFESSOR/ALUNO
        }
        if ($_SESSION["loginTipo"]) {
            require CONTROLLER . "/permissao.class.php";
            $permissao = new Permissoes();
            $menus = $permissao->listaPermissoes($_SESSION["loginTipo"], 'menu');

            function menuMapa($k){
                $mapa['atribuicao_docente'] = 'atribuição docente';
                $mapa['atribuicao'] = 'atribuição';
                $mapa['relatorios'] = 'relatórios';
                if (@$mapa[$k])
                    return $mapa[$k];
                
                return $k;
            }
            
            function makeMenu($ar){
                global $menus;
                foreach ($ar as $k => $v ) {
                    if (!is_array($v)) {
                        ?>
                        <li><a href="javascript:$('#index').load('<?= $v ?>'); void(0);"><span><?= $menus['nome'][$v] ?></span></a></li>
                        <?php
                    }
                    if (is_array($ar[$k])) {
                        $n = menuMapa($k);
                        ?>
                        <li class='active has-sub'><a href='#'><span><?= maiusculo($n) ?></span></a>
                        <ul>
                        <?php
                        makeMenu ($ar[$k]);
                    }
                }
                ?>
                </ul></li>
                <?php
            } 
            print makeMenu($menus['arvore']['view']);
        }
        ?>
        </ul>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#index').load('home.php');
            });
        </script>
        <?php
    }
 }
?>
    </div>

    <div class="right">
        <div id="index"></div>
    </div>
</div>
<div class="footer">
    &nbsp;
    <hr class="footer-hr">
    <a class="link" href="javascript:$('#index').load('creditos.php');void(0);">Grupo de Trabalho WebDi&aacute;rio IFSP</a>
    <br />
    <span>Resolu&ccedil;&atilde;o m&iacute;nima 1024x768</span>
</div>
<a href="#" class="scrollup">Scroll</a>
</div>
</body>
</html>

<script>
    document.title = '<?= $SITE_TITLE ?>';
    
    function time_over() {
        $.Zebra_Dialog('<strong>Aten&ccedil;&atilde;o, sua sess&atilde;o vai expirar em <div id="sessaoTimeOver"></div></strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption) {
                document.title = '<?= $SITE_TITLE ?>';
                first=0;
                if (caption == 'Sim') {
                    $('#sessaoTime').load('home.php?time_over=1');
                } else {
                    $('#index').load('<?= VIEW ?>/logoff.php');
                }
            }
        });
    }
</script>