<?php
include_once "inc/config.inc.php";
require FUNCOES;
require VARIAVEIS;

// Verifica e redireciona para HTTPS
if (!isset($_SERVER['HTTPS']) && $_SERVER['HTTP_HOST']!='localhost') {
    header('Location: https://'.$_SERVER['HTTP_HOST'].LOCATION);
}

// Verifica se está tentando acessar diretamente
if ($_SERVER["PHP_SELF"] != LOCATION."/index.php") {
    header('Location: https://'.$_SERVER['HTTP_HOST'].LOCATION);
}

// Verifica se a extensão GD está instalada no sistema.
if (!extension_loaded('gd')) {
	print "<center><br><br><font size=\"2\" color=\"red\">Aten&ccedil;&atilde;o: a biblioteca GD (PHP) não foi instalada.</b></font></center>";
}

// Verifica se a extensão do DB2 está instalada no sistema.
if (!extension_loaded('ibm_db2')) {
	print "<center><br><br><font size=\"2\" color=\"red\">Aten&ccedil;&atilde;o: a biblioteca IBM_DB2 (PHP) não foi instalada.</b></font></center>";
}

$prontuario = (isset($_SESSION["loginProntuario"])) ? $_SESSION["loginProntuario"] : null;
$nome = (isset($_SESSION["loginNome"])) ? $_SESSION["loginNome"] : null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php print $SITE_TITLE; ?></title>
<meta http-equiv="Content-Language" content="pt-br" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="shortcut icon" type="image/x-icon" href="<?php print ICONS; ?>/favicon.ico">
<link rel="stylesheet" type="text/css" href="<?php print VIEW; ?>/css/style2.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php print VIEW; ?>/css/estilo.css" media="screen" />

<script>!window.jQuery && document.write('<script src="<?php print VIEW; ?>/js/1.7.2.jquery.min.js"><\/script>')</script>
<script src="<?php print VIEW; ?>/js/jquery.loading.js" type="text/javascript"></script>

<script src="<?php print VIEW; ?>/js/jquery.html5form-1.5-min.js"></script>

<script type="text/javascript" src="<?php print LIB; ?>/Zebra_Dialog/public/javascript/zebra_dialog.js"></script>
<link rel="stylesheet" href="<?php print LIB; ?>/Zebra_Dialog/public/css/flat/zebra_dialog.css" type="text/css"></link>
<script src="<?php print VIEW; ?>/js/jquery-ui/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
<link href="<?php print VIEW; ?>/js/jquery-ui/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" type="text/css" media="screen" />
<script src="<?php print VIEW; ?>/js/jquery-ui/jquery.alerts.js" type="text/javascript"></script>
<link href="<?php print VIEW; ?>/js/jquery-ui/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
<script src="<?php print VIEW; ?>/js/jquery.maskedinput-1.3.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/jquery-maxlength-min.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/jquery.form.min.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/css/menu/script.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?php print VIEW; ?>/css/menu/styles.css" media="screen" />

<script>
    $(document).ready(function() {
    $.ajaxSetup({
        cache: false
    });
    $.loading({
        onAjax: true,
        text: 'Aguarde Carregando...',
        mask: true,
        img: '<?php print IMAGES; ?>/loader.gif',
        align: 'center'
    });

    $("#setTroca").click(atualizar);

});

$(document).ready(function() {
    $('a').click(function() {
        if ($(this).attr("id") != 'setTroca')
            $('#campoMenuLink').val($(this).attr("id"));
    });
});

function atualizar() {
    var ano = $('#campoAnoIndex').val();
    var semestre = $('#campoSemestreIndex').val();
    var linkTroca = $('#campoMenuLink').val();
    if (linkTroca == '' || linkTroca == 'setTroca')
        linkTroca = 'home.php';
    $(document).ready(function() {
        $('#index').load(linkTroca + '?ano=' + ano + '&semestre=' + semestre);
    });
}
</script>
</head>

<div id="mask"></div>

<body id="body">
<div id="wrap">
<?php
if (isset($nome)) {
?>
    <div id="header" style='height: 80px;'>
    </div>
    <div id="menu">
    <div id='barra_topo'>
    <?php
    if (in_array($COORD, $_SESSION["loginTipo"]) || in_array($ADM, $_SESSION["loginTipo"]) 
    	|| in_array($SEC, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
    ?>
        <div id='ano_semestre'>
        <span style="color: white">Ano:<input type="text" maxlength="4" style="width: 50px" value="<?php print $_SESSION['ano']; ?>" name="campoAnoIndex" id="campoAnoIndex" /></span>
        <span style="color: white">Sem:<input type="text" maxlength="1" style="width: 50px" value="<?php print $_SESSION['semestre']; ?>" name="campoSemestreIndex" id="campoSemestreIndex" /></span>
        <input type="hidden" name="campoMenuLink" id="campoMenuLink" />
        <div id='botao_ano_semestre'><a href="#" id="setTroca"><img src="<?php print ICONS; ?>/change.png" /></a></div>&nbsp;
	</div>
    
    <?php 
    }
    ?>
    <span style="color: white">Ol&aacute; <a id="senhaTip" title="Para sua segurança, altere sua senha periodicamente (clique no seu nome)." href="javascript:$('#index').load('<?php print VIEW; ?>/senha.php?opcao=alterar'); void(0);" style="color: white"><?php print utf8_encode($nome); ?></a></span>
    <a href="javascript:$('#index').load('<?php print VIEW; ?>/logoff.php'); void(0);" style="color: white">(Sair)</a>
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
            $('#index').load('<?php print VIEW; ?>/login.php');
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
            $('#index').load('<?php print VIEW; ?>/senha.php?opcao=recuperar&est=$est&p=$p');
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
    if ($login->usuarioTrocouSenha($_SESSION["loginCodigo"])) {
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#index').load('<?php print VIEW; ?>/senha.php?opcao=alterar');
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
            $disc = new Atribuicao();
            $disc = $disc->listAtribuicoes($_SESSION['loginCodigo'], 'professor');
            $menuDiferencial='professor';
        }
        if (in_array($ALUNO, $_SESSION["loginTipo"])) {
          // MENU ALUNO
            require CONTROLLER . "/atribuicao.class.php";
            $disc = new Atribuicao();
            $disc = $disc->listAtribuicoes($_SESSION['loginCodigo'], 'aluno');
            $menuDiferencial='aluno';
        }
        if ($menuDiferencial) {
            foreach ($disc as $ano => $reg) {
                ?><li class='active has-sub'><a href='#'><span>Disciplinas <?php print $ano; ?></span></a>
                <ul>
                <?php
                if (isset($disc[$ano]['A']['A']))
                    foreach ($disc[$ano]['A']['A'] as $atribuicao => $reg) { // ANUAL
                        ?>
                        <li><a title="<?php print $reg[0].' - '.$reg[3]; ?>" href="javascript:$('#index').load('<?php print VIEW."/".$menuDiferencial."/".$menuDiferencial.".php?atribuicao=".crip($reg[1]);?>'); void(0);"><span><?php print $reg[2]; ?></span></a></li>
                        <?php
                    }
                foreach ($disc[$ano] as $semestre => $reg) {
                    if ($semestre != 'A') { // SEMESTRAL
                        ?>
                        <li class='active has-sub'><a href='#'><span><?php print $semestre; ?>&ordm; SEMESTRE</span></a>
                        <ul>
                        <?php
                        if (isset($disc[$ano][$semestre]['S'])) { // DISCIPLINA SEMESTRAL
                            foreach ($disc[$ano][$semestre]['S'] as $atribuicao => $reg) {
                                ?>
                                <li><a title="<?php print $reg[0].' - '.$reg[3]; ?>" href="javascript:$('#index').load('<?php print VIEW."/".$menuDiferencial."/".$menuDiferencial.".php?atribuicao=".crip($reg[1]);?>'); void(0);"><span><?php print $reg[2]; ?></span></a></li>
                                <?php
                            }
                        }
                        if (!isset($disc[$ano][$semestre]['S'])) { // BIMESTRE
                            foreach ($disc[$ano][$semestre] as $bimestre => $reg) {
                                ?>
                                <li class='active has-sub'><a href='#'><span><?php print $bimestre; ?>&ordm; BIMESTRE</span></a>
                                <ul>
                                <?php
                                foreach ($disc[$ano][$semestre][$bimestre] as $atribuicao => $reg) {
                                    ?>
                                    <li><a title="<?php print $reg[0].' - '.$reg[3]; ?>" href="javascript:$('#index').load('<?php print VIEW."/".$menuDiferencial."/".$menuDiferencial.".php?atribuicao=".crip($reg[1]);?>'); void(0);"><span><?php print $reg[2]; ?></span></a></li>
                                    <?php
                                }
                                ?></ul></li><?php
                            }
                        }
                        ?></ul></li><?php                    
                    }
                }
                ?></ul></li><?php                    
            } // FIM MENU PROFESSOR
        }
        require CONTROLLER . "/permissao.class.php";
        $permissao = new permissao();
        $permissoes = $permissao->listaPermissoes($_SESSION["loginTipo"]);
        $files = $permissao->listaPermissoes($_SESSION["loginTipo"], 'menu');
        
        function makeMenu($ar){
            global $files;
            foreach ($ar as $k => $v ) {
                if (!is_array($v)) {
                    ?>
                    <li><a href="javascript:$('#index').load('<?php print $v; ?>'); void(0);"><span><?php print $files[$v]; ?></span></a></li>
                    <?php
                }
                if (is_array($ar[$k])) {
                    ?>
                    <li class='active has-sub'><a href='#'><span><?php print maiusculo($k); ?></span></a>
                    <ul>
                    <?php
                    makeMenu ($ar[$k]);
                }
            }
            ?>
            </ul></li>
            <?php
        } 
        print makeMenu($permissoes['view']);
        ?>
        </ul>
        <?php
       }
       ?>
       <script type="text/javascript">
            $(document).ready(function() {
                $('#index').load('home.php');
            });
       </script>
       <?php
 }
?>
</div>

<div class="right">

<div id="index"></div>

<?php
    mysql_close($conexao);
?>
</div>
</div>

    <div class="footer" <?php if (!$_SESSION['loginCodigo']) print 'style="margin-right: 195px; "'; ?> >
	<a class="link" href="javascript:$('#index').load('creditos.php');void(0);">Equipe de desenvolvimento do WebDi&aacute;rio IFSP</a>
</a>
</div>

</body>
</html>