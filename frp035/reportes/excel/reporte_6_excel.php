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

$sheet->getStyle('A1:H5' )->applyFromArray($letrasNegritas);


//Establezco las columnas que tendran ancho automatico
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(32);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(14);


//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $matricula = $_GET['matricula']; 
$claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto']; $nombreCentro = $_GET['nombreCentro'];
$claveEmpresa = $_GET['claveEmpresa']; $nombreProceso =  $_GET['nombreProceso'];

//Agrego la informacion en la cabecera de la tabla de Categorias
$sheet->setCellValue('A1', "Empleados que requieren valoracion - Guia 1");
$sheet->setCellValue('A2', $_GET['nombreProceso']);
$sheet->setCellValue('A3', "Todos los departamentos del centro de trabajo");
$sheet->setCellValue('F1', "Fecha de generacion: ". $fechaHoy);
    

//Ahora establezco que las cabeceras tendra fondo de color
$spreadsheet->getActiveSheet()->getStyle('A5:E5')->applyFromArray($fondoGris);

/********************** SECCION DEL CONTENIDO DE LA TABLA ***********************/

$consulta = "call getEmpsRequierenAtencionMedica__(".$claveProceso.", '".$claveCentro."', 'r_".$claveEmpresa."')";

//Ahora agrego todos los datos a la tabla
require('../../cn.php');

//Ejecuto la query para obtener los datos
$resultado = $mysqli->query($consulta);

//Agrego las cabeceras de la tabla:
$spreadsheet->getActiveSheet()
            ->setCellValue('A5', '#')
            ->setCellValue('B5', 'Depto.')
            ->setCellValue('C5', 'Matricula')
            ->setCellValue('D5', 'Nombre del empleado')
            ->setCellValue('E5', 'Fecha encuesta');
     
$numFila = 6; $numEmpleado = 1;

while($row = $resultado->fetch_assoc()){
    $fechaEncuesta = '-';
    if($row[fecha] != NULL)
        $fechaEncuesta = date("d/m/Y", strtotime($row[fecha]));

    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $numEmpleado)
            ->setCellValue('B'.$numFila, $row[claveDepto])
            ->setCellValue('C'.$numFila, $row[matricula])
            ->setCellValue('D'.$numFila, $row[nombreEmpleado])
            ->setCellValue('E'.$numFila, $fechaEncuesta);

    $numFila++; $numEmpleado++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Empleados que requieren valoracion Guia 1 - '.$nombreCentro.' - '.$nombreProceso.'.xlsx"');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>