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
                //alert("prueba");
            
            });

            window.onload = function(){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getListaClaveDeptos"},
                    success:function(res){
                        var periodos = JSON.parse(res);
                        
                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("select1");
                        for(var i=0 in periodos) {
                            option = document.createElement("option");
                            option.value = periodos[i][0];
                            option.text = periodos[i][3];
                            select.appendChild(option);
                        }
                    }
                }); 
            }

            function prueba1(){
                alert("El valor dela opcion seleccionada es: "+  $('#select1').val());
            }

            function prueba2(){
                alert("Diste clic al boton");
            }

        </script>
    </head>

    <body style="background-color: #f1f3f7;">
        <!-- Incluyo en la pagina la barra superior -->
        <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
        
        <!-- Pongo todo el contenido de a pagina dentro de un container -->
        <div class="container">
            <br>
            <!-- Aqui ya comienza el codigo del segundo card -->
            <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                <div class="card-header" style="background-color: #0070c0;" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link" style="color: white;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <b>Titulo</b>
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        <!-- Primera fila --------->
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Evaluaciones de personal histórico</h3>
                            </div>
                        </div> 
                        <br>

                        <!-- Segunda fila --------->
                        <div class="row">
                            <div class="col-md-6">
                                <!-- NOTA: Pongo todo el contenido denteo de un "form-inline" para que se muestre vertical -->
                                <form class="form-inline" method="get">
                                    <label class="mr-sm-2">Seleccione la opcion: </label>

                                    <!-- Aqui se muestra el PRIMER select -->
                                    <select class="form-control col-lg-4" id="select1"></select>

                                    <button type="submit" class="btn btn-space btn-secondary" onclick="prueba1()">Valor select</button>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Aqui se muestra el SEGUNDO select -->
                                <select class="form-control" id="select2">
                                    <option value="value1">Opcion 1</option>
                                    <option value="value2" selected>Opcion 2</option>
                                    <option value="value3">Opcion 3</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <!-- Tercer fila -------->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-space btn-outline-secondary float-right" onclick="prueba2()">Prueba</button>
                            </div>
                        </div> 

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