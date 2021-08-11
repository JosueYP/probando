<?php
    /*
    session_start();
        
    //Verifico si la variable 'user' ya tiene asignada algun valor
    if (isset($_SESSION['rolUsuario'])) 
        $rol = $_SESSION['user']; //Creo una nueva variable y le asigno un valor
    */
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Esto es para poder usar Datatables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <!-- Esto es para poder usar Bootstrap <<<< ESTOY USANDO BOOTSTRAP 4  -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- Esto es para poder usar JQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <!-- Esto es para poder usar Sweetalert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel=stylesheet type="text/css" href="estilos.css">
        <link rel="shortcut icon" href="favicon.png">

        <title>FRP 035 - Sistema de implementacion de la NOM-035</title>

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
            //Obtengo y guardo la variable que me dice el tipo de usuario que esta logeado
            var rolUsuario = "<?php session_start(); echo $_SESSION['rolUsuario'] ?>";
            var _psw; var _claveEmpresa; var _claveCentro; var _matricula; var _nombre;  
            var _nombreCentro; var _idUsuario; var _nombreEmpresa;

            $(document).ready(function () {
                $('#mostrar_contra').click(function () {
                    if ($('#mostrar_contra').is(':checked')) 
                        $('#contrasena').attr('type', 'text');
                    else 
                        $('#contrasena').attr('type', 'password');
                });
            });

            //Esta funcion se manda a llamar cuando el usuario da clic en la barra en el boton "Ingresar"
            function ingresar(){
                if(rolUsuario == "")
                    //Quiere decir que aun no esta logeado, asi que muestro la ventana de login
                    $('#modalLogin').modal('show');
                else
                    //Es un usuario ya logeado, asi que lo mando a la ventana que le corresponde dependienod 
                    //window.location.href = 'menu.php';
                    window.location.href = 'menu.php';
            }

            function login(){
                 //1. Verifico si fue un Empleado o un Admin el que quiere iniciar sesion <<<<<
                var nombreTab = $('.nav-tabs .active').text();

                if(nombreTab == "Empleado"){
                    //NOTA: La Matricula debe pertenecer al Centro de trabajo ingresado.
                    var claveCentroValida = false; //Por default, va a ser False
                    var matriculaValida = false;

                    //Verifico si los datos ingresados por el EMPLEADO estan correctos
                    //1. Valido los datos ingresados en "claveCentro" ----------------
                    if($("#claveCentro").val() == "")
                        $("#errorCentro").text("*Ingrese el dato solicitado");
                    else{
                        //Si SI tiene datos, verifico que la clave ingresada SI exista (y este activa)
                        if (claveCentroExiste()){
                            claveCentroValida = true;
                            $("#errorCentro").text("");
                        }else
                            $("#errorCentro").text("*Ingrese una clave de centro valida");
                    }


                    //2. AHora verifico la matricula: --------------------------------
                    if($("#matricula").val() == "")
                        $("#errorMatricula").text("*Ingrese el dato solicitado");
                    else{
                        if(claveCentroValida == true){
                            //Ahora verifico si la Matricula esta correcta:
                            if (matriculaExiste())
                                matriculaValida = true;
                            else
                                $("#errorMatricula").text("*Ingrese una matricula valida");
                        }else
                            $("#errorMatricula").text("");
                    }

                    //3. Ya que hice las 2 verificaciones, mando al usuario a su ventana
                    if(claveCentroValida == true && matriculaValida == true)
                        window.location.href = 'menu.php?rolUsuario=0&claveEmpresa='+_claveEmpresa+'&claveCentro='+_claveCentro+'&matricula='+_matricula+'&nombre='+_nombre+'&nombreCentro='+_nombreCentro+'&nombreEmpresa='+_nombreEmpresa;
                        //NOTA: Aqui falta verificar que el usuario pueda entrar o no a hacer encuestas
                }
                else{
                    //Verifico si los datos ingresados por el ADMIN estan correctos
                    var correoValido = false; 
                    var contrasenaValida = false;

                    //1. Valido los datos ingresados en "correo" ----------------
                    if($("#correo").val() == "")
                        $("#errorCorreo").text("*Ingrese el dato solicitado");
                    else{
                        //Si SI tiene datos, verifico si el correo ingresado EXISTA en la tabla de Admins.
                        if (correoExiste()){
                            correoValido = true;
                            $("#errorCorreo").text("");
                        }else
                            $("#errorCorreo").text("*Ingrese un correo válido");
                    }

                    //2. AHora verifico la contraseña: --------------------------------
                    if($("#contrasena").val() == "")
                        $("#errorContrasena").text("*Ingrese el dato solicitado");
                    else{
                        if(correoValido == true){
                            //Ahora verifico si la Contraseña ingresada esta correcta:
                            if(_psw == $("#contrasena").val())
                                contrasenaValida = true;
                            else
                                $("#errorContrasena").text("*Contraseña no valida");
                        }else
                            $("#errorContrasena").text("");
                    }

                    console.log("Clave empresa: "+_claveEmpresa);
                    console.log("Clave centro: "+_claveCentro);

                    //3. Ya que hice las 2 verificaciones, mando al usuario a su ventana
                    if(correoValido == true && contrasenaValida == true){
                        //window.location.href = 'procesos-encuestas.php?rolUsuario=1&claveEmpresa='+_claveEmpresa+'&claveCentro='+_claveCentro+'&matricula='+_matricula+'&nombre='+_nombre;
                        //Aqui le debo de mandar a la pagina login=true, claveEmpresa, matricula, nombre, claveCentro

                        window.location.href = 'menu.php?rolUsuario=1&claveEmpresa='+_claveEmpresa+'&claveCentro='+_claveCentro+'&matricula='+_matricula+'&nombre='+_nombre+'&nombreCentro='+_nombreCentro+'&idUsuario='+_idUsuario+'&nombreEmpresa='+_nombreEmpresa;
                        
                    }
                }
            }

            //Funcion para verificar si la clave de centro ingresada SI existe
            function claveCentroExiste(){
                var claveExistente = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getclaveCentroExistente", claveCentro: $("#claveCentro").val()
                    }, 
                    success:function(res){
                        var datos = JSON.parse(res);
                        console.log(datos);

                        //AQUI TENGO QUE GUARDAR LOS DATOS QUE VOY A NECESITAR DESPUES:
                        if(datos != null){
                            claveExistente = true; 
                            _claveEmpresa = datos.claveEmp; _claveCentro = datos.claveCentro;
                            _nombreCentro = datos.nombreCentro; _nombreEmpresa = datos.nombreEmpresa;
                        
                        }
                    }
                });
                return claveExistente;
            }

            //Funcion para verificar si la Matricula ingresada existe en el Centro de trabajo ingresado
            function matriculaExiste(){
                var matriculaExiste = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getEmpleadoByCentroTrabajo", 
                        claveCentro: $("#claveCentro").val(), matricula: $("#matricula").val()
                    }, 
                    success:function(res){
                        var datos = JSON.parse(res);
                        console.log(datos);

                        if(datos != null){
                            matriculaExiste = true; 
                            //AQUI DEBO GUARDAR LA MATRICULA Y EL NOMBRE:
                            _matricula = datos.matricula; _nombre = datos.nombreEmpleado;
                        }
                    }
                });
                return matriculaExiste;
            }

            //Funcion para verificar si la Matricula ingresada existe en el Centro de trabajo ingresado
            function correoExiste(){
                var correoExiste = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getDatosAdminByCorreo", correo: $("#correo").val()
                    }, 
                    success:function(res){
                        var datos = JSON.parse(res);
                        console.log(datos);
                        
                        if(datos != null){
                            correoExiste = true; 
                            //Guardo los datos que voy a necesitar despues
                            _psw = datos.psw; _claveEmpresa = datos.claveEmp; _claveCentro = datos.claveCentro;
                            _matricula = datos.matricula; _nombre = datos.nombre; _idUsuario = datos.idUsuario;
                            _nombreEmpresa = datos.nombreEmpresa;
                        }
                           
                    }
                });
                return correoExiste;
            }

            /*
                claveCentro, matricula, correo, contrasena
                errorCentro, errorMatricula, errorCorreo, errorContrasena
            */

        </script>
    </head>

    <body style="background-color: #f1f3f7;">
        <!-- Incluyo en la pagina la barra superior -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light" style=" box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); ">
            <a class="navbar-brand mb-0 h1" href="#"><img src="frp035.png" width="140" class="d-inline-block align-top" alt=""> </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item" style="margin-right: 15px;"><a class="nav-link" href="index.php"><b> Inicio </b></a></li>

                <!-- 
                <li class="nav-item" style="margin-right: 15px;"><a class="nav-link" href="#"><b> Caracteristicas </b></a></li>

                <li class="nav-item" style="margin-right: 15px;"><a class="nav-link" href="#"><b> Contacto </b></a></li>

                <li class="nav-item" style="margin-right: 15px;"><a class="nav-link" href="#"><b> ¿Quienes somos? </b></a></li> -->
            </ul>

            <ul class="nav navbar-nav ml-auto">
                <!-- Dependiendo de si ya inicio sesion o no, se mostrara un texto u otro -->
                <button onclick="ingresar()" class="btn btn-outline-primary navbar-btn" type="button">
                Ingresar
                </button> 

                <!-- <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#exampleModal">Ingresar </button> -->
            </ul>
            </div>
        </nav>
        
        <br>
        <div class="container">
            <h1 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"><b>FRP 035</b></h1>
            <div class="row">
                <div class="col-md-7">
                    <h5 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">La Secretaria del Trabajo y Prevision social, a traves de la implementación de la NOM-035-STPS-2018, busca detectar los factores de riesgo psicosocial en el trabajo, proporcionando en dicha norma las herramientas y metodologias para su identificación, analisis y prevención.</h5>

                    <h5 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">Esta norma establece que el patrón, debe implantar, mantener y difundir en el centro de trabajo, la prevención de riesgos psicosociales, la prevención de violencia laboral; y la promoción de un entorno organizacional favorable. </h5>
                </div>
                <br />
                <div class="col-md-5"><img style="max-width: 80%; margin-left: 50px" src="persona.jpg" /></div>
            </div>
            <br>
            <h5 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">Para tal fin, la ARHVER diseño el sistema denominado <b>FRP 035</b>, el cual tiene la finalidad de establecer de manera muy sencilla y clara, las herramientas y metodologías de medición y validación de las encuestas establecidas por la NOM-035</h5>
            <br />

            <h4 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"><b>Confiabilidad </b></h4>
            <h5 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">Con el sistema FRP 035 tendras la confiabilidad de aplicar una encuesta con las directrices establecidas por la STPS, de tal manera que la medición, analisís e identificación de los factores de riesgo psicosocial, cumplan con lo establecido por la normatividad.</h4>

            <h5 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">Dicha medición te permitira redireccionar los esfuerzos que impactaran directamente en la satisfacción de tus empleados, y por ende, en los resultados de toda la empresa.</h4>
            <br />
            <div class="row">
                <div class="col-md-4"><img style="max-width: 90%; margin-top: 20px" src="reportes.jpg" /></div>

                <div class="col-md-8">
                    <h4 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"><b> Beneficios </b></h4>

                    <ul style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: large">
                        <li>Acceso vía internet </li>
                        <li>Integra en una base de datos a los empleados de su Empresa</li>
                        <li>Aplicación de encuesta cuando el administrador de la organización lo requiera </li>
                        <li>Facilita el cumplimiento de la NOM-035 al Identificar los factores de Riesgo Psicosocial y evaluación del Entorno organizacional </li>
                        <li>Identifica y evalúa factores psicosociales (ambiente de trabajo, Factores de la actividad, Organización del trabajo, Liderazgo & relaciones en el trabajo y entorno organizacional) </li>
                        <li>Generación de resultados de la encuesta por diferentes enfoques para definir el plan de acción (individual, departamental o global) </li>
                        <li>Ahorro de tiempo y esfuerzo en la aplicación del cuestionario </li>
                        <li>Optimización en el tiempo de calificación del cuestionario </li>
                        <li>Ahorro de papel </li>
                    </ul>
                </div>
            </div>
        </div>   
        <br> <br>

        <div class="modal fade" id="modalLogin" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel">Ingrese al portal del cliente</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- PESTAÑAS -->
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tabEmpleado" data-toggle="tab" href="#empleado" role="tab" aria-controls="empleado" aria-selected="true">Empleado</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tabAdmin" data-toggle="tab" href="#admin" role="tab" aria-controls="admin" aria-selected="false">Administrador</a>
                            </li>
                        </ul>

                        <!-- CONTENIDO DE LAS PESTAÑAS -->
                        <div class="tab-content" id="myTabContent">
                            <!-- CONTENIDO 1 -->
                            <div class="tab-pane fade show active" id="empleado" role="tabpanel" aria-labelledby="tabEmpleado">
                                <br>
                                <div class="mb-3">
                                    <label class="form-label">Ingrese clave de centro de trabajo:</label>
                                    <input id="claveCentro" type="text" class="form-control"  >
                                    <small id="errorCentro" style="color: red;"></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ingrese su matricula:</label>
                                    <input id="matricula" type="text" class="form-control" >
                                    <small id="errorMatricula" style="color: red;" ></small>
                                </div>
                            </div>

                            <!-- CONTENIDO 2 -->
                            <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="tabAdmin">
                                <br>
                                <div class="mb-3">
                                    <label class="form-label">Ingrese su correo:</label>
                                    <input id="correo" type="email" class="form-control"  >
                                    <small id="errorCorreo" style="color: red;"></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ingrese su contraseña:</label>
                                    <input id="contrasena" type="password" class="form-control" >
                                    <small id="errorContrasena" style="color: red;"></small>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="mostrar_contra">
                                    <small class="form-check-label" for="mostrar_contra"> Mostrar contraseña </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" onclick="login()" class="btn btn-primary">Ingresar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Esto es para... varias cosas -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    </body>
</html>