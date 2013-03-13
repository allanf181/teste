<?php
require '../config.inc.php';

header("Content-type: image/png");

$imagemCaptcha = imagecreatefrompng("captcha.png")
or die("Não foi possível inicializar uma nova imagem");

//Carregar uma nova fonte
$fonteCaptcha = imageloadfont("anonymous.gdf");

// Criar o texto para o captcha
$textoCaptcha = substr(md5(uniqid('')),-4,4);

// Guardar o texto numa variável session
$_SESSION['session_textoCaptcha'] = $textoCaptcha;

// Indicar a cor para o texto
$corCaptcha = imagecolorallocate($imagemCaptcha,0,0,0);

// Escrever a string na cor escolhida
imagestring($imagemCaptcha,$fonteCaptcha,15,5,$textoCaptcha,$corCaptcha);

// Mostrar a imagem captha no formato PNG.
// Outros formatos podem ser usados com imagejpeg, imagegif, imagewbmp, etc.
imagepng($imagemCaptcha);
 
// Liberar memória
imagedestroy($imagemCaptcha);

?>