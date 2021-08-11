<?php

require 'excel/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Style\Fill;

function AsignaNivelesDeRiesgo($c1, $c2, $c3, $c4, $c5, $calificacion){
    if ($calificacion < $c1)
        return "Nulo";
    else if ($calificacion < $c2)
        return "Bajo";
    else if ($calificacion < $c3)
        return "Medio";
    else if ($calificacion < $c4)
        return "Alto";
    else if ($calificacion >= $c5)
        return "Muy alto";
}       

function getColorNivelRiesgo($nivel){
    if($nivel === "Nulo")
        $color = array(0,255,228);  //AZUL
    else if($nivel === "Bajo")
        $color = array(0,255,100);  //VERDE
	else if($nivel === "Medio")
        $color = array(255,255,75);  //AMARILLO
	else if($nivel === "Alto")
        $color = array(255,194,30);  //NARANJA
	else if($nivel === "Muy alto")
        $color = array(255,90,41);  //ROJO

    //Simplemente regreso un Array con los colores
    return $color;
}

//Funcion para obtener el color del Nivel de riesgo en los reportes en Excel
function getColorNivelRiesgoExcel($nivel){

    if($nivel == "Nulo"){
        $color = ['fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A1DCEF'] ]]; //AZUL
    }
    else if($nivel == "Bajo"){
        $color = ['fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00FF64'] ]]; //VERDE
    }
	else if($nivel == "Medio"){
        $color = ['fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF4B'] ]]; //AMARILLO
    }   
	else if($nivel == "Alto"){
        $color = ['fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC21E'] ]]; //NARANJA
    }   
	else if($nivel == "Muy alto"){
        $color = ['fill'=>['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF7A7A'] ]]; //ROJO
    }
    //Simplemente regreso un Array con los colores
    
    return $color;
}


function getNivelRiesgo($tipoNivel, $numCat_Dom, $calif, $numGuia){
    $nivelDeRiesgo = ""; 

    switch ($tipoNivel){
        case "Final":
            if ($numGuia == 2)
                $nivelDeRiesgo = AsignaNivelesDeRiesgo(20, 45, 70, 90, 90, $calif);
            else if ($numGuia == 3)
                $nivelDeRiesgo = AsignaNivelesDeRiesgo(50, 75, 99, 140, 140, $calif);

            break;
            
        case "Categoria":
            if ($numGuia == 2){
                switch ($numCat_Dom){
                    case 1:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(3, 5, 7, 9, 9, $calif);
                        break;
                    case 2:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(10, 20, 30, 40, 40, $calif);
                        break;
                    case 3:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(4, 6, 9, 12, 12, $calif);
                        break;
                    case 4:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(10, 18, 28, 38, 38, $calif);
                        break;
                }
            }
            else if ($numGuia == 3){
                switch ($numCat_Dom)
                {
                    case 1:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(5, 9, 11, 14, 14, $calif);
                        break;
                    case 2:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(15, 30, 45, 60, 60, $calif);
                        break;
                    case 3:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(5, 7, 10, 13, 13, $calif);
                        break;
                    case 4:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(14, 29, 42, 58, 58, $calif);
                        break;
                    case 5:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(10, 14, 18, 23, 23, $calif);
                        break;
                }
            }
            break;

        case "Dominio":
            if ($numGuia == 2){
                switch ($numCat_Dom){
                    case 1:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(3, 5, 7, 9, 9, $calif);
                        break;
                    case 2:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(12, 16, 20, 24, 24, $calif);
                        break;
                    case 3:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(5, 8, 11, 14, 14, $calif);
                        break;
                    case 4:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(1, 2, 4, 6, 6, $calif);
                        break;
                    case 5:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(1, 2, 4, 6, 6, $calif);
                        break;
                    case 6:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(3, 5, 8, 11, 11, $calif);
                        break;
                    case 7:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(5, 8, 11, 14, 14, $calif);
                        break;
                    case 8:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(7, 10, 13, 16, 16, $calif);
                        break;

                }
            }
            //Si quiero calcular los niveles de riesgos por Dominio de la GUIA 3
            else if ($numGuia == 3){
                switch ($numCat_Dom){
                    case 1:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(5, 9, 11, 14, 14, $calif);
                        break;
                    case 2:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(15, 21, 27, 37, 37, $calif);
                        break;
                    case 3:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(11, 16, 21, 25, 25, $calif);
                        break;
                    case 4:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(1, 2, 4, 6, 6, $calif);
                        break;
                    case 5:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(4, 6, 8, 10, 10, $calif);
                        break;
                    case 6:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(9, 12, 16, 20, 20, $calif);
                        break;
                    case 7:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(10, 13, 17, 21, 21, $calif);
                        break;
                    case 8:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(7, 10, 13, 16, 16, $calif);
                        break;
                    case 9:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(6, 10, 14, 18, 18, $calif);
                        break;
                    case 10:
                        $nivelDeRiesgo = AsignaNivelesDeRiesgo(4, 6, 8, 10, 10, $calif);
                        break;
                }
            }
            break;
    }
    return $nivelDeRiesgo;
}

?>