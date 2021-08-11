<?php
    session_start();
    //Guardo el valor de la clave de esta empresa para poder usarla en todo el archivo
    $claveEmpresa = $_SESSION['claveEmpresa'];
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
  	   <!-- these css and js files are required by php grid -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="tabdrop/css/tabdrop.css">

        <link rel=stylesheet type="text/css" href="estilos.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        
        <script type="text/javascript" src="tabdrop/js/bootstrap-tabdrop.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel="shortcut icon" href="favicon.png">

        <title>Personal vigente</title>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            console.log("CLAVE DE EMPRESA: "+ claveEmpresa);

            window.onload = function(){
                //Inicializo los 2 select de la seccion "Nuevo empleado"
                configuraSelectCentrosTrabajo();
                configuraSelectListaDeptos();

                //Incluyo esto para que cuando se muestre la pestaña de la tabla, se acomode la cabecera de la tabla
                $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
                        $($.fn.dataTable.tables(true)).DataTable()
                        .columns.adjust();
                });

                //Hago esto para que se muestre la pestaña 1
                $('[href="#opcion1"]').tab('show');

                $('.nav-pills, .nav-tabs').tabdrop({align: "right"});
            }

            function configuraSelectCentrosTrabajo(){
                //Cargo en el Select 1 la lista de los Centros de trabajo
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaCentroTrabajo", claveEmpresa:claveEmpresa
                    },
                    success:function(res){
                        var centrosTrabajo = JSON.parse(res);
                        var numTabla = 1;
                        
                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("select1");
                        for(var i=0 in centrosTrabajo) {
                            option = document.createElement("option");
                            option.value = centrosTrabajo[i][1];
                            option.text = centrosTrabajo[i][3];
                            select.appendChild(option);

                            configuraTablaEmpleadosVig(centrosTrabajo[i][1], centrosTrabajo[i][1]);
                            numTabla++; 
                        }
                    }
                }); 
            }

            function configuraSelectListaDeptos(){
                $("#select2").empty();

                //Cargo en el Select 2 la lista de los departamentos
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaDeptosByCentroTrabajo", claveCentro: $("#select1").val()
                    },
                    success:function(res){
                        var listaDeptos = JSON.parse(res);
                        
                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("select2");
                        
                        if(listaDeptos.length == 0){
                            option = document.createElement("option");
                            option.value = 0;
                            option.text = "--- No existen departamentos ---";
                            select.appendChild(option);
                        }
                        else{
                            //1. Muestro la lista de los deptos. en el Select de departamentos
                            for(var i=0 in listaDeptos) {
                                option = document.createElement("option");
                                option.value = listaDeptos[i][2];
                                option.text = listaDeptos[i][3];
                                select.appendChild(option);
                            }
                        }
                    }
                }); 
            }

            function cambioSelectCentrosTrabajo(){
                configuraSelectListaDeptos();
            }

            function configuraTablaEmpleadosVig(idTabla, claveCentro){
                console.log("zzz: "+idTabla+" - "+claveCentro);

                var tabla = $('#'+idTabla).DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getEmpleadosVig","claveCentro": claveCentro}
                    },
                    "columns":[
                        {"data":"matricula"},
                        {"data":"nombreEmpleado"}, 
                        {"data":"claveDepto"},
                        {"data":"nombreDepto"}
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [0, 2] }
                    ],
                    "scrollY": "200px", "scrollCollapse": true, "paging": false
                });
            }

            function agregarEmpleadosVig(){
                //1. Verifico si todos los campos estan ingresados:
                if($("#matricula").val() == "" || $("#nombreEmpleado").val() == "" )
                    Swal.fire('', 'Ingrese todos los campos para poder agregar el empleado', 'error')
                
                //2. Verifico si la matricula o correo no esta ya usada por otro 
                else if(laMatriculaYaEstaUsada())
                    Swal.fire('', 'La matricula ingresada ya pertenece a un empleado creado en el centro de trabajo seleccionado', 'error')

                //4. Si no hay ningun error, ya puedo insertar el registro:
                else{
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "insertarEmpleados", claveDepto: $("#select2").val(), matricula: $("#matricula").val(),
                            nombre: $("#nombreEmpleado").val(), claveCentro: $("#select1").val()
                        },
                        success:function(res){
                            if(res>0){
                                //Aqui se limpian los campos que se llenaron:
                                $("#matricula").val("");
                                $("#nombreEmpleado").val("");
                                $("#correo").val("");
                                var idclaveCentro = $("#select1").val()
                                
                                //Actualizo los datos de la tabla para que se muestre el nuevo registro 
                                $('#'+idclaveCentro).DataTable().ajax.reload();
                                //Pasar el id de la tabla que corresponde al centro de trabajo en el cual
                                //se agrego en el nuevo departamento.

                                //Mando mensaje de confirmacion al usuario
                                Swal.fire('', 'El empleado se ha agregado correctamente', 'success')
                            }
                            else
                                Swal.fire('', 'No se pudo agregar al empleado al centro de trabajo', 'info')
                        },
                        error:function(){
                            Swal.fire('', 'Hubo un error en la base de datos', 'error')
                        }
                    });   
                }    
            }

            function laMatriculaYaEstaUsada(){
                var matriculaUsada = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getDatosEmpleadosByMatricula_claveCentro",
                        matricula: $("#matricula").val().trim(), claveCentro: $("#select1").val()
                    }, 
                    success:function(res){
                        //console.log("El resultado del ajax 1 es: "+res);
                        //El AJAX me regresa una fila con todos los datos del centro encontrado
                        var datos = JSON.parse(res);
                        //console.log(datos);
                        if(datos != null){
                            console.log("La matricula ya esta usada");
                            //Quiere decir que SI hay un centro con esa clave
                            matriculaUsada = true;
                        }else
                            console.log("No esta usada la mat");
                           
                    }
                });
                return matriculaUsada;
            }

            function elCorreoYaEstaUsado(){
                var correoUsado = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getDatosEmpleadosByCorreo", correo: $("#correo").val()}, 
                    success:function(res){
                        //console.log("El resultado del ajax 1 es: "+res);
                        //El AJAX me regresa una fila con todos los datos del centro encontrado
                        var datos = JSON.parse(res);
                        //console.log(datos);
                        if(datos != null)
                            //Quiere decir que SI hay un centro con esa clave
                            correoUsado = true;
                    }
                });
                return correoUsado;
            }

            function cerrarSesion(){
                var boton = document.getElementById('btnCerrarSesion');

                //Mando al usuario a la pagina donde se cerrara la sesion
                window.location.href = 'cerrarSesion.php';
            }
     
            function descargarCatalogo(claveCentroExcel){
                window.location.href = 'reportes/excel/reporteEmpsVigs_excel.php?claveCentro='+claveCentroExcel+'&nombreCentro='+$('.nav-tabs .active').text();
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
            <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Personal vigente </b> </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                        <?php
                            require('cn.php');
                            $i=1;
                            $resultado = $mysqli->query("select * from centrostrabajo where  status = 1 and claveEmp = ".$claveEmpresa);

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
                                    <!-- Tabla de personal vigente -->
                                    <table class="display" id="'.$row["claveCentro"].'">
                                        <thead>
                                            <tr>
                                                <th>Matricula</th>
                                                <th>Nombre</th>
                                                <th>Clave depto.</th>
                                                <th>Departamento</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    </br>
                                    <button onclick="descargarCatalogo('.$row["claveCentro"].')" class="btn btn-outline-success float-right btn-sm">Descargar catalogo</button>

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
                        <b>Nuevo empleado</b>
                    </button>
                </h5>
            </div>

            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    <!-- PRIMERA FILA -->
                    <div class="row">
                        <div class="col-md-6">
                            <form class="form-inline">
                                <label>Seleccione el centro de trabajo: </label>
                                <select class="form-control col-lg-6" onchange="cambioSelectCentrosTrabajo()" id="select1"></select>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <form class="form-inline">
                                <label>Departamento: </label>
                                <select class="form-control col-lg-9" id="select2"></select>
                            </form>
                        </div>  
                    </div>
                    <br>

                    <!-- SEGUNDA FILA -->
                    <div class="row">
                        <div class="col-md-3">
                            <form class="form-inline">
                                <label>Matricula:</label>
                                <input type="text" class="form-control col-lg-6"  id="matricula">
                            </form>
                        </div>

                        <div class="col-md-6">
                            <form class="form-inline">
                                <label>Nombre:</label>
                                <input type="text" class="form-control col-lg-8"  id="nombreEmpleado">
                            </form>
                        </div>     

                        <div class="col-md-3">
                            <button class="btn btn-secondary float-right" onclick= "agregarEmpleadosVig()" type="button">Agregar</button>
                        </div>          
                    </div>
                </div>
            </div>
        </div>

    </div><br>

   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
   
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

   

  </body>
</html>