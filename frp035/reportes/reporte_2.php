<?php
require('mc_table.php'); require('nivelesRiesgo.php');

class PDF extends PDF_MC_Table{
	function Header(){   
		$this->SetFont('Arial','',12);
        $this->Cell(70,10, utf8_decode($_GET['nombreCentro']),0,0,'L');
		$this->SetXY(175,11);
		$this->Cell(70,5,date("d")."/".date("m")."/".date("Y"),0,1,'L');
	    $this->Ln(5);

		// Título
		$this->SetFont('Arial','B',13);
		$this->Cell(70,10, 'Distribucion de empleados por nivel de riesgo final de cada departamento - Guia '. $_GET['numGuia'] ,0,1,'L');
		$this->Line(15,31,200,31);

		//Inserto el nombre del proceso de encuestas:
		$this->SetFont('Arial','',12);
		$this->Cell(70,10, utf8_decode($_GET['nombreProceso']),0,1,'L');
		$this->Ln(3);
	}

	function Footer(){
	    $this->SetY(-15);
	    $this->SetFont('Arial','',9);
	    $this->Cell(0,10, utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
	}

	function FancyRow($data, $align=array(), $fondoGris, $widthCols=array(), $style=array(), $maxline=array()){
        //Calculate the height of the row
        $nb = 0;
        for($i=0; $i<count($data); $i++) {
            $nb = max($nb, $this->NbLines($widthCols[$i],$data[$i]));
        }

        if (count($maxline)) {
            $_maxline = max($maxline);
            if ($nb > $_maxline) {
                $nb = $_maxline;
            }
        }
        $h = 5*$nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);

        //Dibuja las celdas de la fila
        for($i=0;$i<count($data);$i++) {
            //$w=$this->widths[$i];      //width[]
            $w = $widthCols[$i];
            // Alineamiento del texto
            $a = isset($align[$i]) ? $align[$i] : 'L';
            // maxline
            $m = isset($maxline[$i]) ? $maxline[$i] : false;
            //Guardo la posicion actual
            $x = $this->GetX();
            $y = $this->GetY();

            //Dependiendo el texto de la celda, la pintare de un color:
            if($fondoGris == true)
                $this->setFillColor(230,230,230); 
            else{
                //if($data[$i] === "X")
                  //  $this->SetFillColor(251, 164, 255);
                //else
                    $this->SetFillColor(255, 255, 255);
            }
            //Dibujo el rectangulo, que sera la celda
            $this->Rect($x,$y,$w,$h, 'DF');

            // Establezco el estilo del texto
            if (isset($style[$i])) {
                $this->SetFont('', $style[$i]);
            }
            $this->MultiCell($w, 5, $data[$i], 0, $a, 0, $m);
            //Pongo la posicion a la derecha de la celda
            $this->SetXY($x+$w, $y);
        }
        //Voy a la siguiente linea
        $this->Ln($h);
    }
}

require('../cn.php');

$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(15, 10, 5);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);

//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $claveCentro = $_GET['claveCentro'];
$claveEmpresa = $_GET['claveEmpresa'];

//Defino los limites dependiendo del numero de Guia
if($numGuia == 2){
    $l1 = 20; $l2 = 45; $l3 = 70; $l4 = 90; 
}else{
    $l1 = 50; $l2 = 75; $l3 = 99; $l4 = 140; 
}
   
$consulta = "call getEmpsPorNivelRiesgoFinalByDepto__(".$numGuia.", ".$claveProceso.", '".$claveCentro."', ".$l1.", ".$l2.", ".$l3.", ".$l4.", 'r_".$claveEmpresa."');";
	
//Paso 2: Hago y ejecuto la quuery para obtener los datos <<<<<<<<<<
$resultado = $mysqli->query($consulta);

//Paso 3: Agrego los datos a la tabla
$pdf->SetFont('Arial','B',11); 
$pdf->setFillColor(230,230,230); 
$pdf->Cell(20,5, "Clave",1,0,'L', 1);
$pdf->Cell(70,5, "Departamento",1,0,'L', 1);
$pdf->Cell(13,5, "Nulo",1,0,'C', 1);
$pdf->Cell(13,5, "Bajo",1,0,'C', 1);
$pdf->Cell(13,5, "Medio",1,0,'C', 1);
$pdf->Cell(13,5, "Alto",1,0,'C', 1);
$pdf->Cell(20,5, "Muy alto",1,0,'C', 1);
$pdf->Cell(25,5,"Encuestados",1,1,'C', 1);

$pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

$align = array('', '', 'C', 'C', 'C', 'C', 'C', 'C');

while($row = $resultado->fetch_assoc()){
	$contenidoFila = array($row[claveDepto], utf8_decode($row[nombreDepto]), $row[nulo], $row[bajo], $row[medio], $row[alto], $row[muy_alto], $row[numEncuestadosDepto]);
    $pdf->FancyRow($contenidoFila, $align, false, array(20, 70, 13, 13, 13, 13, 20, 25));

	/*
	$pdf->Cell(20,5, $row[claveDepto],1,0,'C', 0);
	$pdf->Cell(70,5, utf8_decode($row[nombreDepto]),1,0,'L', 0);
	$pdf->Cell(13,5, $row[nulo],1,0,'C', 0);
    $pdf->Cell(13,5, $row[bajo],1,0,'C', 0);
    $pdf->Cell(13,5, $row[medio],1,0,'C', 0);
    $pdf->Cell(13,5, $row[alto],1,0,'C', 0);
    $pdf->Cell(20,5, $row[muy_alto],1,0,'C', 0);
    $pdf->Cell(25,5,$row[numEncuestadosDepto],1,1,'C', 0);
	*/
}

$pdf->Ln(5);

$pdf->Output('D', 'Empleados por nivel de riesgo final de cada depto.pdf');
?>