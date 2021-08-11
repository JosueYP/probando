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
        <link rel=stylesheet type="text/css" href="estilos.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel="shortcut icon" href="favicon.png">
        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <title>Generación de reportes</title>

         <!-- Aqui va el codigo Javascript -->
         <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            var nombreEmpleado; var listaProcesos; var valorGuia2_3; var claveDepto; var fechaAplicacion;
            var numEncuestados; var nombreDepto;
           
            window.onload = function(){
                console.log("CLAVE DE EMPRESA: "+ claveEmpresa);

                configuraSelectCentrosTrabajo();
                configuraSelectProcesosEncuestas();
                //Configuro todos los Select que muestran la lista de los deptos. del Centro seleccionado:
                configuraSelectsListaDeptos();

                //Hago lo siguiente para manejar el comportamiento de los elementos del los Modales:
                $('#modalRep1').on('shown.bs.modal', function () {
                    $('#todosDeptosRep1').prop('checked', true);
                    modificaElementosModal1(true, true);
                })  

                $('#modalRep3').on('shown.bs.modal', function () {
                    $('#todosDeptosRep3').prop('checked', true);
                    $("#listaDeptosRep3").prop( "disabled", true); 
                })  

                $('#modalRep4').on('shown.bs.modal', function () {
                    $('#todosDeptosRep4').prop('checked', true);
                    $("#listaDeptosRep4").prop( "disabled", true); 
                })  

                $('#modalRep5').on('shown.bs.modal', function () {
                    $("#matriculaRep5").val(""); 
                })  

                $('#modalRep7').on('shown.bs.modal', function () {
                    $('#todosDeptosRep7').prop('checked', true);
                    $("#listaDeptosRep7").prop( "disabled", true); 
                })  

                $('#modalRep8').on('shown.bs.modal', function () {
                    $('#todosDeptosRep8').prop('checked', true);
                    $("#listaDeptosRep8").prop( "disabled", true); 
                })  
            }
            
            function cerrarSesion(){
                var boton = document.getElementById('btnCerrarSesion');

                //Mando al usuario a la pagina donde se cerrara la sesion
                window.location.href = 'cerrarSesion.php';
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

            function configuraSelectsListaDeptos(){
                //Limpio los datos de cada uno de los select ---
                $("#listaDeptosRep1").empty(); $("#listaDeptosRep3").empty(); 
                $("#listaDeptosRep4").empty(); $("#listaDeptosRep8").empty(); $("#listaDeptosRep7").empty(); 

                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaDeptosByCentroTrabajo", claveCentro: $("#selectCentrosTrabajo").val()
                    },
                    success:function(res){
                        var listaDeptos = JSON.parse(res);
                        //NPTA: Hacer la excepcion de cuando no hay ningun depto. en el centro seleccionado.

                        //Muestro la informacion obtenida en el Select
                        selectRep1 = document.getElementById("listaDeptosRep1");
                        selectRep3 = document.getElementById("listaDeptosRep3");
                        selectRep4 = document.getElementById("listaDeptosRep4");
                        selectRep7 = document.getElementById("listaDeptosRep7");
                        selectRep8 = document.getElementById("listaDeptosRep8");

                        for(var i=0 in listaDeptos) {
                            option = document.createElement("option");
                            //El valor corresponde al claveCentro "[1]"
                            option.value = listaDeptos[i][2];
                            option.text = listaDeptos[i][3];

                            //Agrego los valores a cada uno de los Select:
                            selectRep1.appendChild(option); //<-------- Select de deptos del Reporte 1
                        }

                        for(var i=0 in listaDeptos) {
                            option = document.createElement("option");
                            option.value = listaDeptos[i][2];
                            option.text = listaDeptos[i][3];
                            selectRep3.appendChild(option); 
                        }

                        for(var i=0 in listaDeptos) {
                            option = document.createElement("option");
                            option.value = listaDeptos[i][2];
                            option.text = listaDeptos[i][3];
                            selectRep4.appendChild(option); 
                        }

                        for(var i=0 in listaDeptos) {
                            option = document.createElement("option");
                            option.value = listaDeptos[i][2];
                            option.text = listaDeptos[i][3];
                            selectRep7.appendChild(option); 
                        }

                        for(var i=0 in listaDeptos) {
                            option = document.createElement("option");
                            option.value = listaDeptos[i][2];
                            option.text = listaDeptos[i][3];
                            selectRep8.appendChild(option); 
                        }
                    }
                }); 
            }

            function validaProcesoEncuestas(numReporte){
                //Verifico si existe algun proceso Generado en el Centro de Trabajo seleccioado:
                if($("#selectProcesosEncuestas").val() == 0)
                    Swal.fire('', 'No existe ningun proceso de encuestas en el centro de trabajo seleccionado. No se puede generar el reporte', 'info')
                else{
                    //Abro el Modal que corresponda a este reporte:
                    $('#modalRep'+numReporte).modal('show');
                }
            }

            //Cuando se cambia el valor del Select de Centros de trabajo, se llama a esta funcion:
            function cambioSelectCentrosTrabajo(){
                configuraSelectProcesosEncuestas();
                configuraSelectsListaDeptos();
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
                console.log("El proceso mostrado es: "+guia2+" - "+guia3);

                //PARTE 2: En base a eso, muestro esos datos en el Select de tipo de Guia de los reportes 4 y 5:
                //Primero limpio los valores de esos select:
                $("#listaGuiasRep4").empty(); $("#listaGuiasRep5").empty(); $("#listaGuiasRep8").empty(); 

                var selectGuiasRep4 = document.getElementById("listaGuiasRep4");
                var selectGuiasRep5 = document.getElementById("listaGuiasRep5");
                var selectGuiasRep8 = document.getElementById("listaGuiasRep8");

                //Opciones para el select del Reporte 4:
                var option1 = document.createElement("option");
                option1.value = 1; option1.text = "Guia 1";
                selectGuiasRep4.appendChild(option1); 

                //Opciones para el select del Reporte 5:
                var option1 = document.createElement("option");
                option1.value = 1; option1.text = "Guia 1";
                selectGuiasRep5.appendChild(option1); 

                //Opciones para el select del Reporte 8:
                var option1 = document.createElement("option");
                option1.value = 1; option1.text = "Guia 1";
                selectGuiasRep8.appendChild(option1); 

                if(guia2 == 1)
                    valorGuia2_3 = 2;
                else if(guia3 == 1)
                    valorGuia2_3 = 3;

                console.log("La guia del proceso es la: "+ valorGuia2_3);

                var option2 = document.createElement("option");
                option2.value = valorGuia2_3; option2.text = "Guia "+valorGuia2_3;
                selectGuiasRep4.appendChild(option2); 

                var option2 = document.createElement("option");
                option2.value = valorGuia2_3; option2.text = "Guia "+valorGuia2_3;
                selectGuiasRep5.appendChild(option2); 

                var option2 = document.createElement("option");
                option2.value = valorGuia2_3; option2.text = "Guia "+valorGuia2_3;
                selectGuiasRep8.appendChild(option2); 
            }

            //>>>>> NOTA: En la lista del proceso de encuestas, se debe registrar el depto. al que esa persona pertenecia en ese momento <<<<

            function generarReporte(numReporte, _tipoReporte){
                console.log("SE QUIERE GENERA RPORTE");

                //Verifico si ya se selecciono el tipo de reporte a generar:
                var nombreCentro = $("#selectCentrosTrabajo option:selected").text();
                var nombreProceso  = $("#selectProcesosEncuestas option:selected").text()

                if(numReporte == 1){
                    /******************************* REPORTE 1 ***********************************/
                    /**************REPORTE POR CATEGORIAS, DOMINIOS Y RESULTADO FINAL*************/ 
                    var tipoRep1 = $('input:radio[name="tipoRep1"]:checked').val();

                    if(tipoRep1 == undefined)
                        Swal.fire('', 'Seleccione la opcion de reporte para generar', 'info')
                    else{
                        //Verifico que tipo de reporte se quiere generar:
                        if(tipoRep1 == 1){
                            //GENERO EL REPORTE POR TODOS LOS EMPLEADOS DEL CENTRO DE TRABAJO SELECCIONADO
                            //1. Verifico si YA hay empleados del proceso seleccionado que YA hayan contestado la Guia 2 o 3
                            if(ningunEmpCentroHizoEncuesta(valorGuia2_3))
                                Swal.fire('Accion no valida', 'Ningun empleado del centro de trabajo seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                            else{
                                if(_tipoReporte == 1)
                                    //Va a ser un reporte en Excel
                                    window.location.href = 'reportes/excel/reporte_1_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&numEnc='+numEncuestados;
                                else
                                    //Va a ser un reporte en PDF
                                    window.open('reportes/reporte_1.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&numEnc='+numEncuestados);
                            }

                        }else if(tipoRep1 == 2){
                            //PARA DESPUES: Verifico si el centro SI tiene Departamentos ----

                            //1. Veririco que haya empleados del departamento seleccionado que YA hayan hecho la Guia 2 o 3 para poder generar el reporte
                            if(ningunEmpDeptoHizoEncuesta(valorGuia2_3, $("#listaDeptosRep1").val()))
                                Swal.fire('Accion no valida', 'Ningun empleado del departamento seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                            else{
                                if(_tipoReporte == 1)
                                    window.location.href ='reportes/excel/reporte_1_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep1").val()+'&nombreDepto='+$("#listaDeptosRep1 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&numEnc='+numEncuestados;
                                else
                                    //Entonces SI puedo generar el reporte
                                    window.open('reportes/reporte_1.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep1").val()+'&nombreDepto='+$("#listaDeptosRep1 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&numEnc='+numEncuestados);
                            }
                                
                        }else if(tipoRep1 == 3){
                            //Verifico varias cosas....
                            //1. Que SI se haya ingresado la matricula
                            if($("#matriculaRep1").val() == "")
                                Swal.fire('', 'Ingrese una matricula para poder generar el reporte', 'info')
                            //2. Verifico si la matricula ingresada es Valida
                            else if(matriculaNoValida($("#matriculaRep1").val()))
                                Swal.fire('', 'Matricula no valida. Ingrese una matricula que exista en el centro de trabajo seleccionado', 'info')
                            else if(reporteNoSePuedeGenerar($("#matriculaRep1").val(), valorGuia2_3))
                                Swal.fire('', 'Este empleado aun no ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado. No se puede generar el reporte', 'info')
                            else{
                                //3. Si ya verifique las 2 cosas de arriba, ya puedo Generar el reporte
                                if(_tipoReporte == 1)
                                    window.location.href = 'reportes/excel/reporte_1_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=3&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&matricula='+$("#matriculaRep1").val()+'&nombre='+nombreEmpleado+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                                else
                                    window.open('reportes/reporte_1.php?claveEmpresa='+claveEmpresa+'&tipoRep=3&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&matricula='+$("#matriculaRep1").val()+'&nombre='+nombreEmpleado+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                            }
                                
                            //NOTA: Cuando haga el cambio, Que la matricula SI este en la "lista de asistencia" del proceso de encuestas seleccioado
                            //NPTA 2: Poder buscar la matricula del empleado con un List automatico mientras la vas escribiendo
                        }
                    }
                }else if(numReporte == 2){
                    /******************************* REPORTE 2 ***********************************/
                    /*****************************************************************************/ 

                    //1. Verifico si hay empleados que YA hayan hecho la Guia 2 o 3 de este proceso
                    if(ningunEmpCentroHizoEncuesta(valorGuia2_3))
                        Swal.fire('Accion no valida', 'Ningun empleado del centro de trabajo seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                    else{
                        if(_tipoReporte == 1)
                            window.location.href = 'reportes/excel/reporte_2_excel.php?claveEmpresa='+claveEmpresa+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                        else
                            window.open('reportes/reporte_2.php?claveEmpresa='+claveEmpresa+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                    }
                        
                }else if(numReporte == 3){
                     /******************************* REPORTE 3 ***********************************/
                    /*****************************************************************************/ 

                    //1. Verifico si se eligio alguna de las opciones de reporte:
                    var tipoRep3 = $('input:radio[name="tipoRep3"]:checked').val();

                    if(tipoRep3 == undefined)
                        Swal.fire('', 'Seleccione la opcion de reporte para generar', 'info')
                    else if(tipoRep3 == 1){
                        //>Se quiere generar el reporte de Todos los departamentos
                        //1. Verifico si hay empleados que YA hayan hecho la Guia 2 o 3 de este proceso
                        if(ningunEmpCentroHizoEncuesta(valorGuia2_3))
                            Swal.fire('Accion no valida', 'Ningun empleado del centro de trabajo seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                        else{
                            if(_tipoReporte == 1)
                                window.location.href = 'reportes/excel/reporte_3_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                            else
                                window.open('reportes/reporte_3.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                        }
                            
                    }else if(tipoRep3 == 2){
                        //>Se quiere generar el reporte de un solo Depto.
                        //1. Verifico si hay empleados que ESE depto que hayan hecho la Guia 2 o 3 de este proceso
                        if(ningunEmpDeptoHizoEncuesta(valorGuia2_3, $("#listaDeptosRep3").val()))
                            Swal.fire('Accion no valida', 'Ningun empleado del departamento seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                        else{
                            if(_tipoReporte == 1)
                                window.location.href = 'reportes/excel/reporte_3_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep3").val()+'&nombreDepto='+$("#listaDeptosRep3 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                            else
                                window.open('reportes/reporte_3.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep3").val()+'&nombreDepto='+$("#listaDeptosRep3 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                        }
                    }

                }else if(numReporte == 4){
                    /******************************* REPORTE 4 ***********************************/
                    /*****************************************************************************/ 
                    var tipoFiltroRep4 = $('input:radio[name="tipoFiltroRep4"]:checked').val();
                    var numGuiaRep4 = $("#listaGuiasRep4").val();
                    /*
                        tipoRep => 1: Emps. que Ya la hicieron, 2: Emps. que No la han hecho
                        tipoFiltro => 1: Todos los deptos., 2: Emps. de un solo depto

                    */ 

                    if($("#selectTipoRep4").val() == 1){
                        //Reporte de los empleados que YA HICIERON la Guia X **********
                        console.log("EMPLEADOS QUE YA HICIERON LA GUIA");

                        if(tipoFiltroRep4 == 1){
                            //Se van a tomar en cuenta Todos los empleados del Centro ----------
                            //Verifico si hay empleados de este CENTRO que Ya hicieron la Guia X
                            if(ningunEmpCentroHizoEncuesta(numGuiaRep4))
                                Swal.fire('Atención', 'Actualmente ningun empleado del centro de trabajo seleccionado ha realizado la Guia '+numGuiaRep4+' del proceso de encuestas seleccionado.', 'info')
                            else{
                                if(_tipoReporte == 1)
                                    window.location.href = 'reportes/excel/reporte_4_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&tipoFiltro=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                                else
                                    window.open('reportes/reporte_4.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&tipoFiltro=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                            }
                                
                        }else if(tipoFiltroRep4 == 2){
                            //Se van a tomar en cuenta los empleados de UN SOLO Depto. ----------
                            //Verifico si hay empleados de este DEPTO. que Ya hicieron la Guia X
                            if(ningunEmpDeptoHizoEncuesta(numGuiaRep4, $("#listaDeptosRep4").val()))
                                Swal.fire('Atención', 'Actualmente ningun empleado del departamento seleccionado ha realizado la Guia '+numGuiaRep4+' del proceso de encuestas seleccionado.', 'info')
                            else{
                                if(_tipoReporte == 1)
                                    window.location.href = 'reportes/excel/reporte_4_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&tipoFiltro=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep1").val()+'&nombreDepto='+$("#listaDeptosRep1 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                                else
                                    window.open('reportes/reporte_4.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&tipoFiltro=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep1").val()+'&nombreDepto='+$("#listaDeptosRep1 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                            }
                        }
                    }
                    else if($("#selectTipoRep4").val() == 2){
                        console.log("EMPLEADOS QUE NOO HAN HECHO LA GUIA");

                        //Reporte de los empleados que NO HAN HECHO la Guia X **********

                        if(tipoFiltroRep4 == 1){
                            //Se van a tomar en cuenta Todos los empleados del Centro
                            if(getNumEmpsCentroNoHanHechoGuia(numGuiaRep4) == 0)
                                Swal.fire('Atención', 'Actualmente todos los empleados vigentes del centro de trabajo seleccionado ya realizaron la Guia '+numGuiaRep4+' del proceso de encuestas seleccionado.', 'info')
                            else{
                                if(_tipoReporte == 1)
                                    window.location.href = 'reportes/excel/reporte_4_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&tipoFiltro=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                                else
                                    window.open('reportes/reporte_4.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&tipoFiltro=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                            }

                        }else if(tipoFiltroRep4 == 2){
                            //Se van a tomar en cuenta los empleados de UN SOLO Depto.
                            if(getNumEmpsDeptoNoHanHechoGuia(numGuiaRep4) == 0)
                                Swal.fire('Atención', 'Actualmente todos los empleados vigentes del departamento seleccionado ya realizaron la Guia '+numGuiaRep4+' del proceso de encuestas seleccionado.', 'info')
                            else{
                                if(_tipoReporte == 1)
                                    window.location.href ='reportes/excel/reporte_4_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&tipoFiltro=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep1").val()+'&nombreDepto='+$("#listaDeptosRep1 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                                else
                                    window.open('reportes/reporte_4.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&tipoFiltro=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep4+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&claveDepto='+$("#listaDeptosRep1").val()+'&nombreDepto='+$("#listaDeptosRep1 option:selected").text()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                            }
                        }
                    }

                }else if(numReporte == 5){
                    /******************************* REPORTE 5 ***********************************/
                    /*****************************************************************************/ 

                    var numGuiaRep5 = $("#listaGuiasRep5").val();

                    //1. Verifico si se ingreso la matricula del empleado:
                    if($("#matriculaRep5").val() == "")
                        Swal.fire('', 'Ingrese una matricula para poder generar el reporte', 'info')
                    
                    //2. Verifico si la matricula ingresada es Valida
                    else if(matriculaNoValida($("#matriculaRep5").val()))
                        Swal.fire('', 'Matricula no valida. Ingrese una matricula que exista en el centro de trabajo seleccionado', 'info')
                    
                    else if(reporteNoSePuedeGenerar($("#matriculaRep5").val(), numGuiaRep5))
                        Swal.fire('', 'Este empleado aun no ha realizado la Guia '+numGuiaRep5+' del proceso de encuestas seleccionado. No se puede generar el reporte', 'info')
                    
                    else{
                        //3. Si ya verifique las 3 cosas de arriba, ya puedo Generar el reporte
                        //Depto, Nombre depto, fecha aplicacion
                        window.open('reportes/reporte_5.php?claveEmpresa='+claveEmpresa+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep5+'&matricula='+$("#matriculaRep5").val()+'&nombre='+nombreEmpleado+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&claveDepto='+claveDepto+'&fechaAplicacion='+fechaAplicacion+'&nombreDepto='+nombreDepto);
                    }

                }else if(numReporte == 6){
                    /******************************* REPORTE 6 ***********************************/
                    /*****************************************************************************/ 

                    //1. Debo verificar que haya empleados del Centro seleccionado que YA hayan hecho la Guia 1
                    if (ningunEmpCentroHizoEncuesta(1))
                        Swal.fire('Atención', 'Actualmente ningun empleado del centro de trabajo seleccionado ha realizado la Guia 1 del proceso de encuestas seleccionado', 'info')
                    else{
                        //Ya puedo hacer el reporte:
                        if(_tipoReporte == 1)
                            window.location.href = 'reportes/excel/reporte_6_excel.php?claveProceso='+$("#selectProcesosEncuestas").val()+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                        else
                            window.open('reportes/reporte_6.php?claveEmpresa='+claveEmpresa+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                    }
                       
                }else if(numReporte == 7){
                    /******************************* REPORTE 7 ***********************************/
                    /*****************************************************************************/ 

                    //Verifico si el reporte se quiere hacer de Todos los deptos. o de solo Un Depto.
                    var tipoFiltroRep7 = $('input:radio[name="tipoFiltroRep7"]:checked').val();
                    
                    /*
                        tipoRep => 1: Por categorias, 2: Por dominios
                    */

                    if(tipoFiltroRep7 == 1){
                        //Verifioc si hay al menos 1 empleado de ese centro qye haya hecho la Guia 2 o 3 de este proceso
                        if(ningunEmpCentroHizoEncuesta(valorGuia2_3))
                            Swal.fire('Accion no valida', 'Ningun empleado del centro de trabajo seleccionado ha realizado la Guia '+valorGuia2_3+' del proceso de encuestas seleccionado.', 'info')
                        else{
                            if(_tipoReporte == 1)
                                window.location.href = 'reportes/excel/reporte_7_excel.php?claveEmpresa='+claveEmpresa+'&tipoFiltro=1&tipoRep='+$("#selectTipoRep7").val()+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso;
                            else
                                window.open('reportes/reporte_7.php?claveEmpresa='+claveEmpresa+'&tipoFiltro=1&tipoRep='+$("#selectTipoRep7").val()+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso);
                        }
                            
                    }else if(tipoFiltroRep7 == 2){
                        //Verifico qie haya el menos 1 empleado de ESE depto. que ya haya hecho la Guia 2 o 3 del proceso seleccionado:
                        if(ningunEmpDeptoHizoEncuesta(valorGuia2_3, $("#listaDeptosRep7").val()))
                            Swal.fire('Atención', 'Actualmente ningun empleado del departamento seleccionado ha realizado la Guia '+numGuiaRep4+' del proceso de encuestas seleccionado.', 'info')
                        else{
                            if(_tipoReporte == 1)
                                window.location.href = 'reportes/excel/reporte_7_excel.php?claveEmpresa='+claveEmpresa+'&tipoFiltro=2&tipoRep='+$("#selectTipoRep7").val()+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&claveDepto='+$("#listaDeptosRep7").val()+'&nombreDepto='+$("#listaDeptosRep7 option:selected").text();
                            else
                                window.open('reportes/reporte_7.php?claveEmpresa='+claveEmpresa+'&tipoFiltro=2&tipoRep='+$("#selectTipoRep7").val()+'&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+valorGuia2_3+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&claveDepto='+$("#listaDeptosRep7").val()+'&nombreDepto='+$("#listaDeptosRep7 option:selected").text());
                        }
                            
                    }

                }else if(numReporte == 8){
                    /******************************* REPORTE 8 ***********************************/
                    /*****************************************************************************/ 
                    /*
                        TipoRep => 1: Todos los deptos, 2: Empleados de un depto.
                    */

                    //Verifico si selecciono Todos los deptos. o Un solo depto.
                    var tipoRep8 = $('input:radio[name="tipoRep8"]:checked').val();
                    var numGuiaRep8 = $("#listaGuiasRep8").val();

                    if(tipoRep8 == undefined)
                        Swal.fire('', 'Seleccione la opcion de reporte para generar', 'info')
                    else if(tipoRep8 == 1){
                        //Verifico si hay empleados de este centro que ya hayan hecho la Guia 2 o 3
                        if(ningunEmpCentroHizoEncuesta(numGuiaRep8))
                            Swal.fire('Accion no valida', 'Ningun empleado del centro de trabajo seleccionado ha realizado la Guia '+numGuiaRep8+' del proceso de encuestas seleccionado.', 'info')
                        else{
                            if(_tipoReporte == 1)
                                window.location.href = 'reportes/excel/reporte_8_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep8+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&numEnc='+numEncuestados;
                            else
                                window.open('reportes/reporte_8.php?claveEmpresa='+claveEmpresa+'&tipoRep=1&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep8+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&numEnc='+numEncuestados);
                        }
                            
                    }else if(tipoRep8 == 2){
                        //Verifico si hay empleados de ESE Depto. que ya hayan hecho la Guia 2 o 3
                        if (ningunEmpDeptoHizoEncuesta(numGuiaRep8, $("#listaDeptosRep8").val()))
                            Swal.fire('Accion no valida', 'Ningun empleado del departamento seleccionado ha realizado la Guia '+numGuiaRep8+' del proceso de encuestas seleccionado.', 'info')
                        else{
                            if(_tipoReporte == 1)
                                window.location.href = 'reportes/excel/reporte_8_excel.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep8+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&claveDepto='+$("#listaDeptosRep8").val()+'&nombreDepto='+$("#listaDeptosRep8 option:selected").text()+'&numEnc='+numEncuestados;
                            else
                                window.open('reportes/reporte_8.php?claveEmpresa='+claveEmpresa+'&tipoRep=2&claveProceso='+$("#selectProcesosEncuestas").val()+'&numGuia='+numGuiaRep8+'&claveCentro='+$("#selectCentrosTrabajo").val()+'&nombreCentro='+nombreCentro+'&nombreProceso='+nombreProceso+'&claveDepto='+$("#listaDeptosRep8").val()+'&nombreDepto='+$("#listaDeptosRep8 option:selected").text()+'&numEnc='+numEncuestados);
                        }
                            
                    }
                }
            }

            //Funcion para verificar si el empleado Ya hizo la Guia 2 o 3 del proceso seleccionado
            function reporteNoSePuedeGenerar(matriculaEmp, numGuiaRep){
                var reporteNoSePuedeGenerar = true;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "verificaSiYaHizoGuia", claveEmpresa: claveEmpresa, matricula: matriculaEmp, numGuia: numGuiaRep, claveProceso: $("#selectProcesosEncuestas").val() },
                    success:function(res){
                        datos = JSON.parse(res);
                        
                        if(datos != null){ 
                            fechaAplicacion = datos.fecha
                            //Este empleado ya hizo esa guia
                            reporteNoSePuedeGenerar = false; //Osea, SI SE PUEDE GENERAR
                        }
                    }
                });
                return reporteNoSePuedeGenerar;
            }

            //Funcion para verificar si hay al menos 1 empleado del Centro seleccionado que haya hecho ya la Guia 2/3 del proceso seleccionado
            function ningunEmpCentroHizoEncuesta(numGuiaRep){
                var ningunEmpCentroHizoEncuesta = true;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "verificaSiEmpsYaHicieronGuia", claveEmpresa: claveEmpresa, numGuia: numGuiaRep, claveProceso: $("#selectProcesosEncuestas").val() },
                    success:function(res){
                        console.log("Emps que ya hiccieorn la uia: "+ res);

                        if(res > 0){
                            numEncuestados = res;
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
                            numEncuestados = res;
                            ningunEmpDeptoHizoEncuesta = false;
                        }
                            
                    }
                });
                return ningunEmpDeptoHizoEncuesta;
            }

            //*** NUEVO ***
            //Obtengo el Numero de empleados del CENTRO de trabajo que NO han hecho la Guia x
            function getNumEmpsCentroNoHanHechoGuia(numGuiaRep){
                var numEmpsCentroNoHanHechoGuia = 0;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getNumEmpsCentroNoHanHechoGuia", claveCentro: $("#selectCentrosTrabajo").val(), numGuia: numGuiaRep, 
                          claveProceso: $("#selectProcesosEncuestas").val(), claveEmpresa: claveEmpresa
                          },
                    success:function(res){
                        numEmpsCentroNoHanHechoGuia = res;
                    }
                });
                return numEmpsCentroNoHanHechoGuia;
            }

            //*** NUEVO ***
            //Obtengo el Numero de empleados del CENTRO de trabajo que NO han hecho la Guia x
            function getNumEmpsDeptoNoHanHechoGuia(numGuiaRep, _claveDepto){
                var numEmpsDeptoNoHanHechoGuia = 0;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getNumEmpsDeptoNoHanHechoGuia", claveCentro: $("#selectCentrosTrabajo").val(), claveEmpresa: claveEmpresa,
                        numGuia: numGuiaRep, claveProceso: $("#selectProcesosEncuestas").val(), claveDepto: _claveDepto
                    },
                    success:function(res){
                        numEmpsDeptoNoHanHechoGuia = res;
                    }
                });
                return numEmpsDeptoNoHanHechoGuia;
            }


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
                            nombreEmpleado = datos.nombreEmpleado;
                            claveDepto = datos.claveDepto;

                            //Obtengo el nombre del departamento que tenga la misma clave de este centro. En el select del reporte 1
                            nombreDepto = $("#listaDeptosRep1 option[value='"+claveDepto+"']").text();
                        }
                            
                    }
                });
                return matriculaNoExiste;
            }

            function modificaElementosModal1(valor1, valor2){
                $("#listaDeptosRep1").prop( "disabled", valor1); 
                $("#matriculaRep1").prop( "disabled", valor2 ); 
                $("#matriculaRep1").val(""); 
            }

            function modificaElementos(numReporte, tipoDatos){
                if(numReporte == 1){
                    if(tipoDatos == 1)
                        modificaElementosModal1(true, true);
                    else if(tipoDatos == 2)
                        modificaElementosModal1(false, true);
                    else if(tipoDatos == 3)
                        modificaElementosModal1(true, false);                    
                
                }else if(numReporte == 3){
                    if(tipoDatos == 1)
                        $("#listaDeptosRep3").prop( "disabled", true); 
                    else if(tipoDatos == 2)
                        $("#listaDeptosRep3").prop( "disabled", false);  

                }else if(numReporte == 4){
                    if(tipoDatos == 1)
                        $("#listaDeptosRep4").prop( "disabled", true); 
                    else if(tipoDatos == 2)
                        $("#listaDeptosRep4").prop( "disabled", false);   

                }else if(numReporte == 7){
                    if(tipoDatos == 1)
                        $("#listaDeptosRep7").prop( "disabled", true); 
                    else if(tipoDatos == 2)
                        $("#listaDeptosRep7").prop( "disabled", false);   

                }else if(numReporte == 8){
                    if(tipoDatos == 1)
                        $("#listaDeptosRep8").prop( "disabled", true); 
                    else if(tipoDatos == 2)
                        $("#listaDeptosRep8").prop( "disabled", false);   
                }
            }

            function generaRepPrueba(){
                window.location.href = 'reportes/reporte_excel3.php';
            }

        </script>
  </head>

  <body style="background-color: #f1f3f7;">
    <!-- Incluyo en la pagina la barra superior -->
    <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
    
    <div class="container">
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                    <div class="card-header" style="background-color: #0070c0; color: white;"><b>Generacion de reportes</b> </div>
                    <div class="card-body">
                        <label style="font-size: 15px;"> <b> Seleccione los datos para poder generar el reporte: </b></label><br>
                        <div class="row">
                            <div class="col-md-6">
                                <form class="form-inline">
                                    <label class="mr-sm-6">Seleccione el centro de trabajo: </label>
                                    <select class="form-control col-lg-6" onchange="cambioSelectCentrosTrabajo()" id="selectCentrosTrabajo"></select> <br>
                                </form>
                            </div>

                            <div class="col-md-6">
                                <form class="form-inline">
                                    <label class="mr-sm-6">Seleccione el proceso de encuestas: </label>
                                    <select class="form-control col-lg-6" onchange="cambioSelectProcsEncuestas()" id="selectProcesosEncuestas"></select> <br>
                                </form>
                            </div>
                        </div>
                    </div>
                </div><br>

                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                    <div class="card-header" style="background-color: #0070c0; color: white;"> <b> Generación de reportes </b></div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <label style="font-size: 17px;">1. Niveles de riesgo por categorias, dominios y calificacion final</label>
                            <!-- <button class="btn btn-outline-secondary float-right btn-sm" data-toggle="modal" data-target="#modalRep1">Generar</button> -->
                            <button onclick="validaProcesoEncuestas(1)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>                        
                        </li>
                        <li class="list-group-item">
                            <label style="font-size: 17px;">2. Distribucion de empleados por nivel de riesgo final de cada departamento</label>
                            <button onclick="validaProcesoEncuestas(2)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item">
                            <label style="font-size: 17px;">3. Niveles de riesgo final de empleados - Guia 3</label>
                            <button onclick="validaProcesoEncuestas(3)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item">
                            <label style="font-size: 17px;">4. Empleados que realizaron las Guias 1, 2 o 3</label>
                            <button onclick="validaProcesoEncuestas(4)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item">
                            <label style="font-size: 17px;">5. Respuestas del empleado de la Guia 1, 2 o 3</label>
                            <button onclick="validaProcesoEncuestas(5)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item">
                            <label style="font-size: 17px;">6. Empleados que requieren valoracion - Guia 1</label>
                            <button onclick="validaProcesoEncuestas(6)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        <li class="list-group-item">
                            <label style="font-size: 17px;">7. Niveles de riesgo por Categoria o Dominio de cada empleado - Guia 3</label>
                            <button onclick="validaProcesoEncuestas(7)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        
                        <li class="list-group-item">
                            <label style="font-size: 17px;">8. Frecuencia de respuesta de cada pregunta - Guia 1, 2 o 3</label>
                            <button onclick="validaProcesoEncuestas(8)" class="btn btn-outline-secondary btn-sm float-right">Generar</button>
                        </li>
                        
                    </ul>
                </div>
            </div>
        </div>
        <br>  

    </div><br>

    <!-- Modal del reporte 1: Niveles de riesgo por categorias, dominios y calificacion final -->
    <div class="modal fade" id="modalRep1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">1. Niveles de riesgo por categorias, dominios y calificacion final</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <label> Elija una de las siguientes opciones para generar el reporte: </label><br>
                    <input type="radio" id="todosDeptosRep1" onchange="modificaElementos(1, 1)" name="tipoRep1" value="1" > Todos los departamentos <br>

                    <input type="radio" name="tipoRep1" onchange="modificaElementos(1, 2)" value="2"> Por departamento <br>
                    <label> Seleccione el departamento: </label><br>
                    <select class="form-control" id="listaDeptosRep1"></select> <br>

                    <input type="radio" name="tipoRep1" value="3" onchange="modificaElementos(1, 3)"> Por empleado <br>
                    
                    <form class="form-inline">
                        <label class="mr-sm-6">Ingrese la matricula: </label>
                        <input type="text" id="matriculaRep1" class="form-control">
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(1, 1)" class="btn btn-outline-success">Generar Excel</button>

                    <button type="button" onclick="generarReporte(1, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal del reporte 2: Distribucion de empleados por nivel de riesgo final de cada departamento -->
    <div class="modal fade" id="modalRep2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">2. Distribucion de empleados por nivel de riesgo final de cada departamento</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                   
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(2, 1)" class="btn btn-outline-success">Generar Excel</button>

                    <button type="button" onclick="generarReporte(2, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal del reporte 3: Niveles de riesgo final de empleados - Guia 3 -->
    <div class="modal fade" id="modalRep3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">3. Niveles de riesgo final de empleados - Guia 3</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">    
                    <label> Elija una opcion para generar el reporte: </label><br>
                    <input type="radio" name="tipoRep3" value="1" id="todosDeptosRep3" onchange="modificaElementos(3, 1)"> Todos los departamentos <br>

                    <input type="radio" name="tipoRep3" value="2" onchange="modificaElementos(3, 2)"> Por departamento <br>
                    <label> Seleccione el departamento: </label><br>
                    <select class="form-control" id="listaDeptosRep3"></select> <br>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(3, 1)" class="btn btn-outline-success">Generar Excel</button>

                    <button type="button" onclick="generarReporte(3, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal del reporte 4: Empleados que realizaron las Guias 1, 2 o 3  -->
    <div class="modal fade" id="modalRep4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">4. Empleados que realizaron las Guias 1, 2 o 3 </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form class="form-inline">
                        <label> Elija la guia de la cual desea generar el reporte: </label><br>
                        <select class="form-control col-lg-3" id="listaGuiasRep4">
                        </select> <br>
                    </form>
                    <br>
                    <form class="form-inline">
                        <label> Tipo de reporte: </label><br>
                        <select class="form-control col-lg-8" id="selectTipoRep4">
                            <option value="1" selected>Empleados que ya hicieron la guia</option>
                            <option value="2">Empleados que no han hecho la guia</option>
                        </select> <br>
                    </form>
                    <br>

                    <label> Considerar empleados de: </label><br>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="radio" name="tipoFiltroRep4" id="todosDeptosRep4" value="1" onchange="modificaElementos(4, 1)"> Todos los departamentos <br>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="tipoFiltroRep4" value="2" onchange="modificaElementos(4, 2)"> Un solo departamento <br>
                        </div>
                    </div><br>

                    <label> Seleccione el departamento: </label><br>
                    <select class="form-control" id="listaDeptosRep4"></select> <br>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(4, 1)" class="btn btn-outline-success">Generar Excel</button>

                    <button type="button" onclick="generarReporte(4, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal del reporte 5: Respuestas del empleado de la Guia 1, 2 o 3  -->
    <div class="modal fade" id="modalRep5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">5. Respuestas del empleado de las Guias 1, 2 o 3 </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form class="form-inline">
                        <label> Elija la guia de la cual desea generar el reporte: </label><br>
                        <select class="form-control col-lg-3" id="listaGuiasRep5">
                        </select> <br>
                    </form>
                    <br>
                    <form class="form-inline">
                        <label> Ingrese la matricula del empleado: </label><br>
                        <input type="text" id="matriculaRep5" class="form-control">
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(5, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal del reporte 6: Empleados que requieren valoración - Guia 1 -->
    <div class="modal fade" id="modalRep6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">6. Empleados que requieren valoración - Guia 1</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                   
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(6, 1)" class="btn btn-outline-success">Generar Excel</button>

                    <button type="button" onclick="generarReporte(6, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal del reporte 7: Niveles de riesgo por Categoria o Dominio de cada empleado -->
    <div class="modal fade" id="modalRep7" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">7. Niveles de riesgo por Categoria o Dominio de cada empleado </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form class="form-inline">
                        <label> Elija la opción de reporte a generar: </label><br>
                        <select class="form-control col-lg-4" id="selectTipoRep7">
                            <option value="1" selected>Por categorias</option>
                            <option value="2">Por dominios</option>
                        </select> <br>
                    </form>
                    <br>

                    <label> Considerar empleados de: </label><br>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="radio" name="tipoFiltroRep7" id="todosDeptosRep7" value="1" onchange="modificaElementos(7, 1)"> Todos los departamentos <br>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="tipoFiltroRep7" value="2" onchange="modificaElementos(7, 2)"> Un solo departamento <br>
                        </div>
                    </div><br>

                    <label> Seleccione el departamento: </label><br>
                    <select class="form-control" id="listaDeptosRep7"></select> <br>

                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(7, 1)" class="btn btn-outline-success">Generar Excel</button>
                    
                    <button type="button" onclick="generarReporte(7, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal del reporte 8: Frecuencia de respuesta de cada pregunta - Guia 3 -->
    <div class="modal fade" id="modalRep8" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">Frecuencia de respuesta de cada pregunta - Guia 1, 2 o 3</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form class="form-inline">
                        <label> Elija la guia de la cual desea generar el reporte: </label><br>
                        <select class="form-control col-lg-3" id="listaGuiasRep8">
                        </select> <br>
                    </form>
                    <br>
                    
                    <label> Considerar empleados de: </label><br>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="radio" name="tipoRep8" id="todosDeptosRep8" value="1" onchange="modificaElementos(8, 1)"> Todos los departamentos <br>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="tipoRep8" value="2" onchange="modificaElementos(8, 2)"> Un solo departamento <br>
                        </div>
                    </div><br>

                    <label> Seleccione el departamento: </label><br>
                    <select class="form-control" id="listaDeptosRep8"></select> <br>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="generarReporte(8, 1)" class="btn btn-outline-success">Generar Excel</button>

                    <button type="button" onclick="generarReporte(8, 2)" class="btn btn-outline-danger">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>