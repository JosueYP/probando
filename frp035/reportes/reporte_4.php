<?php
require('mc_table.php'); require('nivelesRiesgo.php');

//Defino los valores de las variables que usare:
$nombreCentro = utf8_decode($_GET['nombreCentro']);

class PDF extends PDF_MC_Table{
	// Cabecera de página
	function Header(){   
		$this->SetFont('Arial','',11);
        $this->Cell(70,10, utf8_decode($_GET['nombreCentro']),0,0,'L');
		$this->SetXY(175,11);
		$this->Cell(70,5,date("d")."/".date("m")."/".date("Y"),0,1,'L');
	    $this->Ln(5);

        //Defino el tirulo que tendra el reporte
        if($_GET['tipoRep'] == 1)
            $tituloReporte = 'Empleados del centro de trabajo que realizaron la Guia '. $_GET['numGuia']; 
        else if($_GET['tipoRep'] == 2)
            $tituloReporte = 'Empleados del centro de trabajo que no han realizado la Guia '. $_GET['numGuia']; 

		// Título
		$this->SetFont('Arial','B',13);
        //Dependiendo del tipo de reporte, es el titulo que tendra la pagina
        $this->Cell(70,10, $tituloReporte ,0,1,'L');

        $align = array('', '');
		if($_GET['tipoFiltro'] == 1)
			$tipoDatos = "Todos los departamentos del centro de trabajo";
		else if($_GET['tipoFiltro'] == 2)
			$tipoDatos = utf8_decode($_GET['claveDepto'] .' - '. $_GET['nombreDepto']);
		
		$align = array('', '');		
		//Inserto la fila en la cabecera del reporte
        $this->SetFont('Arial','B',10);
        $contenidoFila = array( utf8_decode($_GET['nombreProceso']) , $tipoDatos);
        $this->FancyRow($contenidoFila, $align, false, array(93, 93));

        $this->Ln(5);
	}

	// Pie de página
	function Footer()
	{
	    // Posición: a 1,5 cm del final
	    $this->SetY(-15);
	    // Arial italic 8
	    $this->SetFont('Arial','',9);
	    // Número de página
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
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto'];
   
//Defino el tirulo que tendra el reporte
if($_GET['tipoRep'] == 1)
    $tituloReporte = 'Empleados del centro de trabajo que realizaron la Guia '. $_GET['numGuia']; 
else if($_GET['tipoRep'] == 2)
    $tituloReporte = 'Empleados del centro de trabajo que no han realizado la Guia '. $_GET['numGuia']; 

/*
    tipoRep => 1: Emps. que Ya la hicieron, 2: Emps. que No la han hecho
    tipoFiltro => 1: Todos los deptos., 2: Emps. de un solo depto
*/ 

if($_GET['tipoFiltro'] == 2){
    //Si busco los empleados que NO han hecho la encuesta, agrego esto:
    $condicion1 = " claveDepto like '".$claveDepto."' and ";
}

if($_GET['tipoRep'] == 2){
    //Si busco los empleados que NO han hecho la encuesta, agrego esto:
    $condicion2 = " not ";
}

$consulta = "select claveDepto, matricula, nombreEmpleado, (select fecha from r_".$_GET['claveEmpresa']." 
                                                            where numGuia = ".$numGuia." and claveProceso = ".$claveProceso." and matricula like m.matricula limit 1) as fecha
            from empleados as m
            where claveCentro like '".$claveCentro."' and ".$condicion1." matricula ".$condicion2." in (SELECT distinct matricula 
            FROM r_".$_GET['claveEmpresa']." 
            where numGuia = ".$numGuia." and claveProceso = ".$claveProceso.")";

//Paso 2: Hago y ejecuto la quuery para obtener los datos <<<<<<<<<<

$resultado = $mysqli->query($consulta);

//Paso 3: Agrego los datos a la tabla
$pdf->SetFont('Arial','B',11); 
$pdf->setFillColor(230,230,230); 
$pdf->Cell(10,5, "#",1,0,'L', 1);
$pdf->Cell(20,5, "Depto.",1,0,'L', 1);
$pdf->Cell(25,5, "Matricula",1,0,'L', 1);
$pdf->Cell(100,5, "Nombre del empleado",1,0,'L', 1);
$pdf->Cell(30,5, "Fecha",1,1,'C', 1);

$pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

$i=1;
while($row = $resultado->fetch_assoc()){
    $pdf->Cell(10,5, $i,1,0,'L', 0);
    $pdf->Cell(20,5, utf8_decode($row[claveDepto]),1,0,'L', 0);
    $pdf->Cell(25,5, utf8_decode($row[matricula]),1,0,'L', 0);
    $pdf->Cell(100,5, utf8_decode($row[nombreEmpleado]),1,0,'L', 0);

    if($row[fecha] == null)
        $pdf->Cell(30,5, "-",1,1,'C', 0);
    else
        $pdf->Cell(30,5,date("d/m/Y", strtotime($row[fecha])),1,1,'C', 0);

    $i++;
}

$pdf->Ln(5);

$pdf->Output('D', $tituloReporte.' - '.$nombreCentro.'.pdf');

?>