<!DOCTYPE html>
<html>
    <head>
        <!-- Esto es para poder usar Datatables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel=stylesheet type="text/css" href="estilos.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <!-- Esto es para poder editar la tabla -->
        <script src="https://markcell.github.io/jquery-tabledit/assets/js/tabledit.min.js"></script>
        <!-- Esto es para poder usar los iconos en los botones -->
        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <link rel="shortcut icon" href="favicon.png">

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            
        </script>
    </head>

    <body style="background-color: #0e1618; ">
        <!-- Incluyo en la pagina la barra superior -->
        <?php session_start(); include ('barraOscura.php'); ?>
        
        <!-- Pongo todo el contenido de a pagina dentro de un container -->
        <div class="container">
            <br>
            <div class="card text-white" style=" background-color: rgba(36,36,38,255); box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Centros de Trabajo </b> </div>
                <div class="card-body">
                    <!-- PRIMERA FILA -->
                    <div class="row">
                        <div class="col-md-6">
                            <form class="form-inline">
                                <label style="margin-right: 5px">Seleccione el centro de trabajo: </label>
                                <select style="background-color : #3a3a3c; color: #C9C9C9; border-color: #3a3a3c"  class="form-control col-lg-6" onchange="cambioSelectCentrosTrabajo()" id="select1">
                                    <option value="1" selected>Chedraui Centro</option>
                                    <option value="2">Chedraui Coyol</option>
                                </select>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <form class="form-inline">
                                <label style="margin-right: 5px">Departamento: </label>
                                <select style="background-color : #3a3a3c; color: #C9C9C9; border-color: #3a3a3c"  class="form-control col-lg-9" id="select2">
                                    <option value="1" selected>Manufactura</option>
                                    <option value="2">Capacitacion virtual</option>
                                </select>
                            </form>
                        </div>  
                    </div>
                    <br>

                    <!-- SEGUNDA FILA -->
                    <div class="row">
                        <div class="col-md-3">
                            <form class="form-inline">
                                <label style="margin-right: 5px">Matricula:</label>
                                <input style="background-color : #3a3a3c; color: #C9C9C9; border-color: #3a3a3c" type="text" class="form-control col-lg-6"  id="matricula">
                            </form>
                        </div>

                        <div class="col-md-6">
                            <form class="form-inline">
                                <label style="margin-right: 5px">Nombre:</label>
                                <input style="background-color : #3a3a3c; color: #C9C9C9; border-color: #3a3a3c"  type="text" class="form-control col-lg-8"  id="nombreEmpleado">
                            </form>
                        </div>     

                        <div class="col-md-3">
                            <button style="color: white;" class="btn btn-success float-right btn-sm" onclick= "agregarEmpleadosVig()" type="button">Agregar</button>
                        </div>          
                    </div>


                </div>
            </div>
            <br>  
            <div class="card text-white" style=" background-color: rgba(36,36,38,255); box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Centros de Trabajo </b> </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item" style="background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">1. Niveles de riesgo por categorias, dominios y calificacion final</label>
                            <!-- <button class="btn btn-outline-secondary float-right btn-sm" data-toggle="modal" data-target="#modalRep1">Generar</button> -->
                            <button onclick="validaProcesoEncuestas(1)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>                        
                        </li>
                        <li class="list-group-item" style="border-top: 1px solid #3a3a3c;  background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">2. Distribucion de empleados por nivel de riesgo final de cada departamento</label>
                            <button onclick="validaProcesoEncuestas(2)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item" style="border-top: 1px solid #3a3a3c;  background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">3. Niveles de riesgo final de empleados - Guia 3</label>
                            <button onclick="validaProcesoEncuestas(3)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item" style="border-top: 1px solid #3a3a3c;  background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">4. Empleados que realizaron las Guias 1, 2 o 3</label>
                            <button onclick="validaProcesoEncuestas(4)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item" style="border-top: 1px solid #3a3a3c;  background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">5. Respuestas del empleado de la Guia 1, 2 o 3</label>
                            <button onclick="validaProcesoEncuestas(5)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item" style="border-top: 1px solid #3a3a3c;  background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">6. Empleados que requieren valoracion - Guia 1</label>
                            <button onclick="validaProcesoEncuestas(6)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item" style="border-top: 1px solid #3a3a3c;  background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">7. Niveles de riesgo por Categoria o Dominio de cada empleado - Guia 3</label>
                            <button onclick="validaProcesoEncuestas(7)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        
                        <li class="list-group-item" style="border-top: 1px solid #3a3a3c;  background-color: rgba(36,36,38,255);">
                            <label style="font-size: 17px;">8. Frecuencia de respuesta de cada pregunta - Guia 1, 2 o 3</label>
                            <button onclick="validaProcesoEncuestas(8)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                    </ul>

                </div>
            </div>
          
        </div><br>

        <!-- Esto es para... varias cosas -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    </body>
</html>