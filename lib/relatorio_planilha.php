<?php

require dirname(__FILE__) . '/../inc/config.inc.php';
require 'PHPExcel/PHPExcel.php';

// Instanciation of inherited class
$objReader = PHPExcel_IOFactory::createReader('Excel5');
$objPHPExcel = $objReader->load("/var/www/academico/lib/PHPExcel/templateIFSP.xls");

if (!$linha2)
    die('Sem dados para gerar a lista.');

$objPHPExcel->getProperties()->setCreator("WebDiario");
$objPHPExcel->getProperties()->setLastModifiedBy("WebDiario");
$objPHPExcel->getProperties()->setTitle($titulo);
$objPHPExcel->getProperties()->setSubject($titulo2);
$objPHPExcel->getProperties()->setDescription($SITE_TITLE);

$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $SITE_TITLE . '&RPage &P of &N');

$j = 1;
foreach (range('A', 'Z') as $letra) {
    $colXls[$j] = $letra;
    $j++;
}

$j = 1;
foreach ($titulosColunas as $coluna => $value) {
    $objPHPExcel->getActiveSheet()->getStyle("$colXls[$j]2")->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("$colXls[$j]2", $value);
    $objPHPExcel->getActiveSheet()->getColumnDimension($colXls[$j])->setWidth($largura[$coluna]);
    $objPHPExcel->getActiveSheet()->getStyle("$colXls[$j]")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
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

$i = 3;
foreach ($linha2 as $linha) {
    $j = 0;
    $k=1;
    foreach ($colunas as $coluna => $value) {
        if ($i % 2 == 0)
            $objPHPExcel->getActiveSheet()->getStyle("$colXls[$k]$i")->getFill()->applyFromArray(
                    array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array('rgb' => 'E9E9E9'),
                        'endcolor' => array('rgb' => 'E9E9E9')
                    )
            );
        $k++;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, $linha[$value]);
        $j++;
    }
    $i++;
}

if ($orientacao == 'L') {
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $titulo);
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

$objWriter->save('php://output');

exit;
?>
