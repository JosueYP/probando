<?php

require 'vendor/autoload.php'; require('../nivelesRiesgo.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

//Creo un nuevo Documento de Excel:
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$fechaHoy = date("d")."/".date("m")."/".date("Y");

$fondoGris = [
    'fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3'] ]
];

//Creo un estilo para la fila de las cabeceras de la tabla
$letrasNegritas = [
    'font' => [
        'bold' => true,
    ]
];

$sheet->getStyle('A1:D6' )->applyFromArray($letrasNegritas);

//Establezco las columnas que tendran ancho automatico
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(36);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(11);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(14);


//Agrego la informacion en la cabecera de la tabla de Categorias
$sheet->setCellValue('A1', "Niveles de riesgo por Categoria, Dominio y Resultado Final");
$sheet->setCellValue('A2', $_GET['nombreProceso']);
$sheet->setCellValue('D1', "Fecha de generacion: ". $fechaHoy);
$sheet->setCellValue('D3', $_GET['nombreCentro']);


//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $matricula = $_GET['matricula']; 
$claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto']; $claveEmpresa = $_GET['claveEmpresa'];


//Dependiendo del tipo de reporte...--------------------------------------
if($_GET['tipoRep'] == 1){
    $sheet->setCellValue('A3', "Todos los departamentos del centro de trabajo");

    //Categorias por Todos los deptos. <<<
    $consulta1 = "call getRiesgosPorCategoriaGuia".$numGuia."_Tipo1__(".$claveProceso.", '".$claveCentro."', 'r_".$claveEmpresa."')";

    //Dominios por Todos los deptos. <<<
    $consulta2 = "call getRiesgosPorDominioGuia".$numGuia."_Tipo1__(".$claveProceso.", '".$claveCentro."', 'r_".$claveEmpresa."')";

    //Calificacion final de los empleados de TODO el centro de trabajo
    $consulta3 = "select round(sum(respu)/(select count( distinct matricula) from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (SELECT matricula FROM empleados where claveCentro like '".$claveCentro."'))) as calif
				 from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (select matricula from empleados where claveCentro like '".$claveCentro."')";
}
else if($_GET['tipoRep'] == 2){
    $sheet->setCellValue('A3', utf8_decode($_GET['claveDepto'] .' - '. $_GET['nombreDepto']));

    //Categorias por Departamento <<<
    $consulta1 = "call getRiesgosPorCategoriaGuia".$numGuia."_Tipo2__(".$claveProceso.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";

    //Dominios por Departamento <<<
    $consulta2 = "call getRiesgosPorDominioGuia".$numGuia."_Tipo2__(".$claveProceso.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";

    //Calificacion final de los empleados de UN SOLO DEPTO <<<
    $consulta3 = "select round(sum(respu)/(select count( distinct matricula) from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (SELECT matricula FROM empleados where claveCentro like '".$claveCentro."' and claveDepto like '".$claveDepto."'))) as calif
				from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (select matricula from empleados where claveCentro like '".$claveCentro."' and claveDepto like '".$claveDepto."')";
}  
else if($_GET['tipoRep'] == 3){
    $sheet->setCellValue('A3', utf8_decode($_GET['matricula'] .' - '. $_GET['nombre']));

    //Categorias por Empleado <<<
    $consulta1 = "select distinct numCategoria, categoria, (select sum(respu) 
														from r_".$_GET['claveEmpresa']." 
														where numPreg in ( (select numPreg 
																			from preguntas 
																			where numGrupo in (select numGrupo 
																							from grupos 
																							where numCategoria = g.numCategoria) and 
																				numGuia = ".$numGuia.")) and 
															claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula like '".$matricula."') as calif
				from grupos as g ";

    //Dominios por Empleado <<<
    $consulta2 = "select distinct numDominio, dominio, (select sum(respu) 
                                                        from r_".$_GET['claveEmpresa']." 
                                                        where numPreg in ( (select numPreg 
                                                                            from preguntas 
                                                                            where numGrupo in (select numGrupo 
                                                                                            from grupos 
                                                                                            where numDominio = g.numDominio) and 
                                                                                numGuia = ".$numGuia.")) and 
                                                            claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula like '".$matricula."') as calif
                from grupos as g ";

    //Calificacion final de UN SOLO EMPLEADO <<<
    $consulta3 = "select sum(respu) as calif from r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula like '".$matricula."'";

    //Si es un reporte de la Guia 2, agrego esto al final de las querys:
	if($numGuia == 2){
        $consulta1 . "where numCategoria < 5"; 
        $consulta2 . "where numDominio < 9";
    }
		
}

//Ahora establezco que las cabeceras tendra fondo de color
$spreadsheet->getActiveSheet()->getStyle('A6:D6')->applyFromArray($fondoGris);

//Ahora agrego todos los datos a la tabla
require('../../cn.php');

/********************** SECCION DE LAS CATEGORIAS ***********************/

$sheet->setCellValue('A5', "Niveles de riesgo por Categorias");

//Agrego las cabeceras de la tabla:
$spreadsheet->getActiveSheet()
            ->setCellValue('A6', 'Numero')
            ->setCellValue('B6', 'Categoria')
            ->setCellValue('C6', 'Calificacion')
            ->setCellValue('D6', 'Nivel de riesgo');

//Ejecuto la query para obtener los datos
$resultado = $mysqli->query($consulta1);
$numFila = 7; 

while($row = $resultado->fetch_assoc()){
    $nivelRiesgo = getNivelRiesgo("Categoria", $row[numCategoria], $row[calif], $numGuia);

    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $row[numCategoria])
            ->setCellValue('B'.$numFila, $row[categoria])
            ->setCellValue('C'.$numFila, $row[calif])
            ->setCellValue('D'.$numFila, $nivelRiesgo);

    //Verifico el Tipo de nivel de riesgo y pinto la celda de ese color
    $colorCelda = getColorNivelRiesgoExcel($nivelRiesgo);
    $spreadsheet->getActiveSheet()->getStyle('D'.$numFila.':D'.$numFila)->applyFromArray($colorCelda);

    $numFila++;
}

$numFila++; 

/********************** SECCION DE LOS DOMINIOS ***********************/

$sheet->setCellValue('A'.$numFila, "Niveles de riesgo por Dominios");

$sheet->getStyle('A'.$numFila.':D'.($numFila+1) )->applyFromArray($letrasNegritas);

$numFila++; 

$spreadsheet->getActiveSheet()->getStyle('A'.$numFila.':D'.$numFila)->applyFromArray($fondoGris);

//Agrego las cabeceras de la tabla:
$spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, 'Numero')
            ->setCellValue('B'.$numFila, 'Dominio')
            ->setCellValue('C'.$numFila, 'Calificacion')
            ->setCellValue('D'.$numFila, 'Nivel de riesgo');


require('../../cn.php');

//Ejecuto la query para obtener los datos
$resultado = $mysqli->query($consulta2);

$numFila++; 
while($row = $resultado->fetch_assoc()){
    $nivelRiesgo = getNivelRiesgo("Dominio", $row[numDominio], $row[calif], $numGuia);

    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $row[numDominio])
            ->setCellValue('B'.$numFila, $row[dominio])
            ->setCellValue('C'.$numFila, $row[calif])
            ->setCellValue('D'.$numFila, $nivelRiesgo);

    //Verifico el Tipo de nivel de riesgo y pinto la celda de ese color
    $colorCelda = getColorNivelRiesgoExcel($nivelRiesgo);
    $spreadsheet->getActiveSheet()->getStyle('D'.$numFila.':D'.$numFila)->applyFromArray($colorCelda);

    $numFila++;
}

$numFila++; 

/********************** SECCION DE LA CALIFICACION FINAL ***********************/

$sheet->setCellValue('A'.$numFila, "Calificacion y nivel de riesgo final del cuestionario");

$sheet->getStyle('A'.$numFila.':D'.($numFila+1) )->applyFromArray($letrasNegritas);

$numFila++; 

$spreadsheet->getActiveSheet()->getStyle('A'.$numFila.':C'.$numFila)->applyFromArray($fondoGris);

//Agrego las cabeceras de la tabla:
$spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, 'Calificacion final')
            ->setCellValue('B'.$numFila, 'Nivel de riesgo')
            ->setCellValue('C'.$numFila, 'Total encuestados');

require('../../cn.php');

//Ejecuto la query para obtener los datos
$resultado = $mysqli->query($consulta3);
$row = $resultado->fetch_assoc();

$numFila++; $numEnc = 1;

if($_GET['tipoRep'] != 3)
	$numEnc = $_GET['numEnc'];    

$nivelRiesgo = getNivelRiesgo("Final", 0, $row[calif], $numGuia);

$spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $row[calif])
            ->setCellValue('B'.$numFila, $nivelRiesgo)
            ->setCellValue('C'.$numFila, $numEnc);

//Verifico el Tipo de Nivel de riesgo
$colorCelda = getColorNivelRiesgoExcel($nivelRiesgo);
$spreadsheet->getActiveSheet()->getStyle('B'.$numFila.':B'.$numFila)->applyFromArray($colorCelda);
        

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Niveles de riesgo por Cat., Dom. y Res. Final - '.$_GET['nombreCentro'].' - '.$_GET['nombreProceso'].'.xlsx"');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>