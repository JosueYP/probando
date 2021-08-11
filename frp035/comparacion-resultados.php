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
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel=stylesheet type="text/css" href="estilos.css">
        <link rel="shortcut icon" href="favicon.png">

        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

        <title>Comparación de resultados</title>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var nombre = "<?php session_start(); echo $_SESSION['nombre'] ?>";
            var rolUsuario = "<?php session_start(); echo $_SESSION['rolUsuario'] ?>";
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            var listaProcesos; var valorGuia2_3; 
            //Creo el arreglo que guardara las lista de los Proceso de encuestas
            var listasProcesosEncuestas = new Array();
            var listasEmpsPorNivelRiesgoCadaCategoria = new Array(); 
            var listasEmpsPorNivelRiesgoCadaDominio = new Array(); 
            var listaNivelesRiesgoProcs = new Array();
            var empsRequierenValoracion = new Array();
            var valoresGuia2_3 = new Array(); var nivelesRiesgoProcs = new Array(); 
            var fechasCreacionProcesos = new Array(); 
            
            window.onload = function(){
                //1. Configuro el Select de los Centros de trabajo
                configuraSelectCentrosTrabajo();

                //2. Configuro los 2 Selects de los Procesos de encuestas que tiene el Centro seleccionado
                configuraSelectProcesosEncuestas(1);
                configuraSelectProcesosEncuestas(2);
            }

            //Esta funcion llena el Select de los centros de trabajo con datos
            function configuraSelectCentrosTrabajo(_idCentro){
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

            function configuraSelectProcesosEncuestas(_numSelectProceso){
                //1. Borro el contenido que tenga este Select
                $("#selectProceso"+_numSelectProceso).empty(); 

                //2. En base al centro de trabajo seleccionado, obtengo la lista de sus procesos de encuestas 
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaProcesosEncuestas", claveCentro: $("#selectCentrosTrabajo").val()
                    },
                    success:function(res){
                        //3. Guardo el resultado en esta variable
                        listaProcesos = JSON.parse(res); 
                        
                        //4. Muestro la informacion obtenida en el Select
                        select = document.getElementById("selectProceso"+_numSelectProceso);
                        
                        //5. Guardo la lista de Este proceso en el arreglo de listas
                        listasProcesosEncuestas[_numSelectProceso - 1] = listaProcesos

                        //console.log(">>>>>>>>>>>>");
                        //console.log(listasProcesosEncuestas);
                        //console.log(">>>>>>>>>>>>");

                        //Lleno las opciones de este Select -----
                        if(listaProcesos.length == 0){
                            option = document.createElement("option");
                            option.value = 0;
                            option.text = "--- No existen procesos ---";
                            select.appendChild(option);
                        }
                        else{
                            var numFechaProc = 0;
                            //1. Muestro la lista de los procesos en el Select de procesos
                            for(var i=0 in listaProcesos) {
                                option = document.createElement("option");
                                //El valor corresponde al claveCentro "[1]"
                                option.value = listaProcesos[i][8];
                                option.text = listaProcesos[i][2];
                                select.appendChild(option);

                                //Guardo la fecha de este Proceso de encuestas en el Array
                                fechasCreacionProcesos[numFechaProc] = [listaProcesos[i][8], listaProcesos[i][3]];

                                //console.log("--Fecha de creacion: "+ listaProcesos[i][3]);
                                numFechaProc++;
                            }
                            //<<<<<<<<<<<< IMPORTANTE!! <<<<<<<<<<<
                            //2. En base al proceso que quedo seleccionado, obtengo sus valores de Guia 2 y Guia 3
                            cambioSelectProcEncuestas(_numSelectProceso);
                        }
                    }
                }); 
            }

            //Se llama a esta funcion cuando se CAMBIA el valor seleccionado en los Select de Proceso de encuestas
            function cambioSelectProcEncuestas(_numProceso){
                //Creo 2 variables para poder guardar los valores de cada Guia
                var guia2; var guia3; 
                var _listaProcesos = listasProcesosEncuestas[_numProceso - 1];

                //Debo buscar si al Proceso de encuestas SELECCIONADO le corresponde la GUIA 2 ó la GUIA 3:
                //Para ello, recorro TODA la lista de los procesos que se muestran
                for(var i=0 in _listaProcesos) {
                    if($("#selectProceso"+_numProceso).val() == _listaProcesos[i][8]){
                        guia2 = _listaProcesos[i][5];
                        guia3 = _listaProcesos[i][6];
                    }
                }
                //console.log("Los valores del Proceso "+_numProceso+" son : "+guia2+" - "+guia3);

                if(guia2 == 1) 
                    valoresGuia2_3[_numProceso - 1] = 2;
                else if(guia3 == 1)
                    valoresGuia2_3[_numProceso - 1] = 3;

                //console.log("La guia del Proceso "+_numProceso+" es: "+ valoresGuia2_3[_numProceso - 1]);
            }

            //Cuando se cambia el valor del Select de Centros de trabajo, se llama a esta funcion:
            function cambioSelectCentrosTrabajo(){
                configuraSelectProcesosEncuestas("selectProceso1");
                configuraSelectProcesosEncuestas("selectProceso2");
            }

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

            //Esta funcion me verifica si al menos 1 empleado del Proceso seleccionado YA hizo la Guia 2 o 3
            function ningunEmpCentroHizoEncuesta(numGuiaRep, numSelect){
                var ningunEmpCentroHizoEncuesta = true;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "verificaSiEmpsYaHicieronGuia", claveEmpresa: claveEmpresa, numGuia: numGuiaRep, claveProceso: $("#selectProceso"+numSelect).val() },
                    success:function(res){
                        if(res > 0){
                            //numEncuestados = res;
                            ningunEmpCentroHizoEncuesta = false;
                        }
                    }
                });
                return ningunEmpCentroHizoEncuesta;
            }

            function proceso2AnteriorProceso1(){
                //Busco las fechas de creacion de los Proceso seleccionado
                return false
            }

            function generarAnalisis(){
                //:: Verifico si se eligio el MISMO proceso en ambos select:
                if($("#selectProceso1").val() == $("#selectProceso2").val())
                    //Swal.fire('Atención', 'Seleccione procesos de encuestas diferentes para poder realizar la comparación', 'info')
                     
                    Swal.fire('', 'Seleccione procesos de encuestas diferentes para poder generar el análisis', 'info')

                //Verifico si en el PROCESO 1 YA hay empleados encuestados
                else if(ningunEmpCentroHizoEncuesta(valoresGuia2_3[0], 1)){
                    Swal.fire('', 'No se puede generar el analisis ya que ningun empleado del Primer proceso de encuestas seleccionado ha realizado la Guia '+valoresGuia2_3[0], 'info')

                //Verifico si en el PROCESO 2 YA hay empleados encuestados
                }else if(ningunEmpCentroHizoEncuesta(valoresGuia2_3[1], 2)){
                    Swal.fire('', 'No se puede generar el analisis ya que ningun empleado del Segundo proceso de encuestas seleccionado ha realizado la Guia '+valoresGuia2_3[1], 'info')

                }else if(proceso2AnteriorProceso1()){
                    Swal.fire('Atención', 'Seleccione como primer proceso un proceso de encuestas que haya sido creado antes que el segundo proceso', 'info')

                }else{
                    //console.log(fechasCreacionProcesos);

                    //::: Ya que hice todas las validaciones, puedo generar el Analisis :::

                    //Verifico si en ambos procesos se eligio la Guia 2:
                    if(valoresGuia2_3[0] == 2 && valoresGuia2_3[1] == 2){
                        var totalCats = 4; var totalDoms = 8;
                         //Escondo la Categoria 5 y el Dominio 9 y 10
                        $('#div_categoria_5').hide(); $('#div_dominio_9').hide(); $('#div_dominio_10').hide();  
                    }else{
                        var totalCats = 5; var totalDoms = 10;
                        //Muestro la Categoria 5 y el Dominio 9 y 10
                        $('#div_categoria_5').show(); $('#div_dominio_9').show(); $('#div_dominio_10').show();  
                    }

                    //1. Obtengo el Nivel de Riesgo final de cada uno de los Procesos de encuestas seleccionados:
                    obtenerNivelRiesgoFinal($("#selectProceso1").val(), valoresGuia2_3[0], 1); //<--------------
                    obtenerNivelRiesgoFinal($("#selectProceso2").val(), valoresGuia2_3[1], 2);
                    //1.1 Ya que tengo los niveles de riesgo de Ambos procesos, genero la grafica
                    generaGraficaNivelesRiesgoProcs();


                    //2. Obtengo el numero de empleados por cada Nivel de riesgo 
                    obtenerNumEmpsNivelRiesgoFinal($("#selectProceso1").val(), valoresGuia2_3[0], 1);
                    obtenerNumEmpsNivelRiesgoFinal($("#selectProceso2").val(), valoresGuia2_3[1], 2);
                    //2.1 Ya que tengo el numero de empleados por Nivel de riesgo final de cada proceso,  genero la grafica:
                    generaGraficaNivelesRiesgoPorEmpleado();


                    //3. Obtengo los datos de los empleados que requieren tencion Medica de cada Proceso
                    obtenerEmpsRequierenAtencionPorProc($("#selectProceso1").val(), 1);
                    obtenerEmpsRequierenAtencionPorProc($("#selectProceso2").val(), 2);
                    //3.1 Genero la grafica de los empleados que requieren o no atencion medica:
                    generaGraficaEmpsProcAtencionMedica();


                    //4. Obtengo los datos de las <CATEGORIAS> de Ambos procesos:
                    obtenerNivelesRiesgoCategoriasProc($("#selectProceso1").val(), valoresGuia2_3[0], 1);
                    obtenerNivelesRiesgoCategoriasProc($("#selectProceso2").val(), valoresGuia2_3[1], 2);
                    //NOTA::: En este punto, ya tengo Todos los datos que necesito para las Graficas::

                    //5. Obtengo los datos de los <DOMINIOS> de Ambos procesos:
                    obtenerNivelesRiesgoDominiosProc($("#selectProceso1").val(), valoresGuia2_3[0], 1);
                    obtenerNivelesRiesgoDominiosProc($("#selectProceso2").val(), valoresGuia2_3[1], 2);

                    //6. Ya que genere Todas las graficas, configuro los botones para poder descargarlas:
                    configuraBtnDescargarGrafica("btnDescargarGrafica_1", "grafica_1");
                    configuraBtnDescargarGrafica("btnDescargarGrafica_2", "grafica_2");
                    configuraBtnDescargarGrafica("btnDescargarGrafica_3", "grafica_3");

                    //CATEGORIAS
                    for (var i = 1; i <= totalCats; i++) {
                        //Genero la grafica que corresponde a ESTA Categoria
                        generaGraficaCategoriasProceso(i);

                        configuraBtnDescargarGrafica("btnDescargarGrafica_cat_"+i, "grafica_categoria_"+i);
                        //console.log("Ya se genero la grafica "+i);
                    }

                    //DOMINIOS
                    for (var i = 1; i <= totalDoms; i++) {
                        generaGraficaDominiosProceso(i); //Genero la grafica que corresponde a ESTE Dominio

                        configuraBtnDescargarGrafica("btnDescargarGrafica_dom_"+i, "grafica_dominio_"+i);
                        //console.log("Ya se genero la grafica "+i);
                    }

                    //Muestro las cards de la comparacion general, por Categorias y Dominios
                    $('#card_resultados_generales').show();  
                    $('#card_categorias').show();  
                    $('#card_dominios').show();  
                }
            }

            //Funcion para generar la NUEVA grafica de las Categoria :::
            function generaGraficaCategoriasProceso(_numCategoria){
                var grafica = document.getElementById('grafica_categoria_'+_numCategoria).getContext('2d');

                var labelsCategorias = ["1. Ambiente de trabajo", "2. Factores propios de la actividad", "3. Organización del tiempo de trabajo", "4. Liderazgo y relaciones en el trabajo", "5. Trabajo en equipo en el trabajo"];

                //Guardo en variables los arreglos que contienen los datos de las Categorias
                var datosCatProc1 = listasEmpsPorNivelRiesgoCadaCategoria[0];
                var datosCatProc2 = listasEmpsPorNivelRiesgoCadaCategoria[1];

                //::: NUEVO :::: 
                if(window.window["chart_cat_"+_numCategoria.toString()]){
                    //Limpio y destruyo la grafica que ya estaba creada
                    window.window["chart_cat_"+_numCategoria.toString()].clear();
                    window.window["chart_cat_"+_numCategoria.toString()].destroy();
                }

                window.window["chart_cat_"+_numCategoria.toString()] = new Chart(grafica, {
                    type: 'horizontalBar',
                    data: {
                        labels: ["Proceso 1", "Proceso 2"],
                        datasets: [
                            {
                                label: 'Nulo', axis: 'y',
                                data: [
                                    datosCatProc1[0][_numCategoria - 1], 
                                    datosCatProc2[0][_numCategoria - 1]
                                ], 
                                backgroundColor: 'rgb(0,207,227, 0.7)',
                                borderColor: 'rgb(0,207,227, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Bajo', axis: 'y',
                                data: [
                                    datosCatProc1[1][_numCategoria - 1], 
                                    datosCatProc2[1][_numCategoria - 1]
                                ],
                                backgroundColor: 'rgb(0, 222, 17, 0.7)',
                                borderColor: 'rgb(0,222,17, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Medio', axis: 'y',
                                data: [
                                    datosCatProc1[2][_numCategoria - 1], 
                                    datosCatProc2[2][_numCategoria - 1]
                                ],
                                backgroundColor: 'rgb(255,239,39, 0.7)',
                                borderColor: 'rgb(255,239,39, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Alto', axis: 'y',
                                data: [
                                    datosCatProc1[3][_numCategoria - 1], 
                                    datosCatProc2[3][_numCategoria - 1]
                                ],
                                backgroundColor: 'rgb(255,165,25, 0.7)',
                                borderColor: 'rgb(255,165,25, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Muy alto', axis: 'y',
                                data: [
                                    datosCatProc1[4][_numCategoria - 1], 
                                    datosCatProc2[4][_numCategoria - 1]
                                ],
                                backgroundColor: 'rgb(255,50,50, 0.7)',
                                borderColor: 'rgb(255,50,50, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                stacked: true, 
                                ticks: {
                                    //display: false //this will remove only the label
                                }
                            }],
                            yAxes: [{
                                stacked: true, barPercentage: 0.8,
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
                            text: labelsCategorias[_numCategoria - 1]
                        }
                    }
                });
            }

            //::: Funcion para generar la NUEVA grafica de los Dominios :::
            function generaGraficaDominiosProceso(_numDominio){
                var grafica = document.getElementById('grafica_dominio_'+_numDominio).getContext('2d');

                var labelsDominios = ["1. Condiciones en el ambiente de trabajo", "2. Carga de trabajo", "3. Falta de control sobre el trabajo", "4. Jornada de trabajo", 
                                      "5. Interferencia en la relación trabajo-familia", "6. Liderazgo", "7. Relaciones en el trabajo", "8. Violencia", "9. Reconocimiento del desempeño", "10. Insuficiente sentido de pertenencia e inestabilidad"];

                //Guardo en variables los arreglos que contienen los datos de las Categorias
                var datosDomProc1 = listasEmpsPorNivelRiesgoCadaDominio[0];
                var datosDomProc2 = listasEmpsPorNivelRiesgoCadaDominio[1];

                //::: NUEVO :::: 
                if(window.window["chart_dom_"+_numDominio.toString()]){
                    //Limpio y destruyo la grafica que ya estaba creada
                    window.window["chart_dom_"+_numDominio.toString()].clear();
                    window.window["chart_dom_"+_numDominio.toString()].destroy();
                }

                window.window["chart_dom_"+_numDominio.toString()] = new Chart(grafica, {
                    type: 'horizontalBar',
                    data: {
                        labels: ["Proceso 1", "Proceso 2"],
                        datasets: [
                            {
                                label: 'Nulo', axis: 'y',
                                data: [
                                    datosDomProc1[0][_numDominio - 1], 
                                    datosDomProc2[0][_numDominio - 1]
                                ], 
                                backgroundColor: 'rgb(0,207,227, 0.7)',
                                borderColor: 'rgb(0,207,227, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Bajo', axis: 'y',
                                data: [
                                    datosDomProc1[1][_numDominio - 1], 
                                    datosDomProc2[1][_numDominio - 1]
                                ],
                                backgroundColor: 'rgb(0, 222, 17, 0.7)',
                                borderColor: 'rgb(0,222,17, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Medio', axis: 'y',
                                data: [
                                    datosDomProc1[2][_numDominio - 1], 
                                    datosDomProc2[2][_numDominio - 1]
                                ],
                                backgroundColor: 'rgb(255,239,39, 0.7)',
                                borderColor: 'rgb(255,239,39, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Alto', axis: 'y',
                                data: [
                                    datosDomProc1[3][_numDominio - 1], 
                                    datosDomProc2[3][_numDominio - 1]
                                ],
                                backgroundColor: 'rgb(255,165,25, 0.7)',
                                borderColor: 'rgb(255,165,25, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Muy alto', axis: 'y',
                                data: [
                                    datosDomProc1[4][_numDominio - 1], 
                                    datosDomProc2[4][_numDominio - 1]
                                ],
                                backgroundColor: 'rgb(255,50,50, 0.7)',
                                borderColor: 'rgb(255,50,50, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                stacked: true, 
                                ticks: {
                                    //display: false //this will remove only the label
                                }
                            }],
                            yAxes: [{
                                stacked: true, barPercentage: 0.8,
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
                            text: labelsDominios[_numDominio - 1]
                        }
                    }
                });
            }

            //Funcion para Obtener los datos de los Niveles de riesgo de cada categoria de un Proceso de encuestas
            function obtenerNivelesRiesgoCategoriasProc(_claveProcesoSelec, _valorGuia2_3, _numProc){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getNivelesRiesgoPorCategorias", claveCentro: $("#selectCentrosTrabajo").val(), tipoGraficas: 1,
                        claveProceso: _claveProcesoSelec, numGuia: _valorGuia2_3, claveDepto: 0, claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        listasEmpsPorNivelRiesgoCadaCategoria[_numProc - 1] = JSON.parse(res);
                        //console.log("LOS NIVELES DE LAS CATEGORIAS SON:");
                        //console.log(listasEmpsPorNivelRiesgoCadaCategoria[_numProc - 1]);
                    }
                }); 
            }

            function obtenerNivelesRiesgoDominiosProc(_claveProcesoSelec, _valorGuia2_3, _numProc){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getNivelesRiesgoPorDominios", claveCentro: $("#selectCentrosTrabajo").val(), tipoGraficas: 1,
                        claveProceso: _claveProcesoSelec, numGuia: _valorGuia2_3, claveDepto: 0, claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        listasEmpsPorNivelRiesgoCadaDominio[_numProc - 1] = JSON.parse(res);
                        //console.log("LOS NIVELES DE LOS DOMINIOS SON:");
                        //console.log(listasEmpsPorNivelRiesgoCadaDominio[_numProc - 1]);
                    }
                }); 
            }

            function obtenerEmpsRequierenAtencionPorProc(_claveProcesoSelec, _numProc){
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getEmpsReqAtencionMedica", claveCentro: $("#selectCentrosTrabajo").val(), claveDepto: 0,
                        claveProceso: _claveProcesoSelec, tipoGraficas: 1, claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        empsRequierenValoracion[_numProc - 1] = res;
                    }
                }); 
            }

            function obtenerNumEmpsNivelRiesgoFinal(_claveProcesoSelec, _valorGuia2_3, _numProc){
                //2. Obtengo el numero de empleados por cada Nivel de riesgo final de Todos los empleados del centro de trabajo
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getNivelesRiesgoFinalEmps", claveCentro: $("#selectCentrosTrabajo").val(), claveDepto: 0,
                        claveProceso: _claveProcesoSelec, numGuia: _valorGuia2_3, tipoGraficas: 1, claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        listaNiveles = JSON.parse(res); //Guardo la lista obtenida en una variable

                        //Guardo la lista de los Niveles en una variable::
                        listaNivelesRiesgoProcs[_numProc - 1] = listaNiveles

                        //console.log("NUMERO DE EMPLEADOS");
                        //console.log(listaNiveles);
                        //>> Me regresa una lista con el numero de empleados por cada Nivel de Riesgo final
                        //Ej: [0, 3, 5, 2, 1]
                    }
                }); 
            }

            //Funcion que me da el Nivel de riesgi final de un proceso de encuestas <-----------
            function obtenerNivelRiesgoFinal(_claveProcesoSelec, _valorGuia2_3, _numProc){
                //Hago un AJAX para obtener el Nivel de Riesgo final de este proceso
                //Necesito --> La clave del proceso, la clave del centro y el Numero de guia

                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getNivelRiesgoFinalTodosEmps", claveCentro: $("#selectCentrosTrabajo").val(), 
                        claveProceso: _claveProcesoSelec, numGuia: _valorGuia2_3, claveEmpresa: claveEmpresa
                    },
                    success:function(res){
                        //El AJAX me regresa el Nivel de riesgo de Final de este proceso
                        //Asi que lo guardo en el array:
                        nivelesRiesgoProcs[_numProc - 1] = res;

                        //console.log("El nivel de riesgo del proceso "+_claveProcesoSelec+" es: "+ res);
                    }
                }); 
            }

            function regresaColor(_nivelRiesgo){
                if(_nivelRiesgo == 1)
                    return 'rgba(0,207,227, 0.7)'; //Nulo
                else if(_nivelRiesgo == 2)
                    return 'rgba(0,222,17, 0.7)'; //Bajo
                else if(_nivelRiesgo == 3)
                    return 'rgba(255,239,39, 0.7)'; //Medio
                else if(_nivelRiesgo == 4)
                    return 'rgba(255,165,25, 0.7)'; //Alto
                else if(_nivelRiesgo == 5)
                    return 'rgba(255,50,50, 0.7)';  //Muy alto
            }

            //Funcion para generar el Avance de cada una de las Guias del proceso seleccionado
            function generaGraficaNivelesRiesgoProcs(){
                var grafica_GraficaNivelesRiesgo = document.getElementById('grafica_1').getContext('2d');

                var yLabels = {
                    1 : 'Nulo', 2 : 'Bajo', 3 : 'Medio', 4 : 'Alto', 5 : 'Muy alto'
                }

                //Verifico si la grafica YA habia sido creada antes:
                if (window.chart_1) {
                    //Si ya esta creado un Chart que se llame "chart_1", lo limpio y lo destruyo
                    window.chart_1.clear();
                    window.chart_1.destroy();
                }

                window.chart_1 = new Chart(grafica_GraficaNivelesRiesgo, {
                    type: 'bar',
                    data: {
                        labels: ["Proceso 1", "Proceso 2"],
                        datasets: [{
                            data: nivelesRiesgoProcs,
                            backgroundColor: [
                                regresaColor(nivelesRiesgoProcs[0]), 
                                regresaColor(nivelesRiesgoProcs[1])
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true, 
                                    callback: function(value, index, values) {
                                        return yLabels[value];
                                    }
                                }
                            }],
                            xAxes: [{
                                barPercentage: 0.6
                            }]
                        },
                        plugins: {
                            datalabels: {
                                font: {
                                    weight: 'bold'
                                },
                                formatter: function(value, context) {
                                    //Regreso el valor tal y como esta pero en Negritas
                                    if(value == 1)
                                        return "Nulo";
                                    else if(value == 2)
                                        return "Bajo";
                                    else if(value == 3)
                                        return "Medio";
                                    else if(value == 4)
                                        return "Alto";
                                    else if(value == 5)
                                        return "Muy alto";
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Niveles de riesgo final por proceso'
                        }, 
                        maintainAspectRatio: false
                    }
                });
            }

            //Funcion para generar el Avance de cada una de las Guias del proceso seleccionado
            function generaGraficaNivelesRiesgoPorEmpleado(){
                var grafica = document.getElementById('grafica_2').getContext('2d');

                //Aqui va el codigo para la grafica que va acoostada
                var lista1 = listaNivelesRiesgoProcs[0];
                var lista2 = listaNivelesRiesgoProcs[1];

                //Verifico si el chart ya habia sido creado
                if (window.chart_2) {
                    //Si ya esta creado un Chart que se llame "chart_2", lo limpio y lo destruyo
                    window.chart_2.clear();
                    window.chart_2.destroy();
                }

                window.chart_2 = new Chart(grafica, {
                    type: 'horizontalBar',
                    data: {
                        labels: ["Proceso 1", "Proceso 2"],
                        datasets: [
                            {
                                label: 'Nulo',
                                data: [lista1[0], lista2[0]], axis: 'y',
                                backgroundColor: 'rgb(0,207,227, 0.7)',
                                borderColor: 'rgb(0,207,227, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Bajo',
                                data: [lista1[1], lista2[1]], axis: 'y',
                                backgroundColor: 'rgb(0, 222, 17, 0.7)',
                                borderColor: 'rgb(0,222,17, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Medio',
                                data: [lista1[2], lista2[2]], axis: 'y',
                                backgroundColor: 'rgb(255,239,39, 0.7)',
                                borderColor: 'rgb(255,239,39, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Alto',
                                data: [lista1[3], lista2[3]], axis: 'y',
                                backgroundColor: 'rgb(255,165,25, 0.7)',
                                borderColor: 'rgb(255,165,25, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Muy alto',
                                data: [lista1[4], lista2[4]], axis: 'y',
                                backgroundColor: 'rgb(255,50,50, 0.7)',
                                borderColor: 'rgb(255,50,50, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                stacked: true, 
                                ticks: {
                                    //display: false //this will remove only the label
                                }
                            }],
                            yAxes: [{
                                stacked: true, barPercentage: 0.6,
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
                            text: 'Numero de empleados por nivel de riesgo final de cada proceso'
                        }
                    }
                });
            }

            //Muestra el numero de empleados que requieren Atencion medica de cada proceso
            function generaGraficaEmpsProcAtencionMedica(){
                var grafica_EmpsAtencionMedicaProc = document.getElementById('grafica_3').getContext('2d');

                if (window.chart_3) {
                    //Si ya esta creado un Chart que se llame "chart_3", lo limpio y lo destruyo
                    window.chart_3.clear();
                    window.chart_3.destroy();
                }

                window.chart_3 = new Chart(grafica_EmpsAtencionMedicaProc, {
                    type: 'bar',
                    data: {
                        labels: ["Proceso 1", "Proceso 2"],
                        datasets: [
                            {
                            label: "Numero de empleados",
                            backgroundColor: ["#3e95cd", "#3cba9f"],
                            data: empsRequierenValoracion
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
                            text: 'Empleados que requieren atención médica por proceso'
                        },
                        scales: {
                            xAxes: [{
                                barPercentage: 0.6
                            }],
                            yAxes: [{
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
                    }
                });
            }

            //Funcion para verificar si hay algun empleado del Depto. seleccionado YA hizo la Guia seleccionada
            function ningunEmpDeptoHizoEncuesta(numGuiaRep, claveDeptoRep){
                var ningunEmpDeptoHizoEncuesta = true;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "verificaSiEmpsDeptoYaHicieronGuia", claveEmpresa: claveEmpresa, numGuia: numGuiaRep, claveProceso: $("#selectProcesosEncuestas").val(), 
                        claveCentro: $("#selectCentrosTrabajo").val(), claveDepto: claveDeptoRep
                    },
                    success:function(res){
                        if(res > 0){
                            //numEncuestados = res;
                            ningunEmpDeptoHizoEncuesta = false;
                        }
                    }
                });
                return ningunEmpDeptoHizoEncuesta;
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
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Comparación de resultados de diferentes procesos </div>
                        
                        <div class="card-body">
                            <!-- Fila del Centro de trabajo -->
                            <div class="row">
                                <div class="col-md-12">
                                    <form class="form-inline">
                                        <label class="mr-sm-6"><b> Seleccione el centro de trabajo: </b></label>
                                        <select class="form-control col-lg-4" onchange="cambioSelectCentrosTrabajo()" id="selectCentrosTrabajo">

                                        </select> <br>
                                    </form>
                                </div>
                            </div><br>

                            <!-- Fila de los Procesos de encuestas del centro de trabajo -->
                            <label style="font-size: 15px;"> <b> Seleccione los datos para poder generar la comparación: </b></label><br>
                            <div class="row">
                                <div class="col-md-6">
                                    <form class="form-inline">
                                        <label class="mr-sm-6">Seleccione el primer proceso: </label>
                                        <select class="form-control col-lg-6" onchange="cambioSelectProcEncuestas(1)" id="selectProceso1">

                                        </select> <br>
                                    </form>
                                </div>

                                <div class="col-md-6">
                                    <form class="form-inline">
                                        <label class="mr-sm-6">Seleccione el segundo proceso: </label>
                                        <select class="form-control col-lg-6" onchange="cambioSelectProcEncuestas(2)" id="selectProceso2">
                                            
                                        </select> <br>
                                    </form>
                                </div>
                            </div><br>

                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-outline-secondary btn-sm float-right" onclick="generarAnalisis()" type="button">Generar comparación</button>
                                </div>
                            </div>
                        </div>
                    </div><br>


                    <!-- DIV 1 - RESULTADOS GENERALES -->
                    <div class="card sombra" id="card_resultados_generales"  style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); margin-bottom: 25px; display: none">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis comparativo de resultados generales </div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <!-- Avance de cada una de las Guias -->
                                    <div style="height: 250px">
                                        <canvas id="grafica_1"></canvas>

                                        <!--Agrego el boton para descargar la imagen -->
                                        <a id="btnDescargarGrafica_1" download="Niveles de riesgo final por proceso.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                            <!-- Download Icon -->
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!-- Grafica de empleados por Nivel de riesgo final -->
                                    <div style="height: 200px">
                                        <canvas id="grafica_2"></canvas>

                                        <!--Agrego el boton para descargar la imagen -->
                                        <a id="btnDescargarGrafica_2" download="Numero de empleados por nivel de riesgo final de cada proceso.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                            <!-- Download Icon -->
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <!-- Grafica de empleados que requieren valoracion medica -->
                                    <div style="height: 250px">
                                        <canvas id="grafica_3"></canvas>

                                        <!--Agrego el boton para descargar la imagen -->
                                        <a id="btnDescargarGrafica_3" download="Empleados que requieren atencion medica por proceso.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                            <!-- Download Icon -->
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- DIV 2 - ANALISIS POR CATEGORIAS -->
                    <div class="card sombra" id="card_categorias" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); margin-bottom: 25px; display: none">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis comparativo por Categorias</div>
                        
                        <div class="card-body">
                            <!-- CATEGORIA 1 Y 2 -->
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="grafica_categoria_1" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_cat_1" download="Comparacion de numero de empleados por nivel de riesgo de la Categoria 1.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>

                                <div class="col-md-6">
                                    <canvas id="grafica_categoria_2" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_cat_2" download="Comparacion de numero de empleados por nivel de riesgo de la Categoria 2.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div><br>

                            <!-- CATEGORIA 3 Y 4 -->
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="grafica_categoria_3" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_cat_3" download="Comparacion de numero de empleados por nivel de riesgo de la Categoria 3.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>

                                <div class="col-md-6">
                                    <canvas id="grafica_categoria_4" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_cat_4" download="Comparacion de numero de empleados por nivel de riesgo de la Categoria 4.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div><br>

                            <!-- CATEGORIA 5 -->
                            <div class="row">
                                <div class="col-md-6" id="div_categoria_5">
                                    <canvas id="grafica_categoria_5" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_cat_5" download="Comparacion de numero de empleados por nivel de riesgo de la Categoria 5.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ANALISIS POR DOMINIOS -->
                    <div class="card sombra" id="card_dominios" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); margin-bottom: 25px; display: none">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis comparativo por Dominios</div>
                        
                        <div class="card-body">
                            <!-- DOMINIO 1 Y 2 -->
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_1" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_1" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 1.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>

                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_2" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_2" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 2.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div><br>

                            <!-- DOMINIO 3 Y 4 -->
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_3" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_3" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 3.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>

                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_4" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_4" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 4.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div><br>

                            <!-- DOMINIO 5 Y 6 -->
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_5" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_5" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 5.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>

                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_6" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_6" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 6.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div><br>

                            <!-- DOMINIO 7 Y 8 -->
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_7" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_7" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 7.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>

                                <div class="col-md-6">
                                    <canvas id="grafica_dominio_8" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_8" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 8.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- DOMINIO 9 Y 10 -->
                            <div class="row">
                                <div class="col-md-6" id="div_dominio_9">
                                    <canvas id="grafica_dominio_9" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_9" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 9.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>

                                <div class="col-md-6" id="div_dominio_10">
                                    <canvas id="grafica_dominio_10" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_dom_10" download="Comparacion de numero de empleados por nivel de riesgo del Dominio 10.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
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