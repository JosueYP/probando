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

        <title>Análisis de resultados</title>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            var nombre = "<?php session_start(); echo $_SESSION['nombre'] ?>";
            var rolUsuario = "<?php session_start(); echo $_SESSION['rolUsuario'] ?>";
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            var listaProcesos; var valorGuia2_3;
            
            window.onload = function(){
                //1. Configuro el Select de los Centros de trabajo
                configuraSelectCentrosTrabajo();

                //2. Configuro el Select de los Procesos de encuestas en base al Centro de trabajo seleccionado
                configuraSelectProcesosEncuestas();

                //3. Configuro el Select de los Departamentos del Centro de trabajo seleccionado
                configuraSelectsListaDeptos();
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
                configuraSelectsListaDeptos();
            }

            function configuraSelectsListaDeptos(){
                //Limpio los datos de cada uno de los select ---
                $("#selectDepartamentos").empty(); 

                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaDeptosByCentroTrabajo", claveCentro: $("#selectCentrosTrabajo").val()
                    },
                    success:function(res){
                        var listaDeptos = JSON.parse(res);

                        //Muestro la informacion obtenida en el Select
                        selectDeptos = document.getElementById("selectDepartamentos");

                        for(var i=0 in listaDeptos) {
                            option = document.createElement("option");
                            //El valor corresponde al claveCentro "[1]"
                            option.value = listaDeptos[i][2];
                            option.text = listaDeptos[i][3];

                            //Agrego los valores a cada uno de los Select:
                            selectDeptos.appendChild(option);
                        }

                    }
                }); 
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

            function cambioSelectDatosAnalisis(){
                if($("#selectDatosAnalisis").val() == 1){
                    //Escondo ambas secciones
                    $('#seccionDepto').hide();  
                    $('#seccionEmpleado').hide();
                }
                else if($("#selectDatosAnalisis").val() == 2){
                    //Por depto
                    $('#seccionDepto').show();  
                    $('#seccionEmpleado').hide();
                }
                else if($("#selectDatosAnalisis").val() == 3){
                    //Por empleado
                    $('#seccionDepto').hide();  
                    $('#seccionEmpleado').show();
                }
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
                //console.log("El proceso mostrado es: "+guia2+" - "+guia3);

                if(guia2 == 1)
                    valorGuia2_3 = 2;
                else if(guia3 == 1)
                    valorGuia2_3 = 3;

                //console.log("La guia del proceso es la: "+ valorGuia2_3);
            }

            function generarAnalisis(){
                //TODOS LOS DEPARTAMENTOS **** :::::
                if($("#selectDatosAnalisis").val() == 1){
                    //Verifico si hay al menos 1 empleado del Centro seleccionado que ya haya hecho la Guia 2 o 3
                    if(ningunEmpCentroHizoEncuesta(valorGuia2_3))
                        Swal.fire('', 'No se puede generar el analisis ya que ningun empleado del centro de trabajo seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                    else{
                        //Hay al menos 1 empleado de ese centro que YA hizo la Guia 2/3 del proceso seleccionado, asi que si puedo generar el analisis
                        generarGraficas(1); //<<<-----------------------

                        //Muestro unas cards
                        $('#card_resultados_generales').show();  
                        $('#card_categorias').show();  
                        $('#card_dominios').show();  

                        //Escondo otras cards
                        $('#cards_empleado').hide();  
                        $('#card_dominios_empleado').hide();  
                    }
                }

                //POR DEPARTAMENTO ****
                else if($("#selectDatosAnalisis").val() == 2){
                    //Verifico si hay al menos 1 empleado del Departamento del Centro seleccionado que ya haya hecho la Guia 2 o 3
                    if(ningunEmpDeptoHizoEncuesta(valorGuia2_3, $("#selectDepartamentos").val()))
                        Swal.fire('', 'No se puede generar el analisis ya que ningun empleado del departamento seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                    else{
                        generarGraficas(2); //<<<-----------------------

                        //Muestro unas cards
                        $('#card_resultados_generales').show();  
                        $('#card_categorias').show();  
                        $('#card_dominios').show();  

                        //Escondo otras cards
                        $('#cards_empleado').hide();  
                        $('#card_dominios_empleado').hide();  
                    }
                }

                //POR EMPLEADO ****
                else if($("#selectDatosAnalisis").val() == 3){
                    //1. Verifico que se haya ingresado la matricula
                    if($("#matricula").val() == "")
                        Swal.fire('', 'Ingrese una matricula para poder generar el análisis', 'info')
                    
                    //2. Verifico si la matricula ingresada pertenece a Algun empleado del centro seleccionado
                    else if(matriculaNoValida($("#matricula").val()))
                        Swal.fire('', 'Matricula no valida. Ingrese una matricula que exista en el centro de trabajo seleccionado', 'info')
                    
                    //3. Verifico si el empleado Ya hizo a Guia 2 o 3
                    else if(reporteNoSePuedeGenerar($("#matricula").val(), valorGuia2_3))
                        Swal.fire('', 'Este empleado aun no ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado. No se puede generar el análisis', 'info')
                    else{
                        //Ya se puede generar el analisis
                        generarGraficas(3); //<<<-----------------------

                        //Escondo unas cards
                        $('#card_resultados_generales').hide();  
                        $('#card_categorias').hide();  
                        $('#card_dominios').hide();  

                        //Escondo otras cards
                        $('#cards_empleado').show();  
                        $('#card_dominios_empleado').show();  
                    }
                }
            }

            function generarGraficas(_tipoGraficas){
                //Dependiendo del tipo de graficas a generar, hare una cosa u otra
                if(_tipoGraficas == 3){
                    //Se quieren generar las graficas POR EMPLEADO

                    //1. Guardo en una variable cada una de las graficas para poder manipularlas
                    var grafica_riesgoFinal_empleado = document.getElementById('grafica_riesgoFinal_empleado').getContext('2d');
                    var grafica_categorias_empleado = document.getElementById('grafica_categorias_empleado').getContext('2d');
                    var grafica_dominios_empleado = document.getElementById('grafica_dominios_empleado').getContext('2d');
                    
                    //GRAFICA DEL NIVEL DE RIESGO FINAL DEL EMPLEADO :::
                    var graficaRiesgoFinalEmpleado = new Chart(grafica_riesgoFinal_empleado, {
                        type: 'bar',
                        data: { 
                            labels: ["Nivel de riesgo final"],
                            datasets: [{
                                label: "Calificación final y Nivel de riesgo",
                                data: [12],
                                backgroundColor: [
                                    'rgba(0, 255, 228)'
                                ], hoverOffset: 5
                            }]
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    maxBarThickness: 70,
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

                    //ANALISIS POR DOMINIOS DEL EMPLEADO
                    var graficaDominiosEmpleado = new Chart(grafica_dominios_empleado, {
                        type: 'bar',
                        data: { 
                            labels: ["1. Condiciones en el ambiente de trabajo", "2. Carga de trabajo", "3. Falta de control sobre el trabajo", "4. Jornada de trabajo", "5. Interferencia en la relación trabajo-familia",
                                                "6. Liderazgo", "7. Relaciones en el trabajo", "8. Violencia", "9. Reconocimiento del desempeño", "10. Insuficiente sentido de pertenencia e, inestabilidad"],
                            datasets: [{
                                label: "Calificación y Nivel de riesgo",
                                data: [12, 19, 3, 5, 25, 40, 15, 8, 20, 10],
                                backgroundColor: [
                                    'rgba(0, 255, 228)',
                                    'rgba(0, 255, 100)',
                                    'rgba(255, 255, 75)',
                                    'rgba(255, 194, 30)',
                                    'rgba(255, 90, 41)',
                                    'rgba(255, 255, 75)',
                                    'rgba(255, 194, 30)',
                                    'rgba(0, 255, 228)',
                                    'rgba(255, 255, 75)',
                                    'rgba(255, 90, 41)'
                                ], hoverOffset: 5
                            }]
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    maxBarThickness: 50,
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

                    //ANALISIS POR CATEGORIAS DEL EMPLEADO
                    var graficaCategoriasEmpleado = new Chart(grafica_categorias_empleado, {
                        type: 'bar',
                        data: { 
                            labels: ["1. Ambiente de trabajo", "2. Factores propios de la actividad", "3. Organización del tiempo de trabajo", "4. Liderazgo y relaciones en el trabajo", "5. Trabajo en equipo en el trabajo"],
                            datasets: [{
                                label: "Calificación y Nivel de riesgo",
                                data: [12, 19, 3, 5, 25],
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
                            scales: {
                                xAxes: [{
                                    maxBarThickness: 70,
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
                    //Si se quiere generar el Analisis por Todos los deptos. o por Departamento:
                    var listaNiveles; var datosEmpsPorNivelRiesgoCadaCategoria; var datosEmpsPorNivelRiesgoCadaDominio;
                    var datosAvanceGuias; var encuestadosGuia1; var totalEmpleados; var empsRequierenValoracion;

                    //Dependiendo de si genererar las graficas para TODOS los Deptos. o POR DEPARTAMENTO, es como obtendre los datos

                    //¿COMO PUEDO OPTIMIZAR LA GENERACION DE CADA UNA DE LAS GRAFICAS?

                    //1. Obtengo el numero de empleado que YA hicieron y que NO han hecho la Guia 1 y la Guia (2 o 3)
                    //El AJAX me tiene que regresar esto => [50, 30], [10, 30]
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "getAvanceGuias", claveCentro: $("#selectCentrosTrabajo").val(), claveDepto: $("#selectDepartamentos").val(),
                            claveProceso: $("#selectProcesosEncuestas").val(), numGuia: valorGuia2_3, tipoGraficas: _tipoGraficas, claveEmpresa: claveEmpresa
                        },
                        success:function(res){
                            datosAvanceGuias = JSON.parse(res); //Guardo los datos del avance de las guias

                            //console.log(datosAvanceGuias);
                            //console.log("Ya obtube datos del paso 1");

                            //Guardo estos datos para usarlo en otra grafica
                            encuestadosGuia1 = datosAvanceGuias[2];
                            totalEmpleados = datosAvanceGuias[3];
                        }
                    }); 

                    //::::: YA ENCONTRE UNA POSIBLE FORMA DE OPTIMIZARLA ::::::

                    //2. Obtengo el numero de empleados por cada Nivel de riesgo final de Todos los empleados del centro de trabajo
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "getNivelesRiesgoFinalEmps", claveCentro: $("#selectCentrosTrabajo").val(), claveDepto: $("#selectDepartamentos").val(),
                            claveProceso: $("#selectProcesosEncuestas").val(), numGuia: valorGuia2_3, tipoGraficas: _tipoGraficas, claveEmpresa: claveEmpresa
                        },
                        success:function(res){
                            listaNiveles = JSON.parse(res); //Guardo la lista obtenida en una variable

                            //console.log("Aqui va la lista de los niveles por empleado");
                            //console.log(listaNiveles);
                            //>> Me regresa una lista con el numero de empleados por cada Nivel de Riesgo final
                            //Ej: [0, 3, 5, 2, 1] <<--------
                        }
                    }); 

                    //3. Obtengo el numero de empleado que requieren atencion medica, que no y que aun no han hecho la Guia 1
                    //Esto me tiene que regresar algo asi ==> [40, 70, 15] osea [Si requieren, No requieren, No han hecho Guia 1]
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "getEmpsReqAtencionMedica", claveCentro: $("#selectCentrosTrabajo").val(), claveDepto: $("#selectDepartamentos").val(),
                            claveProceso: $("#selectProcesosEncuestas").val(), tipoGraficas: _tipoGraficas, claveEmpresa: claveEmpresa
                        },
                        success:function(res){
                            //console.log("Empleados que requieren: "+ res);
                            //console.log("Ya obtube datos del paso 3");
                            empsRequierenValoracion = res;
                        }
                    }); 

                    //console.log("Tipo de graficas: "+_tipoGraficas);
                    //console.log("Depto seleccionado: "+$("#selectDepartamentos").val());

                    //4. Obtengo los datos para la grafica de CATEGORIAS de Todos los departamentos
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "getNivelesRiesgoPorCategorias", claveCentro: $("#selectCentrosTrabajo").val(), tipoGraficas: _tipoGraficas,
                            claveProceso: $("#selectProcesosEncuestas").val(), numGuia: valorGuia2_3, claveDepto: $("#selectDepartamentos").val(),
                            claveEmpresa: claveEmpresa
                        },
                        success:function(res){
                            datosEmpsPorNivelRiesgoCadaCategoria = JSON.parse(res);
                            //console.log("Ya obtube datos del paso 4");
                            console.log(datosEmpsPorNivelRiesgoCadaCategoria);
                        }
                    }); 

                    //5. Obtengo los datos para la grafica de DOMINIOS de Todos los departamentos
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "getNivelesRiesgoPorDominios", claveCentro: $("#selectCentrosTrabajo").val(), tipoGraficas: _tipoGraficas,
                            claveProceso: $("#selectProcesosEncuestas").val(), numGuia: valorGuia2_3, claveDepto: $("#selectDepartamentos").val(),
                            claveEmpresa: claveEmpresa
                        },
                        success:function(res){
                            datosEmpsPorNivelRiesgoCadaDominio = JSON.parse(res);
                            //console.log("Ya obtube datos del paso 5");
                        }
                    }); 

                    
                    //Genero todas las graficas que corresponden:
                    //1. Esta es la grafica del avance de las Guias 1 y 2 o 3
                    generaGraficaAvanceGuias(valorGuia2_3, datosAvanceGuias[0], datosAvanceGuias[1]);

                    //2. GRAFICA de numero de Empleados por Nivel de Riesgo Final (Dona)
                    generaGraficaEmpsPorNivelRiesgoFinal(listaNiveles);

                    //3. GRAFICA de Empleados que necesitan atencion medica
                    var empsNo_RequierenValoracion = encuestadosGuia1 - empsRequierenValoracion;
                    var empsNo_HanRealizadoGuia1 = totalEmpleados-encuestadosGuia1;

                    generaGraficaEmpsAtencionMedica([empsRequierenValoracion, empsNo_RequierenValoracion, empsNo_HanRealizadoGuia1]); //<------[Si requieren, No requiere, No han hecho Guia 1]

                    //4. GRAFICA del Analisis del nivel de Riesgo de cada una de las Categorias
                    generaGraficaCategorias(valorGuia2_3, datosEmpsPorNivelRiesgoCadaCategoria);
                    
                    //5. GRAFICA del Analisis del nivel de Riesgo de cada una de las Dominios
                    generaGraficaDominios(valorGuia2_3, datosEmpsPorNivelRiesgoCadaDominio);

                    //Por ultimo, configuro todos los botones para poder descargar las graficas
                    configuraBtnDescargarGrafica("btnDescargarGrafica_AvanceGuias", "grafica_AvanceGuias");
                    configuraBtnDescargarGrafica("btnDescargarGrafica_EmpsPorNivelRiesgoFinal", "grafica_EmpsPorNivelRiesgoFinal");
                    configuraBtnDescargarGrafica("btnDescargarGrafica_EmpsAtencionMedica", "grafica_EmpsAtencionMedica");
                    configuraBtnDescargarGrafica("btnDescargarGrafica_Categorias", "grafica_categorias");
                    configuraBtnDescargarGrafica("btnDescargarGrafica_Dominios", "grafica_dominios");
                }
            }

            //Funcion para generar el Avance de cada una de las Guias del proceso seleccionado
            function generaGraficaAvanceGuias(_numGuia, _yaRealizo, _noRealizo){
                var grafica_AvanceGuias = document.getElementById('grafica_AvanceGuias').getContext('2d');

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

            function generaGraficaEmpsPorNivelRiesgoFinal(_listaNiveles){
                var grafica_EmpsPorNivelRiesgoFinal = document.getElementById('grafica_EmpsPorNivelRiesgoFinal').getContext('2d');

                var graficaEmpsPorNivelRiesgoFinal = new Chart(grafica_EmpsPorNivelRiesgoFinal, {
                    type: 'doughnut',
                    data: { 
                        labels: ['Nulo', 'Bajo', 'Medio', 'Alto', 'Muy alto'],
                        datasets: [{
                            label: "Niveles",
                            data: _listaNiveles,
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
                            text: 'Número de empleados por nivel de riesgo final'
                        }
                    }
                });
            }

            function generaGraficaEmpsAtencionMedica(_listaEmpleados){
                var grafica_EmpsAtencionMedica = document.getElementById('grafica_EmpsAtencionMedica').getContext('2d');

                var graficaEmpsAtencionMedica = new Chart(grafica_EmpsAtencionMedica, {
                    type: 'doughnut',
                    data: { 
                        labels: ['Si requieren', 'No requieren', 'No han realizado la Guia 1'],
                        datasets: [{
                            label: "Numero de empleados",
                            data: _listaEmpleados,
                            backgroundColor: [
                                'rgba(212, 145, 255)',
                                'rgba(0, 255, 228)',
                                'rgba(216, 216, 216)'
                            ], hoverOffset: 5 //219, 163, 255
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Empleados que requieren valoración médica'
                        }
                    }
                });
            }

            function generaGraficaCategorias(_numGuia, _datosEmpsPorNivelRiesgoCadaCategoria){
                var grafica_Categorias = document.getElementById('grafica_categorias').getContext('2d');

                var labelsCategorias = ["1. Ambiente de trabajo", "2. Factores propios de la actividad", "3. Organización del tiempo de trabajo", "4. Liderazgo y relaciones en el trabajo"];

                //Si es la Guia 3, le agrego la Categorias Numero 5
                if(_numGuia == 3){
                    labelsCategorias.push("5. Trabajo en equipo en el trabajo");
                }
                    
                var arrayDatosCategorias = labelsCategorias,
                    nulo = _datosEmpsPorNivelRiesgoCadaCategoria[0];
                    bajo = _datosEmpsPorNivelRiesgoCadaCategoria[1];
                    medio  = _datosEmpsPorNivelRiesgoCadaCategoria[2];
                    alto = _datosEmpsPorNivelRiesgoCadaCategoria[3];
                    muy_alto = _datosEmpsPorNivelRiesgoCadaCategoria[4];

                //Hago la comprobacion de su ya esta creada la grafica. Osea, borro los datos que ya habia
                if (window.graficaCategorias) {
                    //Si ya esta creado un Chart que se llame "graficaCategorias", lo limpio y lo destruyo
                    window.graficaCategorias.clear();
                    window.graficaCategorias.destroy();
                    //Esto antes de que se vuelva a generar otro con el mismo nombre
                }
                
                window.graficaCategorias = new Chart(grafica_Categorias, {
                    type: 'bar',
                    data: {
                        labels: arrayDatosCategorias,
                        datasets: [
                            {
                                label: 'Nulo', data: nulo,
                                backgroundColor: 'rgb(0,207,227, 0.7)',
                                borderColor: 'rgb(0,207,227)', borderWidth: 1
                            },
                            {
                                label: 'Bajo', data: bajo,
                                backgroundColor: 'rgb(0,222,17, 0.7)',
                                borderColor: 'rgb(0,222,17)', borderWidth: 1
                            },
                            {
                                label: 'Medio', data: medio,
                                backgroundColor: 'rgb(255,239,39, 0.7)',
                                borderColor: 'rgb(255,239,39)', borderWidth: 1,
                            },
                            {
                                label: 'Alto', data: alto,
                                backgroundColor: 'rgb(255,165,25, 0.7)',
                                borderColor: 'rgb(255, 165, 25)', borderWidth: 1,
                            },
                            {
                                label: 'Muy alto', data: muy_alto,
                                backgroundColor: 'rgb(255,50,50, 0.7)',
                                borderColor: 'rgb(255,50,50)', borderWidth: 1,
                            }
                        ]
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                stacked: true, maxBarThickness: 70,
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
                            text: 'Número de empleados por nivel de riesgo de cada Categoria'
                        }
                    }
                });
            }

            function generaGraficaDominios(_numGuia, _datosEmpsPorNivelRiesgoCadaDominio){
                var grafica_Dominios = document.getElementById('grafica_dominios').getContext('2d');

                var labelsDominios = ["1. Condiciones en el ambiente de trabajo", "2. Carga de trabajo", "3. Falta de control sobre el trabajo", "4. Jornada de trabajo", 
                                      "5. Interferencia en la relación trabajo-familia", "6. Liderazgo", "7. Relaciones en el trabajo", "8. Violencia"];

                //Si es la Guia 3, le agrego los Dominios 9 y 10
                if(_numGuia == 3){
                    labelsDominios.push("9. Reconocimiento del desempeño");
                    labelsDominios.push("10. Insuficiente sentido de pertenencia e, inestabilidad");
                }

                var arrayDatosDominios = labelsDominios,
                    nulo = _datosEmpsPorNivelRiesgoCadaDominio[0];
                    bajo = _datosEmpsPorNivelRiesgoCadaDominio[1];
                    medio = _datosEmpsPorNivelRiesgoCadaDominio[2];
                    alto = _datosEmpsPorNivelRiesgoCadaDominio[3];
                    muy_alto = _datosEmpsPorNivelRiesgoCadaDominio[4];

                //Hago la comprobacion de su ya esta creada la grafica
                if (window.graficaDominios) {
                    window.graficaDominios.clear();
                    window.graficaDominios.destroy();
                }

                window.graficaDominios = new Chart(grafica_Dominios, {
                    type: 'bar',
                    data: {
                        labels: arrayDatosDominios,
                        datasets: [
                            {
                                label: 'Nulo', data: nulo,
                                backgroundColor: 'rgb(0,207,227, 0.7)',
                                borderColor: 'rgb(0,207,227)', borderWidth: 1
                            },
                            {
                                label: 'Bajo', data: bajo,
                                backgroundColor: 'rgb(0,222,17, 0.7)',
                                borderColor: 'rgb(0,222,17)', borderWidth: 1
                            },
                            {
                                label: 'Medio', data: medio,
                                backgroundColor: 'rgb(255,239,39, 0.7)',
                                borderColor: 'rgb(255,239,39)', borderWidth: 1,
                            },
                            {
                                label: 'Alto', data: alto,
                                backgroundColor: 'rgb(255,165,25, 0.7)',
                                borderColor: 'rgb(255, 165, 25)', borderWidth: 1,
                            },
                            {
                                label: 'Muy alto', data: muy_alto,
                                backgroundColor: 'rgb(255,50,50, 0.7)',
                                borderColor: 'rgb(255,50,50)', borderWidth: 1,
                            }
                        ]
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                stacked: true, maxBarThickness: 50,
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
                            text: 'Número de empleados por nivel de riesgo de cada Dominio'
                        }
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
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis de resultados </div>
                        
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
                                        <label class="mr-sm-6">Seleccione los datos a mostrar en el analisis: </label>
                                        <select class="form-control" onchange="cambioSelectDatosAnalisis()" id="selectDatosAnalisis">
                                            <option value="1" selected>Todos los empleados</option>
                                            <option value="2">Por departamento</option>
                                            
                                        </select> 
                                    </form>
                                </div>

                                <div class="col-md-6">
                                    <div id="seccionDepto" style="display: none">
                                        <form class="form-inline">
                                            <label class="mr-sm-6">Seleccione el departamento: </label>
                                            <select class="form-control" id="selectDepartamentos">
                                               
                                            </select>
                                        </form>
                                    </div>

                                    <div id="seccionEmpleado" style="display: none">
                                        <form class="form-inline">
                                            <label class="mr-sm-6">Ingrese matricula del empleado: </label>
                                            <input type="text" class="form-control" id="matricula">
                                        </form>
                                    </div>
                                </div>
                            </div><br>

                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-outline-secondary btn-sm float-right" onclick="generarAnalisis()" type="button">Generar análisis</button>
                                </div>
                            </div>
                        </div>
                    </div><br>


                    <!-- RESULTADOS GENERALES -->
                    <div class="card sombra" id="card_resultados_generales"  style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); display: none; margin-bottom: 25px;">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Resultados generales </div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <!-- Avance de cada una de las Guias -->
                                    <div style="height: 200px">
                                        <canvas id="grafica_AvanceGuias"></canvas>

                                        <!--Agrego el boton para descargar la imagen -->
                                        <a id="btnDescargarGrafica_AvanceGuias" download="Avance de cada una de las Guias.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                            <!-- Download Icon -->
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Grafica de empleados por Nivel de riesgo final -->
                                    <div style="height: 200px">
                                        <canvas id="grafica_EmpsPorNivelRiesgoFinal"></canvas>

                                        <!--Agrego el boton para descargar la imagen -->
                                        <a id="btnDescargarGrafica_EmpsPorNivelRiesgoFinal" download="Numero de empleados por nivel de riesgo final.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                            <!-- Download Icon -->
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Grafica de empleados que requieren valoracion medica -->
                                    <div style="height: 200px">
                                        <canvas id="grafica_EmpsAtencionMedica"></canvas>

                                        <!--Agrego el boton para descargar la imagen -->
                                        <a id="btnDescargarGrafica_EmpsAtencionMedica" download="Empleados que requieren atencion medica.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                            <!-- Download Icon -->
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ANALISIS POR CATEGORIAS -->
                    <div class="card sombra" id="card_categorias" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); display: none; margin-bottom: 25px">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis por Categorias</div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="grafica_categorias" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_Categorias" download="Num. de empleados por nivel de riesgo de cada Categoria.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ANALISIS POR DOMINIOS -->
                    <div class="card sombra" id="card_dominios" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); display: none; margin-bottom: 25px ">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis por Dominios</div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="grafica_dominios"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="btnDescargarGrafica_Dominios" download="Num. de empleados por nivel de riesgo de cada Dominio.jpg" href="" class="btn btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ESTE CODIGO CORRESPONDE A LAS GRAFICAS POR EMPLEADO -->
                    <div class="row" id="cards_empleado" style="display: none" >
                        <div class="col-md-4"> 
                            <!-- RESULTADO FINAL (DEL EMPLEADO) -->
                            <div class="card sombra" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); margin-bottom: 25px">
                                <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Resultado final del empleado</div>
                                
                                <div class="card-body">
                                    <canvas id="grafica_riesgoFinal_empleado" height="200"></canvas> <br><br>

                                    ¿Requiere vaoracion medica?: NO
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8"> 
                            <!-- ANALISIS POR CATEGORIAS (POR EMPLEADO) -->
                            <div class="card sombra" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); margin-bottom: 25px">
                                <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis por Categorias del empleado</div>
                                
                                <div class="card-body">
                                    <canvas id="grafica_categorias_empleado"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="download" download="Num. de empleados por nivel de riesgo de cada Categorias.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
                                        <!-- Download Icon -->
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ANALISIS POR DOMINIOS (DEL EMPLEADO) -->
                    <div class="card sombra" id="card_dominios_empleado" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); display: none; margin-bottom: 25px ">
                        <div class="card-header" style="background-color: #0070c0; color: white; font-family: 'Montserrat', sans-serif;" > Análisis por Dominios del empleado</div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="grafica_dominios_empleado" height="100"></canvas>

                                    <!--Agrego el boton para descargar la imagen -->
                                    <a id="download_2" download="Num. de empleados por nivel de riesgo de cada Dominio.jpg" href="" class="btn btn-sm btn-outline-secondary float-right bg-flat-color-1" title="Descargar grafica">
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