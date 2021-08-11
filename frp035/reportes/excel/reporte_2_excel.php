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
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(36);


//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $matricula = $_GET['matricula']; 
$claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto']; $claveEmpresa = $_GET['claveEmpresa'];

//Agrego la informacion en la cabecera de la tabla de Categorias
$sheet->setCellValue('A1', "Distribucion de empleados por nivel de riesgo final de cada departamento - Guia ".$numGuia);
$sheet->setCellValue('A2', $_GET['nombreProceso']);
$sheet->setCellValue('A3', "Fecha de generacion: ". $fechaHoy);


//Ahora establezco que las cabeceras tendra fondo de color
$spreadsheet->getActiveSheet()->getStyle('A5:H5')->applyFromArray($fondoGris);

/********************** SECCION DEL CONTENIDO DE LA TABLA ***********************/

//Defino los limites dependiendo del numero de Guia
if($numGuia == 2){
    $l1 = 20; $l2 = 45; $l3 = 70; $l4 = 90; 
}else{
    $l1 = 50; $l2 = 75; $l3 = 99; $l4 = 140; 
}

$consulta = "call getEmpsPorNivelRiesgoFinalByDepto__(".$numGuia.", ".$claveProceso.", '".$claveCentro."', ".$l1.", ".$l2.", ".$l3.", ".$l4.", 'r_".$claveEmpresa."');";

//Ahora agrego todos los datos a la tabla
require('../../cn.php');

//Ejecuto la query para obtener los datos
$resultado = $mysqli->query($consulta);

//Agrego las cabeceras de la tabla:
$spreadsheet->getActiveSheet()
            ->setCellValue('A5', 'Clave')
            ->setCellValue('B5', 'Departamento')
            ->setCellValue('C5', 'Nulo')
            ->setCellValue('D5', 'Bajo')
            ->setCellValue('E5', 'Medio')
            ->setCellValue('F5', 'Alto')
            ->setCellValue('G5', 'Muy alto')
            ->setCellValue('H5', 'Encuestados');
     
$numFila = 6; 

while($row = $resultado->fetch_assoc()){
    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $row[claveDepto])
            ->setCellValue('B'.$numFila, $row[nombreDepto])
            ->setCellValue('C'.$numFila, $row[nulo])
            ->setCellValue('D'.$numFila, $row[bajo])
            ->setCellValue('E'.$numFila, $row[medio])
            ->setCellValue('F'.$numFila, $row[alto])
            ->setCellValue('G'.$numFila, $row[muy_alto])
            ->setCellValue('H'.$numFila, $row[numEncuestadosDepto]);

    $numFila++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Empleados por nivel de riesgo final de cada depto.xlsx"');

//Empleados por nivel de riesgo final de cada depto

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>