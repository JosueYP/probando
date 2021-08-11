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
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <link rel="shortcut icon" href="favicon.png">


         <!-- Aqui va el codigo Javascript -->
         <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";
           
            

        </script>
  </head>

  <body style="background-color: #f1f3f7;">
    <!-- Incluyo en la pagina la barra superior -->
    <?php session_start(); include ('barra'.$_SESSION['rolUsuario'].'.php'); ?>
    
    <div class="container">
        <br>
        <div class="row">
            <div class="col-md-4">
                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 250px">
                    <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Guia 1 </b> </div>
                    <div class="card-body">
                        Cuestionario para identificar a los trabajadores que fueron sujetos a eventos traumaticos severos

                        <br><br><a href="instrucciones.php?" class="btn btn-secondary">Realizar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); height: 250px">
                    <div class="card-header" style="background-color: #0070c0; color: white;" > <b> Guia 2 </b> </div>
                    <div class="card-body">
                        Cuestionario para identificar los factores de riesgos psicosocial en los centros de trabajo

                        <br><br><a href="instrucciones.php" class="btn btn-secondary">Realizar</a>
                    </div>
                </div>
            </div>
        </div>
        <br>  

    </div><br>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>