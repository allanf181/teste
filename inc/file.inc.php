<?php

require $_SESSION['CONFIG'] ;
require MYSQL;
require FUNCOES;

$type = $_GET['type'];

if ($type == 'pic') {
    $codigo = dcrip($_GET['id']);
    
    if (isset($_GET['force']))
        $force = dcrip($_GET['force']);

    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new pessoa();
    $res = $pessoa->getFoto($codigo);
    
    // Caso o user tenha foto
    if ($res['foto'] && ($res['bloqueioFoto'] == 0 || $force)) {
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        header("Content-Type: image/jpeg");
        print $res['foto'];
    } else if ($res['bloqueioFoto'] == 1) { // Caso esteja configurado para bloquear foto
        $arquivo = PATH . IMAGES . "user_bloq.png";
        $fp = fopen($arquivo, "rb");
        $pic = fread($fp, filesize($arquivo));
        fclose($fp);
        header('Content-type: image/png');
        print $pic;
    } else if ($res['bloqueioFoto'] == 0) { // User sem foto
        $arquivo = PATH . IMAGES . "user.png";
        $fp = fopen($arquivo, "rb");
        $pic = fread($fp, filesize($arquivo));
        fclose($fp);
        header('Content-type: image/png');
        print $pic;
    }
}

if ($type == 'pdf') {
    if (isset($_GET['atribuicao'])) {
        $codigo = dcrip($_GET['atribuicao']);
        
        require CONTROLLER . "/plano.class.php";

        $plano = new plano();
        $res = $plano->getPlano($codigo);
    	header("Content-type: application/pdf");
	header("Content-Disposition: attachment; filename=".$res['disciplina'].".pdf");
	print $res['file'];
    }
}
?>

