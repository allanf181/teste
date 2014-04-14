<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

$x = 0;
$y = 0;
$x2 = 0;
$y2 = 0;
$TempSrc = '';

if (isset($_POST)) {
    $DestinationDirectory = sys_get_temp_dir() . "/"; //diretorio temporario
    if (!empty($_POST['codigo'])) {
        $codigo = $_POST['codigo'];
    }

    if (!$_POST['filePath']) {
        if (!isset($_FILES['ImageFile']) || !is_uploaded_file($_FILES['ImageFile']['tmp_name'])) {
            die('Ocorreu algum problema ao carregar o arquivo! Tente novamente!');
        }

        $ImageName = str_replace(' ', '-', strtolower($_FILES['ImageFile']['name']));
        //$ImageSize = $_FILES['ImageFile']['size'];
        $TempSrc = $_FILES['ImageFile']['tmp_name'];
        $ImageType = $_FILES['ImageFile']['type'];

        if (strripos($ImageName, '.zip') !== false) { // Verificando se foi zipado.
            $zip = new ZipArchive;
            if ($zip->open($TempSrc) === TRUE) {
                $res = $zip->extractTo($DestinationDirectory);
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    $ImageName1 = $stat['name'];
                    $TempSrc = $DestinationDirectory . $stat['name'];
                    $info = new SplFileInfo($ImageName1);
                    $prontuario = $info->getBasename('.' . $info->getExtension());
                    $codigo = '';

                    if (strripos($info->getExtension(), 'png') !== false) {
                        $ImageType = 'image/png';
                    }
                    if (strripos($info->getExtension(), 'gif') !== false) {
                        $ImageType = 'image/gif';
                    }
                    if (strripos($info->getExtension(), 'jpeg') !== false || strripos($info->getExtension(), 'jpg') !== false) {
                        $ImageType = 'image/jpeg';
                    }
                    createImage($ImageName1, $ImageType, $TempSrc, $codigo, $prontuario);
                }
                $zip->close();
                unlink($ImageName);
            } else {
                $TEXTO = "Problema ao descompactar o arquivo $ImageName. Verifique as permiss&otilde;es de $tmp.<br>";
            }
            if (!$res) {
                $TEXTO = "Problema ao descompactar o arquivo $ImageName. Verifique as permiss&otilde;es de $tmp.<br>";
            }
        } else {
            if (!$codigo) {
                $info = new SplFileInfo($ImageName);
                $prontuario = $info->getBasename('.' . $info->getExtension());
                createImage($ImageName, $ImageType, $TempSrc, $codigo, $prontuario);
            } else {
                createImage($ImageName, $ImageType, $TempSrc, $codigo);
            }
        }
    } else { // A imgagem veio por path, sem input com acao de usuario.
        $ImageName = str_replace(' ', '-', strtolower($_POST['fileName']));
        //$ImageSize = $_FILES['ImageFile']['size'];
        $TempSrc = $_POST['filePath'];
        $ImageType = $_POST['fileType'];
        $info = new SplFileInfo($ImageName);
        $x = $_POST['x_axis'];
        $y = $_POST['y_axis'];
        $x2 = $_POST['x2_axis'];
        $y2 = $_POST['y2_axis'];
        createImage($ImageName, $ImageType, $TempSrc, $codigo);
    }
}

function createImage($ImageName, $ImageType, $TempSrc, $codigo, $prontuario) {
    ############ Edit settings ##############
    $BigImageMaxSize = 300; //Image Maximum height or width
    $DestinationDirectory = sys_get_temp_dir() . "/"; //specify upload directory ends with / (slash)
    $Quality = 50; //jpeg quality
    ##########################################
    // Random number will be added after image name
    $RandomNumber = rand(0, 9999999999);

    //Let's check allowed $ImageType, we use PHP SWITCH statement here
    switch (strtolower($ImageType)) {
        case 'image/png':
            //Create a new image from file 
            $CreatedImage = imagecreatefrompng($TempSrc);
            break;
        case 'image/gif':
            $CreatedImage = imagecreatefromgif($TempSrc);
            break;
        case 'image/jpeg':
        case 'image/pjpeg':
            $CreatedImage = imagecreatefromjpeg($TempSrc);
            break;
        default:
            die('Tipo de imagem sem suporte!'); //output error and exit
    }

    $TMPImageName = $ImageName; //PARA APAGAR DO LIXO DEPOIS
    //PHP getimagesize() function returns height/width from image file stored in PHP tmp folder.
    //Get first two values from image, width and height. 
    //list assign svalues to $CurWidth,$CurHeight
    list($CurWidth, $CurHeight) = getimagesize($TempSrc);

    //Get file extension from Image name, this will be added after random name
    $ImageExt = substr($ImageName, strrpos($ImageName, '.'));
    $ImageExt = str_replace('.', '', $ImageExt);

    //remove extension from filename
    $ImageName = preg_replace("/\\.[^.\\s]{3,4}$/", "", $ImageName);

    //Construct a new name with random number and extension.
    $NewImageName = $ImageName . '-' . $RandomNumber . '.' . $ImageExt;

    //set the Destination Image
    $DestRandImageName = $DestinationDirectory . $NewImageName; // Image with destination directory
    //Resize image to Specified Size by calling resizeImage function.
    if (resizeImage($CurWidth, $CurHeight, $BigImageMaxSize, $DestRandImageName, $CreatedImage, $Quality, $ImageType)) {
        // Insert info into database table!
        $instr = fopen($DestinationDirectory . $NewImageName, "rb");
        $image = addslashes(fread($instr, filesize($DestinationDirectory . $NewImageName)));

        global $ALUNO;
        if ($_POST['filePath'])
            if (in_array($ALUNO, $_SESSION["loginTipo"]))
                $sqlAdd = ",bloqueioFoto=(SELECT i.bloqueioFoto FROM Instituicoes i)";

        if ($codigo)
            $sql = "UPDATE Pessoas SET foto=(\"" . $image . "\") $sqlAdd WHERE codigo = $codigo";

        if ($prontuario)
            $sql = "UPDATE Pessoas SET foto=(\"" . $image . "\") WHERE prontuario = '$prontuario'";

        //LIMPANDO LIXO
        global $TempSrc;
        unlink($TempSrc);
        unlink($DestinationDirectory . $NewImageName);

        if ($prontuario)
            unlink($DestinationDirectory . $TMPImageName);
        $res = mysql_query($sql) or die("Erro:" . mysql_error());

        if ($_POST['filePath']) { // se filePath � pq veio do trocaFoto, fecha a janela aberta.
            print "<h1>Foto foi adicionada com sucesso.</h1>";
            print "<img alt=\"foto\" style=\"width: 150px; height: 150px\" src=\"".INC."/file.inc.php?type=pic&time=".time()."&id=".crip($codigo)." />";
        }

        if ($prontuario)
            print "Foto inserida com sucesso para: $prontuario<br>";
        if ($codigo)
            print "<img style=\"width: 20px; height: 20px\" src=\"" . INC . "/file.inc.php?type=pic&id=" . crip($codigo) . "&timestamp=" . rand(0, 100000) . "\">";
    }else {
        die("Resize Error: $CurWidth,$CurHeight,$BigImageMaxSize,$DestRandImageName,$CreatedImage,$Quality,$ImageType"); //output error
    }
}

// This function will proportionally resize image 
function resizeImage($CurWidth, $CurHeight, $MaxSize, $DestFolder, $SrcImage, $Quality, $ImageType) {
    //Check Image size is not 0
    if ($CurWidth <= 0 || $CurHeight <= 0) {
        return false;
    }

    //Construct a proportional size of new image
    $ImageScale = min($MaxSize / $CurWidth, $MaxSize / $CurHeight);
    $NewWidth = ceil($ImageScale * $CurWidth);
    $NewHeight = ceil($ImageScale * $CurHeight);
    $NewCanves = imagecreatetruecolor($NewWidth, $NewHeight);

    // Resize Image
    if (imagecopyresampled($NewCanves, $SrcImage, 0, 0, 0, 0, $NewWidth, $NewHeight, $CurWidth, $CurHeight)) {

        // Verificando se foi selecionada uma area especifica da foto.
        global $x, $y, $x2, $y2;
        if ($x2 || $y2) {
            $ratio = ($MaxSize / $NewWidth);
            $nw = ceil($NewWidth * $ratio);
            $nh = ceil($NewHeight * $ratio);

            $nimg = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($nimg, $NewCanves, 0, 0, $x, $y, $nw, $nh, $x2 - $x, $y2 - $y);
            $NewCanves = $nimg;
        }

        // ADICIONANDO LOGO NA IMAGEM	
        $logo = imagecreatefromgif("../$IMAGES/ifsp.gif");
        $logo_x = imagesx($logo);
        $logo_y = imagesy($logo);
        imagecopymerge($NewCanves, $logo, 0, ($NewHeight - $logo_y), 0, 0, $logo_x, $logo_y, 100);

        switch (strtolower($ImageType)) {
            case 'image/png':
                imagepng($NewCanves, $DestFolder);
                break;
            case 'image/gif':
                imagegif($NewCanves, $DestFolder);
                break;
            case 'image/jpeg':
            case 'image/pjpeg':
                imagejpeg($NewCanves, $DestFolder, $Quality);
                break;
            default:
                return false;
        }
        //Destroy image, frees memory	
        if (is_resource($NewCanves)) {
            imagedestroy($NewCanves);
        }
        return true;
    }
}
