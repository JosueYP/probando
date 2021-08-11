<?php
require('mc_table.php'); require('nivelesRiesgo.php');

class PDF extends PDF_MC_Table
{
	// Cabecera de página
	function Header()
	{   
		$this->SetFont('Arial','',12);
        $this->Cell(70,10, utf8_decode($_GET['nombreCentro']),0,0,'L');

        //Dependiendo del tipo de reporte, es la posicion que tendra la fecha:
        if($_GET['tipoRep'] == 1){
            $this->SetXY(175,11); $cat_dom = "Categoria";
        }else{
            $this->SetXY(245,11); $cat_dom = "Dominio";
        }
            
		$this->Cell(70,5,date("d")."/".date("m")."/".date("Y"),0,1,'L');
	    $this->Ln(5);

		// Título
		$this->SetFont('Arial','B',12);
        $this->Cell(70,10, 'Niveles de riesgo por '.$cat_dom.' de cada empleado -  Guia '.$_GET['numGuia'] ,0,1,'L');
        

        $align = array('', '');		
		//Inserto la fila en la cabecera del reporte
        $this->SetFont('Arial','B',10);

        //Agrego la etiqueta correcta al reporte (Todos los deptos o UN solo depto.)
        if($_GET['tipoFiltro'] == 1)
            $contenidoFila = array( utf8_decode($_GET['nombreProceso']) , 'Todos los departamentos del centro de trabajo');
        else if($_GET['tipoFiltro'] == 2){
            $contenidoFila = array( utf8_decode($_GET['nombreProceso']) , utf8_decode($_GET['claveDepto'] .' - '. $_GET['nombreDepto']));
        }

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
                if($data[$i] === "Nulo")
                    $this->SetFillColor(0, 255, 228);
                else if($data[$i] === "Bajo")
                    $this->SetFillColor(0, 255, 100);
                else if($data[$i] === "Medio")
                    $this->SetFillColor(255, 255, 75);
                else if($data[$i] === "Alto")
                    $this->SetFillColor(255, 194, 30);
                else if($data[$i] === "Muy alto")
                    $this->SetFillColor(255, 90, 41);
                else
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

if($_GET['tipoRep'] == 1)
    $posicion = 'P';
else
    $posicion = 'L';

$pdf = new PDF($posicion,'mm','Letter');
$pdf->SetMargins(15, 10, 5);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',11);

//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $claveCentro = $_GET['claveCentro']; $numGuia = $_GET['numGuia']; $claveDepto = $_GET['claveDepto']; 
$nombreProceso = $_GET['nombreProceso']; $nombreCentro = $_GET['nombreCentro']; $tipoFiltro = $_GET['tipoFiltro']; $claveEmpresa = $_GET['claveEmpresa'];
   
//Verifico que tipo de reporte es el que se quiere generar (Por Categoria o Dominio)
if($_GET['tipoRep'] == 1){

    $cat_dom = "Categoria";
    //******************** REPORTE POR CATEGORIA ***********************

    //Defino si el reporte va a ser de Todos los deptos. o de Un solo Depto.
    if($tipoFiltro == 1)
        //TODOS LOS DEPTOS
        $consulta = "call getRiesgoPorCategoriaByEmp__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', 'r_".$claveEmpresa."')";
    else if($tipoFiltro == 2){
        //UN SOLO DEPTO
        $consulta = "call getRiesgoPorCategoriaByDepto__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";
    }
        
    $resultado = $mysqli->query($consulta);

    //Dependiendo del numero de Guia, 2 o 3, es el numero de columnas que tendra la tabla
    if($numGuia == 2){
        //PRIMERO, agrego los titulos de cada una de las Categorias
        //$pdf->Ln(30);
        $pdf->SetFont('Arial','',10); 
        $pdf->Cell(92,5,"C1 - Ambiente de trabajo",0,0,'L', 0);
        $pdf->Cell(92,5,"C2 - Factores propios de la actividad",0,1,'L', 0);
        $pdf->Cell(92,5, utf8_decode("C3 - Organización del tiempo de trabajo"),0,0,'L', 0);
        $pdf->Cell(92,5,"C4 - Liderazgo y relaciones en el trabajo",0,1,'L', 0);
        $pdf->Ln(5);

        //Paso 3: Agrego los datos a la tabla
        $pdf->SetFont('Arial','B',11); 
        $pdf->setFillColor(230,230,230); 
        $pdf->Cell(10,5, "#",1,0,'L', 1);
        $pdf->Cell(20,5, "Depto.",1,0,'L', 1);
        $pdf->Cell(25,5, "Matricula",1,0,'L', 1);
        $pdf->Cell(70,5, "Nombre",1,0,'L', 1);
        $pdf->Cell(15,5, "C1",1,0,'L', 1);
        $pdf->Cell(15,5, "C2",1,0,'L', 1);
        $pdf->Cell(15,5, "C3",1,0,'L', 1);
        $pdf->Cell(15,5, "C4",1,1,'L', 1);
        $pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

        $align = array('', '', '', '', '', '', '', '');

        $i=1;
        while($row = $resultado->fetch_assoc()){
            //Obtengo cada uno de los Niveles de roesgo en base a la calificacion de ese Categoria:
            $nivel1 = getNivelRiesgo("Categoria", 1, $row[e1], 2); 
            $nivel2 = getNivelRiesgo("Categoria", 2, $row[e2], 2); 
            $nivel3 = getNivelRiesgo("Categoria", 3, $row[e3], 2); 
            $nivel4 = getNivelRiesgo("Categoria", 4, $row[e4], 2); 

            //Inserto la fila al reporte:
            $contenidoFila = array($i, $row[claveDepto], $row[matricula], utf8_decode($row[nombreEmpleado]), $nivel1, $nivel2, $nivel3, $nivel4);
            $pdf->FancyRow($contenidoFila, $align, false, array(10, 20, 25, 70, 15, 15, 15, 15));
            $i++;
        }

    }else if($numGuia == 3){
        $pdf->SetFont('Arial','',10); 
        $pdf->Cell(92,5,"C1 - Ambiente de trabajo",0,0,'L', 0);
        $pdf->Cell(92,5,"C2 - Factores propios de la actividad",0,1,'L', 0);
        $pdf->Cell(92,5, utf8_decode("C3 - Organización del tiempo de trabajo"),0,0,'L', 0);
        $pdf->Cell(92,5,"C4 - Liderazgo y relaciones en el trabajo",0,1,'L', 0);
        $pdf->Cell(92,5,"C5 - Entorno organizacional",0,1,'L', 0);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',11); 
        $pdf->setFillColor(230,230,230); 
        $pdf->Cell(10,5, "#",1,0,'L', 1);
        $pdf->Cell(20,5, "Depto.",1,0,'L', 1);
        $pdf->Cell(25,5, "Matricula",1,0,'L', 1);
        $pdf->Cell(55,5, "Nombre",1,0,'L', 1);
        $pdf->Cell(15,5, "C1",1,0,'L', 1);
        $pdf->Cell(15,5, "C2",1,0,'L', 1);
        $pdf->Cell(15,5, "C3",1,0,'L', 1);
        $pdf->Cell(15,5, "C4",1,0,'L', 1);
        $pdf->Cell(15,5, "C5",1,1,'L', 1);
        $pdf->SetFont('Arial','',11);

        $align = array('', '', '', '', '', '', '', '', '');

        $i=1;
        while($row = $resultado->fetch_assoc()){
            //Obtengo cada uno de los Niveles de roesgo en base a la calificacion de ese Categoria:
            $nivel1 = getNivelRiesgo("Categoria", 1, $row[e1], 3); 
            $nivel2 = getNivelRiesgo("Categoria", 2, $row[e2], 3); 
            $nivel3 = getNivelRiesgo("Categoria", 3, $row[e3], 3); 
            $nivel4 = getNivelRiesgo("Categoria", 4, $row[e4], 3);
            $nivel5 = getNivelRiesgo("Categoria", 5, $row[e5], 3); 

            //Inserto la fila al reporte:
            $contenidoFila = array($i, $row[claveDepto], $row[matricula], utf8_decode($row[nombreEmpleado]), $nivel1, $nivel2, $nivel3, $nivel4, $nivel5);
            $pdf->FancyRow($contenidoFila, $align, false, array(10, 20, 25, 55, 15, 15, 15, 15, 15));
            $i++;
        }
    }
}
else if($_GET['tipoRep'] == 2){

    //******************** REPORTE POR DOMINIO ***********************
    $cat_dom = "Dominio";

    //Defino si el reporte va a ser de Todos los deptos. o de Un solo Depto.
    if($tipoFiltro == 1)
        $consulta = "call getRiesgoPorDominioByEmp__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', 'r_".$claveEmpresa."')";
    else if($tipoFiltro == 2){
        $consulta = "call getRiesgoPorDominioByDepto__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";
    }
    
    $resultado = $mysqli->query($consulta);

    //Dependiendo del numero de Guia, 2 o 3, es el numero de columnas que tendra la tabla
    if($numGuia == 2){
        $pdf->SetFont('Arial','',10); 
        $pdf->Cell(92,5,"D1 - Condiciones en el ambiente de trabajo",0,0,'L', 0);
        $pdf->Cell(92,5,"D2 - Carga de trabajo",0,0,'L', 0);
        $pdf->Cell(92,5, utf8_decode("D3 - Falta de control sobre el trabajo"),0,1,'L', 0);
        $pdf->Cell(92,5,"D4 - Jornada trabajo",0,0,'L', 0);
        $pdf->Cell(92,5, utf8_decode("D5 - Interferencia en la relación trabajo-familia"),0,0,'L', 0);
        $pdf->Cell(92,5,"D6 - Liderazgo",0,1,'L', 0);
        $pdf->Cell(92,5,"D7 - Relaciones en el trabajo",0,0,'L', 0);
        $pdf->Cell(92,5, utf8_decode("D8 - Violencia"),0,1,'L', 0);
        $pdf->Ln(5);

        //Paso 3: Agrego los datos a la tabla
        $pdf->SetFont('Arial','B',11); 
        $pdf->setFillColor(230,230,230); 
        $pdf->Cell(10,5, "#",1,0,'L', 1);
        $pdf->Cell(20,5, "Depto.",1,0,'L', 1);
        $pdf->Cell(25,5, "Matricula",1,0,'L', 1);
        $pdf->Cell(70,5, "Nombre",1,0,'L', 1);
        $pdf->Cell(15,5, "D1",1,0,'L', 1);
        $pdf->Cell(15,5, "D2",1,0,'L', 1);
        $pdf->Cell(15,5, "D3",1,0,'L', 1);
        $pdf->Cell(15,5, "D4",1,0,'L', 1);
        $pdf->Cell(15,5, "D5",1,0,'L', 1);
        $pdf->Cell(15,5, "D6",1,0,'L', 1);
        $pdf->Cell(15,5, "D7",1,0,'L', 1);
        $pdf->Cell(15,5, "D8",1,1,'L', 1);
        $pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

        $align = array('', '', '', '', '', '', '', '', '', '', '', '');

        $i=1;
        while($row = $resultado->fetch_assoc()){
            //Obtengo cada uno de los Niveles de roesgo en base a la calificacion de ese Categoria:
            $nivel1 = getNivelRiesgo("Dominio", 1, $row[e1], 2); 
            $nivel2 = getNivelRiesgo("Dominio", 2, $row[e2], 2); 
            $nivel3 = getNivelRiesgo("Dominio", 3, $row[e3], 2); 
            $nivel4 = getNivelRiesgo("Dominio", 4, $row[e4], 2); 
            $nivel5 = getNivelRiesgo("Dominio", 5, $row[e5], 2); 
            $nivel6 = getNivelRiesgo("Dominio", 6, $row[e6], 2); 
            $nivel7 = getNivelRiesgo("Dominio", 7, $row[e7], 2); 
            $nivel8 = getNivelRiesgo("Dominio", 8, $row[e8], 2); 

            //Inserto la fila al reporte:
            $contenidoFila = array($i, $row[claveDepto], $row[matricula], utf8_decode($row[nombreEmpleado]), $nivel1, $nivel2, $nivel3, $nivel4, $nivel5, $nivel6, $nivel7, $nivel8);
            $pdf->FancyRow($contenidoFila, $align, false, array(10, 20, 25, 70, 15, 15, 15, 15, 15, 15, 15, 15));

            $i++;
        }

    }else if($numGuia == 3){
        $pdf->SetFont('Arial','',10); 
        $pdf->Cell(92,5,"D1 - Condiciones en el ambiente de trabajo",0,0,'L', 0);
        $pdf->Cell(92,5,"D2 - Carga de trabajo",0,0,'L', 0);
        $pdf->Cell(92,5, utf8_decode("D3 - Falta de control sobre el trabajo"),0,1,'L', 0);
        $pdf->Cell(92,5,"D4 - Jornada trabajo",0,0,'L', 0);
        $pdf->Cell(92,5, utf8_decode("D5 - Interferencia en la relación trabajo-familia"),0,0,'L', 0);
        $pdf->Cell(92,5,"D6 - Liderazgo",0,1,'L', 0);
        $pdf->Cell(92,5,"D7 - Relaciones en el trabajo",0,0,'L', 0);
        $pdf->Cell(92,5, utf8_decode("D8 - Violencia"),0,0,'L', 0);
        $pdf->Cell(92,5, utf8_decode("D9 - Reconocimiento del desempeño"),0,1,'L', 0);
        $pdf->Cell(92,5,"D10 - Insuficiente sentido de pertenencia e, inestabilidad",0,1,'L', 0);
        $pdf->Ln(5);

        //Agrego la tabla y sus datos
        $pdf->SetFont('Arial','B',11); 
        $pdf->setFillColor(230,230,230); 
        $pdf->Cell(10,5, "#",1,0,'L', 1);
        $pdf->Cell(15,5, "Depto.",1,0,'L', 1);
        $pdf->Cell(20,5, "Matricula",1,0,'L', 1);
        $pdf->Cell(55,5, "Nombre",1,0,'L', 1);
        $pdf->Cell(15,5, "D1",1,0,'L', 1);
        $pdf->Cell(15,5, "D2",1,0,'L', 1);
        $pdf->Cell(15,5, "D3",1,0,'L', 1);
        $pdf->Cell(15,5, "D4",1,0,'L', 1);
        $pdf->Cell(15,5, "D5",1,0,'L', 1);
        $pdf->Cell(15,5, "D6",1,0,'L', 1);
        $pdf->Cell(15,5, "D7",1,0,'L', 1);
        $pdf->Cell(15,5, "D8",1,0,'L', 1);
        $pdf->Cell(15,5, "D9",1,0,'L', 1);
        $pdf->Cell(15,5, "D10",1,1,'L', 1);
        $pdf->SetFont('Arial','',11);

        $align = array('', '', '', '', '', '', '', '', '', '', '', '');

        $i=1;
        while($row = $resultado->fetch_assoc()){
            //Obtengo cada uno de los Niveles de roesgo en base a la calificacion de ese Categoria:
            $nivel1 = getNivelRiesgo("Dominio", 1, $row[e1], 3); 
            $nivel2 = getNivelRiesgo("Dominio", 2, $row[e2], 3); 
            $nivel3 = getNivelRiesgo("Dominio", 3, $row[e3], 3); 
            $nivel4 = getNivelRiesgo("Dominio", 4, $row[e4], 3); 
            $nivel5 = getNivelRiesgo("Dominio", 5, $row[e5], 3); 
            $nivel6 = getNivelRiesgo("Dominio", 6, $row[e6], 3); 
            $nivel7 = getNivelRiesgo("Dominio", 7, $row[e7], 3); 
            $nivel8 = getNivelRiesgo("Dominio", 8, $row[e8], 3); 
            $nivel9 = getNivelRiesgo("Dominio", 9, $row[e9], 3); 
            $nivel10 = getNivelRiesgo("Dominio", 10, $row[e10], 3); 

            //Inserto la fila al reporte:
            $contenidoFila = array($i, $row[claveDepto], $row[matricula], utf8_decode($row[nombreEmpleado]), $nivel1, $nivel2, $nivel3, $nivel4, $nivel5, $nivel6, $nivel7, $nivel8, $nivel9, $nivel10);
            $pdf->FancyRow($contenidoFila, $align, false, array(10, 15, 20, 55, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15));

            $i++;
        }
    }
}

$pdf->Ln(5);

$pdf->Output('D', 'Niveles de riesgo por '.$cat_dom.' de cada empleado - Guia '.$numGuia.' - '.$nombreProceso.'.pdf');

?>