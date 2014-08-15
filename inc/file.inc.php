<?php

require 'config.inc.php';
require MYSQL;
require FUNCOES;

$type = $_GET['type'];

if ($type == 'pic') {
    $codigo = dcrip($_GET['id']);
    
    if (isset($_GET['force']))
        $force = dcrip($_GET['force']);

    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new Pessoas();
    $params = array('codigo' => $codigo);
    $res = $pessoa->listRegistros($params);
    
    // Caso o user tenha foto
    if ($res[0]['foto'] && ($res[0]['bloqueioFoto'] == 0 || $force)) {
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        header("Content-Type: image/jpeg");
        print $res[0]['foto'];
    } else if ($res[0]['bloqueioFoto'] == 1) { // Caso esteja configurado para bloquear foto
        $arquivo = PATH . IMAGES . "user_bloq.png";
        $fp = fopen($arquivo, "rb");
        $pic = fread($fp, filesize($arquivo));
        fclose($fp);
        header('Content-type: image/png');
        print $pic;
    } else if ($res[0]['bloqueioFoto'] == 0) { // User sem foto
        $arquivo = PATH . IMAGES . "user.png";
        $fp = fopen($arquivo, "rb");
        $pic = fread($fp, filesize($arquivo));
        fclose($fp);
        header('Content-type: image/png');
        print $pic;
    }
}

?>

