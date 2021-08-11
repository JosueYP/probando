<?php
    session_start();
    //Guardo el valor de la clave de esta empresa para poder usarla en todo el archivo
    $claveEmpresa = $_SESSION['claveEmpresa'];
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <!-- Esto es para poder usar Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- Esto es para poder usar JQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <!-- Esto es para poder usar Sweetalert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  	    <!-- Esto es para poder editar la tabla -->
        <script src="https://markcell.github.io/jquery-tabledit/assets/js/tabledit.min.js"></script>
        <!-- Esto es para poder usar los iconos en los botones -->
        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <link rel="shortcut icon" href="favicon.png">

        <!-- <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script> -->

        <title>Procesos de encuestas</title>

        <!-- Aqui va el codigo Javascript -->
         <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            var ultColAgregada = false;

            window.onload = function(){
             //Este Ajax es para el select 1 CENTROS DE TRABAJO
                
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaCentroTrabajo", claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        var centrosTrabajo = JSON.parse(res);
                        var numTabla = 1;
                        
                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("selectCentrosTrabajo");
                        for(var i=0 in centrosTrabajo) {
                            option = document.createElement("option");
                            option.value = centrosTrabajo[i][1];
                            option.text = centrosTrabajo[i][3];
                            select.appendChild(option);

                            configuraTablaEncuestas(centrosTrabajo[i][1], centrosTrabajo[i][1]);
                            numTabla++; 
                        }
                    }
                }); 
                
                //Incluyo esto para que cuando se muestre la pestaña de la tabla, se acomode la cabecera de la tabla
                $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
                    $($.fn.dataTable.tables(true)).DataTable()
                        .columns.adjust();
                });
  
                //Hago esto para que se muestre la pestaña 1
                $('[href="#opcion1"]').tab('show');

            }

            function cerrarSesion(){
                var boton = document.getElementById('btnCerrarSesion');

                //Mando al usuario a la pagina donde se cerrara la sesion
                window.location.href = 'cerrarSesion.php';
            }

            function configuraTablaEncuestas(idTabla, claveCentro){
                
                var tabla = $('#'+idTabla).DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getEncuestas", "claveCentro": claveCentro, "claveEmpresa": claveEmpresa}
                    },
                    "language":
                    {
                        "loadingRecords": "No hay datos para mostrar",
                        "sSearch": "Buscar:",
                        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sZeroRecords": "No se encontraron resultados",
                        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                        "oPaginate": {
                            "sFirst": "Primero",
                            "sLast": "Último",
                            "sNext": "Siguiente",
                            "sPrevious": "Anterior"
                        }
                    },
                    "filter": false, "responsive": true, "pageLength": 5,
                    "columns":[
                        {"data":"idProceso"},
                        {"data":"nombreProceso"},
                        {"data":"fechaCreacion"}, 
                        {"data":"numEncG1"}, 
                        {   
                            //Numero de encuestados de la Guia 2 -----
                            "render": function (data, type, full, meta) { 
                                if(full.guia2 == 1)
                                    return full.numEncG2;
                                else
                                    return '---'; 
                            }
                        }, 
                        {
                            //Numero de encuestados de la Guia 3 -----
                            "render": function (data, type, full, meta) { 
                                if(full.guia3 == 1)
                                    return full.numEncG3;
                                else
                                    return '---'; 
                            }
                        },
                        {
                            //Muestro si el proceso esta Activo o Inactivo
                            "render": function (data, type, full, meta) { 
                                if(full.status == 1)
                                    return 'Activo';
                                else
                                    return 'Inactivo'; 
                            }
                        }
                    ],
                    "columnDefs":[ 
                        {"className": "dt-center", "targets": [2,3,4,5,6] },
                        //{"targets": [0], "visible": false}
                        { "width": "40%", "targets": 1 }
                        
                    ]
                });

                $('#'+idTabla).on('draw.dt', function(){
                    var _idTabla = idTabla;

                    //Esto se ejecutara cuando se dibuje la tabla. Pero solo se debe de hacer una vez
                        $('#'+idTabla).Tabledit({
                            url:'edicion.php', dataType:'json',
                            columns:{
                                identifier : [0, 'idProceso'],
                                editable:[
                                    [1, 'nombreProceso'], [6, 'status', '{"1":"Activo", "2":"Inactivo"}']
                                ],
                                attributes: [
                                    [1, '{"required": ""}']
                                ]
                            },hideIdentifier:true, restoreButton:false, deleteButton: false,
                            buttons: {
                                edit: {
                                    class: 'btn btn-sm btn-outline-secondary',
                                    html: '<span class="fas fa-pencil-alt"></span>',
                                    action: 'edit'
                                },
                                delete: {
                                    class: 'btn btn-sm btn-outline-secondary',
                                    html: '<span class="fa fa-trash"></span>',
                                    action: 'delete'
                                },
                                save: {
                                    class: 'btn btn-sm btn-success',
                                    html: '<span class="fas fa-save"></span>'
                                },
                                confirm: {
                                    class: 'btn btn-sm btn-outline-danger',
                                    html: 'Confirmar'
                                }
                            },
                            onSuccess:function(data, textStatus, jqXHR){
                                //Esto pasara si las acciones SI se llevaron a cabo
                                if(data.action == 'delete'){
                                    $('#' + data.idProceso).remove();
                                    $('#'+_idTabla).DataTable().ajax.reload();
                                    Swal.fire('', 'El proceso se ha eliminado correctamente', 'success')

                                }else if(data.action == 'edit'){
                                    Swal.fire('', 'El proceso se ha editado correctamente', 'success')
                                    $('#'+_idTabla).DataTable().ajax.reload();
                                }
                            },
                            onAjax:function(action, data, serialize) {
                                //console.log("LA URL ES: "+ serialize);
                                //console.log("LA ACCION ES: "+ data);

                                //Se ejecuta CADA VEZ que se llama a un Ajax
                                //"Serialize" es la URL que se manda al AJAX
                                if(action === "delete"){
                                    alert("No se pued eeliminar");
                                    return false;

                                }else if(action === "edit"){
                                    var valores = data.split('&');

                                    var fila_IdProceso = valores[0]; 
                                    var fila_IdProceso_1 = fila_IdProceso.split('=');
                                    var idProceso = fila_IdProceso_1[1];

                                    var fila_NombreProc = valores[1];
                                    var fila_NombreProc_1 = fila_NombreProc.split('=');
                                    var nombreProceso = fila_NombreProc_1[1];
                                    //Elimino los + en el nombre
                                    nombreProceso = nombreProceso.replace("+"," ");
                                    
                                    console.log("EL ID DEL PROCESO ES: "+ idProceso);
                                    console.log("EL NOMBRE DEL PROCESO ES: "+ nombreProceso);

                                    if(nombreProceso == ""){
                                        Swal.fire('', 'Ingrese el nombre del proceso de encuestas', 'info')
                                        return false;
                                    }else{
                                        //Verifico si hay OTRO proceso que tenga el mismo nombre
                                        if(elNombreProcesoEstaUsado(_idTabla, nombreProceso, idProceso)){
                                            Swal.fire('', 'Ya existe otro proceso de encuestas con el mismo nombre. Ingrese un nombre diferente', 'info')
                                            return false;
                                        }else{
                                            //Verifico si el status de este Proceso se puso como Activo:
                                            //....
                                            return true;
                                        }
                                            
                                    }
                                }
                            }
                        });
                        ultColAgregada = true;
                });
                
            }

            function elNombreProcesoEstaUsado(_claveCentro, _nombreProceso, _idProceso){
                //Verifico si el nombre del proceso ingresado Ya existe en el centro seleccionado
                var elNombreProcesoEstaUsado = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getDatosProcesoEncuestasByNombre", claveCentro: _claveCentro, nombreProceso: _nombreProceso, idProceso: _idProceso},
                    success:function(res){
                        var datos = JSON.parse(res);
                        
                        if(datos != null)
                            //Quiere decir que SI hay un Proceso en ese centro de trabajo que se llama igual
                            elNombreProcesoEstaUsado = true;
                    }
                });
                return elNombreProcesoEstaUsado;
            }

            function agregarEncuesta(){
                //1. Verifico si todos los campos estan ingresados:
                if($("#identificador").val()== "")
                    Swal.fire('', 'Ingrese todos los campos para poder agregar la encuesta', 'error')
                
                //3. Verifico si el nombre del centro no esta ya usado por otro centro
                //else if(elNombreProcesoEstaUsado())
                    //Swal.fire('', 'Mensaje', 'error')
                
                else if(guiaErronea()){
                    Swal.fire({
                        title: 'Advertencia',
                        icon: 'info',
                        text: 'Por el numero de empleados del centro de trabajo seleccionado, le corresponde realizar la Guia III, ¿Desea aun asi generar el nuevo proceso con la Guia II?',
                        showCancelButton: true,
                        confirmButtonText: 'Generar',
                        cancelButtonText:'Cancelar'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            //Genero el nuevo proceso de encuestas para este centro
                            generarProcesoEncuestas();
                        }
                    })
                }
                //4. Si no hay ningun error, ya puedo insertar el registro:
                else{                    
                    generarProcesoEncuestas();
                }    
            }

            function generarProcesoEncuestas(){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "generarProcesoEncuestas", claveCentro: $("#selectCentrosTrabajo").val(),
                        nombreProceso: $("#identificador").val().trim(), numGuia: $("#selectNumGuia").val()
                    },
                    success:function(res){
                        console.log("El resultado de la insercion fue: "+res);
                        if(res>0){
                            //Ya que se inserto el nuevo proceso, hago lo siguinete:
                            //1. Aqui se limpian los campos que se llenaron:
                            $("#identificador").val("");
                            var claveCentroSelecc = $("#selectCentrosTrabajo").val()
                            
                            //2. Actualizo los datos de la tabla para que se muestre el nuevo registro 
                            $('#'+claveCentroSelecc).DataTable().ajax.reload();
                            
                            //3. Mando mensaje de confirmacion al usuario
                            Swal.fire('', 'El proceso de aplicación de encuestas se he generado correctamente', 'success')
                        }
                        else
                            Swal.fire('', 'No se pudo generar el proceso de aplicación de encuestas', 'error')
                    },
                    error:function(){
                        Swal.fire('', 'No se pudo generar el proceso de aplicación de encuestas', 'error')
                    }
                });  
            }

            function guiaErronea(){
                var guiaErronea = false;
                //Verifico si la Guia seleccionado le corresponde segun el numero de empleados
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getEmpleadosByCentroTrabajo", claveCentro: $("#selectCentrosTrabajo").val()},
                    success:function(res){
                        var empleados = JSON.parse(res);
                        
                        //Si el empleado selecciono la Guia 2, y tiene menos de 50 trabajadores
                        if($("#selectNumGuia").val() == 3 && empleados.length < 5)
                            guiaErronea = true;
                    }
                });
                return guiaErronea;
            }

        </script>
  </head>

  <body style="background-color: #f1f3f7;">
    <!-- Incluyo en la pagina la barra superior -->
    <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
    
    <div class="container">
        <br>
        <!-- CODIGO DE LA PRIMERA CARD -->
        <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 550px">
            <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Procesos de encuestas </b> </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <?php
                        require('cn.php');
                        $i=1;
                        $resultado = $mysqli->query("select * from centrostrabajo where status = 1 and claveEmp = ".$claveEmpresa);

                        while($row = $resultado->fetch_assoc()){
                            //Agrego una pestaña por cada centro de trabajo y le pongo el nombre del centro en la etiqueta
                            echo '<li class="nav-item">
                                    <a href="#opcion'.$i.'" class="nav-link" role="tab" data-toggle="tab"> <b>'.$row["nombreCentro"].' </b></a>
                                </li>';
                            $i++;
                        }                    
                    ?>
                </ul>

                <!-- Aqui ya comienza el codigo de lo que habra en cada una de las pestañas -->
                <div class="tab-content">
                    <?php
                        require('cn.php');
                        $i=1;
                        $resultado = $mysqli->query("select * from centrostrabajo where status = 1 and claveEmp = ".$claveEmpresa);

                        while($row = $resultado->fetch_assoc()){
                            //Agrego una pestaña por cada centro de trabajo y le pongo el nombre del centro en la etiqueta
                            echo '<!-- Contenido de la pestaña N -->
                                <div class="tab-pane fade" id="opcion'.$i.'"> <br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <!-- Tabla de personal vigente -->
                                            <table class="display" style="width:100%" id="'.$row["claveCentro"].'">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Nombre del proceso</th>
                                                        <th>Fecha de <br>creación</th>
                                                        <th>Encuestados <br>Guía 1</th>
                                                        <th>Encuestados <br>Guía 2</th>
                                                        <th>Encuestados <br>Guía 3</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>';
                            $i++;
                        }                    
                    ?> 
                </div>
            </div>
        </div>
        <br>  
            
        <!-- CODIGO DE LA SEGUNDA CARD -->
        <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
            <div class="card-header" style="background-color: #0070c0;" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" style="color: white;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <b>Nuevo proceso de encuestas</b>
                    </button>
                </h5>
            </div>

            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form class="form-inline" method="get">
                                <label class="mr-sm-2">Centro de trabajo:</label>

                                <!-- Aqui se muestra el PRIMER select -->
                                <select class="form-control col-lg-10" id="selectCentrosTrabajo"></select>
                            </form>
                        </div>

                        <div class="col-md-4">
                            <form class="form-inline" method="get">
                                <label class="mr-sm-2">Nombre del proceso:</label>
                                <input type="text" class="form-control col-lg-10" id="identificador">
                            </form>
                        </div>

                        <div class="col-md-2">
                            <!-- NOTA: Pongo todo el contenido denteo de un "form-inline" para que se muestre vertical -->
                            <form class="form-inline" method="get">
                                <label class="mr-sm-2">Elija la guía: </label>

                                <select class="form-control" id="selectNumGuia">
                                    <option value="2" selected> 2</option>
                                    <option value="3" > 3</option>
                                </select>
                            </form>
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-secondary float-right" onclick="agregarEncuesta()" type="button">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div><br><br>

    </div><br>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>