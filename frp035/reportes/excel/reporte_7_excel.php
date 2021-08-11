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

$fondoGris = ['fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3'] ]];

//Creo un estilo para la fila de las cabeceras de la tabla
$letrasNegritas = ['font' => ['bold' => true]];

$sheet->getStyle('A1:N5' )->applyFromArray($letrasNegritas);

//Establezco el ancho de la columna del Nombre del empleado
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(36);


//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $matricula = $_GET['matricula']; $claveEmpresa = $_GET['claveEmpresa'];
$claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto']; $tipoFiltro = $_GET['tipoFiltro']; $nombreProceso = $_GET['nombreProceso'];

if($tipoFiltro == 1)
    $subtitulo = 'Todos los departamentos del centro de trabajo'; 
else if($_GET['tipoFiltro'] == 2)
    $subtitulo = $_GET['claveDepto'] .' - '. $_GET['nombreDepto'];

/*
    TipoRep => 1: Por categoria, 2: Por dominio
    TipoFiltro => 1: Todos los deptos, 2: Por departamento
*/

if($_GET['tipoRep'] == 1){
    $cat_dom = "Categoria"; $elemento = "C";

    //Defino cual va a ser la Query del reporte por Categorias <<<<<<<<<<<
    if($tipoFiltro == 1)
        $consulta = "call getRiesgoPorCategoriaByEmp__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', 'r_".$claveEmpresa."')";
    else if($tipoFiltro == 2)
        $consulta = "call getRiesgoPorCategoriaByDepto__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";

    if($numGuia == 2)   
        $totalColsExtra = 4;
    else if($numGuia == 3)
        $totalColsExtra = 5;
        
}else{ 
    $cat_dom = "Dominio";  $elemento = "D";

    //Defino cual va a ser la Query del reporte por Dominios <<<<<<<<<<<
    if($tipoFiltro == 1)
        $consulta = "call getRiesgoPorDominioByEmp__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', 'r_".$claveEmpresa."')";
    else if($tipoFiltro == 2)
        $consulta = "call getRiesgoPorDominioByDepto__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";

    if($numGuia == 2)   
        $totalColsExtra = 8;
    else if($numGuia == 3)
        $totalColsExtra = 10;
}

//Agrego la informacion en la cabecera de la tabla de Categorias
$sheet->setCellValue('A1', "Niveles de riesgo por ".$cat_dom." de cada empleado - Guia ".$numGuia);
$sheet->setCellValue('A2', $_GET['nombreProceso']);
$sheet->setCellValue('A3', $subtitulo);
$sheet->setCellValue('F1', "Fecha de generacion: ". $fechaHoy);


//Ahora establezco que las cabeceras tendra fondo de color <<<<<<<<<<<<<<<<<<<<<<
$spreadsheet->getActiveSheet()->getStyle('A5:F5')->applyFromArray($fondoGris);

/********************** SECCION DEL CONTENIDO DE LA TABLA ***********************/

//Ahora agrego todos los datos a la tabla
require('../../cn.php');

$resultado = $mysqli->query($consulta);

$spreadsheet->getActiveSheet()
            ->setCellValue('A5', '#')
            ->setCellValue('B5', 'Depto.')
            ->setCellValue('C5', 'Matricula')
            ->setCellValue('D5', 'Nombre');

//Ahora agrego de forma dinamica las demas cabeceras de la tabla

//Creo un array para poder llegar de la columna E a la M
$columna = array("E", "F", "G", "H", "I", "J", "K", "L", "M", "N");

for ($i = 1; $i <= $totalColsExtra; $i++) {
    $sheet->setCellValue($columna[$i-1].'5', $elemento. $i);
    $spreadsheet->getActiveSheet()->getStyle($columna[$i-1].'5')->applyFromArray($fondoGris);
}


//Agrego a la tabla el contenido de la query ********
$numFila = 6; $numEmpleado = 1;

while($row = $resultado->fetch_assoc()){
    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $numEmpleado)
            ->setCellValue('B'.$numFila, $row[claveDepto])
            ->setCellValue('C'.$numFila, $row[matricula])
            ->setCellValue('D'.$numFila, $row[nombreEmpleado]);

    //Imprimo los niveles de riesgo de cada elemento. del e1 al e10
    for ($i = 1; $i <= $totalColsExtra; $i++) {
        $nivelRiesgo = getNivelRiesgo($cat_dom, $i, $row["e" . $i], $numGuia);

        $sheet->setCellValue($columna[$i-1] . $numFila, $nivelRiesgo);

        //Verifico el Tipo de Nivel de riesgo
        $colorCelda = getColorNivelRiesgoExcel($nivelRiesgo);
        $spreadsheet->getActiveSheet()->getStyle($columna[$i-1] . $numFila .':'. $columna[$i-1] . $numFila)->applyFromArray($colorCelda);
    }

    $numFila++; $numEmpleado++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Niveles de riesgo por '.$cat_dom.' de cada empleado - Guia '.$numGuia.' - '.$nombreProceso.'.xlsx"');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>