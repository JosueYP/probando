<?php
    //Guardo el valor de la clave de esta empresa para poder usarla en todo el archivo
    $claveCentro = $_GET['claveCentro'];
    $claveProceso = $_GET['claveProceso'];
    $nombreProceso = $_GET['nombreProceso'];
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
  	   <!-- these css and js files are required by php grid -->
        <!-- Esto es para poder usar Datatables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <!-- Esto es para poder usar Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
        <!-- Esto es para poder usar JQuery -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <!-- Esto es para poder usar Sweetalert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel=stylesheet type="text/css" href="estilos.css">

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var claveCentro = "<?php echo $claveCentro?>";
            //Obtengo y guardo el valor de la clave del proceso que estoy viendo
            var claveProceso = "<?php echo $claveProceso?>";
            var tipoGuia;

            //NOTA: Para poder saber si la segunda pestaña es de la Guia 2 o 3, debo obtener los valores de "guia2" y "guia3" del proceso:
            var guia2 = "<?php echo $_GET['a'] ?>";

            if(guia2 == 1)
                tipoGuia = 2;
            else
                tipoGuia = 3;

            window.onload = function(){
                //Al cargar la pagina, debo configurar las 2 tablas para que aparezcan la lista de las personas asignadas a cada Guia
                configuraTabla(claveProceso, 1, "tabla1"); //<<-------- MEJORAR. El valor del Num. de Guia debe ser dinamico <----
                configuraTabla(claveProceso, tipoGuia, "tabla2"); //<<-------- MEJORAR. El valor del Num. de Guia debe ser dinamico <----

                //Incluyo esto para que cuando se muestre la pestaña de la tabla, se acomode la cabecera de la tabla
                $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
                    $($.fn.dataTable.tables(true)).DataTable()
                        .columns.adjust();
                });

                //Hago esto para que se muestre la pestaña 1
                $('[href="#opcion1"]').tab('show');

                //Configuro la tabla de empleados vigentes que aparecera en el Modal
                configuraTablaPersonalVig(claveCentro);
            }

            function configuraTabla(_claveProceso, _numGuia, idTabla){
                var tabla = $('#'+idTabla).DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getListaAsistentes", "claveProceso": _claveProceso, "numGuia": _numGuia}
                    },
                    "columns":[
                        {"data":"idEmp"},
                        {"data":"claveDepto"}, 
                        {"data":"matricula"}, 
                        {"data":"nombre"},
                        {"data":"fecha"}
                    ],
                    "columnDefs": [ 
                        {"className": "dt-center", "targets": [1,2,4] }, 
                        {"targets": [0], "visible": false}
                    ],
                    "scrollY": "200px", "scrollCollapse": true, "paging": false
                });
            }

            function configuraTablaPersonalVig(_claveCentro){
                var tabla = $('#tablaPersonalVig').DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getPersonalVigByCentro", "claveCentro": _claveCentro}
                    },
                    "columns":[
                        {"data":"claveDepto"},
                        {"data":"matricula"}, 
                        {"data":"nombreEmpleado"},
                        {"data":"correo"}
                    ],
                    "columnDefs": [
                        {"className": "dt-center", "targets": [0,1] }
                    ],
                    "scrollY": "200px", "scrollCollapse": true, "paging": false
                });
                
                $('#tablaPersonalVig tbody').on( 'click', 'tr', function () {
                    $(this).toggleClass('selected');
                });

                $('#btnAgregarSelecc').click( function () {
                    var filasSelecc = tabla.rows('.selected').data();

                    //console.log(filasSelecc);

                    //Por cada una de las filas seleccionadas, debo agregar un registro a la lista de asistentes de esta Guia
                    
                    //Pero Antes de eso, debo verificar si el empleado no esta ya agregado a la lista de esa Guia

                    for (var i=0; i < filasSelecc.length ;i++){
                        console.log("Matricula:" + filasSelecc[i]["matricula"]);
                    }
                } );
                            
                /*$('#tablaPersonalVig tbody').on('click', 'tr', function () {
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    } else {
                        tabla.$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                    }
                });*/

                /*
                $('#tablaPersonalVig tbody').on('dblclick', 'tr', function () {
                    var data = tabla.row(this).data();
                    //Le paso la matricula del empleado al Input y cierro el modal
                    $("#matricula").val(data["matricula"]);
                    $('#modalPersonalVig').modal('toggle');
                    //Luego recargo la pagina para que se muestre la info del empleado
                    window.location.href = 'personal.php?matricula='+$("#matricula").val();
                });
                */
            }

            function agregarEmpleados(){
                //alert("EJEMPLO");

                $('#tablaPersonalVig').modal('show');
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
            <!-- NOTA: Aqui se debe de mostrar el NOMBRE del proceso de encuestas que estoy viendo -->
            <div class="card-header" style="background-color: #0070c0; color: white;" > <b> <?php echo $nombreProceso?> </b> </div>
            
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a href="#opcion1" class="nav-link" role="tab" data-toggle="tab"> <b> Guia 1 </b></a>
                    </li>
                    <li class="nav-item">
                        <a href="#opcion2" class="nav-link" role="tab" data-toggle="tab"> <b> Guia 2/3 </b></a>
                    </li>
                </ul>

                <!-- Aqui ya comienza el codigo de lo que habra en cada una de las pestañas -->
                <div class="tab-content">
                    <div class="tab-pane fade" id="opcion1"> <br>
                        <!-- Tabla de personal vigente -->
                        <table class="display" id="tabla1">
                            <thead>
                                <tr>
                                <th>ID</th>
                                <th>Depto</th>
                                <th>Matricula</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                </tr>
                            </thead>
                        </table>

                        <br>
                        <button id="btnAccion" class="btn btn-outline-secondary float-right" type="button" data-bs-toggle="modal" data-bs-target="#modalPersonalVig">Agregar empleados</button>
                    </div>

                    <div class="tab-pane fade" id="opcion2"> <br>
                        <!-- Tabla de personal vigente -->
                        <table class="display" id="tabla2">
                            <thead>
                                <tr>
                                <th>ID</th>
                                <th>Depto</th>
                                <th>Matricula</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <br>  
            
    </div><br>

    <!-- MODAL DE PERSONAL VIGENTE -->
    <div class="modal fade" id="modalPersonalVig">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Catálogo de personal vigente</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <table class="table table-sm" id="tablaPersonalVig">
                        <thead>
                            <tr>
                            <th>Depto.</th>
                            <th>Matricula</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            </tr>
                        </thead>
                    </table>

                    <br>
                    <button class="btn btn-secondary float-right" id="btnAgregarSelecc" type="button">Agregar seleccionados</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>

    <!-- Esto es para... varias cosas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>