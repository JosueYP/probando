<?php
    include('conexion.php');

    if($_POST['action'] == 'edit'){
        $data = array(
            ':nombreCentro' => $_POST['nombreCentro'],
            ':ubicacion' => $_POST['ubicacion'],
            ':idCentro' => $_POST['idCentro']
        );

        $query = "UPDATE centrostrabajo SET nombreCentro= :nombreCentro, ubicacion = :ubicacion WHERE idCentro = :idCentro";

        $statement = $mysqli->prepare($query);
        $statement->execute($data);
        echo json_encode($_POST);
    }

?>