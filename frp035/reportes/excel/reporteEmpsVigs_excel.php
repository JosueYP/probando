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
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(11);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(35);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(13);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);

//Ahora establezco que las cabeceras tendra fondo de color
$spreadsheet->getActiveSheet()->getStyle('A4:E4')->applyFromArray($estilo1);


//Agrego las cabeceras de la tabla:
$spreadsheet->getActiveSheet()
            ->setCellValue('A4', '#')
            ->setCellValue('B4', 'MATRICULA')
            ->setCellValue('C4', 'NOMBRE')
            ->setCellValue('D4', 'CLAVE DEPTO.')
            ->setCellValue('E4', 'DEPARTAMENTO');

//Agrego un filtro a la fila de la cabecera
$spreadsheet->getActiveSheet()->setAutoFilter('A4:E4'); 


//Ahora agrego todos los datos a la tabla
require('../../cn.php');

//Agrego la informacion en la cabecera del reporte
$sheet->setCellValue('A1', "CATALOGO DE EMPLEADOS VIGENTES");
$sheet->setCellValue('A2', $_GET['nombreCentro']);
$sheet->setCellValue('E1', "Fecha de generacion: ". $fechaHoy);

//Ejecuto la query para obtener los datos
$resultado = $mysqli->query("select matricula, nombreEmpleado, claveDepto, (select nombreDepto from deptos where claveDepto like e.claveDepto and claveCentro like '".$_GET['claveCentro']."') as nombreDepto
                            from empleados as e where claveCentro like '".$_GET['claveCentro']."' and status = 1 order by claveDepto, matricula");

$numFila = 5; $numEmpleado = 1;

while($row = $resultado->fetch_assoc()){
    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $numEmpleado)
            ->setCellValue('B'.$numFila, $row[matricula])
            ->setCellValue('C'.$numFila, $row[nombreEmpleado])
            ->setCellValue('D'.$numFila, $row[claveDepto])
            ->setCellValue('E'.$numFila, $row[nombreDepto]);

    $numFila++; $numEmpleado++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Empleados vigentes FRP 035 - '.$_GET['nombreCentro'].'.xlsx"');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>