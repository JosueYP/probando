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
                configuraTablaPersonalVig();
            });

            function configuraTablaPersonalVig(){
                var tabla = $('#tablaPersonalVig').DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getPersonalVig"}
                    },
                    "columns":[
                        {"data":"depto"},
                        {"data":"matricula"}, 
                        {"data":"nombre"}, 
                        {"data":"puesto"}, 
                        {"data":"fecAlta"}
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [0,1,3,4] }
                    ],
                    "scrollY": "350px", "scrollCollapse": true, "paging": false
                });
            }

            function configuraTablaPersonalBaja(){
                var tabla = $('#tablaPersonalBaja').DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getPersonalBaja"}
                    },
                    "columns":[
                        {"data":"depto"},
                        {"data":"matricula"}, 
                        {"data":"nombre"}, 
                        {"data":"puesto"}, 
                        {"data":"fecAlta"}
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [0,1,3,4] }
                    ],
                    "scrollY": "350px", "scrollCollapse": true, "paging": false
                });
            }
        </script>
    </head>

    <body style="background-color: #f1f3f7;">
        <!-- Incluyo en la pagina la barra superior -->
        <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
        
        <!-- Pongo todo el contenido de a pagina dentro de un container -->
        <div class="container">
            <br>
            <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 600px">
                <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Nombre del catalogo </b> </div>
                <div class="card-body">
                    <!-- Tabla de personal vigente -->
                    <table class="table table-sm" id="tablaPersonalVig">
                        <thead>
                            <tr>
                                <th>Depto</th>
                                <th>Matricula</th>
                                <th>Nombre</th>
                                <th>Puesto</th>
                                <th>Fecha alta</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <br>  
            
            <!-- Aqui ya comienza el codigo del segundo card -->
            <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                <div class="card-header" style="background-color: #0070c0;" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link" style="color: white;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <b>Nuevo registro</b>
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        Aqui va el contenido del card
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