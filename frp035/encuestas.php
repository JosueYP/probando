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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel="shortcut icon" href="favicon.png">

        <title>Realizar encuestas</title>

         <!-- Aqui va el codigo Javascript -->
         <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            var claveCentro = "<?php session_start(); echo $_SESSION['claveCentro'] ?>";
            var matricula = "<?php session_start(); echo $_SESSION['matricula'] ?>";
            var numGuiaProceso; var claveProceso; var fechaEncuesta;
            console.log("La matricula del usuario es: "+ matricula);
            console.log("La clave del centro del empleado es: "+ claveCentro);
            var numGuiaParaContestar;

            window.onload = function(){
                /*1. Verifico si hay algun proceso de encuestas Abierto en el Centro de trabajo
                    al que pertenece el empleado logeado
                */
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getProcesoEncuestasAbiertoByCentro", claveCentro: claveCentro
                    },
                    success:function(res){
                        var datos = JSON.parse(res);

                        if (datos == null){
                            //Escondo las 2 guias disponibles y muestro el card escondido
                            $('#divNoProceso').show();
                        }else{
                            $('#divGuias').show();
                            //Guardo la clave del Proceos Abierto para usarla despues
                            claveProceso = datos.claveProceso
                            //-------------
                            console.log("La clave del proceso actual es: "+ claveProceso);

                            //Verifico si este proceso es con la Guia 2 o 3:
                            if(datos.guia2 == 1)
                                numGuiaProceso = 2;
                            else if(datos.guia3 == 1){
                                numGuiaProceso = 3;
                                $('#labelNumGuia').html("Guia 3"); 
                            }
                        }
                    }
                });

                //NOTA: Aqui tengo que validar si el proceso que esta Abierto le toca la Guia 1 o la Guia 2. Para poder cambiar
                //      la etiqueta que esta en las Cards
                

                //Dependiendo del tipo de Usuario, escondo o no algunas de las opciones de la barra
                //$('#tabCatalogos').hide();
                //$('#tabReps').hide();

                $('#modalInstrucciones').on('hidden.bs.modal', function () {
                    //Desmarco el checkBox y limpio el mensaje de error
                    $("#checkBoxConfirmar" ).prop( "checked", false );
                    $("#errorConfirmar").text("");
                })

                $('#modalInstrucciones').on('shown.bs.modal', function () {
                    $('#modalInstrucciones .modal-body').animate({ scrollTop: 0 }, 500);
                })  
            }

            function cerrarSesion(){
                var boton = document.getElementById('btnCerrarSesion');

                //Mando al usuario a la pagina donde se cerrara la sesion
                window.location.href = 'cerrarSesion.php';
            }

            function ingresarEncuesta(numGuia){
                //NPOTA: Cuando es la Guia 1 o 2, recibo "0" como valor
                if(numGuia == 0) 
                    //Si manda 0, entonces es o 2 o 3 ----
                    numGuiaParaContestar = numGuiaProceso;
                else
                    numGuiaParaContestar = numGuia; //Es la Guia 1

                //$('#modalInstrucciones').modal('show');

                //console.log("La guia a la que quier ingresar es: "+ numGuiaParaContestar);
                console.log("claveEmpresa: "+ claveEmpresa);
                console.log("matricula: "+ matricula);
                console.log("Num Guia: "+ numGuiaParaContestar);
                console.log("Clave Proceso: "+ claveProceso);

                //1. Verifico si la persona YA hizo esta Guia
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "verificaSiYaHizoGuia", claveEmpresa: claveEmpresa, matricula: matricula, numGuia: numGuiaParaContestar, claveProceso: claveProceso },
                    success:function(res){
                        datos = JSON.parse(res);
                        
                        //console.log("ANTES DE QUE VERIFIQUE");
                        console.log(datos)

                        if(datos != null){
                            //Quiere decir que ESTA persona YA hizo la encuesta a la que quiere ingresar

                            fechaEncuesta = datos.fecha;
                            //Quiere decir que SI hizo esta Guia de este proceso
                            fechaEncuesta = fechaEncuesta.split('-').reverse().join('/');
                            //Guardo la fecha en la que la persona hizo la encuesta
                            Swal.fire('', 'Usted ya realizo la guia seleccionada el '+fechaEncuesta+'. No puede volver a realizarla', 'info')
                        }else{ 
                            //Le muestro al usuario el Modal de las Instrucciones:
                            $('#modalInstrucciones').modal('show');
                        }
                    }
                })
            }

            function comenzarEncuesta(){
                //1. Verifico si el empleado YA marco la casilla de confirmacion
                if( $('#checkBoxConfirmar').prop('checked') ) {
                    //Ya puede comenzar con la encuesta
                    $("#errorConfirmar").text("");
                    window.location.href = 'encuesta.php?numGuia='+numGuiaParaContestar+'&claveProceso='+claveProceso;

                }else{
                    $('#errorConfirmar').html('Por favor marque la casilla de confirmación para poder comenzar ');
                    $('#modalInstrucciones .modal-body').animate({ scrollTop: $('#modalInstrucciones .modal-body').height() }, 'slow');
                }
            }

            function validaCheckBox(){
                if( $('#checkBoxConfirmar').prop('checked') )
                    $("#errorConfirmar").text(""); //Limpio la etiqueta
            }

        </script>
  </head>

  <body style="background-color: #f1f3f7;">
    <!-- Incluyo en la pagina la barra superior -->
    <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
    
    <div class="container">
        <br>
        <div class="row" id="divGuias" style="display: none;">
            <div class="col-md-9">
                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); ">
                    <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Realizar encuestas </b> </div>
                    <div class="card-body">
                        <!-- Agrego el titulo del proceso de encuestas (Solo para el admin) -->
                        <h5 id="nombreProceso">  </h5>

                        <!-- Agrego las 2 cards de cada tipo de Guia -->
                        <div class="row">
                            
                            <div class="col-md-6">
                               
                                <div class="card" style="height: 230px">
                                    <div class="card-header" > <b> Guia 1 </b> </div>
                                    <div class="card-body">
                                        Cuestionario para identificar a los trabajadores que fueron sujetos a eventos traumaticos severos

                                        <br><br><a onclick="ingresarEncuesta(1)" class="btn btn-outline-secondary float-right">Realizar</a>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="col-md-6">
                                
                                <div class="card" style="height: 230px">
                                    <div class="card-header" > <b> <label id="labelNumGuia"> Guia 2 </label></b> </div>
                                    <div class="card-body">
                                        Cuestionario para identificar los factores de riesgos psicosocial en los centros de trabajo

                                        <br><br><a onclick="ingresarEncuesta(0)" class="btn btn-outline-secondary float-right">Realizar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>  

        <div class="row" id="divNoProceso" style="display: none;">
            <div class="col-md-8">
                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 250px">
                    <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Aplicación de encuestas </b> </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); ">
                                    <div class="card-header" > <b> Funcionalidad no disponible </b> </div>
                                    <div class="card-body">
                                        Por el momento no hay ningun proceso de encuestas abierto en el centro de trabajo al que usted pertenece. Consulte con su administrador para mas información.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><br>

    <!-- Modal de las instrucciones -->
    <div class="modal fade" id="modalInstrucciones" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title col-11 text-center">Instrucciones</h5>
                    <button type="button" class="close col-1" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="max-height: calc(100vh - 210px); overflow-y: auto;">
                    <h6>Lea con atención las instrucciones para el llenado de la encuesta:</h6>
                    <br>
                    <label> <b>1.</b> Requerimos su total concentracion durante la aplicación de la encuesta.</label>
                    <label><b>2.</b> Lea con atencion la pregunta mostrada y seleccione una respuesta. No existen respuestas correctas o incorrectas.</label>
                    <label> <b>3.</b> Conteste todas las preguntas mostradas en pantalla. Su opinion es muy importante, por lo que le pedimos conteste con sinceridad.</label>
                    <label><b>4.</b> De clic en "Continuar" una vez haya contestado las preguntas del bloque mostrado.</label>
                    <label><b>5.</b> Al haber contestado todas las preguntas, se mostrara un mensaje que indicara que usted ha terminado la encuesta.</label>
                    <label><b>6.</b> Asegurese de no cerrar su navegador antes de haber terminado la encuesta ya que sus respuestas no seran guardadas.</label>
                    <label><b>7.</b> Si usted va a realizar la Guia 2 o la Guia 3, considere solo las condiciones de <u>los ultimos 2 meses</u> para el llenado de la encuesta.</label>
                    <br><br>
                    <label>Los resultados de la encuesta seran utilizados exclusivamente para fines de mejora el ambiente de trabajo. Sus datos seran manejados de manera confidencial.</label>
                    <br><br>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="checkBoxConfirmar" onchange="validaCheckBox()">
                        <label class="form-check-label"> Confirmo que he leido atentamente las instrucciones mostradas y deseo comenzar la encuesta.</label><br>
                        <small id="errorConfirmar" style="color: red;"> <b>  </b> </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="comenzarEncuesta()" class="btn btn-success">Comenzar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>