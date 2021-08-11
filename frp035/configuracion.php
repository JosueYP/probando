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

         <!-- Esto es para poder editar la tabla -->
         <script src="https://markcell.github.io/jquery-tabledit/assets/js/tabledit.min.js"></script>
        <!-- Esto es para poder usar los iconos en los botones -->
        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js"></script>

        <title>Configuración</title>

         <!-- Aqui va el codigo Javascript -->
         <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
            var idUsuario = "<?php session_start(); echo $_SESSION['idUsuario'] ?>";
            var nombreAdministrador;

            $(document).ready(function(){
                configuraTablaAdmins(); 
                configuraSelectCentrosTrabajo();

                console.log("ID USUARIO: "+ idUsuario);

                //Hago esto para que se muestre la pestaña 1
                //$('[href="#tabEmpleado"]').tab('show');
            });

            function configuraSelectCentrosTrabajo(){
                //Cargo en el Select 1 la lista de los Centros de trabajo
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getListaCentroTrabajo", claveEmpresa:claveEmpresa
                    },
                    success:function(res){
                        var centrosTrabajo = JSON.parse(res);

                        //Muestro la informacion obtenida en el Select
                        select = document.getElementById("selectCentrosTrabajo");
                        for(var i=0 in centrosTrabajo) {
                            option = document.createElement("option");
                            option.value = centrosTrabajo[i][1];
                            option.text = centrosTrabajo[i][3];
                            select.appendChild(option);
                        }
                    }
                }); 
            }

            function configuraTablaAdmins(){
                //NOTA: Tengo que mostrar SOLO los Administradores de ESTA EMPRESA...

                var tabla = $('#tablaAdministradores').DataTable({
                    "ajax":{
                        "method":"GET", "url": "ajax.php", 
                        "data": {"funcion": "getAdministradores", "claveEmpresa": claveEmpresa}
                    },
                    "columns":[
                        {"data":"idUsuario"},
                        {   
                            //Numero de encuestados de la Guia 2 -----
                            "render": function (data, type, full, meta) { 
                                if(full.matricula == null)
                                    return '---';
                                else
                                    return full.matricula; 
                            }
                        },
                        {"data":"nombre"}, 
                        {   
                            //Numero de encuestados de la Guia 2 -----
                            "render": function (data, type, full, meta) { 
                                if(full.claveCentro == null)
                                    return '---';
                                else
                                    return full.claveCentro; 
                            }
                        },
                        {"data":"correo"} 
                    ],
                        "columnDefs": [ {"className": "dt-center", "targets": [1, 3] }
                    ]
                });


                $('#tablaAdministradores').on('draw.dt', function(){
                    //Esto se ejecutara cuando se dibuje la tabla. Pero solo se debe de hacer una vez
                    $('#tablaAdministradores').Tabledit({
                        url:'edicionTablaAdmins.php', dataType:'json',
                        columns:{
                            identifier : [0, 'idUsuario'],
                            editable:[
                                [2, 'nombre'], [4, 'correo']
                            ]
                        },
                        hideIdentifier:true, restoreButton:false,
                        buttons: {
                            edit: {
                                class: 'btn btn-sm btn-outline-secondary',
                                html: '<span class="fas fa-pencil-alt"></span>',
                                action: 'edit'
                            },
                            delete: {
                                class: 'btn btn-sm btn-outline-secondary',
                                html: '<span class="fa fa-trash"></span>',
                                action: 'delete'
                            },
                            save: {
                                class: 'btn btn-sm btn-success',
                                html: '<span class="fas fa-save"></span>'
                            },
                            confirm: {
                                class: 'btn btn-sm btn-outline-danger',
                                html: 'Confirmar'
                            }
                        },
                        onSuccess:function(data, textStatus, jqXHR){
                            //Esto pasara si las acciones SI se llevaron a cabo
                            if(data.action == 'edit'){
                                Swal.fire('', 'Los datos del administrador se han editado correctamente', 'success')
                                $('#tablaAdministradores').DataTable().ajax.reload();
                            }

                            else if(data.action == 'delete'){
                                Swal.fire('', 'Administrador eliminado correctamente', 'success')
                                $('#tablaAdministradores').DataTable().ajax.reload();
                            }
                        },
                        onAjax:function(action, data, serialize) {
                            //Si voy a Editar el centro de trabajo, valido lo siguiente:

                            if(action === "delete"){
                                var valores = data.split('&');

                                //Obtengo el ID del Usuario que se quiere eliminar
                                var fila_idUsuario = valores[0];
                                var fila_idUsuario_ = fila_idUsuario.split('=');
                                var idUsuario_ = fila_idUsuario_[1];

                                console.log("El ID del usario que se quiere eliminar es: "+ idUsuario_);

                                //Verifico si el Usuario que se quiere eliminar es el mismo que esta logeado
                                if(idUsuario_ == idUsuario){
                                    Swal.fire('', 'El administrador seleccionado no puede ser eliminado ya se es el mismo que se encuentra logeado', 'info')
                                    return false;
                                }
                                else{
                                    return true; //Procedo a eliminar este usuario.
                                }
                                   
                            }
                            else if(action === "edit"){
                                var valores = data.split('&');

                                //Obtengo el ID del Admin.
                                var fila_idAdmin = valores[0];
                                var fila_idAdmin_ = fila_idAdmin.split('=');
                                var idAdmin = fila_idAdmin_[1];

                                //Obtengo la Nombre del admin
                                var fila_nombre = valores[1];
                                var fila_nombre_ = fila_nombre.split('=');
                                var nombreAdmin = fila_nombre_[1];
                            
                                //Obtengo la Correo del admin
                                var fila_correo = valores[2];
                                var fila_correo_ = fila_correo.split('=');
                                var correo = fila_correo_[1];
                                correo = correo.replace("%40","@"); //<--- 
                                
                                if(nombreAdmin == ""){
                                    Swal.fire('', 'Ingrese el nombre del administrador', 'info')
                                    return false;
                                }
                                else if(correo == ""){
                                    Swal.fire('', 'Ingrese el correo del administrador', 'info')
                                    return false;
                                }else{
                                    //Verifico si hay OTRO Centro de trabajo que tenga el mismo nombre
                                    if(elCorreoEstaUsado(idAdmin, correo)){
                                        Swal.fire('', 'Correo no valido. Ingrese un correo diferente', 'error')
                                        return false;
                                    }else
                                        return true;
                                }
                                
                            }
                        }
                    });
                });
            }

            function elCorreoEstaUsado(_idUsuario, _correo){
                //Verifico si hay otro Admin que tenga el correo ingresado pero que NO sea sea ESTE Admin.
                var elCorreoEstaUsado = false;
                
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "verificaCorreoRepetido", idUsuario: _idUsuario, correo: _correo},
                    success:function(res){
                        var datos = JSON.parse(res);
                        
                        if(datos != null)
                            //Quiere decir que SI hay un Centro de trabajo en esta empresa que se llama igual (sin contar a este)
                            elCorreoEstaUsado = true;
                    }
                });
                return elCorreoEstaUsado;
            }

            function cambiarPassword(){
                //Verifico si todos los campos estan llenados
                if($("#contraActual").val().trim() == "" || $("#contraNueva1").val().trim() == "" || $("#contraNueva2").val().trim() == ""){
                    console.log("Inputs vacios");
                    Swal.fire('Atención', 'Ingrese todos los campos requeridos', 'info')
                }
                //Verifico si la contraseña actual ingresada es la correcta
                else if(contraActualIncorrecta()){
                    Swal.fire('Atención', 'La contraseña actual ingresada no coincide con su contraseña actual', 'info')
                }
                //Verifico si se ingreso igual la nueva contraseña en ambos campos
                else if($("#contraNueva1").val().trim() != $("#contraNueva2").val().trim()){
                    Swal.fire('Atención', 'Ingrese correctamente la nueva contraseña en ambos campos', 'info')
                }
                //Si ya verifique Todo lo anterior, entonces ya la puedo cambiar
                else{
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "cambiarPassword", nuevaContra: $("#contraNueva1").val(), idUsuario: idUsuario
                        },
                        success:function(res){
                            if(res>0){
                                //Borro lo que se ingreso en los Inputs
                                $("#contraActual").val(""); $("#contraNueva1").val(""); $("#contraNueva2").val("");
                                
                                //Mando mensaje de confirmacion al usuario
                                Swal.fire('', 'La contraseña se ha actualizado correctamente', 'success')
                            }
                            else
                                Swal.fire('', 'No se pudo actualizar la contraseña', 'error')
                        },
                        error:function(){
                            Swal.fire('', 'Hubo un error en la base de datos', 'error')
                        }
                    }); 
                }
            }

            function contraActualIncorrecta(){
                var contraActualIncorrecta = true;
                
                //Busco los datos del admin. logeado 
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getDatosAdminByIdUsuario", idUsuario: idUsuario},
                    success:function(res){
                        var datos = JSON.parse(res);
                        
                        if(datos.psw == $("#contraActual").val())
                            contraActualIncorrecta = false;
                    }
                });
                return contraActualIncorrecta;
            }

            function cambiaSelectTipoAdmin(){
                //Verifico cual es el valor del select
                if($("#selectTipoAdmin").val() == 2){
                    //Inabilito estos 2 elemenmtos:
                    $("#selectCentrosTrabajo").prop( "disabled", true); 
                    $("#matriculaAdmin").prop( "disabled", true); 
                    $("#matriculaAdmin").val("");
                    $("#selectCentrosTrabajo").empty();
                }else{
                    //Habilito los 2 elementos
                    $("#selectCentrosTrabajo").prop( "disabled", false); 
                    $("#matriculaAdmin").prop( "disabled", false);
                    configuraSelectCentrosTrabajo(); 
                }
            }

            function agregarAdmin(_tipoAdmin){
                if(_tipoAdmin == 1){
                    //QUIERO AGREGAR A UN EMPLEADO
                    if($("#matriculaEmpleado").val().trim() == "" || $("#correoEmpleado").val().trim() == "" || $("#contraEmpleado1").val().trim() == "" || $("#contraEmpleado2").val().trim() == "")
                        Swal.fire('Atención', 'Ingrese todos los campos requeridos para poder agregar al administrador', 'info')
                    
                    else if($("#contraEmpleado1").val().trim() != $("#contraEmpleado2").val().trim())
                        Swal.fire('Atención', 'Ingrese correctamente la contraseña en ambos campos', 'info')
                    
                    else if(correoNoValido($("#correoEmpleado").val().trim()))
                        Swal.fire('Atención', 'Correo no valido. Ingrese un correo diferente', 'info')

                    else if(matriculaNoValida())
                        Swal.fire('Atención', 'La matricula ingresada no pertenece a ningun empleado vigente del centro de trabajo seleccionado', 'info')

                    else if(yaExisteAdminEnCentro())
                        Swal.fire('Atención', 'Ya existe un administrador con la misma matricula que pertenece al centro de trabajo seleccionado', 'info')
                    else
                        agregarAdminBD(1); //Ya esta todo correcto. Asi que ya puedo agregar al EMPLEADO como Administrador
                }
                else{
                    //QUIERO AGREGAR A UN ASESOR
                    if($("#nombreAsesor").val().trim() == "" || $("#correoAsesor").val().trim() == "" || $("#contraAsesor1").val().trim() == "" || $("#contraAsesor2").val().trim() == "")
                        Swal.fire('Atención', 'Ingrese todos los campos requeridos para poder agregar al administrador', 'info')
                    
                    else if($("#contraAsesor1").val().trim() != $("#contraAsesor2").val().trim())
                        Swal.fire('Atención', 'Ingrese correctamente la contraseña en ambos campos', 'info')
                    
                    else if(correoNoValido($("#correoAsesor").val().trim()))
                        Swal.fire('Atención', 'Correo no valido. Ingrese un correo diferente', 'info')
                    else
                        agregarAdminBD(2); //Ya esta todo correcto. Asi que ya puedo agregar al ASESOR como Administrador
                }
            }

            function matriculaNoValida(){
                //Aqui verifico que la matricula ingresada SI exista y que pertenezca al Centro de trabajo seleccionado
                var matriculaNoExiste = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "getEmpleadoByCentroTrabajo", claveCentro: $("#selectCentrosTrabajo").val(), matricula: $("#matriculaEmpleado").val().trim()
                    }, 
                    success:function(res){
                        var datos = JSON.parse(res);

                        if(datos == null)
                            matriculaNoExiste = true; 
                        else
                            //Guardo el nombre del empleado para poder usarlo luego
                            nombreAdministrador = datos.nombreEmpleado;                            
                    }
                });
                return matriculaNoExiste;
            }

            //Funcion para agregar el nuevo admin a la BD
            function agregarAdminBD(_tipoAdmin){
                
                var _claveCentro = $("#selectCentrosTrabajo").val(); 
                var _nombreAdmin; var _correoAdmin; var _pswAdmin; var _matriculaAdmin;

                if(_tipoAdmin == 1){
                    _nombreAdmin = nombreAdministrador; 
                    _matriculaAdmin = $("#matriculaEmpleado").val().trim();
                    _correoAdmin = $("#correoEmpleado").val().trim();
                    _pswAdmin = $("#contraEmpleado1").val().trim();
                }
                else{
                    _nombreAdmin = $("#nombreAsesor").val().trim();
                    _correoAdmin = $("#correoAsesor").val().trim();
                    _pswAdmin = $("#contraAsesor1").val().trim();
                    _claveCentro = ""; _matriculaAdmin = "";
                }
                   
                /*
                console.log("NOMBRE: "+_nombreAdmin);
                console.log("MATRICULA: "+_matriculaAdmin );
                console.log("CLAVE CENTRO: "+_claveCentro );
                console.log("-----------------------");
                */

                //Ahora si ya puedo ejecutar el AJAX
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {
                        funcion: "insertarAdmin", matricula: _matriculaAdmin, nombre: _nombreAdmin,
                        claveEmp: claveEmpresa, claveCentro: _claveCentro, correo: _correoAdmin, psw: _pswAdmin
                    },
                    success:function(res){
                        if(res>0){
                            //Dependoendp del Tipo de Administrador, son los campos que se van a limpiar
                            if(_tipoAdmin == 1){
                                $("#matriculaEmpleado").val(""); $("#correoEmpleado").val(""); $("#contraEmpleado1").val(""); $("#contraEmpleado2").val("");
                            }else{
                                $("#nombreAsesor").val(""); $("#correoAsesor").val(""); $("#contraAsesor1").val(""); $("#contraAsesor2").val("");
                            }
                           
                            //Actualizo los datos de la tabla de Admins. para que se muestre el nuevo registro 
                            $('#tablaAdministradores').DataTable().ajax.reload();
  
                            //Mando mensaje de confirmacion al usuario
                            Swal.fire('', 'El administrador se ha agregado correctamente', 'success')
                        }
                        else
                            Swal.fire('', 'No se pudo agregar al administrador', 'info')
                    },
                    error:function(){
                        Swal.fire('', 'Hubo un error en la base de datos', 'error')
                    }
                });   
            }

            function correoNoValido(_correoAdmin){
                //Verifico si hay otro Admin que tenga el correo ingresado.
                var correoNoValido = false;
                
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: { funcion: "verificaCorreoRepetido_NuevoAdmin", correo: _correoAdmin },
                    success:function(res){
                        var datos = JSON.parse(res);

                        if(datos != null){
                            //Quiere decir que SI hay un Admin que ya tiene ese correo. Asi que NO se puede usar de nuevo
                            correoNoValido = true;
                        }
                    }
                });
                return correoNoValido;
            }

            function yaExisteAdminEnCentro(){
                var yaExisteAdminEnCentro = false;
                
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "yaExisteAdminEnCentro", matricula: $("#matriculaEmpleado").val().trim(), claveCentro: $("#selectCentrosTrabajo").val() },
                    success:function(res){
                        var datos = JSON.parse(res);
                        
                        if(datos != null)
                            //Quiere decir que SI hay un Centro de trabajo en esta empresa que se llama igual (sin contar a este)
                            yaExisteAdminEnCentro = true;
                    }
                });
                return yaExisteAdminEnCentro;
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
    <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
    
    <div class="container">
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                    <div class="card-header" style="background-color: #0070c0; color: white;"><b>Configuración</b> </div>
                    <div class="card-body">
                        <label style="font-size: 15px;"> <b> Cambiar contraseña </b></label><br>
                        <div class="row">
                            <div class="col-md-3">
                                <form class="form-inline">
                                    <label>Ingrese su contraseña actual:</label> 
                                    <input type="password" style="margin-top: 10px;" class="form-control" id="contraActual">
                                </form>
                            </div>

                            <div class="col-md-3">
                                <form class="form-inline">
                                    <label>Ingrese la nueva contraseña:</label>
                                    <input type="password" style="margin-top: 10px;" class="form-control"  id="contraNueva1">
                                </form>
                            </div>

                            <div class="col-md-3">
                                <form class="form-inline">
                                    <label>Repita la nueva contraseña:</label>
                                    <input type="password" style="margin-top: 10px;" class="form-control"  id="contraNueva2">
                                </form>
                            </div>

                            <div class="col-md-3">
                                <button class="btn btn-outline-secondary float-right" onclick= "cambiarPassword()" type="button">Cambiar contraseña</button>
                            </div>   
                        </div>
                    </div>
                </div><br>

                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                    <div class="card-header" style="background-color: #0070c0; color: white;"><b>Administradores</b> </div>
                    <div class="card-body">
                        <!-- Tabla de personal vigente -->
                        <table class="table table-sm" id="tablaAdministradores">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Matricula</th>
                                    <th>Nombre</th>
                                    <th>Clave centro</th>
                                    <th>Correo</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <br>

                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                    <div class="card-header" style="background-color: #0070c0;" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn btn-link" style="color: white;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <b>Agregar administrador</b>
                            </button>
                        </h5>
                    </div>

                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a href="#tabEmpleado" class="nav-link active" role="tab" data-toggle="tab"> Empleado </a>
                                </li>

                                <li class="nav-item">
                                    <a href="#tabAsesor" class="nav-link" role="tab" data-toggle="tab"> Asesor externo </a>
                                </li>
                            </ul>

                            <!-- Aqui ya comienza el codigo de lo que habra en cada una de las pestañas -->
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tabEmpleado"> <br>
                                    <!-- FILA 1 -->
                                    <div class="row">
                                        <div class="col-md-5">
                                            <form class="form-inline">
                                                <label>Centro de trabajo: </label>
                                                <select class="form-control col-lg-8" id="selectCentrosTrabajo"></select>
                                            </form>
                                        </div>

                                        <div class="col-md-3">
                                            <form class="form-inline">
                                                <label>Matricula: </label>
                                                <input type="text" id="matriculaEmpleado" class="form-control col-lg-6">
                                            </form>
                                        </div>  

                                        <div class="col-md-4">
                                            <form class="form-inline">
                                                <label>Correo:</label>
                                                <input type="text" class="form-control col-lg-10"  id="correoEmpleado">
                                            </form>
                                        </div>
                                    </div><br>

                                    <!-- FILA 2 -->
                                    <div class="row">
                                        <div class="col-md-7">
                                            <form class="form-inline">
                                                <label>Contraseña:</label>
                                                <input type="password" class="form-control col-lg-3"  id="contraEmpleado1">

                                                <label style="margin-left: 15px;">Confirme la contraseña:</label>
                                                <input type="password" class="form-control col-lg-3"  id="contraEmpleado2">
                                            </form>
                                        </div>  

                                        <div class="col-md-5">
                                            <button class="btn btn-secondary float-right" onclick= "agregarAdmin(1)" type="button">Agregar</button>
                                        </div>     
                                    </div>
                                </div>


                                <div class="tab-pane fade" id="tabAsesor"> <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <form class="form-inline">
                                                <label>Nombre:</label>
                                                <input type="text" class="form-control col-lg-10"  id="nombreAsesor">
                                            </form>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <form class="form-inline">
                                                <label>Correo:</label>
                                                <input type="text" class="form-control col-lg-10"  id="correoAsesor">
                                            </form>
                                        </div>
                                    </div><br>

                                    <div class="row">
                                        <div class="col-md-7">
                                            <form class="form-inline">
                                                <label>Contraseña:</label>
                                                <input type="password" class="form-control col-lg-3"  id="contraAsesor1">

                                                <label style="margin-left: 15px;">Confirme la contraseña:</label>
                                                <input type="password" class="form-control col-lg-3"  id="contraAsesor2">
                                            </form>
                                        </div>  

                                        <div class="col-md-5">
                                            <button class="btn btn-secondary float-right" onclick= "agregarAdmin(2)" type="button">Agregar</button>
                                        </div>   
                                          
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
        <br>  
    </div><br>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>