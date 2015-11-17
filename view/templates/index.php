<?php
require_once '../../lib/doctrine/vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('./');
$twig = new Twig_Environment($loader, array(
    'cache' => '/tmp',
));

echo $twig->render('child.html', array('name' => 'Fabien'));