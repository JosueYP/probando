<?php
    session_start();

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Esto es para poder usar Datatables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <!-- Esto es para poder usar Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
        <!-- Esto es para poder usar JQuery -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <!-- Esto es para poder usar Sweetalert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel=stylesheet type="text/css" href="estilos.css">

        <!-- Aqui va el codigo Javascript -->
        <script type="text/javascript">
   
        function login(){

          //1. detectar si es un empleado o admin el que quiere ingresar al darle click en el boton
          //2. si es un empleado buscar si la clave del centro de trabajo ingresada existe y si esta activa
          //3. si clave de centro de trabajo existe, verificar que la matricula ingresada pertenezca a un empleado
          //  vigente de ese centro de trabajo.
          //4. si ambos campos en la pestaña de empleado son correctos, luego de haberlo verificado, se mandara al empleado
          //   a una pagina especial
          //5. si es un admin que quiere ingresar, se verificara si el correo se encuentra registrado en la tabla de administradores
          //6. si el correo ingresado existe verificar si la contraseña ingresada es del correo. ya que ambos campos se verificaron, 
          // guardar la clave de empresa que pertenece a ese admnistrador (mediante una varible de sesion) y mandarlo a la pagina de menu

          if($("#claveCentro").val() == ""){
            //Quiere decir que el admin intento iniciar sesion
            console.log("Soy admin");
          } else{
            //Quiere decir que el empleado intento iniciar sesion
            console.log("Soy empleado");
            if(empleadoExistente())
              Swal.fire('', 'Empleado existente', 'success')
            else 
              Swal.fire('', 'Empleado no existe', 'error')

          }

        } 

        function limpiaInput(elemento){
          if(elemento == "claveCentro"){
            //Borro lo que hay en admin 
            $("#correo").val(""); 
          }else if(elemento == "correo"){
            $("#claveCentro").val("");
          }
        }

        function empleadoExistente(){
            var empleadoExistente = false;
            $.ajax({
                type: "GET", url: "ajax.php", async : false,
                data: {
                    funcion: "getEmpleadoExistente", 
                    claveCentro: $("#claveCentro").val(), 
                    matricula: $("#matricula").val()
                }, 
                success:function(res){
                    var datos = JSON.parse(res);

                    console.log(datos);
                    //console.log(res);
                    //if(res != null)
                      //  empleadoExistente = true; 
                }
            });
            return empleadoExistente;
        }

       /*     function claveCentroExistente(){
                var claveCentroExistente = false;
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getclaveCentroExistente", claveCentro: $("#claveCentro").val()}, 
                    success:function(res){
                        //si el resultado devuelto, osea la clave del centro, es diferente de vacio
                        //entonces
                        if(res != "")
                            //Quiere decir que SI hay un centro con esa clave
                            claveCentroExistente = true; 
                    }
                });
                return claveCentroExistente;
            } */        

        </script>
    </head>

    <body style="background-color: #f1f3f7;">

         <nav class="navbar navbar-expand-lg navbar-light bg-light" style=" box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); ">
            <a class="navbar-brand mb-0 h1" href="#"><img src="logo.png" width="140" class="d-inline-block align-top" alt=""> </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
              <ul class="nav navbar-nav ml-auto">
                <button id="btnAccion" class="btn btn-outline-secondary navbar-btn" type="button" data-bs-toggle="modal" data-bs-target="#iniciarSesion">Iniciar sesión</button>
              </ul>
            </div>
        </nav>
        
        <!-- MODAL INICIAR SESION -->
        <div class="modal fade" id="iniciarSesion" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 id="btnSesion"  class="modal-title"  onclick="sesion()">Iniciar sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- MODAL BODY -->
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
                            <label for="exampleFormControlInput1" class="form-label">Ingrese clave de centro de trabajo:</label>
                            <input id="claveCentro" type="text" class="form-control" onclick="limpiaInput(this.id)" >
                            <small id="errorCentro" style="color: red;"></small><br>
                        </div>
                        <div class="mb-3">
                            <label for="exampleFormControlTextarea1" class="form-label">Ingrese su matricula:</label>
                            <input id="matricula" type="text" class="form-control" >
                            <small id="errorMatricula" style="color: red;" ></small><br><br>
                        </div>
                    </div>
                    <!-- CONTENIDO 2 -->
                    <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="tabAdmin">
                    <br>
                        <div class="mb-3">
                            <label for="exampleFormControlInput1" class="form-label">Ingrese su correo:</label>
                            <input id="correo" type="email" class="form-control" onclick="limpiaInput(this.id)" >
                        </div>
                        <div class="mb-3">
                            <label for="exampleFormControlTextarea1" class="form-label">Ingrese su contraseña:</label>
                            <input id="contraseña" type="text" class="form-control" >
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" onclick="login()" class="btn btn-primary">Ingresar</button>
            </div>
            </div>
        </div>
        </div>

        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>

        <!-- Esto es para... varias cosas -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    </body>
</html>