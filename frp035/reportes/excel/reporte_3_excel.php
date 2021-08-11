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
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(8);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(31);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(11);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(13);


//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $matricula = $_GET['matricula']; 
$claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto'];

//Agrego la informacion en la cabecera de la tabla de Categorias
$sheet->setCellValue('A1', "Niveles de riesgo final de empleados - Guia ".$numGuia);
$sheet->setCellValue('A2', $_GET['nombreProceso']);
$sheet->setCellValue('F1', "Fecha de generacion: ". $fechaHoy);

//Dependiendo del tipo de reporte, agrego lo siguiente:
if($_GET['tipoRep'] == 1)
    $sheet->setCellValue('A3', 'Todos los departamentos del centro de trabajo');
else if($_GET['tipoRep'] == 2)
    $sheet->setCellValue('A3', $_GET['claveDepto'] .' - '. $_GET['nombreDepto']);


//Ahora establezco que las cabeceras tendra fondo de color
$spreadsheet->getActiveSheet()->getStyle('A5:F5')->applyFromArray($fondoGris);

/********************** SECCION DEL CONTENIDO DE LA TABLA ***********************/

if($_GET['tipoRep'] == 2){
    //Agrego al query el codigo para que sea solo por Depto:
    $condicion = "claveDepto like '".$claveDepto."' and";
}

$consulta = "select claveDepto, matricula, nombreEmpleado, (select sum(respu) from r_".$_GET['claveEmpresa']." 
            where numGuia = ".$numGuia." and claveProceso = ".$claveProceso."  and bloque > 0 and matricula like m.matricula) as calif
            from empleados as m
            where claveCentro like '".$claveCentro."' and ".$condicion." matricula in (SELECT distinct matricula 
                    FROM r_".$_GET['claveEmpresa']." 
                    where numGuia = ".$numGuia." and claveProceso = ".$claveProceso.") order by claveDepto, matricula";
	

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
            ->setCellValue('E5', 'Calificacion')
            ->setCellValue('F5', 'Nivel de riesgo');
     
$numFila = 6; $numEmpleado = 1;

while($row = $resultado->fetch_assoc()){
    $nivelRiesgo = getNivelRiesgo("Final", 0, $row[calif], $numGuia);

    $spreadsheet->getActiveSheet()
            ->setCellValue('A'.$numFila, $numEmpleado)
            ->setCellValue('B'.$numFila, $row[claveDepto])
            ->setCellValue('C'.$numFila, $row[matricula])
            ->setCellValue('D'.$numFila, $row[nombreEmpleado])
            ->setCellValue('E'.$numFila, $row[calif])
            ->setCellValue('F'.$numFila, $nivelRiesgo);

    //Verifico el Tipo de Nivel de riesgo
    $colorCelda = getColorNivelRiesgoExcel($nivelRiesgo);
    $spreadsheet->getActiveSheet()->getStyle('F'.$numFila.':F'.$numFila)->applyFromArray($colorCelda);

    $numFila++; $numEmpleado++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Niveles de riesgo final de empleados - Guia '.$numGuia.'.xlsx"');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>