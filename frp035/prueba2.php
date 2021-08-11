<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
  	   <!-- these css and js files are required by php grid -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel=stylesheet type="text/css" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

        <script type="text/javascript">
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
                    "pageLength": 5  
                });
            }
        </script>
  </head>

  <body style="background-color: #f1f3f7;">
    <!-- Incluyo en la pagina la barra superior -->
    <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
    
    <div class="container">
        <br>
        <!-- CODIGO DE LA PRIMERA CARD -->
        <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 500px">
            <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Empleados vigentes </b> </div>
            <div class="card-body">
                <!-- Codigo de la barra de navegacion con pestañas -->
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a href="#opcion1" class="nav-link active" role="tab" data-toggle="tab"> <b> Centro 1 </b></a>
                    </li>
                    <li class="nav-item">
                        <a href="#opcion2" class="nav-link" role="tab" data-toggle="tab">Centro 2</a>
                    </li>
                    <li class="nav-item">
                        <a href="#opcion3" class="nav-link" role="tab" data-toggle="tab">Centro 3</a>
                    </li>
                </ul>

                <!-- Aqui ya comienza el codigo de lo que habra en cada una de las pestañas -->
                <div class="tab-content">
                    <!-- Contenido de la pestaña 1 -->
                    <div class="tab-pane fade show active" id="opcion1"> <br>
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

                    <!-- Contenido de la pestaña 2 -->
                    <div class="tab-pane fade" id="opcion2"> <br>
                        PESTAÑA 2
                        <!-- Aqui debera ir el codigo... -->
                    </div>

                     <!-- Contenido de la pestaña 3 -->
                     <div class="tab-pane fade" id="opcion3"> <br>
                        PESTAÑA 2
                        <!-- Aqui debera ir el codigo... -->
                    </div>
                </div>
            </div>
        </div>
        <br>  
            
        <!-- CODIGO DE LA SEGUNDA CARD -->
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>