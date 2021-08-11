<?php
require('fpdf.php'); require('nivelesRiesgo.php');

//NOTA: Esta pagona es SOLO para poder generar el Reporte 1 en sus 3 variantes

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{   
		$this->SetFont('Arial','',11);
        $this->Cell(70,10, utf8_decode($_GET['nombreCentro']),0,0,'L');
		$this->SetXY(175,11);
		$this->Cell(70,5,date("d")."/".date("m")."/".date("Y"),0,1,'L');
	    $this->Ln(5);

		// Título
		$this->SetFont('Arial','B',13);
		$this->Cell(70,10, 'Niveles de riesgo final de empleados - Guia '. $_GET['numGuia'] ,0,1,'L');
	
        //Inserto la linea
		$this->Line(15,31,200,31);

        $this->SetFont('Arial','',12);
        $this->Cell(70,10, utf8_decode($_GET['nombreProceso']),0,1,'L');

        $this->SetFont('Arial','',11);

        if($_GET['tipoRep'] == 1){
            $this->Cell(70,10, 'Todos los departamentos del centro de trabajo' ,0,1,'L');
            $this->Ln(3);
        }else if($_GET['tipoRep'] == 2){
            $this->Cell(70,10, $_GET['claveDepto'] .' - '. $_GET['nombreDepto'],0,1,'L');
            $this->Ln(3);
        }
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
}

require('../cn.php');

$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(15, 10, 5);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);

//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto'];
   
if($_GET['tipoRep'] == 2){
    //Agrego al query el codigo para que sea solo por Depto:
    $condicion = "claveDepto like '".$claveDepto."' and";
}

//Esta consulta me da la calificacion final de cada uno de los empleados de Todos los departamentos
$consulta = "select claveDepto, matricula, nombreEmpleado, (select sum(respu) from r_".$_GET['claveEmpresa']." 
            where numGuia = ".$numGuia." and claveProceso = ".$claveProceso." and bloque > 0 and matricula like m.matricula) as calif
            from empleados as m
            where claveCentro like '".$claveCentro."' and ".$condicion." matricula in (SELECT distinct matricula 
                    FROM r_".$_GET['claveEmpresa']." 
                    where numGuia = ".$numGuia." and claveProceso = ".$claveProceso.") order by claveDepto, matricula";
	
//Paso 2: Hago y ejecuto la quuery para obtener los datos <<<<<<<<<<

$resultado = $mysqli->query($consulta);


//Paso 3: Agrego los datos a la tabla
$pdf->SetFont('Arial','B',11); 
$pdf->setFillColor(230,230,230); 
$pdf->Cell(10,5, "#",1,0,'L', 1);
$pdf->Cell(20,5, "Depto.",1,0,'L', 1);
$pdf->Cell(25,5, "Matricula",1,0,'L', 1);
$pdf->Cell(85,5, "Nombre del empleado",1,0,'L', 1);
$pdf->Cell(15,5, "Calif.",1,0,'C', 1);
$pdf->Cell(30,5, "Nivel riesgo",1,1,'C', 1);

$pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

$i=1;

while($row = $resultado->fetch_assoc()){
    $pdf->Cell(10,5, $i,1,0,'L', 0);
    $pdf->Cell(20,5, $row[claveDepto],1,0,'L', 0);
    $pdf->Cell(25,5, $row[matricula],1,0,'L', 0);
    $pdf->Cell(85,5, utf8_decode($row[nombreEmpleado]),1,0,'L', 0);
    $pdf->Cell(15,5, $row[calif],1,0,'C', 0);

    //Obtengo el nivel de riesgo en base a la calificacion
	$nivelRiesgo = getNivelRiesgo("Final", 0, $row[calif], $numGuia);

	//Dependiendo de lo que me devuelva la funcion, es el color del que pintare la celda
	$color = getColorNivelRiesgo($nivelRiesgo);
	$pdf->setFillColor($color[0], $color[1], $color[2]); 
    $pdf->Cell(30,5,$nivelRiesgo,1,1,'C', 1);
    $i++;
}

$pdf->Ln(5);

$pdf->Output('D', 'Niveles de riesgo final de empleados - Guia '.$numGuia.'.pdf');
?>