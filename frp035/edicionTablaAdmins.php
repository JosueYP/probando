<?php
    include('conexion.php');

    if($_POST['action'] == 'edit'){
        $data = array(
            ':nombre' => $_POST['nombre'],
            ':correo' => $_POST['correo'],
            ':idUsuario' => $_POST['idUsuario']
        );

        $query = "UPDATE usuarios SET nombre= :nombre, correo = :correo WHERE idUsuario = :idUsuario";

        $statement = $mysqli->prepare($query);
        $statement->execute($data);
        echo json_encode($_POST);
    }

    else if($_POST['action'] == 'delete'){
        $data = array(
            ':idUsuario' => $_POST['idUsuario']
        );

        $query = "delete from usuarios WHERE idUsuario = :idUsuario";

        $statement = $mysqli->prepare($query);
        $statement->execute($data);
        echo json_encode($_POST);
    }

?>