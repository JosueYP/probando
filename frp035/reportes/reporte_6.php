<?php
require('mc_table.php'); require('nivelesRiesgo.php');

class PDF extends PDF_MC_Table{
	// Cabecera de página
	function Header()
	{   
		$this->SetFont('Arial','',12);
        $this->Cell(70,10, utf8_decode($_GET['nombreCentro']),0,0,'L');
		$this->SetXY(175,11);
		$this->Cell(70,5,date("d")."/".date("m")."/".date("Y"),0,1,'L');
	    $this->Ln(5);

		// Título
		$this->SetFont('Arial','B',13);
        $this->Cell(70,10, 'Empleados que requieren valoracion -  Guia 1' ,0,1,'L');
        
        //NUEVA SECCION ******************
		$align = array('', '');		
		//Inserto la fila en la cabecera del reporte
        $this->SetFont('Arial','B',10);
        $contenidoFila = array( utf8_decode($_GET['nombreProceso']) , "Todos los departamentos del centro de trabajo" );
        $this->FancyRow($contenidoFila, $align, false, array(93, 93));

		$this->Ln(5);
	}

	// Pie de página
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
$claveProceso = $_GET['claveProceso']; $claveCentro = $_GET['claveCentro']; 
$nombreProceso = $_GET['nombreProceso']; $nombreCentro = $_GET['nombreCentro']; 
$claveEmpresa = $_GET['claveEmpresa'];
   
$consulta = "call getEmpsRequierenAtencionMedica__(".$claveProceso.", '".$claveCentro."', 'r_".$claveEmpresa."')";

$resultado = $mysqli->query($consulta);
$numEmps = $resultado->num_rows;

if($numEmps == 0){
    $pdf->SetFont('Arial','',11); 
    $pdf->MultiCell(190,5, utf8_decode('En base a los resultados recabados hasta el momento, ninguno de los empleados del centro de trabajo "'.$nombreCentro.'" que realizaron la Guia 1 del proceso "'.$nombreProceso.'" requiere atención medica.'),0,'L',0);

}else{
    //Paso 3: Agrego los datos a la tabla
    $pdf->SetFont('Arial','B',11); 
    $pdf->setFillColor(230,230,230); 
    $pdf->Cell(10,5, "#",1,0,'L', 1);
    $pdf->Cell(25,5, "Depto.",1,0,'L', 1);
    $pdf->Cell(30,5, "Matricula",1,0,'L', 1);
    $pdf->Cell(90,5, "Nombre del empleado",1,0,'L', 1);
    $pdf->Cell(30,5, "Fecha encuesta",1,1,'C', 1);
    $pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

    $i=1;
    while($row = $resultado->fetch_assoc()){
        $pdf->Cell(10,5, $i,1,0,'L', 0);
        $pdf->Cell(25,5, $row[claveDepto],1,0,'L', 0);
        $pdf->Cell(30,5, $row[matricula],1,0,'L', 0);
        $pdf->Cell(90,5, utf8_decode($row[nombreEmpleado]) ,1,0,'L', 0);
        $pdf->Cell(30,5,date("d/m/Y", strtotime($row[fecha])),1,1,'C', 0);

        $i++;
    }
}

$pdf->Ln(5);

$pdf->Output('D', 'Empleados que requieren valoracion Guia 1 - '.$nombreCentro.' - '.$nombreProceso.'.pdf');

?>