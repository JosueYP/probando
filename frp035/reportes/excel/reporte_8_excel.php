<?php

require 'vendor/autoload.php'; require('../nivelesRiesgo.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

//Creo un nuevo Documento de Excel:
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$fechaHoy = date("d")."/".date("m")."/".date("Y");
$fondoGris = ['fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3'] ]];

//Creo un estilo para la fila de las cabeceras de la tabla
$letrasNegritas = ['font' => ['bold' => true]];

$sheet->getStyle('A1:G5' )->applyFromArray($letrasNegritas);

//Establezco las columnas que tendran ancho automatico
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(80);


//Almaceno los valores de estas variables:
$claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia']; $matricula = $_GET['matricula'];  $claveEmpresa = $_GET['claveEmpresa'];
$claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto']; $nombreCentro = $_GET['nombreCentro']; $nombreProceso = $_GET['nombreProceso']

if($_GET['tipoRep'] == 1)
    $subtitulo = 'Todos los departamentos del centro de trabajo';
else if($_GET['tipoRep'] == 2){
    $subtitulo = $_GET['claveDepto'] .' - '. $_GET['nombreDepto'];
}

//Agrego la informacion en la cabecera de la tabla de Categorias
$sheet->setCellValue('A1', 'Frecuencia de respuesta de cada pregunta -  Guia '.$numGuia);
$sheet->setCellValue('A2', $_GET['nombreProceso']);
$sheet->setCellValue('A3', $subtitulo);
$sheet->setCellValue('C1', "Fecha de generacion: ". $fechaHoy);
$sheet->setCellValue('C2', "Total de encuestados: ". $_GET['numEnc']);


/********************** SECCION DEL CONTENIDO DE LA TABLA ***********************/

//Verifico que tipo de reporte es el que se quiere generar (Por Categoria o Dominio)
if($numGuia == 1){
    //Ahora establezco que las cabeceras tendra fondo de color
    $spreadsheet->getActiveSheet()->getStyle('A5:D5')->applyFromArray($fondoGris);

    /****************** FRECUENCIA DE RESPUESTAS DE LA GUIA 1 ******************/ 
    if($_GET['tipoRep'] == 2){
        //Si se va a generar el reporte por Deptos, agrego una condicion extra a la query:
        $condicionExtra = "and matricula in (select matricula from empleados 
                                            where claveCentro like '".$claveCentro."' and 
                                                claveDepto like '".$claveDepto."')";
    }

    //NOTA: Esta query es para obtener los datos de TODOS los empleados que hicieron la Guia 1 de ese proceso
    $consulta = "select numPreg, pregunta, bloque, titulo,
                    (select count(respu) from r_".$_GET['claveEmpresa']." where numPreg = p.numPreg and numGuia = 1 and respu = 1  ".$condicionExtra."  and claveProceso = ".$claveProceso." ) as si,
                    (select count(respu) from r_".$_GET['claveEmpresa']." where numPreg = p.numPreg and numGuia = 1 and respu = 0  ".$condicionExtra."  and claveProceso = ".$claveProceso.") as _no
                from preguntas as p where numGuia = 1";
    
    require('../../cn.php');
    $resultado = $mysqli->query($consulta);

    //Agrego las cabeceras de la tabla:
    $spreadsheet->getActiveSheet()
                ->setCellValue('A5', '#')
                ->setCellValue('B5', 'Pregunta')
                ->setCellValue('C5', 'Si')
                ->setCellValue('D5', 'No');

    $numFila = 6;
    while($row = $resultado->fetch_assoc()){
        //Inserto los valores en las celdas
        $spreadsheet->getActiveSheet()
                ->setCellValue('A'.$numFila, $row[numPreg])
                ->setCellValue('B'.$numFila, $row[pregunta])
                ->setCellValue('C'.$numFila, $row[si])
                ->setCellValue('D'.$numFila, $row[_no]);

        $numFila++; 
    }
}
else{
    //Establezco cuales son las celdas que tendran fondo gris
    $spreadsheet->getActiveSheet()->getStyle('A5:G5')->applyFromArray($fondoGris);

    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(11);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(13);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);

    if($_GET['tipoRep'] == 1)
        //Datos de TODOS los departamentos:
        $consulta = "call getFrecTipoRespGuia2_3_TodosDeptos__(".$numGuia.", ".$claveProceso.", 'r_".$claveEmpresa."')";
    else if($_GET['tipoRep'] == 2){
        $consulta = "call getFrecTipoRespGuia2_3_ByDepto__(".$numGuia.", ".$claveProceso.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";
    }
    
    require('../../cn.php');
    $resultado = $mysqli->query($consulta);

    //Agrego las cabeceras de la tabla:
    $spreadsheet->getActiveSheet()
                ->setCellValue('A5', '#')
                ->setCellValue('B5', 'Pregunta')
                ->setCellValue('C5', 'Siempre')
                ->setCellValue('D5', 'Casi siempre')
                ->setCellValue('E5', 'Algunas veces')
                ->setCellValue('F5', 'Casi nunca')
                ->setCellValue('G5', 'Nunca');
  
    $numFila = 6;
    while($row = $resultado->fetch_assoc()){
        //Inserto los valores en las celdas
        $spreadsheet->getActiveSheet()
                ->setCellValue('A'.$numFila, $row[numPreg])
                ->setCellValue('B'.$numFila, $row[pregunta])
                ->setCellValue('C'.$numFila, $row[siempre])
                ->setCellValue('D'.$numFila, $row[casi_siempre])
                ->setCellValue('E'.$numFila, $row[algunas_veces])
                ->setCellValue('F'.$numFila, $row[casi_nunca])
                ->setCellValue('G'.$numFila, $row[nunca]);
                
        $numFila++; 
    }
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Frecuencia de respuestas de cada pregunta  - Guia '.$numGuia.' - '.$nombreCentro.' - '.$nombreProceso.'.xlsx"');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

?>