<?php
require('fpdf.php');


// Personalizada por JP

class PDF extends FPDF
{
    var $titulo="";
    var $cabecalho="";
    var $rodape="";
    
    var $angle=0;
    
    function RotatedText($x,$y,$txt,$angle)
{
    //Text rotated around its origin
    $this->Rotate($angle,$x,$y);
    $this->Text($x,$y,$txt);
    $this->Rotate(0);
}
    
    function Rotate($angle,$x=-1,$y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}

function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}

// Page header
function Header()
{
    // Logo
//    $this->Image('../images/logo.png',12,12,50);
    // Arial bold 15
//    $this->SetFont('Arial','B',15);
//    // Move to the right
//    $this->Cell(80);
//    // Title
//    $this->Cell(100,15,  $this->cabecalho,0,0,'C');
//    // Page title
//    $this->Text(90,40, $this->titulo);
//    // Line break
//    $this->Ln(20);
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
