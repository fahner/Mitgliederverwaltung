<?php

require('fpdf/fpdf.php');
  
class PDF extends FPDF {

  var $headertitle;
  
  //Kopfzeile
  function Header() {
    $this->Image('images/sck-logo.jpg',10,8,20);
    $this->SetFont('Arial','B',12);
    $this->Cell(25);
    $this->Cell(30,6,'SC 1910 Käfertal e.V. - Abt. Boule');
    $this->Ln(8);
    $this->SetFont('Arial','B',16);
    $this->Cell(25);
    $w = $this->GetStringWidth($this->headertitle);
    $this->Cell($w+2,5,$this->headertitle);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,5,'(Stand: '.date('d.m.Y').')');
    $this->Ln(15);
  }

  //Fusszeile
  function Footer() {
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Seite '.$this->PageNo().' von {nb}',0,0,'C');
  }
  
  function setHeadertitle($title) {
    $this->headertitle = $title;
  }
  
  function Table($header, $data, $width) {
    $this->SetFillColor(0,0,180);
    $this->SetTextColor(255);
    $this->SetFont('Arial','B',10);
    for($i=0;$i<count($header);$i++)
      $this->Cell($width[$i],7,$header[$i],1,0,'L',1);
    $this->Ln();
    $this->SetFillColor(176,196,255);
    $this->SetTextColor(0);
    $this->SetFont('Arial','',10);
    $fill = 1;
    foreach($data as $row) {
      for($i=0; $i<count($row)/2; $i++) {
        $this->Cell($width[$i],5,$row[$i],1,0,'L',$fill);
      }
      $this->Ln();
      $fill = !$fill;
    }
  }
}


?>