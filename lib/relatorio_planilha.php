<?php
require dirname(__FILE__).'/../inc/config.inc.php';
require 'PHPExcel/PHPExcel.php';

// Instanciation of inherited class
$objPHPExcel = new PHPExcel();

if (!$linha2) die ('Sem dados para gerar a lista.');

$objPHPExcel->getProperties()->setCreator("WebDiario");
$objPHPExcel->getProperties()->setLastModifiedBy("WebDiario");
$objPHPExcel->getProperties()->setTitle($titulo);
$objPHPExcel->getProperties()->setSubject($titulo2);
$objPHPExcel->getProperties()->setDescription($SITE_TITLE);

$j=1;
foreach(range('A', 'Z') as $letra) {
    $colXls[$j] = $letra;
    $j++;
}

$j=1;
foreach ($titulosColunas as $coluna => $value) {
    $objPHPExcel->getActiveSheet()->getStyle("$colXls[$j]1")->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("$colXls[$j]1", $value );
    $objPHPExcel->getActiveSheet()->getColumnDimension($colXls[$j])->setWidth($largura[$coluna]);
    $j++;
}

if (strlen($titulo) > 31) {
    if (strlen($titulo2) > 31) {
        $objPHPExcel->getActiveSheet()->setTitle('Dados');
    } else {
        $objPHPExcel->getActiveSheet()->setTitle($titulo2);
    }
} else {
    $objPHPExcel->getActiveSheet()->setTitle($titulo);
}


$i=2;
$j=0;
foreach ($linha2 as $linha) {
    $j=0;
    foreach ($colunas as $coluna => $value) {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, utf8_decode($linha[$value]));
        $j++;
    }
    $i++;
}


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$titulo);
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

$objWriter->save('php://output'); 

exit;
?>
