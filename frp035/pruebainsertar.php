<!DOCTYPE html>
<html>
    <head>
        <!-- Esto es para poder usar Datatables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <!-- Esto es para poder usar Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- Esto es para poder usar JQuery -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <!-- Esto es para poder usar Sweetalert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel=stylesheet type="text/css" href="estilos.css">

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            //Al cargar la pagina, se ejecutara el siguiente codigo:
            $(document).ready(function(){
                configuraTablaCentrosTrabajo();
            });

            function configuraTablaCentrosTrabajo(){
                var tabla = $('#tablaCentrosTrabajo').DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "centroTrabajo"}
                    },
                    "columns":[
                        {"data":"claveCentro"},
                        {"data":"nombreCentro"}, 
                        {"data":"ubicacion"}, 
                        {"data":"numEmpsVigs"}, 
                        {"data":"numEmpsBaja"} 
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [0,1,3,4] }
                    ],
                    "scrollY": "350px", "scrollCollapse": true, "paging": false
                });
            }

            function agregarCentro(){
                //1. Verifico si todos los campos estan ingresados:
                if($("#claveCentro").val() == "" || $("#nombreCentro").val() == "" || $("#ubicacion").val() == "")
                    Swal.fire('', 'Ingrese todos los campos para poder agregar el centro de trabajo', 'error')
                
                //2. Verifico si la clave del centro no esta ya usada por otro centro
                else if(laClaveYaEstaUsada())
                    Swal.fire('', 'La clave ingresada ya pertenece a otro centro de trabajo', 'error')

                //3. Verifico si el nombre del centro no esta ya usado por otro centro
                else if(elNombreCentroEstaUsado())
                    Swal.fire('', 'El nombre ingresado ya pertenece a otro centro de trabajo', 'error')

                //4. Si no hay ningun error, ya puedo insertar el registro:
                else{
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "insertarCentroTrabajo", 
                            claveCentro: $("#claveCentro").val(),
                            claveEmp: 2324,
                            nombreCentro: $("#nombreCentro").val().trim(),
                            ubicacion: $("#ubicacion").val(),
                            status: 1
                        },
                        success:function(res){
                            console.log("El resultado de la insercion fue: "+res);
                            if(res>0){
                                //Aqui se limpian los campos que se llenaron:
                                $("#claveCentro").val("");
                                $("#nombreCentro").val("");
                                $("#ubicacion").val("");

                                //Actualizo los datos de la tabla para que se muestre el nuevo registro 
                                $('#tablaCentrosTrabajo').DataTable().ajax.reload();

                                //Mando mensaje de confirmacion al usuario
                                Swal.fire('', 'El centro se ha agregado correctamente', 'success')
                            }
                            else
                                Swal.fire('', 'No se pudo insertar el centro de trabajo', 'info')
                        },
                        error:function(){
                            Swal.fire('', 'Hubo un error en la base de datos', 'error')
                        }
                    });  
                }    
            }

            function laClaveYaEstaUsada(){
                var claveUsada = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getDatosCentroByClave", claveCentro: $("#claveCentro").val()},
                    success:function(res){
                        //console.log("El resultado del ajax 1 es: "+res);
                        //El AJAX me regresa una fila con todos los datos del centro encontrado
                        var datos = JSON.parse(res);
                        //console.log(datos);
                        if(datos != null)
                            //Quiere decir que SI hay un centro con esa clave
                            claveUsada = true;
                    }
                });
                return claveUsada;
            }

            function elNombreCentroEstaUsado(){
                //NOTA: ".trim() es para quitar los espacios del valor de un Input"
                var nombreUsado = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getDatosCentroByNombre", nombreCentro: $("#nombreCentro").val().trim()},
                    success:function(res){
                        //El AJAX me regresa una fila con todos los datos del centro encontrado
                        var datos = JSON.parse(res);
                        
                        if(datos != null)
                            //Quiere decir que SI hay un centro con ese nombre
                            nombreUsado = true;
                    }
                });
                return nombreUsado;
            }

        </script>
    </head>

    <body style="background-color: #f1f3f7;">
        <!-- Incluyo en la pagina la barra superior -->
        <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
        
        <!-- Pongo todo el contenido de a pagina dentro de un container -->
        <div class="container">
            <br>
            <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 500px">
                <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Centros de Trabajo </b> </div>
                <div class="card-body">
                    <!-- Tabla de personal vigente -->
                    <table class="table table-sm" id="tablaCentrosTrabajo">
                        <thead>
                            <tr>
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

            <!-- CODIGO DE LA SEGUNDA CARD -->
            <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                <div class="card-header" style="background-color: #0070c0;" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link" style="color: white;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <b>Nuevo centro</b>
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        <!-- PRIMERA FILA -->
                        <div class="row">
                            <div class="col-md-2">
                                <label class="mr-sm-2">Clave:</label>
                                <input type="text" class="form-control" id="claveCentro">
                            </div>

                            <div class="col-md-4">                            
                                <label class="mr-sm-2">Nombre:</label>
                                <input type="text" class="form-control" id="nombreCentro">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="mr-sm-1">Ubicacion:</label>                            
                                <input type="text" class="form-control" id="ubicacion">
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-secondary float-right" onclick="agregarCentro()" type="button">Agregar</button>
                            </div>

                        </div>
                        <br>
                    </div>
                </div>
            </div>
          
        </div><br>

        <!-- Esto es para... varias cosas -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    </body>
</html>