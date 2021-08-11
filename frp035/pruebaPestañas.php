<?php
    //Guardo el valor de la clave de esta empresa para poder usarla en todo el archivo
    $claveEmpresa = 2324;
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
  	   <!-- these css and js files are required by php grid -->
  
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel=stylesheet type="text/css" href="estilos.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            //NUEVO: Guardo el valor de la clave de empresa que esta en PHP
            var claveEmpresa = "<?php echo $claveEmpresa?>";
            
            //Al cargar la pagina, se ejecutara el siguiente codigo:
            window.onload = function(){
                //NUEVO: Obtengo la lista de centros de trabajos asignados a esa empresa mediante un AJAX
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getListaCentroTrabajo"},
                    success:function(res){
                        var centrosTrabajo = JSON.parse(res);
                        var numTabla = 1;

                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("select1"); //<<<<<<<<<<<<<<<<<<<<<<<
                        for(var i=0 in centrosTrabajo) {
                            option = document.createElement("option"); //<<<<<<<<<<<<<<<<<<<<<<<
                            option.value = centrosTrabajo[i][0]; //<<<<<<<<<<<<<<<<<<<<<<<
                            option.text = centrosTrabajo[i][3]; //<<<<<<<<<<<<<<<<<<<<<<<
                            select.appendChild(option); //<<<<<<<<<<<<<<<<<<<<<<<

                            //NUEVO: Configuro el datatable que corresponde a ese centro <<<<<
                            configuraTablaDeptos("tabla"+numTabla, centrosTrabajo[i][1]);
                            //console.log(numTabla +" - " +centrosTrabajo[i][3])
                            //Aumento el valor del numero de la tabla
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
        
            function configuraTablaDeptos(idTabla, claveCentro){
                var tabla = $('#'+idTabla).DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getDepartamentos","claveCentro": claveCentro}
                    },
                    "columns":[
                        {"data":"claveDepto"},
                        {"data":"nombre"}, 
                        {"data":"empleadosvig"} 
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [0,2] }
                    ],
                    "scrollY": "350px", "scrollCollapse": true, "paging": false
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
            <div class="card-header" style="background-color: #0070c0; color: white;" > <b>Departamentos</b> </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <!-- NUEVO: Obtengo la lista de los Centros de trabajo que tiene esta empresa -->
                    <?php
                        require('cn.php');
                        $i=1;
                        $resultado = $mysqli->query("select * from centrotrabajo where claveEmp = ".$claveEmpresa);

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
                    <!-- NUEVO: Igual que arriba, hago un ciclo para imprimir el N numero de tablas para cada centro de trabajo -->
                    <?php
                        require('cn.php');
                        $i=1;
                        $resultado = $mysqli->query("select * from centrotrabajo where claveEmp = ".$claveEmpresa);

                        while($row = $resultado->fetch_assoc()){
                            //Agrego una pestaña por cada centro de trabajo y le pongo el nombre del centro en la etiqueta
                            echo '<!-- Contenido de la pestaña N -->
                                <div class="tab-pane fade" id="opcion'.$i.'"> <br>
                                    <!-- Tabla de personal vigente -->
                                    <table class="display" id="tabla'.$i.'">
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Nombre del depto.</th>
                                                <th>Num. empleados</th>
                                            </tr>
                                        </thead>
                                    </table>
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

                    <!-- PRIMERA FILA -->
                    <div class="row">
                        <div class="col-md-3">
                            <!-- NOTA: Pongo todo el contenido denteo de un "form-inline" para que se muestre vertical -->
                            <form class="form-inline" method="get">
                                <label class="mr-sm-2">Centro de trabajo: </label>

                                <!-- Aqui se muestra el PRIMER select -->
                                <select class="form-control col-lg-4" id="select1"></select>
                            </form>
                        </div>

                        <label class="mr-sm-2">Clave:</label>

                        <div class="col-md-2">                            
                            <input type="text" class="form-control"  aria-label="State">
                        </div>
                        
                        <label class="mr-sm-1">Nombre:</label>
                        <div class="col-md-4">                            
                            <input type="text" class="form-control"  aria-label="Zip">
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-secondary float-right" type="button">Agregar</button>
                        </div>  
                    </div>
                    <br>
                </div>
            </div>
        </div>

    </div><br>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>