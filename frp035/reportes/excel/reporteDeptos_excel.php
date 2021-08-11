<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

//Creo un nuevo Documento de Excel:
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$fechaHoy = date("d")."/".date("m")."/".date("Y");

$estilo1 = [
    'fill'=>[
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'A1DCEF'
        ]
    ]
];

//Creo un estilo para la fila de las cabeceras de la tabla
$styleArrayFirstRow = [
    'font' => [
        'bold' => true,
    ]
];

$sheet->getStyle('A1:E4' )->applyFromArray($styleArrayFirstRow);

//Establezco las columnas que tendran ancho automatico
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(40);
//Ahora establezco que las cabeceras tendra fondo de color
$spreadsheet->getActiveSheet()->getStyle('A4:C4')->applyFromArray($estilo1);


//Agrego las cabeceras de la tabla:
$spreadsheet->getActiveSheet()
            ->setCellValue('A4', '#')
            ->setCellValue('B4', 'CLAVE DEPTO.')
            ->setCellValue('C4', 'NOMBRE DEPTO');

//Agrego un filtro a la fila de la cabecera
$spreadsheet->getActiveSheet()->setAutoFilter('A4:C4'); 


//Ahora agrego todos los datos a la tabla
require('../../cn.php');

//Agrego la informacion en la cabecera del reporte
$sheet->setCellValue('A1', "CATALOGO DE DEPARTAMENTOS");
$sheet->setCellValue('A2', $_GET['nombreCentro']);
$sheet->setCellValue('E1', "Fecha de generacion: ". $fechaHoy);

//Ejecuto la query para obtener los datos
$resultado = $mysqli->query("select * from deptos where claveCentro like '".$_GET['claveCentro']."' and status = 1 order by claveDepto");

$numFila = 5; $numEmpleado = 1;

while($row = $resultado->fetch_assoc()){
    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $numEmpleado)
            ->setCellValue('B'.$numFila, $row[claveDepto])
            ->setCellValue('C'.$numFila, $row[nombreDepto]);

    $numFila++; $numEmpleado++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Departamentos FRP 035 - '.$_GET['nombreCentro'].'.xlsx"');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>