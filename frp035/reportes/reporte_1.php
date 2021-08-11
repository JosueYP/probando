<?php
require('mc_table.php'); require('nivelesRiesgo.php');

class PDF extends PDF_MC_Table{
	function Header(){   
		$this->SetFont('Arial','',12);
        $this->Cell(70,10, utf8_decode($_GET['nombreCentro']),0,0,'L');
		$this->SetXY(175,11);
		$this->Cell(70,5,date("d")."/".date("m")."/".date("Y"),0,1,'L');
	    $this->Ln(5);

		// Título del reporte
		$this->SetFont('Arial','B',13);
		$this->Cell(70,10, utf8_decode('Niveles de riesgo por categorias, dominios y calificación final'),0,1,'L');


		//NUEVA SECCION ******************
        $align = array('', '');
		if($_GET['tipoRep'] == 1)
			$tipoDatos = "Todos los departamentos del centro de trabajo";
		else if($_GET['tipoRep'] == 2)
			$tipoDatos = utf8_decode($_GET['claveDepto'] .' - '. $_GET['nombreDepto']);
		else if($_GET['tipoRep'] == 3){
			$tipoDatos = utf8_decode($_GET['matricula'] .' - '. $_GET['nombre']);
		}

		$align = array('', '');		
		//Inserto la fila en la cabecera del reporte
        $this->SetFont('Arial','B',10);
        $contenidoFila = array( utf8_decode($_GET['nombreProceso']) , $tipoDatos);
        $this->FancyRow($contenidoFila, $align, false, array(90, 90));

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
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $matricula = $_GET['matricula']; $claveCentro = $_GET['claveCentro'];
$claveDepto = $_GET['claveDepto']; $nombreDepto = $_GET['nombreDepto']; $claveEmpresa = $_GET['claveEmpresa'];

//================================ TABLA POR CATEGORIAS ================================

$pdf->Cell(70,10, 'Niveles de riesgo por categoria',0,1,'L');

//Paso 1: Defino cual va a ser la query que se va a ejecutar dependiendo del Tipo de reporte
if($_GET['tipoRep'] == 1)
	//Todos los deptos. del centro de trabajo
	$consulta1 = "call getRiesgosPorCategoriaGuia".$numGuia."_Tipo1__(".$claveProceso.", '".$claveCentro."', 'r_".$claveEmpresa."')";
else if($_GET['tipoRep'] == 2)
	//Por departamento
	$consulta1 = "call getRiesgosPorCategoriaGuia".$numGuia."_Tipo2__(".$claveProceso.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";
else if($_GET['tipoRep'] == 3){
	//Por empleado
	$consulta1 = "select distinct numCategoria, categoria, (select sum(respu) 
														from r_".$_GET['claveEmpresa']."
														where numPreg in ( (select numPreg 
																			from preguntas 
																			where numGrupo in (select numGrupo 
																							from grupos 
																							where numCategoria = g.numCategoria) and 
																				numGuia = ".$numGuia.")) and 
															claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula like ".$matricula.") as calif
				from grupos as g ";

	if($numGuia == 2)
		$consulta1 = $consulta1 . "where numCategoria < 5"; //Si es un reporte de la Guia 2, agrego esto al final de la query
}
	
//Paso 2: Hago y ejecuto la quuery para obtener los datos <<<<<<<<<<
$resultado1 = $mysqli->query($consulta1);

//Paso 3: Agrego los datos a la tabla
$pdf->SetFont('Arial','B',11); 
$pdf->setFillColor(230,230,230); 
$pdf->Cell(20,5, "Numero",1,0,'C', 1);
$pdf->Cell(100,5, "Categoria",1,0,'L', 1);
$pdf->Cell(30,5, utf8_decode("Calificación"),1,0,'C', 1);
$pdf->Cell(30,5,"Nivel de riesgo",1,1,'L', 1);

$pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla


while($row = $resultado1->fetch_assoc()){
	$pdf->Cell(20,5, $row[numCategoria],1,0,'C', 0);
	$pdf->Cell(100,5, utf8_decode($row[categoria]),1,0,'L', 0);
	$pdf->Cell(30,5,$row[calif],1,0,'C', 0);

	//Aqui va adepender de la calificacion...
	$nivelRiesgo = getNivelRiesgo("Categoria", $row[numCategoria], $row[calif], $numGuia);
	//Dependiendo de lo que me devuelva la funcion, es el color del que pintare la celda

	$color = getColorNivelRiesgo($nivelRiesgo);
	$pdf->setFillColor($color[0], $color[1], $color[2]); 
    $pdf->Cell(30,5,$nivelRiesgo,1,1,'C', 1);
}

$pdf->Ln(5);


//================================ TABLA POR DOMINIOS ==================================

$pdf->SetFont('Arial','B',12);
$pdf->Cell(70,10, 'Niveles de riesgo por dominio',0,1,'L');

//Paso 1: Defino cual va a ser la query que se va a ejecutar dependiendo del Tipo de reporte
if($_GET['tipoRep'] == 1)
	$consulta2 = "call getRiesgosPorDominioGuia".$numGuia."_Tipo1__(".$claveProceso.", '".$claveCentro."', 'r_".$claveEmpresa."')";
else if($_GET['tipoRep'] == 2)
	$consulta2 = "call getRiesgosPorDominioGuia".$numGuia."_Tipo2__(".$claveProceso.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";
else if($_GET['tipoRep'] == 3){
	$consulta2 = "select distinct numDominio, dominio, (select sum(respu) 
														from r_".$_GET['claveEmpresa']."
														where numPreg in ( (select numPreg 
																			from preguntas 
																			where numGrupo in (select numGrupo 
																							from grupos 
																							where numDominio = g.numDominio) and 
																				numGuia = ".$numGuia.")) and 
															claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula like ".$matricula.") as calif
				from grupos as g ";

	if($numGuia == 2)
		$consulta2 = $consulta2 . "where numDominio < 9"; //Si es un reporte de la Guia 2, agrego esto al final de la query
}


require('../cn.php');
//Ejecuto al Query
$resultado2 = $mysqli->query($consulta2);
$row_cnt = $resultado2->num_rows;

$pdf->SetFont('Arial','B',11); 
$pdf->setFillColor(230,230,230); 
$pdf->Cell(20,5, "Numero",1,0,'C', 1);
$pdf->Cell(100,5, "Dominio",1,0,'L', 1);
$pdf->Cell(30,5, utf8_decode("Calificación"),1,0,'C', 1);
$pdf->Cell(30,5,"Nivel de riesgo",1,1,'L', 1);

$pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

//$pdf->Cell(20,5, $row_cnt,1,0,'L', 0);


while($row = $resultado2->fetch_assoc()){
	$pdf->Cell(20,5, $row[numDominio],1,0,'C', 0);
	$pdf->Cell(100,5, utf8_decode($row[dominio]),1,0,'L', 0);
	$pdf->Cell(30,5,$row[calif],1,0,'C', 0);

	$nivelRiesgo = getNivelRiesgo("Dominio", $row[numDominio], $row[calif], $numGuia);
	//Dependiendo de lo que me devuelva la funcion, es el color del que pintare la celda
	
	$color = getColorNivelRiesgo($nivelRiesgo);
	$pdf->setFillColor($color[0], $color[1], $color[2]); 
    $pdf->Cell(30,5,$nivelRiesgo,1,1,'C', 1);
}

$pdf->Ln(5);

//================================ TABLA DE CALIFICACION FINAL ==================================

$pdf->SetFont('Arial','B',12);
$pdf->Cell(70,10, 'Calificacion y nivel de riesgo final del cuestionario',0,1,'L');

if($_GET['tipoRep'] == 1){
	//<<<<<<<<<<<------------------------------<<<<<<<<<------------ESTE ES EL QUE DEBO USAR
	//Calificacion final de Todos los empleados del Centro de trabajo seleccionado que Ya hicieron la encuesta
	$consulta3 = "select round(sum(respu)/(select count( distinct matricula) from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (SELECT matricula FROM empleados where claveCentro like '".$claveCentro."'))) as calif
				 from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (select matricula from empleados where claveCentro like '".$claveCentro."')";
}
else if($_GET['tipoRep'] == 2)
	//Query para obtener la calificacion final por empleados que hicicieron la encuesta de un Depto.
	$consulta3 = "select round(sum(respu)/(select count( distinct matricula) from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (SELECT matricula FROM empleados where claveCentro like '".$claveCentro."' and claveDepto like '".$claveDepto."'))) as calif
				from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (select matricula from empleados where claveCentro like '".$claveCentro."' and claveDepto like '".$claveDepto."')";

else if($_GET['tipoRep'] == 3){
	//Obtengo la calificacion final de ESTE empleado
	$consulta3 = "select sum(respu) as calif from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula like ".$matricula;
}

require('../cn.php');
//Ejecuto al Query
$resultado3 = $mysqli->query($consulta3);
$row = $resultado3->fetch_assoc();

$pdf->SetFont('Arial','B',11); 
$pdf->setFillColor(230,230,230); 

//Si NO es el reporte por Empleado, agrego una celda con el numero de empleados encuestados
if($_GET['tipoRep'] != 3){
	$pdf->Cell(35,5, "Total encuestados",1,0,'C', 1);
}
$pdf->Cell(40,5, "Calificacion final",1,0,'C', 1);
$pdf->Cell(30,5,"Nivel de riesgo",1,1,'L', 1);

$pdf->SetFont('Arial','',11); //Cambio el tipo de fuente para la tabla

if($_GET['tipoRep'] != 3){
	$pdf->Cell(35,5, $_GET['numEnc'],1,0,'C', 0);
}
$pdf->Cell(40,5,$row[calif],1,0,'C', 0);

$nivelRiesgo = getNivelRiesgo("Final", 0, $row[calif], $numGuia);

$color = getColorNivelRiesgo($nivelRiesgo);
$pdf->setFillColor($color[0], $color[1], $color[2]); 
$pdf->Cell(30,5,$nivelRiesgo,1,1,'C', 1);


$pdf->Output('D', 'Niveles de riesgo por Cat., Dom. y Res. Final - '.$_GET['nombreCentro'].' - '.$_GET['nombreProceso'].'.pdf');
?>