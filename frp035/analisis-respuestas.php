<?php
    session_start();
    $claveEmpresa = $_SESSION['claveEmpresa'];
    $nombre = $_SESSION['nombre'];
    $rolUsuario = $_SESSION['rolUsuario'];
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

        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

        <title>Análisis de respuestas</title>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var nombre = "<?php session_start(); echo $_SESSION['nombre'] ?>";
            var rolUsuario = "<?php session_start(); echo $_SESSION['rolUsuario'] ?>";
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            var listaProcesos; var valorGuia2_3; var datosRespuestasGuia2_3 = new Array();
            var numBloque = 1; var numBloques; var numGuiaAnalisis;
            
            window.onload = function(){
                //1. Configuro el Select de los Centros de trabajo
                configuraSelectCentrosTrabajo();

                //2. Configuro el Select de los Procesos de encuestas en base al Centro de trabajo seleccionado
                configuraSelectProcesosEncuestas();
            }

            function configuraSelectCentrosTrabajo(){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaCentroTrabajo", claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        var centrosTrabajo = JSON.parse(res);
                        
                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("selectCentrosTrabajo");
                        for(var i=0 in centrosTrabajo) {
                            option = document.createElement("option");
                            //El valor corresponde al claveCentro "[1]"
                            option.value = centrosTrabajo[i][1];
                            option.text = centrosTrabajo[i][3];
                            select.appendChild(option);
                        }
                    }
                }); 
            }

            function configuraSelectProcesosEncuestas(){
                $("#selectProcesosEncuestas").empty();

                //>> En base al centro de trabajo seleccionado, obtengo la lista de sus procesos de encuestas 
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaProcesosEncuestas", claveCentro: $("#selectCentrosTrabajo").val()
                    },
                    success:function(res){
                        listaProcesos = JSON.parse(res); //Guardo el resultado en esta variable
                        
                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("selectProcesosEncuestas");

                        if(listaProcesos.length == 0){
                            option = document.createElement("option");
                            option.value = 0;
                            option.text = "--- No existen procesos ---";
                            select.appendChild(option);
                        }
                        else{
                            //1. Muestro la lista de los procesos en el Select de procesos
                            for(var i=0 in listaProcesos) {
                                option = document.createElement("option");
                                //El valor corresponde al claveCentro "[1]"
                                option.value = listaProcesos[i][8];
                                option.text = listaProcesos[i][2];
                                select.appendChild(option);
                            }
                            //2. En base al proceso que quedo seleccionado, obtengo sus valores de Guia 2 y Guia 3
                            cambioSelectProcsEncuestas();
                        }
                    }
                }); 
            }

            //Cuando se cambia el valor del Select de Centros de trabajo, se llama a esta funcion:
            function cambioSelectCentrosTrabajo(){
                configuraSelectProcesosEncuestas();
               
            }

            //Esta funcion se manda a llamar cada que se cambia el valor seleccionado del Select de Proceso de encuestas
            function cambioSelectProcsEncuestas(){
                var guia2; var guia3;
                //Debo buscar si al Proceso de encuestas seleccionado le corresponde la Guia 2 o la Guia 3:
                //Para ello, recorro TODA la lista de los procesos que se muestran
                for(var i=0 in listaProcesos) {
                    if($("#selectProcesosEncuestas").val() == listaProcesos[i][8]){
                        guia2 = listaProcesos[i][5];
                        guia3 = listaProcesos[i][6];
                    }
                }
                
                $("#selectGuiaAnalisis").empty(); //Vacio el select que contiene los tipos de Guias

                //Guardo en una variable el Select que contendra las Guias
                var selectGuiaAnalisis = document.getElementById("selectGuiaAnalisis");

                var option1 = document.createElement("option");
                option1.value = 1; option1.text = "Guia 1";
                selectGuiaAnalisis.appendChild(option1); 

                if(guia2 == 1)
                    valorGuia2_3 = 2;
                else if(guia3 == 1)
                    valorGuia2_3 = 3;

                //console.log("La guia del proceso es la: "+ valorGuia2_3);

                var option2 = document.createElement("option");
                option2.value = valorGuia2_3; option2.text = "Guia "+valorGuia2_3;
                selectGuiaAnalisis.appendChild(option2); 

            }

            function generarAnalisis(){
                //Guardo el valor de la Guia que el usuario selecciono para el analisis:
                numGuiaAnalisis = $("#selectGuiaAnalisis").val(); 

                //1. Verifico que haya empleados que ya hayan hecho la Guia seleccionada para poder generar el analisis
                if(ningunEmpCentroHizoEncuesta(numGuiaAnalisis))
                    Swal.fire('', 'No se puede generar el análisis ya que ningún empleado ha realizado la Guía '+numGuiaAnalisis+' del proceso seleccionado', 'info')
                else{
                    //:: Al menos hay 1 empleado que hizo la Guia seleccionada. Asi que si puedo hacer el analisis
                   
                    numBloque = 1;  //Cada vez que genero un nuevo Analisis de respuestas, debo resetear el valor de "numBloque"

                    //::: Analisis de las respuestas de la Guia 1, 2 o 3 :::
                    $('#btnAnterior').attr("disabled", true);
                    $('#btnContinuar').attr("disabled", false);

                    //1. Obtengo todas los datos de las respuestas de la Guia seleccionada (2 o 3)
                    obtenerDatosRespuestasGuia2_3($("#selectProcesosEncuestas").val(), numGuiaAnalisis);

                    //Ya que le usuario le dio CLIC en "Generar", guardo la Guia que selecciono para el analisis:
                    //::: El titulo especial SOLO se debe mostrar en el BLOQUE 1 de la GUIA 1 :::
                    if(numGuiaAnalisis == 1){
                        numBloques = 4;
                        //Muestro tambien el titulo del Bloque 1 de la Guia 1
                        $('#titulo_bloque1_G1').show();
                    }else{
                        //Si NO estoy mostrando la Guia 1, escondo el titulo especial
                        $('#titulo_bloque1_G1').hide();

                        if(numGuiaAnalisis == 2)
                            numBloques = 8;
                        else if(numGuiaAnalisis == 3)
                            numBloques = 14;
                    }

                    //Ya que tengo los datos de todas las preguntas, muestro el Bloque 1 y sus graficas:                  
                    muestraGraficasBloque();
                    $('#div_respsGuia2_3').show(); //Por ultimo, muestro el DIV de las respuestas

                }
            }

            function muestraGraficasBloque(){
                //En base a la Guia que estoy mostrando, son los titulos que tendran los Bloques

                //Muestro el titulo y numero del bloque en la card
                var x = document.getElementById("tituloBloque");
                x.innerHTML = "Guía "+numGuiaAnalisis+" - Bloque "+ numBloque + " de " + numBloques;

                //Paso 1: Escondo tambien todos los divs que contienen las graficas:
                for (var i = 1; i <= 13; i++) {
                    $('#div_grafica_' + i).hide(); 
                }

                var numGrafica = 1; //Siempre se van a comenzar a generar las graficas desde el Bloque 1
                //Sin importar si se avanza o retrocede de Bloque
            
                for(var i=0 in datosRespuestasGuia2_3) {
                    if(datosRespuestasGuia2_3[i][2] == numBloque){
                        //Si la pregunta pertenece al bloque que estoy mostrando...
                        //>Le paso a la funcion el Numero de grafica que usare y el Numero de Pregunta
                        generaGraficaRespsPregunta(numGrafica, datosRespuestasGuia2_3[i][0]);
                        
                        //Ya que genere la grafica, muestra esa grafica que esta escondida
                        $('#div_grafica_' + numGrafica).show();
                        
                        //:::: Por cada GRAFICA CREADA, configuro el boton que se usara para Descargarla:
                        configuraBtnDescargarGrafica("btnDescargarGrafica_" + numGrafica, "grafica_"+ numGrafica, datosRespuestasGuia2_3[i][0]);

                        numGrafica++; //Por ultimo, aumento el valor del numero de grafica
                    }
                }
            }

            //::: CONFIGURA LOS BOTONES QUE SE USARAN PARA DESCARGAR LA GRAFICA :::
            function configuraBtnDescargarGrafica(_idBoton, _idGrafica, _numPregGrafica){
                //Cuando se le de CLIC al boton... pasara los siguiente:
                document.getElementById(_idBoton).addEventListener('click', function(){
                    /*Get image of canvas element*/
                    var url_base64jp = document.getElementById(_idGrafica).toDataURL("image/jpg");
                    /*get download button (tag: <a></a>) */
                    var a =  document.getElementById(_idBoton);

                    //Obtengo varios datos para el nombre que tendra el archivo JPG de la grafica:
                    var nombreCentro = $("#selectCentrosTrabajo option:selected").text();
                    var nombreProceso = $("#selectProcesosEncuestas option:selected").text();
                    //Creo el nombre que tendra el archivo cuando se descargue
                    var nombreArchivo = nombreCentro + " - " + nombreProceso + " - Guia " + numGuiaAnalisis + " - Analisis de respuestas Pregunta "+ _numPregGrafica + ".jpg";

                    a.setAttribute("download",  nombreArchivo);
                    /*insert chart image url to download button (tag: <a></a>) */
                    a.href = url_base64jp;
                });
            }

            //CONTINUA AL SIGUIENTE BLOQUE DE PREGUNTAS
            function continuar(){
               
                numBloque++; //Primero aumento el numero de bloque que se mostrará
                muestraGraficasBloque(); //Luego muestro las graficas que corresponden a ese bloque
               
                if(numBloque == 1)
                    $('#btnAnterior').attr("disabled", true); //Inabilito el boton de Anterior
                else
                    $('#btnAnterior').attr("disabled", false);

                //Verifico si se llego al Ultimo bloque de preguntas. Si es asi, Inabilito el boton de Continuar
                if(numBloque == numBloques)
                    $('#btnContinuar').attr("disabled", true); //Inabilito el boton de Anterior
               
                //:: Verifico si estoy mostrando al GUIA 1 ::::
                if(numGuiaAnalisis == 1 && numBloque > 1)
                    $('#titulo_bloque1_G1').hide();
            }

            //REGRESA AL BLOQUE DE PREGUNTAS ANTERIOR
            function anterior(){
                numBloque--; //Disminuyo el numero de Bloque
                muestraGraficasBloque(); //Luego muestro las graficas de ese bloque 
                
                if(numBloque == 1)
                    $('#btnAnterior').attr("disabled", true); //Inabilito el boton de Anterior
                else{
                    $('#btnAnterior').attr("disabled", false);
                }

                if(numBloque < numBloques)
                    $('#btnContinuar').attr("disabled", false);

                //:: Verifico si estoy mostrando al GUIA 1 ::::
                if(numGuiaAnalisis == 1 && numBloque == 1)
                    $('#titulo_bloque1_G1').show();
            }    

            function obtenerDatosRespuestasGuia2_3(_claveProcesoSelec, _numGuiaAnalisis){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getDatosRespuestasGuia2_3", claveProceso: _claveProcesoSelec, 
                        numGuia: _numGuiaAnalisis, claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        datosRespuestasGuia2_3 = JSON.parse(res);

                        //listasEmpsPorNivelRiesgoCadaDominio[_numProc - 1] = JSON.parse(res);
                        //console.log("LAS RESPUESTAS DE ESTA GUIA SON:");
                        //console.log(datosRespuestasGuia2_3);
                    }
                }); 
            }

            function generaGraficaRespsPregunta(_numGrafica, _numPreg){
                var grafica = document.getElementById('grafica_'+_numGrafica).getContext('2d');
               
                if(window.window["chart_"+_numGrafica.toString()]){
                    //Limpio y destruyo la grafica que ya estaba creada
                    window.window["chart_"+_numGrafica.toString()].clear();
                    window.window["chart_"+_numGrafica.toString()].destroy();
                }
                
                //Guardo la fila que contiene Todos los datos de las pregunta que quiero graficar
                var filaDatosPregunta = datosRespuestasGuia2_3[_numPreg - 1]

                if(numGuiaAnalisis == 1){
                    //::: SE QUIERE GENERAR UNA GRAFICA DE LA GUIA 1 :::
                    //:::::::::::::::::::::::

                    window.window["chart_"+_numGrafica.toString()] = new Chart(grafica, {
                        type: 'horizontalBar',
                        data: {
                            labels: ["Si", "No"],
                            datasets: [
                                {
                                    label: "Numero de empleados",
                                    backgroundColor: ["#3cba6f", "#4cba9f"],
                                    data: [ filaDatosPregunta[4], filaDatosPregunta[5] ]
                                }
                            ]
                        },
                        options: {
                            legend: { 
                                display: false
                            }, 
                            maintainAspectRatio: false,
                            title: {
                                display: true,
                                text: _numPreg + '. '+ filaDatosPregunta[1]
                            },
                            scales: {
                                yAxes: [{
                                    barPercentage: 0.6
                                }],
                                xAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        userCallback: function(label, index, labels) {
                                            if(Math.floor(label) === label){
                                                return label;
                                            }
                                        }
                                    }
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
                            }
                        }
                    });


                }else{
                    //::: SE QUIERE GENERAR UNA GRAFICA DE LA GUIA 2 O 3 :::
                    //:::::::::::::::::::::::

                    window.window["chart_"+_numGrafica.toString()] = new Chart(grafica, {
                        type: 'horizontalBar',
                        data: {
                            labels: ["Siempre", "Casi siempre", "Algunas veces", "Casi nunca", "Nunca"],
                            datasets: [
                                {
                                    label: "Numero de empleados",
                                    backgroundColor: ["#3e95cd", "#1cea9f", "#3cba6f", "#4cba9f", "#8cba9f"],
                                    data: [
                                        filaDatosPregunta[3], filaDatosPregunta[4], filaDatosPregunta[5], filaDatosPregunta[6], filaDatosPregunta[7]
                                        //4,8,12,13,23
                                    ]
                                }
                            ]
                        },
                        options: {
                            legend: { 
                                display: false
                            }, 
                            maintainAspectRatio: false,
                            title: {
                                display: true,
                                text: _numPreg + '. '+ filaDatosPregunta[1]
                            },
                            scales: {
                                yAxes: [{
                                    barPercentage: 0.8
                                }],
                                xAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        userCallback: function(label, index, labels) {
                                            if(Math.floor(label) === label){
                                                return label;
                                            }
                                        }
                                    }
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
                            }
                        }
                    });
                }
            }   

            function obtenerDatosRespuestasGuia2_3(_claveProcesoSelec, _guia2_3){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getDatosRespuestasGuia2_3", claveProceso: _claveProcesoSelec, 
                        numGuia: _guia2_3, claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        datosRespuestasGuia2_3 = JSON.parse(res);

                        //listasEmpsPorNivelRiesgoCadaDominio[_numProc - 1] = JSON.parse(res);
                        //console.log("LAS RESPUESTAS DE ESTA GUIA SON:");
                        //console.log(datosRespuestasGuia2_3);
                    }
                }); 
            }
     
            //Funcion para verificar si hay al menos 1 empleado del Centro seleccionado que haya hecho ya la Guia 2/3 del proceso seleccionado
            function ningunEmpCentroHizoEncuesta(numGuiaRep){
                var ningunEmpCentroHizoEncuesta = true;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "verificaSiEmpsYaHicieronGuia", claveEmpresa: claveEmpresa, numGuia: numGuiaRep, claveProceso: $("#selectProcesosEncuestas").val() },
                    success:function(res){
                        if(res > 0){
                            //numEncuestados = res;
                            ningunEmpCentroHizoEncuesta = false;
                        }
                    }
                });
                return ningunEmpCentroHizoEncuesta;
            }

            //Funcion para verificar que la matricula ingresada sea Valida
            function matriculaNoValida(_matricula){
                //Aqui verifico que la matricula ingresada SI exista y que pertenezca al Centro de trabajo seleccionado
                var matriculaNoExiste = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getEmpleadoByCentroTrabajo2", claveCentro: $("#selectCentrosTrabajo").val(), matricula: _matricula
                    }, 
                    success:function(res){
                        var datos = JSON.parse(res);

                        if(datos == null)
                            matriculaNoExiste = true; 
                        else{
                            //Guardo el nombre del empleado para poder usarlo luego
                            //nombreEmpleado = datos.nombreEmpleado; --------------
                            //claveDepto = datos.claveDepto; --------------

                            //Obtengo el nombre del departamento que tenga la misma clave de este centro. En el select del reporte 1
                            //nombreDepto = $("#listaDeptosRep1 option[value='"+claveDepto+"']").text();
                        }
                            
                    }
                });
                return matriculaNoExiste;
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
            include ('barra1.php'); 
        ?>

        <div class="container" style="justify-content: center; align-items: center;">
            <br>
            <!-- FILA DE LOS DATOS DEL PROCESO -->
            <div class="row">
                <div class="col-md-12"> 
                    <div class="card sombra" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); ">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis de respuestas </div>
                        
                        <div class="card-body">
                            <label style="font-size: 15px;"> <b> Seleccione los datos para poder generar el análisis: </b></label><br>
                            <div class="row">
                                <div class="col-md-6">
                                    <form class="form-inline">
                                        <label class="mr-sm-6">Seleccione el centro de trabajo: </label>
                                        <select class="form-control col-lg-6" onchange="cambioSelectCentrosTrabajo()" id="selectCentrosTrabajo">

                                        </select> <br>
                                    </form>
                                </div>

                                <div class="col-md-6">
                                    <form class="form-inline">
                                        <label class="mr-sm-6">Seleccione el proceso de encuestas: </label>
                                        <select class="form-control col-lg-6" onchange="cambioSelectProcsEncuestas()" id="selectProcesosEncuestas">
                                            
                                        </select> <br>
                                    </form>
                                </div>
                            </div><br>

                            <div class="row">
                                <div class="col-md-6">
                                    <form class="form-inline">
                                        <label class="mr-sm-6">Seleccione la guia que desea analizar: </label>
                                        <select class="form-control" id="selectGuiaAnalisis">
                                        </select> 
                                    </form>
                                </div>

                                <div class="col-md-6">
                                    <button class="btn btn-outline-secondary btn-sm float-right" onclick="generarAnalisis()" type="button">Generar análisis</button>
                                </div>
                            </div><br>

                        </div>
                    </div><br>

                    <!-- AQUI IRAN TODAS LAS RESPUESTAS DE LA GUIA 1, 2 o 3 -->
                    <div id="div_respsGuia2_3" style="display: none">
                        <div class="card sombra" id="card_resultados_generales"  style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); margin-bottom: 25px;">
                            <div class="card-header" id="tituloBloque" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > </div>
                            
                            <div class="card-body">
                                <h6 id="titulo_bloque1_G1" style="text-align: center; display: none"> ¿Ha presenciado o sufrido alguna vez, durante o con motivo del trabajo un acontecimiento como los siguientes?: </h6>

                                <!------ FILA DE PREGUNTAS 1 ------->
                                <div class="row" id="fila_1">
                                    <div class="col-md-6" id="div_grafica_1" style="margin-bottom: 25px;">
                                        <!-- Avance de cada una de las Guias -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_1"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_1" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="div_grafica_2" style="margin-bottom: 25px;">
                                        <!-- Grafica de empleados por Nivel de riesgo final -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_2"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_2" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!------ FILA DE PREGUNTAS 2 ------->
                                <div class="row" id="fila_2">
                                    <div class="col-md-6"  id="div_grafica_3" style="margin-bottom: 25px;">
                                        <!-- Avance de cada una de las Guias -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_3"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_3" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="div_grafica_4" style="margin-bottom: 25px;">
                                        <!-- Grafica de empleados por Nivel de riesgo final -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_4"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_4" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!------ FILA DE PREGUNTAS 3 ------->
                                <div class="row" id="fila_3" >
                                    <div class="col-md-6" id="div_grafica_5" style="margin-bottom: 25px;">
                                        <!-- Avance de cada una de las Guias -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_5"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_5" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="div_grafica_6" style="margin-bottom: 25px;">
                                        <!-- Grafica de empleados por Nivel de riesgo final -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_6"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_6"  href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!------ FILA DE PREGUNTAS 4 ------->
                                <div class="row" id="fila_4">
                                    <div class="col-md-6" id="div_grafica_7" style="margin-bottom: 25px;">
                                        <!-- Avance de cada una de las Guias -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_7"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_7"  href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="div_grafica_8" style="margin-bottom: 25px;">
                                        <!-- Grafica de empleados por Nivel de riesgo final -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_8"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_8" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!------ FILA DE PREGUNTAS 5 ------->
                                <div class="row" id="fila_5" >
                                    <div class="col-md-6" id="div_grafica_9" style="margin-bottom: 25px;">
                                        <!-- Avance de cada una de las Guias -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_9"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_9" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="div_grafica_10" style="margin-bottom: 25px;">
                                        <!-- Grafica de empleados por Nivel de riesgo final -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_10"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_10" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!------ FILA DE PREGUNTAS 6 ------->
                                <div class="row" id="fila_6">
                                    <div class="col-md-6" id="div_grafica_11" style="margin-bottom: 25px;">
                                        <!-- Avance de cada una de las Guias -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_11"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_11"  href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="div_grafica_12" style="margin-bottom: 25px;">
                                        <!-- Grafica de empleados por Nivel de riesgo final -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_12"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_12"  href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!------ FILA DE PREGUNTAS 7 ------->
                                <div class="row" id="fila_7" >
                                    <div class="col-md-6" id="div_grafica_13" style="margin-bottom: 25px;">
                                        <!-- Avance de cada una de las Guias -->
                                        <div style="height: 200px">
                                            <canvas id="grafica_13"></canvas>

                                            <!--Agrego el boton para descargar la imagen -->
                                            <a id="btnDescargarGrafica_13" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                                <!-- Download Icon -->
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" >
                            <div class="col-md-12">
                                <button type="button" id="btnContinuar" class="btn btn-space btn-success float-right" onclick="continuar()">Continuar</button>
                                <button type="button" id="btnAnterior" class="btn btn-space btn-outline-secondary float-right" onclick="anterior()">Anterior</button>
                            </div>
                        </div>
                    </div>

                </div>            
            </div>
            <br>
            <br>  
            
        </div><br>

        <!-- Esto es para... varias cosas -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

        <!--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>-->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

    </body>
</html>