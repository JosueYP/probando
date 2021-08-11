<?php
    include('conexion.php');

    if($_POST['action'] == 'edit'){
        $data = array(
            ':nombreProceso' => $_POST['nombreProceso'],
            ':idProceso' => $_POST['idProceso']
        );
        
        //Antes de continuar con la Edicion, pongo todos los demas procesos como Inactivos

        $query = "
        UPDATE procesosencuestas SET nombreProceso = :nombreProceso WHERE idProceso = :idProceso";

        //$query = "UPDATE procesosencuestas SET nombreProceso = ".$_POST['nombreProceso']." WHERE idProceso = ". $_POST['idProceso'];

        $statement = $mysqli->prepare($query);
        $statement->execute($data);
        echo json_encode($_POST);
    }

    if($_POST['action'] == 'delete'){
        $query = "
        DELETE FROM procesosencuestas 
        WHERE idProceso = ".$_POST["idProceso"];

        $statement = $mysqli->prepare($query);
        $statement->execute();
        echo json_encode($_POST);
    }

?>
