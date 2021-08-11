<?php
require('mc_table.php'); require('nivelesRiesgo.php');

class PDF extends PDF_MC_Table
{
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
        $this->Cell(70,10, 'Frecuencia de respuesta de cada pregunta -  Guia '.$_GET['numGuia'] ,0,1,'L');
        

        $align = array('', '');		
		//Inserto la fila en la cabecera del reporte
        $this->SetFont('Arial','B',10);

        $contenidoFila = array( utf8_decode($_GET['nombreProceso']) , 'Total de encuestados: '. $_GET['numEnc'] );
        $this->FancyRow($contenidoFila, $align, false, array(93, 93));  

        $this->SetFont('Arial','',10);

        //Agrego la etiqueta correcta al reporte (Todos los deptos o UN solo depto.)
        if($_GET['tipoRep'] == 1)
            $contenidoFila = array('Todos los departamentos del centro de trabajo');
        else if($_GET['tipoRep'] == 2){
            $contenidoFila = array(utf8_decode($_GET['claveDepto'] .' - '. $_GET['nombreDepto']));
        }

        $this->FancyRow($contenidoFila, $align, false, array(186));  
        
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

$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(15, 10, 5);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',11);

//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $claveCentro = $_GET['claveCentro']; $numGuia = $_GET['numGuia'];  $nombreCentro = $_GET['nombreCentro']; 
$claveDepto = $_GET['claveDepto'];  $tipoFiltro = $_GET['tipoFiltro']; $claveEmpresa = $_GET['claveEmpresa']; $nombreProceso = $_GET['nombreProceso']; 
   
//Verifico que tipo de reporte es el que se quiere generar (Por Categoria o Dominio)
if($numGuia == 1){
    /****************** FRECUENCIA DE RESPUESTAS DE LA GUIA 1 ******************/ 
    if($_GET['tipoRep'] == 2){
        //Si se va a generar el reporte por Deptos, agrego una condicion extra a la query:
        $condicionExtra = "and matricula in (select matricula from empleados 
                                            where claveCentro like '".$claveCentro."' and 
                                                claveDepto like '".$claveDepto."')";
    }

    //NOTA: Esta query es para obtener los datos de << TODOS LOS EMPLEADOS >> que hicieron la Guia 1 de ese proceso
    $consulta = "select numPreg, pregunta, bloque, titulo,
                    (select count(respu) from r_".$_GET['claveEmpresa']." where numPreg = p.numPreg and numGuia = 1 and respu = 1  ".$condicionExtra."  and claveProceso = ".$claveProceso." ) as si,
                    (select count(respu) from r_".$_GET['claveEmpresa']." where numPreg = p.numPreg and numGuia = 1 and respu = 0  ".$condicionExtra."  and claveProceso = ".$claveProceso.") as _no
                from preguntas as p where numGuia = 1";
    
    $resultado = $mysqli->query($consulta);

    $align = array('', '', 'C', 'C');
    
    $numBloque = 0;
    while($row = $resultado->fetch_assoc()){
        if($numBloque != $row[bloque]){
            $pdf->Ln(5);
            $pdf->SetFont('Arial','B',10); 
            //Inserto la fila de los nombre de las columnas:
            $contenidoFila = array("#", "Pregunta", "Si", "No");
            $pdf->FancyRow($contenidoFila, $align, true, array(8, 147, 15, 15));

            //Inserto el subitulo 
            $contenidoFila = array("", utf8_decode($row[bloque] .'. '. $row[titulo]), "", "");
            $pdf->FancyRow($contenidoFila, $align, false, array(8, 147, 15, 15));

            $numBloque = $row[bloque];

            if($numBloque == 1){
                $pdf->SetFont('Arial','',10); 
                //Inserto el texto especial antes de las preguntas del bloque 1:
                $contenidoFila = array("", utf8_decode('¿Ha presenciado o sufrido alguna vez, durante o con motivo del trabajo un acontecimiento como los siguientes:?'), "", "");
                $pdf->FancyRow($contenidoFila, $align, false, array(8, 147, 15, 15));
            }
        }
 
        $pdf->SetFont('Arial','',10);
        $contenidoFila = array($row[numPreg], utf8_decode($row[pregunta]), $row[si], $row[_no]);
        $pdf->SetWidths($anchoCols);
        $pdf->FancyRow($contenidoFila, $align, false, array(8, 147, 15, 15));
    }
}
else{
    /****************** FRECUENCIA DE RESPUESTAS DE LA GUIA 2 o 3 ******************/ 
    if($_GET['tipoRep'] == 1)
        //Datos de TODOS los departamentos:
        $consulta = "call getFrecTipoRespGuia2_3_TodosDeptos__(".$numGuia.", ".$claveProceso.", 'r_".$claveEmpresa."')";
    else if($_GET['tipoRep'] == 2){
        //Datos de UN SOLO DEPTOS:
        $consulta = "call getFrecTipoRespGuia2_3_ByDepto__(".$numGuia.", ".$claveProceso.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";
    }
    
    $resultado = $mysqli->query($consulta);
    $align = array('', '', 'C', 'C', 'C', 'C', 'C');
    
    $numBloque = 0;
    while($fila = $resultado->fetch_assoc()){
        if($numBloque != $fila[bloque]){
            $pdf->Ln(5);
            $pdf->SetFont('Arial','B',9); 
            //Inserto la fila de los nombre de las columnas:
            $contenidoFila = array("#", "Pregunta", "Siempre", "Casi siempre", "Algunas veces", "Casi nunca", "Nunca");
            $pdf->FancyRow($contenidoFila, $align, true, array(8, 100, 15, 15, 15, 15, 15));

            $numBloque = $fila[bloque];
        }

        $pdf->SetFont('Arial','',10);
        $contenidoFila = array($fila['numPreg'], utf8_decode($fila[pregunta]), $fila[siempre], $fila[casi_siempre], $fila[algunas_veces], $fila[casi_nunca], $fila[nunca]);
        //Agrego al reporte la Fila con las respuestas de esa pregunta
        $pdf->FancyRow($contenidoFila, $align, false, array(8, 100, 15, 15, 15, 15, 15));
    }
    $pdf->Ln(5);

}

$pdf->Ln(5);

$pdf->Output('D', 'Frecuencia de respuestas de cada pregunta  - Guia '.$numGuia.' - '.$nombreCentro.' - '.$nombreProceso.'.pdf');

?>