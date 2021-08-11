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
		$this->SetFont('Arial','B',12);
        //Dependiendo del tipo de reporte, es el TITULO que tendra la pagina
        $this->Cell(70,10, 'Respuestas del empleado de la Guia '. $_GET['numGuia'] ,0,1,'L');
        //$this->Ln(2);

        //NUEVA SECCION ******************
        $align = array('', '');
        //Establezco el ancho de las columnas para la fila
        //FILA 1
        $this->SetFont('Arial','B',11);
        $contenidoFila = array( utf8_decode($_GET['matricula'].' - '.$_GET['nombre']), 
                                utf8_decode($_GET['claveDepto'].' - '.$_GET['nombreDepto']));
        $this->FancyRow($contenidoFila, $align, false, array(93, 93));

        //FILA 2
        $this->SetFont('Arial','',11);
        $contenidoFila = array(utf8_decode($_GET['nombreProceso']),
                               utf8_decode("Fecha de aplicación de la Guia: ".date("d/m/Y", strtotime($_GET['fechaAplicacion']))));
        $this->FancyRow($contenidoFila, $align, false, array(93, 93));
        $this->Ln(5);
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
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto'];
$matricula = $_GET['matricula']; $nombreCentro = $_GET['nombreCentro']; $fechaAplicacion = $_GET['fechaAplicacion'];


if($_GET['numGuia'] == 1){
    //Query para generar el reporte de la Guia 1 <<<<<<<<<<<<<
    $consulta = "select numPreg, (select pregunta from preguntas where numGuia = 1 and numPreg = r.numPreg) as pregunta, bloque, respu, 
                (select titulo from preguntas where numGuia = 1 and numPreg = r.numPreg) as titulo
                from r_".$_GET['claveEmpresa']." as r
                where numGuia = 1 and bloque > 0 and claveProceso = ".$claveProceso." and matricula like '".$matricula. "'";

    $resultado = $mysqli->query($consulta);

    $align = array('', '', 'C', 'C');
    
    $i=1; $numBloque = 0;
    while($row = $resultado->fetch_assoc()){
        if($numBloque != $row[bloque]){
            $pdf->Ln(4);
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

        //Defino cual es la respuesta
        if($row[respu] == 1){
            $op1 = "X"; $op2 = ""; 
        }
        else if($row[respu] == 0){
            $op1 = ""; $op2 = "X"; 
        }
            
        $pdf->SetFont('Arial','',10);
        $contenidoFila = array($i, utf8_decode($row[pregunta]), $op1, $op2);
        $pdf->SetWidths($anchoCols);
        $pdf->FancyRow($contenidoFila, $align, false, array(8, 147, 15, 15));
        $i++;
    }
    $pdf->Ln(5);
    
    if($i == 7)
        $pdf->MultiCell(190,5, utf8_decode("No fue necesario que el empleado respondiera las demas secciones ya que todas sus respuestas en la Sección I fueron 'NO'"),0,'L',0);

    //Agrego la seccion para que el trabajador pueda firmar el reporte
    $pdf->Ln(7);
    $pdf->Cell(80,5,"Firma del empleado: _________________________   Fecha: ______________________",0,1,'L', 0);

}else{
    //Query para generar el reporte de la Guia 2 o 3 <<<<<<<<<<<<<<
    $consulta = "select numPreg, (select pregunta from preguntas where numPreg = r.numPreg and numGuia = ".$numGuia.") as pregunta, bloque,
                    (select respuA from preguntas where numPreg = r.numPreg and numGuia = ".$numGuia." and respuA = r.respu) as op1,
                    (select respuB from preguntas where numPreg = r.numPreg and numGuia = ".$numGuia." and respuB = r.respu) as op2,
                    (select respuC from preguntas where numPreg = r.numPreg and numGuia = ".$numGuia." and respuC = r.respu) as op3,
                    (select respuD from preguntas where numPreg = r.numPreg and numGuia = ".$numGuia." and respuD = r.respu) as op4,
                    (select respuE from preguntas where numPreg = r.numPreg and numGuia = ".$numGuia." and respuE = r.respu) as op5
                from r_".$_GET['claveEmpresa']." as r
                where numGuia = ".$numGuia." and claveProceso = ".$claveProceso." and matricula like '".$matricula."'";

    $resultado = $mysqli->query($consulta);
    //En este momento ya obtuve Todas las respuestas de la Guia 2 o 3 del empleado

    $align = array('', '', 'C', 'C', 'C', 'C', 'C');
    $numBloque = 0; $numRespsBloque = 0; $totalBloquesGuia;

    if($numGuia == 2)
        $totalBloquesGuia = 8;
    else if($numGuia == 3)
        $totalBloquesGuia = 14;

    //NUEVO ***
    //Hago lo siguiente para poder recorrer Varias veces el resultado de la Query <<<<
    while ($fila = mysqli_fetch_array($resultado)) {
        unset($campos);
        foreach ($fila as $campo => $valor) {
            $campos[$campo] = $valor;
        }
        $filas[] = $campos;
    }

    //Hago un For del 1 al total de bloque de esta Guia
    for ($bloque = 1; $bloque <= $totalBloquesGuia; $bloque++) {
        $numRespsBloque = 0;

        //1. Verifico cuantas respuestas se guardaron en el Bloque actual:
        foreach ($filas as $fila) {
            if($fila['bloque'] == $bloque){
                $numRespsBloque++;
            }
        }

        if($numRespsBloque > 0){
            //2. Imprimo el titulo del numero de bloque
            $pdf->Ln(5);
            $pdf->SetFont('Arial','B',9); 
            //3. Inserto la fila de los nombre de las columnas:
            $contenidoFila = array("#", "Pregunta", "Siempre", "Casi siempre", "Algunas veces", "Casi nunca", "Nunca");
            $pdf->FancyRow($contenidoFila, $align, true, array(8, 100, 15, 15, 15, 15, 15));

            //4. Agrego cada una de las preguntas de este bloque
            //Recorro todas las preguntas pero solo imprimo las que sean de ESTE bloque:
            foreach ($filas as $fila) {
                if($fila['bloque'] == $bloque){
                    $op1 = ""; $op2 = ""; $op3 = ""; $op4 = ""; $op5 = ""; 
        
                    if($fila['op1'] != null)
                        $op1 = "X";
                    else if($fila['op2'] != null)
                        $op2 = "X";
                    else if($fila['op3'] != null)
                        $op3 = "X";
                    else if($fila['op4'] != null)
                        $op4 = "X"; 
                    else if($fila['op5'] != null)
                        $op5 = "X"; 
            
                    $pdf->SetFont('Arial','',10);
                    $contenidoFila = array($fila['numPreg'], utf8_decode($fila[pregunta]), $op1, $op2, $op3, $op4, $op5);
                    //Agrego al reporte la Fila con las respuestas de esa pregunta
                    $pdf->FancyRow($contenidoFila, $align, false, array(8, 100, 15, 15, 15, 15, 15));
                }
            }
        }else{
            //Imprimo el mensaje de que no se guardaron respuestas de este bloque
            if ($bloque == ($totalBloquesGuia - 1)){
                $pdf->Ln(5);
                $pdf->Cell(80,5,"El empleado no contesto las preguntas del Bloque ". ($totalBloquesGuia - 1) ." ya que no brinda servicio a clientes o usuarios.",0,1,'L', 0);
            }
            else if ($bloque == $totalBloquesGuia){
                $pdf->Ln(5);
                $pdf->Cell(80,5,"El empleado no contesto las preguntas del Bloque ". $totalBloquesGuia ." ya que no es jefe de otros trabajadores.",0,1,'L', 0);
            }
        }
    }
    //Agrego la seccion para que el trabajador pueda firmar el reporte
    $pdf->Ln(30);
    $pdf->Cell(80,5,"Firma del empleado: _________________________   Fecha: ______________________",0,1,'L', 0);
}

$pdf->Ln(5);

$pdf->Output('D',  $matricula.' - Respuestas Guia '.$numGuia.' - '.$nombreCentro.'__'. date("d-m-Y", strtotime($fechaAplicacion)). '.pdf');

?>