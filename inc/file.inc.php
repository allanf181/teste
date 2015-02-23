<?php

require 'config.inc.php';
require FUNCOES;
require VARIAVEIS;

$type = $_GET['type'];
$codigo = dcrip($_GET['id']);

if ($type == 'pic') {
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

if ($type == 'arquivo') {
    // Registra o usuário que está solicitando
    require CONTROLLER . "/arquivoLog.class.php";
    $arqLogs = new ArquivosLogs();
    $params = array('arquivo' => $codigo, 'pessoa' => dcrip($_GET['pessoa']));
    $res = $arqLogs->insertOrUpdate($params);

    require CONTROLLER . "/arquivo.class.php";
    $arq = new Arquivos();
    $params = array('codigo' => $codigo);
    $res = $arq->listRegistros($params);

    $file = ARQUIVOS . '/' . $res[0]['pessoa'] . '/' . $res[0]['atribuicao'] . '/' . $res[0]['arquivo'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $res[0]['arquivo'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        flush();
        readfile($file);
    }
}

if ($type == 'chat') {
    require CONTROLLER . "/chat.class.php";
    $chat = new Chat();

    $params = array(':prontuario' => $_SESSION['loginProntuario'], ':ano' => $ANO);
    $sqlAdicional = " AND str_to_date(:ano, '%Y') = str_to_date(c.data, '%Y') ";

    if (dcrip($_GET['atribuicao'])) {
        $params = array(':prontuario' => $_SESSION['loginProntuario'], ':ano' => $ANO);
        $sqlAdicional .= " AND c.atribuicao = :atribuicao ";
        $params[':atribuicao'] = dcrip($_GET['atribuicao']);
    }
    if (dcrip($_GET['origem'])) {
        $params = array(':prontuario' => $_SESSION['loginProntuario']);
        $sqlAdicional = " AND c.origem = :origem ";
        $params[':origem'] = dcrip($_GET['origem']);
    }

    $message = $chat->qdeMessage($params, $sqlAdicional);

    list($width, $height, $type, $attr) = getimagesize(PATH . IMAGES . "chat.png");
    $imgPng = imageCreateFromPng(PATH . IMAGES . "chat.png");
    imageAlphaBlending($imgPng, true);
    imageSaveAlpha($imgPng, true);
    $cor = imagecolorallocate($imgPng, 255, 0, 0);
    $font = imageloadfont(PATH . VIEW . "/css/fonts/chat.gdf");
    imagestring($imgPng, $font, $width - 45, 0, urldecode($message), $cor);

    header("Content-type: image/png");
    imagePng($imgPng);
}
?>