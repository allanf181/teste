<?php
require('force_justify.php');


// Personalizada por JP

class PDF extends PDFj
{
    var $titulo="";
    var $titulo2="";
    var $cabecalho="";
    var $rodape="";
    
// Page header
function Header()
{
    // Logo
    $this->Image($this->logo,$this->A,$this->L,$this->C);
    // Arial bold 15
    $this->SetFont('Arial','B',12);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(100,15,  $this->cabecalho,0,0,'C');
    // Page title
    $this->Text(90,20, $this->titulo);
    $this->Text(90,25, $this->titulo2);
    // Line break
    $this->Ln(20);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,utf8_decode($this->rodape),0,0,'C');
}
}

?>
