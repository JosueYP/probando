<?php
    session_start();
    //Guardo el valor de la clave de esta empresa para poder usarla en todo el archivo
    $claveEmpresa = $_SESSION['claveEmpresa'];
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Añado Todos los archivos CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        
        <link rel=stylesheet href="estilos.css">
        <link rel="shortcut icon" href="favicon.png">
        <link rel=stylesheet type="text/css" href="tabdrop/css/tabdrop.css">

        <title>Departamentos</title>

        <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";

            window.onload = function(){
                //Ajusto los nav tabs
                //$('.nav-pills, .nav-tabs').tabdrop({text: "Mas opciones"}, 'layout');

                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaCentroTrabajo",
                        claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        var centrosTrabajo = JSON.parse(res);
                        var numTabla = 1;
                        
                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("select1");
                        for(var i=0 in centrosTrabajo) {
                            option = document.createElement("option");
                            
                            //El valor corresponde al claveCentro "[1]"
                            option.value = centrosTrabajo[i][1];
                            option.text = centrosTrabajo[i][3];
                            select.appendChild(option);

                            //Configuro Cada una de las tablas de departamemtos <<<
                            configuraTablaDeptos(centrosTrabajo[i][1], centrosTrabajo[i][1]);
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

                /*
                //NUEVO <------------
                $('.nav-tabs').tabdrop().on('tabdrop.layout.complete', function () {
                    // initialize dropdown
                    $('.dropdown-toggle').dropdown();

                    // initialize items
                    $('.dropdown-menu a[data-toggle="tab"]').click(function (e) {
                        e.stopPropagation();
                        e.preventDefault();
                        $(this).tab('show');
                    });
                });*/

                $('.nav-pills, .nav-tabs').tabdrop();
            }

            function configuraTablaDeptos(idTabla, claveCentro){
                var tabla = $('#'+idTabla).DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getDepartamentos","claveCentro": claveCentro}
                    },
                    "columns":[
                        {"data":"claveDepto"},
                        {"data":"nombreDepto"}, 
                        {"data":"empleadosvig"} 
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [0,2] }
                    ],
                    "scrollY": "230px", "scrollCollapse": true, "paging": false
                });
            }

            function agregarDepto(){
                //1. Verifico si todos los campos estan ingresados:
                if($("#claveDepto").val() == "" || $("#nombreDepto").val() == "" )
                    Swal.fire('', 'Ingrese todos los campos para poder agregar el departamento', 'info')
                
                //2. Verifico si la clave de depto. ingresada ya esta usada por Otro Depto. del Mismo centro de trabajo
                else if(laClaveYaEstaUsada())
                    Swal.fire('', 'Ya existe un departamento en el centro de trabajo seleccionado con la misma clave de departamento. Ingrese una clave diferente', 'info')

                //3. Verifico si el nombre de depto. ingresado no lo tiene ya otro depto. del Mismo centro de trabajo
                else if(elNombreDeptoEstaUsado())
                    Swal.fire('', 'Ya existe un departamento en el centro de trabajo seleccionado con el mismo nombre. Ingrese un nombre diferente', 'error')

                //4. Si no hay ningun error, ya puedo insertar el registro:
                else{
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "insertarDepto", 
                            claveCentro: $("#select1").val(),
                            claveDepto: $("#claveDepto").val(),
                            nombreDepto: $("#nombreDepto").val().trim()
                        },
                        success:function(res){
                            console.log("El resultado de la insercion fue: "+res);
                            if(res>0){
                                //Aqui se limpian los campos que se llenaron:
                                $("#claveDepto").val("");
                                $("#nombreDepto").val("");
                                var idclaveCentro = $("#select1").val()
                                
                                //Actualizo los datos de la tabla para que se muestre el nuevo registro 
                                $('#'+idclaveCentro).DataTable().ajax.reload();
                                //Pasar el id de la tabla que corresponde al centro de trabajo en el cual
                                //se agrego en el nuevo departamento.

                                //Mando mensaje de confirmacion al usuario
                                Swal.fire('', 'Departamento agregado correctamente', 'success')
                            }
                            else
                                Swal.fire('', 'No se pudo agregar el departamento al centro de trabajo', 'info')
                        },
                        error:function(){
                            Swal.fire('', 'Hubo un error en la base de datos', 'error')
                        }
                    });   
                }    
            }

            function cerrarSesion(){
                var boton = document.getElementById('btnCerrarSesion');

                //Mando al usuario a la pagina donde se cerrara la sesion
                window.location.href = 'cerrarSesion.php';
            }

            function laClaveYaEstaUsada(){
                var claveUsada = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getDatosDeptosByClave", claveDepto: $("#claveDepto").val(), claveCentro: $("#select1").val()},
                    success:function(res){
                        //El AJAX me regresa una fila con todos los datos del Departamento encontrado
                        var datos = JSON.parse(res);
                        //console.log(datos);
                        if(datos != null)
                            //Quiere decir que SI hay un centro con esa clave
                            claveUsada = true;
                    }
                });
                return claveUsada;
            }

            function elNombreDeptoEstaUsado(){
                //NOTA: ".trim() es para quitar los espacios del valor de un Input"
                var nombreUsado = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getDatosDeptoByNombre", nombreDepto: $("#nombreDepto").val(), claveCentro: $("#select1").val()
                    },
                    success:function(res){
                        //El AJAX me regresa una fila con todos los datos del Departamento encontrado
                        var datos = JSON.parse(res);
                        //console.log(datos);
                        if(datos != null){
                            //Quiere decir que SI hay un centro con esa clave
                            nombreUsado = true;

                            console.log("Si hay un depto con el mismo nombre");
                        }else{
                            console.log("NO hay un depto con el mismo nombre");
                        }
                    }
                });

                return nombreUsado;
            }
     
            function descargarCatalogo(claveCentroExcel){
                window.location.href = 'reportes/excel/reporteDeptos_excel.php?claveCentro='+claveCentroExcel+'&nombreCentro='+$('.nav-tabs .active').text();
            
                ///window.location.href = 'reportes/excel/reportePrueba_excel.php';
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
            <div class="card-header" style="background-color: #0070c0; color: white;" > <h6>Departamentos</h6> </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                        <!-- NUEVO: Obtengo la lista de los Centros de trabajo que tiene esta empresa -->
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
                                    <!-- Tabla de personal vigente -->
                                    <table class="display" id="'.$row["claveCentro"].'">
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Nombre del depto.</th>
                                                <th>Num. empleados</th>
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
                        <b>Nuevo departamento</b>
                    </button>
                </h5>
            </div>

            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form class="form-inline">
                                <label>Seleccione el centro de trabajo: </label>
                                <!-- Aqui se muestra el PRIMER select -->
                                <select class="form-control col-lg-9" id="select1"></select>
                            </form>
                        </div>

                        <div class="col-md-2">
                            <label>Clave del depto:</label>
                            <input type="text" class="form-control"  id="claveDepto">
                        </div>

                        <div class="col-md-4">
                            <label>Nombre del departamento:</label>
                            <input type="text" class="form-control"  id="nombreDepto">
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-secondary float-right" onclick="agregarDepto()" type="button">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><br>
   
    <!-- Agrego todos los archivos Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>    
    <script type="text/javascript" src="tabdrop/js/bootstrap-tabdrop.js"></script>

    

  </body>
</html>