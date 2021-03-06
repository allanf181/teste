<?php
$LOCATION = substr(LOCATION, 1);

if (strpos($_SERVER["HTTP_REFERER"],"/$LOCATION/") == false) {
    header('Location: https://'.$_SERVER['HTTP_HOST'].LOCATION);
}

// PERMISSOES DO ARQUIVO
if (isset($_SESSION["loginTipo"])) {
    if(!class_exists('Permissoes'))
        require CONTROLLER . "/permissao.class.php";
    $permissao = new Permissoes();        
   
    $LOCATION = substr(LOCATION, 1);
    $SITE = str_replace("/$LOCATION/", '', $_SERVER['PHP_SELF']);
    $TITLE='';
    $TITLE = $permissao->isAllowed($_SESSION["loginTipo"], $SITE);
  
    if ($TITLE) {
        $TITLE_DESCRICAO = $permissao->fileDescricao($SITE);
        $TITLE_DESCRICAO = "<span class=\"help\"><a title='Sobre esse m&oacute;dulo' data-placement='right' data-content=\"".$TITLE_DESCRICAO['descricaoArquivo']."\" href=\"#\"><img src=\"".ICONS."/help.png\"></a></span>";
    }
    
    if (!$TITLE) {
        print "Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar esse arquivo.\n";
    die;
    }
} else {
    print "<script type=\"text/javascript\">\n ";
    print "    $(document).ready(function() { \n";
    print "     $('#menuEsquerdo').load('". VIEW ."/login.php'); \n";
    print "     $('#index').load('". VIEW ."/system/home.php'); \n";
    print "    }); \n";
    print " </script>\n";
  die;
}
// FIM PERMISSOES DO ARQUIVO
?>