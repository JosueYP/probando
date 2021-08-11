<?php
    session_start();
    //Guardo el valor de la clave de esta empresa para poder usarla en todo el archivo
    $claveEmpresa = $_SESSION['claveEmpresa'];
?>


<!DOCTYPE html>
<html>
    <head>
        <!-- Esto es para poder usar Datatables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel=stylesheet type="text/css" href="estilos.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <!-- Esto es para poder editar la tabla -->
        <script src="https://markcell.github.io/jquery-tabledit/assets/js/tabledit.min.js"></script>
        <!-- Esto es para poder usar los iconos en los botones -->
        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <link rel="shortcut icon" href="favicon.png">

        <title>Centros de trabajo</title>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";

            function cerrarSesion(){
                var boton = document.getElementById('btnCerrarSesion');

                //Mando al usuario a la pagina donde se cerrara la sesion
                window.location.href = 'cerrarSesion.php';
            }

            //Al cargar la pagina, se ejecutara el siguiente codigo:
            $(document).ready(function(){
                configuraTablaCentrosTrabajo();
            });

            function configuraTablaCentrosTrabajo(){
                var tabla = $('#tablaCentrosTrabajo').DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getCentrosTrabajo", "claveEmpresa": claveEmpresa}
                    }, "pageLength": 8,
                    "columns":[
                        {"data":"idCentro"},
                        {"data":"claveCentro"},
                        {"data":"nombreCentro"}, 
                        {"data":"ubicacion"}, 
                        {"data":"numEmpsVigs"}, 
                        {"data":"numEmpsBaja"} 
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [1, 4, 5] }
                    ]
                });

                $('#tablaCentrosTrabajo').on('draw.dt', function(){
                    //Esto se ejecutara cuando se dibuje la tabla. Pero solo se debe de hacer una vez
                    $('#tablaCentrosTrabajo').Tabledit({
                        url:'edicionTablaCentrosTrabajo.php', dataType:'json',
                        columns:{
                            identifier : [0, 'idCentro'],
                            editable:[
                                [2, 'nombreCentro'], [3, 'ubicacion']
                            ]
                        },
                        hideIdentifier:true, restoreButton:false, deleteButton:false,
                        buttons: {
                            edit: {
                                class: 'btn btn-sm btn-outline-secondary',
                                html: '<span class="fas fa-pencil-alt"></span>',
                                action: 'edit'
                            },
                            save: {
                                class: 'btn btn-sm btn-success', onclick: 'prueba2()',
                                html: '<span class="fas fa-save"></span>'
                            }
                        },
                        onSuccess:function(data, textStatus, jqXHR){
                            //Esto pasara si las acciones SI se llevaron a cabo
                            if(data.action == 'edit'){
                                Swal.fire('', 'El centro de trabajo se ha editado correctamente', 'success')
                                $('#tablaCentrosTrabajo').DataTable().ajax.reload();
                            }
                        },
                        onAjax:function(action, data, serialize) {
                            //Si voy a Editar el centro de trabajo, valido lo siguiente:
                            if(action === "edit"){
                                var valores = data.split('&');

                                //Obtengo el ID del centro
                                var fila_IdCentro = valores[0]; 
                                var fila_IdCentro_ = fila_IdCentro.split('=');
                                var idCentro = fila_IdCentro_[1];

                                //Obtengo el Nombre del centro
                                var fila_NombreCentro = valores[1];
                                var fila_NombreCentro_ = fila_NombreCentro.split('=');
                                var nombreCentro = fila_NombreCentro_[1];
                                nombreCentro = nombreCentro.replace("+"," "); //<--- Elimino los + en el nombre

                                //Obtengo la Ubicacion del centro
                                var fila_Ubicacion = valores[2];
                                var fila_Ubicacion_ = fila_Ubicacion.split('=');
                                var ubicacion = fila_Ubicacion_[1];
                                ubicacion = ubicacion.replace("+"," "); //<--- Elimino los + en el nombre
                                ubicacion = ubicacion.replace("%2C"," "); //<--- Cambio los "%2C" por comas (,)

                                if(ubicacion == ""){
                                    Swal.fire('', 'Ingrese la ubicacion del centro de trabajo', 'info')
                                    return false;
                                }
                                else if(nombreCentro == ""){
                                    Swal.fire('', 'Ingrese el nombre del centro de trabajo', 'info')
                                    return false;
                                }else{
                                    //Verifico si hay OTRO Centro de trabajo que tenga el mismo nombre
                                    if(elNombreCentroEstaUsado(nombreCentro, idCentro)){
                                        Swal.fire('', 'Ya existe otro centro de trabajo con el mismo nombre. Ingrese un nombre diferente', 'error')
                                        return false;
                                    }else
                                        return true;
                                }
                            }
                        }
                    });
                });
            }

            function elNombreCentroEstaUsado(_nombreCentro, idCentro){
                //Busco la Clave del centro que estoy editando, para poder usar bien el AJAX:
                var datosTabla = $('#tablaCentrosTrabajo').DataTable().rows().data();
                var _claveCentro;

                for(var i=0; i<datosTabla.length; i++){
                    if(datosTabla[i]["idCentro"] == idCentro)
                        _claveCentro = datosTabla[i]["claveCentro"];
                }

                //Verifico si hay otro centro de trabajo (de Esta misma empresa) que tenga el mismo nombre PERO que no sea este:
                var elNombreCentroEstaUsado = false;
                
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "verificaNombreCentroRepetido", nombreCentro: _nombreCentro, claveEmp: claveEmpresa, claveCentro: _claveCentro},
                    success:function(res){
                        var datos = JSON.parse(res);
                        
                        if(datos != null)
                            //Quiere decir que SI hay un Centro de trabajo en esta empresa que se llama igual (sin contar a este)
                            elNombreCentroEstaUsado = true;
                    }
                });
                return elNombreCentroEstaUsado;
            }

        </script>
    </head>

    <body style="background-color: #f1f3f7;">
        <!-- Incluyo en la pagina la barra superior -->
        <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
        
        <!-- Pongo todo el contenido de a pagina dentro de un container -->
        <div class="container">
            <br>
            <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 650px">
                <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Centros de Trabajo </b> </div>
                <div class="card-body">
                    <!-- Tabla de personal vigente -->
                    <table class="table table-sm" id="tablaCentrosTrabajo">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Clave</th>
                                <th>Nombre</th>
                                <th>Ubicacion</th>
                                <th>Empleados vigentes</th>
                                <th>Empleados dados de baja</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <br>  
          
        </div><br>

        <!-- Esto es para... varias cosas -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    </body>
</html>