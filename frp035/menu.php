<?php
    session_start();
        
    //>>>Aqui va a faltar mejor para evitar que un curioso quiera ingresar a fuerzas.

    //Recibo lo que me mandaron por GET y lo alnacneo en variables de sesion
    if (isset($_GET['rolUsuario'])){
        $_SESSION['rolUsuario'] = $_GET['rolUsuario']; 
        $_SESSION['claveEmpresa'] = $_GET['claveEmpresa']; 
        $_SESSION['claveCentro'] = $_GET['claveCentro']; 
        $_SESSION['matricula'] = $_GET['matricula']; 
        $_SESSION['nombre'] = $_GET['nombre']; 

        //Almaceno en variable de sesion el Nombre del centro de trabajo
        $_SESSION['nombreCentro'] = $_GET['nombreCentro']; 
        $_SESSION['nombreEmpresa'] = $_GET['nombreEmpresa']; 
        $_SESSION['idUsuario'] = $_GET['idUsuario']; 
    
        //>>>>>>>>>>>>>>>>>>>>
        //Dependiendo del ROL del usuario, lo mandare a una pagina u otra:
        
        /*
        if($_GET['rolUsuario'] == 1)
            header("Location: procesos-encuestas.php");
        else if($_GET['rolUsuario'] == 0)
            header("Location: encuestas.php");
        */
        //Ya que defini las variables de sesion, mando de nuevo a la pagina de "Mi menu"
        header("Location: menu.php");
    }

    $rolUsuario = $_SESSION['rolUsuario'];
    $nombre = $_SESSION['nombre'];
    $nombreEmpresa = $_SESSION['nombreEmpresa'];
    $claveEmpresa = $_SESSION['claveEmpresa'];

    /*
    if($_SESSION['rolUsuario'] == ""){
        //Este usuario no debe de estar en esta pagina, y debe volver a la Landing page..
    }*/
        
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Esto es para poder usar Datatables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <!-- Esto es para poder usar Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- Esto es para poder usar JQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <!-- Esto es para poder usar Sweetalert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel=stylesheet type="text/css" href="estilos.css">
        <link rel="shortcut icon" href="favicon.png">

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <title>FRP 035 | Portal del cliente</title>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var nombre = "<?php session_start(); echo $_SESSION['nombre'] ?>";
            var rolUsuario = "<?php session_start(); echo $_SESSION['rolUsuario'] ?>";
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            
            window.onload = function(){
                //1. Solo si el empleado logeado es un Administrador, hare lo siguiente:
                if(rolUsuario == 1){
                    //Verifico si la empresa de donde es el Admin tiene algun proceso de encuestas Abierto en ese momento
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "getProcesosEncuestasAbiertosByEmpresa", claveEmpresa: claveEmpresa
                        },
                        success:function(res){
                            var procesosEncuestas = JSON.parse(res);

                            if(procesosEncuestas != null){
                                //Si SI hay procesos de encuestas activos en esta empresa, entonces obtengo sus datos:
                                for(var i=0 in procesosEncuestas) {
                                    //Por cada proceso, obtengo los datos para poder generar las graficas
                                    var _claveCentro = procesosEncuestas[i][1];
                                    var _claveProceso = procesosEncuestas[i][8];
                                    var _guia2 = procesosEncuestas[i][5]; var _guia3 = procesosEncuestas[i][6];
                                    var numEmpsRequierenValoracion;

                                    //Verifico si a este proceso le corresponde la Guia 2 o la Guia 3
                                    if(_guia2 == 1)
                                        valorGuia2_3 = 2;
                                    else if(_guia3 == 1)
                                        valorGuia2_3 = 3;


                                    /////////////////// CHECKED.
                                    //Paso 1: Obtengo el numero de empleados por cada Nivel de riesgo final de Todos los empleados del centro de trabajo
                                    $.ajax({
                                        type: "GET", url: "ajax.php", async : false, timeout: 0,
                                        data: {
                                            funcion: "getNivelesRiesgoFinalEmps", claveCentro: _claveCentro, claveDepto: 0,
                                            claveProceso: _claveProceso, numGuia: valorGuia2_3, tipoGraficas: 1, claveEmpresa: claveEmpresa
                                        },
                                        success:function(res){
                                            listaNiveles = JSON.parse(res); //Guardo la lista obtenida en una variable

                                            console.log(listaNiveles);
                                        }
                                    }); 


                                    /////////////////// CHECKED.
                                    //Paso 2: Obtengo los datos del avance de las Guias de este proceso
                                    $.ajax({
                                        type: "GET", url: "ajax.php", async : false, timeout: 0,
                                        data: {
                                            funcion: "getAvanceGuias", claveCentro: _claveCentro, claveDepto: 0, claveEmpresa: claveEmpresa,
                                            claveProceso: _claveProceso, numGuia: valorGuia2_3, tipoGraficas: 1
                                        },
                                        success:function(res){
                                            datosAvanceGuias = JSON.parse(res); //Guardo los datos del avance de las guias
                                            console.log(datosAvanceGuias);

                                            //Guardo estos datos para usarlo en otra grafica
                                            encuestadosGuia1 = datosAvanceGuias[2];
                                            totalEmpleadosCentro = datosAvanceGuias[3];
                                        }
                                    }); 


                                    /*
                                    /////////////////// 
                                    //Paso 3: Obtengo el numero de empleados que requieren atencion medica
                                    $.ajax({
                                        type: "GET", url: "ajax.php", async : false, timeout: 0,
                                        data: {
                                            funcion: "getEmpsReqAtencionMedica", claveCentro: _claveCentro, 
                                            claveDepto: 0, claveProceso: _claveProceso, tipoGraficas: 1, claveEmpresa: claveEmpresa
                                        },
                                        success:function(res){
                                            numEmpsRequierenValoracion = res;
                                        }
                                    }); 
                                    */

                                    //Paso 4: Ya que tengo todos los datos, genero las 2 graficas:
                                    generaGraficaNumEmpsNivelRiesgoFinal("grafica1_"+_claveProceso, listaNiveles);
                                    generaGraficaAvanceGuias("grafica2_"+_claveProceso, valorGuia2_3, datosAvanceGuias[0], datosAvanceGuias[1]);

                                    //Paso 5: Muestro el numero de empleados que requieren valoracion:
                                    //$("#numEmpsReqValoracion_"+_claveProceso).text("Empleados para valoración médica: "+ numEmpsRequierenValoracion);

                                    //Paso 6: Por ultimo, configuro los botones para poder descargar las graficas:
                                    configuraBtnDescargarGrafica("btnDescargarGrafica1_"+_claveProceso, "grafica1_"+_claveProceso);
                                    configuraBtnDescargarGrafica("btnDescargarGrafica2_"+_claveProceso, "grafica2_"+_claveProceso);
                                }
                            }
                        }
                    }); 
                }
            }

            //Funcion para poder descargar las graficas en JPG
            function configuraBtnDescargarGrafica(_idBoton, _idGrafica){
                document.getElementById(_idBoton).addEventListener('click', function(){
                    /*Get image of canvas element*/
                    var url_base64jp = document.getElementById(_idGrafica).toDataURL("image/jpg");
                    /*get download button (tag: <a></a>) */
                    var a =  document.getElementById(_idBoton);
                    /*insert chart image url to download button (tag: <a></a>) */
                    a.href = url_base64jp;
                });
            }

            function generaGraficaNumEmpsNivelRiesgoFinal(_idGrafica, _datosNiveles){
                var ctx = document.getElementById(_idGrafica).getContext('2d');

                var myChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: { 
                        labels: ['Nulo', 'Bajo', 'Medio', 'Alto', 'Muy alto'],
                        datasets: [{
                            label: "Niveles",
                            data: _datosNiveles,
                            backgroundColor: [
                                'rgba(0, 255, 228)',
                                'rgba(0, 255, 100)',
                                'rgba(255, 255, 75)',
                                'rgba(255, 194, 30)',
                                'rgba(255, 90, 41)'
                            ], hoverOffset: 5
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Numero de empleados por nivel de riego final'
                        },
                        legend: {
                            position: 'left'
                        }
                    }
                });
            }

            function generaGraficaAvanceGuias(_idGrafica, _numGuia, _yaRealizo, _noRealizo){
                var grafica_AvanceGuias = document.getElementById(_idGrafica).getContext('2d');

                var arrayAvanceGuias = ["Avance Guia 1", "Avance Guia "+_numGuia],
                    yaRealizo = _yaRealizo;
                    noRealizo = _noRealizo;

                var graficaAvanceGuias = new Chart(grafica_AvanceGuias, {
                    type: 'horizontalBar',
                    data: {
                        labels: arrayAvanceGuias,
                        datasets: [
                            {
                                label: 'Ya realizo',
                                data: yaRealizo, axis: 'y',
                                backgroundColor: 'rgb(113,255,120)',
                                borderColor: 'rgb(113,255,120)',
                                borderWidth: 1
                            },
                            {
                                label: 'No ha realizado',
                                data: noRealizo, axis: 'y',
                                backgroundColor: 'rgb(228,228,228)',
                                borderColor: 'rgb(228,228,228)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                stacked: true, maxBarThickness: 70,
                                ticks: {
                                    display: false //this will remove only the label
                                }
                            }],
                            yAxes: [{
                                stacked: true
                            }]
                        },
                        plugins: {
                            datalabels: {
                                font: {
                                    weight: 'bold'
                                    },
                                formatter: function(value, context) {
                                    return value;
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Avance de cada una de las guias'
                        }
                    }
                });
            }

            function cerrarSesion(){
                var boton = document.getElementById('btnCerrarSesion');

                //Mando al usuario a la pagina donde se cerrara la sesion
                window.location.href = 'cerrarSesion.php';
            }

        </script>

    </head>

    <body style="background-color: #f1f3f7;">
        <!-- Incluyo en la pagina la barra superior -->
        <?php 
            session_start(); 
            include ('barra'.$_SESSION['rolUsuario'].'.php'); 
        ?>

        <div class="container" style="justify-content: center; align-items: center;">
            <br>
            <div class="row">
                <!-- Dependiendo de si es Admin o Empleado, mostrare unas card u otras -->
                <?php
                    session_start(); 

                    //Defino cuales van a ser las porciones de codigo que se repetiran:
                    $cardDatosUsuario = '<div class="col-md-3"> </div>
                                        <div class="col-md-6"> 
                                            <div class="card sombra" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); ">
                                                <div class="card-header" style="background-color: #0070c0; color: white;" > <b> ¡Bienvenido/a! </b> </div>
                                                
                                                <div class="card-body" style="text-align: center;">
                                                    <img src="user.png" width="20%" style="margin-bottom: 10px;"> <br>
                                                    <h6 class="modal-title col-12 text-center">'.$nombre.'</h6>
                                                    
                                                    <h6 class="modal-title col-12 text-center" style="color: gray">'.$nombreEmpresa.'</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3"> </div>';
                    
                    if($rolUsuario == 1){
                        //Verifico si en alguno de los centros de trabajo (en los cuales el empleado es Admin) hay algun proceso de encuestas Abierto
                        require('cn.php');

                        $consulta = "SELECT claveProceso, nombreProceso, (select nombreCentro from centrostrabajo where claveCentro like p.claveCentro limit 1) as nombreCentro
                                    FROM procesosencuestas as p
                                    where claveCentro in (SELECT claveCentro FROM centrostrabajo where claveEmp = ".$claveEmpresa." and status = 1) and status = 1 order by claveCentro";
                        
                        //Ejecuto la consulta
                        $resultado = $mysqli->query($consulta);
                        $numProcesosActivos = $resultado->num_rows;
          
                        if($numProcesosActivos > 0){
                            //Imprimo las cards con las graficas
                            echo '<div class="col-md-4"> 
                                    <div class="card sombra" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); ">
                                        <div class="card-header" style="background-color: #0070c0; color: white; ">  ¡Bienvenido/a! </div>
                                        
                                        <div class="card-body" style="text-align: center;">
                                            <img src="user.png" width="25%" style="margin-bottom: 10px;"> <br>
                                            <h6 class="modal-title col-12 text-center">'.$nombre.'</h6>
                                            
                                            <h6 class="modal-title col-12 text-center" style="color: gray">'.$nombreEmpresa.'</h6>
                                        </div>
                                    </div>
                                </div>
                
                                <div class="col-md-8"> 
                                    <div class="row">
                                        <div class="col-md-12">';

                            //Imprimo una card por cada uno de los Proceso Activos:
                            while($row = $resultado->fetch_assoc()){
                                echo    '<div class="card sombra" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2)">
                                            <div class="card-header" style="background-color: #0070c0; color: white; "> '.$row[nombreCentro].'</div>
                                            
                                            <div class="card-body">
                                                <div style="text-align: center; margin-bottom: 5px;">
                                                    <h6 class="modal-title col-12 text-center">'.$row[nombreProceso].'</h6>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-7"> 
                                                        <div style="height: 200px">
                                                            <canvas id="grafica1_'.$row[claveProceso].'" width="100" height="100"></canvas>

                                                            <!--Agrego el boton para descargar la imagen -->
                                                            <a id="btnDescargarGrafica1_'.$row[claveProceso].'" download="Num. de empleados por nivel de riesgo final - '.$row[nombreCentro].' - '.$row[nombreProceso].'.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                                <!-- Download Icon -->
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-5"> 
                                                        <div style="height: 200px">
                                                            <canvas id="grafica2_'.$row[claveProceso].'"></canvas>

                                                            <!--Agrego el boton para descargar la imagen -->
                                                            <a id="btnDescargarGrafica2_'.$row[claveProceso].'" download="Avance de cada una de las Guias - '.$row[nombreCentro].' - '.$row[nombreProceso].'.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                                <!-- Download Icon -->
                                                                <i class="fa fa-download"></i>
                                                            </a><br><br>

                                                            <label style="font-size:15px" id="numEmpsReqValoracion_'.$row[claveProceso].'"> </label><br>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><br>';
                            }
                            
                            //Imprimo el codigo que cierra los Divs
                            echo '
                                        </div>
                                    </div>
                                </div>';

                        }else{
                            //Solo imprimo los datos del usuario
                            echo $cardDatosUsuario;
                        }
                            
                    }else if($rolUsuario == 0){
                        //Solo tengo que mostrar los datos del usuario en el centro de la pantalla
                        echo $cardDatosUsuario;
                    }
                    
                ?>

            </div>

            <br>
            <br>  
            
        </div><br>

        <!-- Esto es para... varias cosas -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    </body>
</html>