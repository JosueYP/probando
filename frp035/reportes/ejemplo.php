<?php

require('mc_table.php'); require('nivelesRiesgo.php');


class PDF extends PDF_MC_Table
{
	// Cabecera de página
	function Header()
	{   
		
	}

	function Footer(){
	    $this->SetY(-15);
	    $this->SetFont('Arial','',9);
	    $this->Cell(0,10, utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
	}

   
}


$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(15, 10, 5);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);


//$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFillColor(251, 164, 255);
$pdf->Rect(15, 10, 180, 34, 'F');

$pdf->Ln(5);


$pdf->Output();

?>