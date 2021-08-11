<?php
    session_start();
    //Guardo el valor de la clave de esta empresa para poder usarla en todo el archivo
    $claveEmpresa = $_SESSION['claveEmpresa'];

    //Si me estan maandando el numero de Guia por GET, entonces lo guardo en una variable de SESSION
    $numGuia = $_GET['numGuia']; //<--- Temporal <------------------------
    $claveProceso = $_GET['claveProceso']; //<--- Temporal <------------------------
    
    //Si el numero de bloque NO esta guardado en la variable de sesion, entonces el numero actual sera 1
    if($_SESSION['numBloque'] == "")
        $_SESSION['numBloque'] = 1;
    else
        $_SESSION['numBloque'] = $_GET['numBloque'];

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

         <!-- Aqui va el codigo Javascript -->
         <script type="text/javascript">
            var claveEmpresa = "<?php session_start(); echo $_SESSION['claveEmpresa'] ?>";           
            var matricula = "<?php session_start(); echo $_SESSION['matricula'] ?>";           
            var claveProceso = "<?php echo $claveProceso ?>";           
            
            //Tengo que obtener cual es la guia que se esta mostrando en pantalla
            var numGuia = "<?php echo $numGuia ?>";
            //Guardo en variable el valor del numero de bloque que se esta mostrando
            //var numBloque = "<?php echo $numBloque ?>";
            var numBloque = 1; var totalBloques; var numRespondidas = 0; //<<----------------
            var _numPreg; var _numPregValid = 1; 
            var titulos; var numBloquesSaltados = 0;

            var r1; var r2; var r3; var r4; var r5; var r6; var r7; var r8; var r9; var r10; 
            var resps = new Array(); //NOTA::: Si a la posicion de un arreglo No le asigno valor, su valor sera "undefined" 

            window.onload = function(){
                //Dependiendo del numero de Guia, es el total de bloques que tendra
                if(numGuia == 1){
                    resps.length = 20; arr2 = [6, 2, 7, 5]; totalBloques = 4; arr1 = [1, 7, 9, 16];
                }
                else if(numGuia == 2){
                    totalBloques = 8; arr2 = [9, 4, 4, 5, 5, 13, 3, 3]; resps.length = 46;
                    //arr1 = [9, 13, 17, 22, 27, 40, 43, 46];
                    arr1 = [1, 10, 14, 18, 23, 28, 41, 44];
                }
                else if(numGuia == 3){
                    //arr1 = [5, 8, 12, 16, 22, 28, 30, 36, 41, 46, 56, 64, 68, 72];
                    totalBloques = 14; arr2 = [5, 3, 4, 4, 6, 6, 2, 6, 5, 5, 10, 8, 4, 4]; resps.length = 72;
                    arr1 = [1, 6, 9, 13, 17, 23, 29, 31, 37, 42, 47, 57, 65, 69];
                }

                //NUEV0: Obtengo los titulos de cada uno de los bloques
                $.ajax({
                    type: "GET", url: "ajax.php", async : false,
                    data: {funcion: "getTitulosBloques", numGuia: numGuia },
                    success:function(res){
                        titulos = JSON.parse(res);
                        //Muestro el nombre del titulo para el bloque 1
                        $('#tituloPreguntas').html(titulos[0][1]); 
                    }
                });

                //Cuando un Input Radio cambie de valor...
                $('input:radio').change(function() {
                    //console.log("El valor de este radio es: "+ $(this).val());

                    var nombre = $(this).attr('name');
                    var id = this.id;
                    numRespondidas++;
                });

                //Dependiendo del numero de bloque actual, son las preguntas que voy a mostrar
                //Al cargar la pagina, muestro el Bloque 1 de preguntas
                $('#b1').show();
            }

            function muestraMensaje1(){
                Swal.fire({
                icon: 'question',
                text: '¿En su trabajo brinda servicio a clientes o usuarios?',
                showDenyButton: true,
                confirmButtonText: 'Si',
                denyButtonText:'No'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        console.log("DIJO QUE SI");

                        siguienteBloque();
                    }else if(result.isDenied){
                        numBloquesSaltados++; //<-----Aumento el numero de Bloque saltados
                        //Si contesto que NO, muestro entonces el mensaje 2
                        muestraMensaje2();
                        //siguienteBloque(true); //Le mando un "true" para que se brinque un bloque

                        console.log("DIJO QUE NO");
                    } 
                })
            }

            function muestraMensaje2(){
                Swal.fire({
                icon: 'question',
                text: '¿Usted es jefe de otros trabajadores?',
                showDenyButton: true,
                confirmButtonText: 'Si',
                denyButtonText:'No'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        //>>> Si dijo que SI, pero ya se habia saltado el 13/7, entonces lo debo pasar al ultimo
                        siguienteBloque();
                    }else if(result.isDenied){
                        //>>> AQUI, ya no se le mostrara el Ultimo bloque. Y hay que terminar la encuesta
                        terminarEncuesta(); 
                    } 
                })
            }

            function siguienteBloque(){
                $('#b'+numBloque).hide(); //Escondo el Bloque mostrado actualmente

                //console.log("El numero de bloque saltados es: "+ numBloquesSaltados);

                if(numBloquesSaltados > 0)
                    numBloque = numBloque+2;
                else
                    numBloque++;

                if(numBloque == (totalBloques+1))
                    terminarEncuesta(); //Ya se termina y guardan las respuesta del empleado
                else{
                    //console.log("El bloque que se mostrara es el: "+ numBloque);

                    //Muestro el siguiente Bloque de preguntas
                    $('#b'+numBloque).show(); 

                    //Hago esto para que posicione en la parte superior de la pagina
                    $('html, body').animate({scrollTop:0}, 'slow');
             
                    //Modifico el Valor del label del numero de bloque
                    document.getElementById("tituloBloque").innerHTML = "Bloque "+numBloque+" de "+totalBloques;
                    $('#tituloPreguntas').html(titulos[numBloque-1][1]); 

                    //NUEVO ***** Imprimo los valores del Array de respuestas -------------
                    //resps.forEach(function(elemento, indice, array) {
                      //  console.log(elemento, indice);
                    //})
                }
            }

            //Se manda a llamar cuando se da clic en el boton
            function continuar(){
                //NOTA: El numero de bloque que se usa AQUI es el Bloque actual mostrado antes de pasar al siguiente

                //Verifico si Todas las preguntas del bloque ya se contestaron
                if(faltanPreguntasPorResponder(numBloque))
                    Swal.fire('', 'Conteste todas las preguntas del bloque actual para poder continuar', 'info')
                else{
                    if(numGuia == 2 || numGuia == 3){
                        //Si voy a pasar al ULTIMO bloque, antes de pasar, debo mostrar el mensaje:
                        if(numBloque == (totalBloques-2))
                            muestraMensaje1();
                        else if(numBloque == (totalBloques-1))
                            muestraMensaje2();
                        else{
                            //Muestro el sig. bloque de preguntas:
                            siguienteBloque();
                        }
                    }else{
                        //Verifico si estoy en el bloque 1 y si Todas las preguntas fueron NO
                        if(numBloque == 1 && todasLasPregsFueronNo())
                            terminarEncuesta();
                        else
                            //Esto se ejecutaria para el Bloque 1. Solo continuo al Sig. bloque
                            siguienteBloque();
                    }
                }                
            }

            function anterior(){
                if(numBloque == 1)
                    //Regreso al usuario a la pagina de las guias
                    window.location.href = 'encuestas.php';

                    //NOTA: Aqui seria bueno que le pregunta al usuario si esta seguro ya que se comenzo la encuesta <<<
                else{
                    console.log("El bloque que voy A ESCONDER es el: "+numBloque);

                    //1. Escondo el bloque actual
                    $('#b'+numBloque).hide();

                    //Verifico si voy a regresar del bloque que me salte
                    if(numBloquesSaltados > 0){
                        numBloque=numBloque-2; numBloquesSaltados--;  
                    }else{
                        numBloque--;
                    }

                    console.log("El bloque que voy A MOSTRAR es el: "+numBloque);

                    $('#b'+numBloque).show(); //Muestro las preguntas del bloque cual sea el anterior al mostrado actualmente
    
                    //Hago esto para que posicione en la parte superior de la pagina 
                    $('html, body').animate({scrollTop:0}, 'slow');

                    document.getElementById("tituloBloque").innerHTML = "Bloque "+numBloque+" de "+totalBloques;
                    $('#tituloPreguntas').html(titulos[numBloque-1][1]); 
                }
            }

            //Verifica si todas las respuestas del bloque actual de la Guia 1 fueoron NO
            function todasLasPregsFueronNo(){
                var totalPregsNo = 0;
                
                for (var i = 1; i <= 6; i++) {
                    if($('input:radio[name='+i+']:checked').val() == 0)
                        //Esa pregunta aun no ha sido respondida
                        totalPregsNo++;
                }
                //Ya que recorri todas las preguntas...
                if(totalPregsNo == 6)
                    //Entonces aqui se termina la encuesta:
                    return true;
                else
                    return false;
            }

            function faltanPreguntasPorResponder(_numBloque){
                var totalPregsBloque = arr2[_numBloque-1];
                var _numPregValid = arr1[_numBloque-1]; //Obtengo el Numero de la Primer pregunta del bloque que voy a validar

                console.log("El total de pregs. del bloque a evaluar es: "+ totalPregsBloque);

                var totalPregsSinResp = 0;

                for (var i = 1; i <= totalPregsBloque; i++) {
                    if($('input:radio[name='+_numPregValid+']:checked').val() == undefined){
                        //Esa pregunta aun no ha sido respondida
                        totalPregsSinResp++;
                    }else{
                        //**** NUEVO *****
                        //Si la pregunta YA fue respondida, guardo la respuesta en el Array de respuestas en la Posicion donde corresponde
                        resps[_numPregValid-1] = $('input:radio[name='+_numPregValid+']:checked').val();
                    }
                    _numPregValid++; //Aumento su valor para que valida la sig. pregunta
                }

                if(totalPregsSinResp > 0){
                    console.log("Total de pregs sin responder: "+ totalPregsSinResp);

                    //Hago lo sig. para restarle el numero de las que se validaron
                    //_numPregValid = _numPregValid - totalPregsBloque;
                    return true;
                }
                else    
                    return false;
            }

            function terminarEncuesta(){
                //Inabilito el boton de "Continuar"
                $('#btnContinuar').attr("disabled", true);

                //console.log('AQUI EL USUARIO YA TERMINO LA ENCUESTA');

                //En este momento ya TODAS las preguntas estan respondidas y tienen valores
                //Al AJAX le voy a pasar ya sean 42 respuestas si es la Guia 2 o 72 resps. si es a Guia 3
                if(numGuia == 1){
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "guardaRespsGuia", claveEmpresa: claveEmpresa, claveProceso: claveProceso, matricula: matricula, p21: resps[20], numGuia: numGuia, p20: resps[19],
                            p1: resps[0], p2: resps[1], p3: resps[2], p4: resps[3], p5: resps[4], p6: resps[5], p7: resps[6], p8: resps[7], p9: resps[8], p10: resps[9],
                            p11: resps[10], p12: resps[11], p13: resps[12], p14: resps[13], p15: resps[14], p16: resps[15], p17: resps[16], p18: resps[17], p19: resps[18]
                        },
                        success:function(res){
                            //Si si se pudo...
                            //console.log("SI SE PUDIERON INSERTAR TODAS LAS RESPS. DE LA GUIA 2");
                        }
                    });

                    //Ya que se guardaron TODOS los bloques de respuestas, mando mensaje de que ya se guardo
                    $('#divEncuesta').hide();
                    $('#divTerminoEncuesta').show();

                }else if(numGuia == 2){
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "guardaRespsGuia", claveEmpresa: claveEmpresa, claveProceso: claveProceso, matricula: matricula, numGuia: numGuia,
                            p1: resps[0], p2: resps[1], p3: resps[2], p4: resps[3], p5: resps[4], p6: resps[5], p7: resps[6], p8: resps[7], p9: resps[8], p10: resps[9],
                            p11: resps[10], p12: resps[11], p13: resps[12], p14: resps[13], p15: resps[14], p16: resps[15], p17: resps[16], p18: resps[17], p19: resps[18], p20: resps[19],
                            p21: resps[20], p22: resps[21], p23: resps[22], p24: resps[23], p25: resps[24], p26: resps[25], p27: resps[26], p28: resps[27], p29: resps[28], p30: resps[29],
                            p31: resps[30], p32: resps[31], p33: resps[32], p34: resps[33], p35: resps[34], p36: resps[35], p37: resps[36], p38: resps[37], p39: resps[38], p40: resps[39],
                            p41: resps[40], p42: resps[41], p43: resps[42], p44: resps[43], p45: resps[44], p46: resps[45], p47: resps[46]
                        },
                        success:function(res){
                            //Si si se pudo...
                            //console.log("SI SE PUDIERON INSERTAR TODAS LAS RESPS. DE LA GUIA 2");
                        }
                    });

                    //Ya que se guardaron TODOS los bloques de respuestas, mando mensaje de que ya se guardo
                    $('#divEncuesta').hide();
                    $('#divTerminoEncuesta').show();

                }else if(numGuia == 3){
                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "guardaRespsGuia", claveEmpresa: claveEmpresa, claveProceso: claveProceso, matricula: matricula, p71: resps[70], p72: resps[71], numGuia: numGuia,
                            p1: resps[0], p2: resps[1], p3: resps[2], p4: resps[3], p5: resps[4], p6: resps[5], p7: resps[6], p8: resps[7], p9: resps[8], p10: resps[9],
                            p11: resps[10], p12: resps[11], p13: resps[12], p14: resps[13], p15: resps[14], p16: resps[15], p17: resps[16], p18: resps[17], p19: resps[18], p20: resps[19],
                            p21: resps[20], p22: resps[21], p23: resps[22], p24: resps[23], p25: resps[24], p26: resps[25], p27: resps[26], p28: resps[27], p29: resps[28], p30: resps[29],
                            p31: resps[30], p32: resps[31], p33: resps[32], p34: resps[33], p35: resps[34], p36: resps[35], p37: resps[36], p38: resps[37], p39: resps[38], p40: resps[39],
                            p41: resps[40], p42: resps[41], p43: resps[42], p44: resps[43], p45: resps[44], p46: resps[45], p47: resps[46], p48: resps[47], p49: resps[48], p50: resps[49],
                            p51: resps[50], p52: resps[51], p53: resps[52], p54: resps[53], p55: resps[54], p56: resps[55], p57: resps[56], p58: resps[57], p59: resps[58], p60: resps[59],
                            p61: resps[60], p62: resps[61], p63: resps[62], p64: resps[63], p65: resps[64], p66: resps[65], p67: resps[66], p68: resps[67], p69: resps[68], p70: resps[69],
                        },
                        success:function(res){
                            //Si si se pudo...
                            //console.log("SI SE PUDIERON INSERTAR TODAS LAS RESPS. DE LA GUIA 3");
                        }
                    });

                    //Ya que se guardaron TODOS los bloques de respuestas, mando mensaje de que ya se guardo
                    $('#divEncuesta').hide();
                    $('#divTerminoEncuesta').show();
                }
            }

            function yaNoSeUsa(){
                for (var i = 1; i <= totalBloques; i++) {
                    //Antes de usar el AJAX, debo de asignar los valores a las variables de las respuestas
                    asignaValoresResps(i);

                    //>>NOTA: Aqui se debe considerar que que hay ciertos bloques que NO se reponsideron y por lo tanto No deben ser almacenados

                    $.ajax({
                        type: "GET", url: "ajax.php", async : false,
                        data: {
                            funcion: "guardaRespsBloque", claveProceso: 1234, matricula: 155725, numGuia: numGuia, bloque: i,
                            r1: $("#r1").val(), r2: $("#r2").val(), r3: $("#r3").val(), r4: $("#r4").val(), r5: $("#r5").val(), 
                            r6: $("#r6").val(), r7: $("#r7").val(), r8: $("#r8").val(), r9: $("#r9").val(), r10: $("#r10").val()
                        },
                        success:function(res){
                            //Si si se pudo...
                            console.log("Se insertaron las respuestas del bloque: "+i);
                        }
                    });

                    //Ya que se guardaron TODOS los bloques de respuestas, mando mensaje de que ya se guardo
                    $('#divEncuesta').hide();
                    $('#divTerminoEncuesta').show();
                }
            }

            //Por cada uno de los Bloques se va a llamar a esta funcion
            function asignaValoresResps(_numBloque){
                //Le asigno un valor vacio a Todos los hidden input
                for (var j = 1; j <= 10; j++) {
                    $("#r"+j).val("");
                }

                if(_numBloque == 1)
                    _numPreg = 1;

                var totalPregsBloque = arr2[_numBloque-1];

                for (var i = 1; i <= totalPregsBloque; i++) {
                    $("#r"+i).val($('input:radio[name='+_numPreg+']:checked').val());
                    /*
                        1 al 5      1, 2, 3, 4, 5
                        1 al 3      6, 7, 8
                        1 al 4      9, 10, 11, 12
                        1 al 4      13, 14, 15, 16
                    */
                    _numPreg++;
                }

            }

            function volverMenu(){
                window.location.href = 'encuestas.php';
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
                    <div class="card-body" style="background-color: #DFDFDF;">
                        <?php
                        //Dependiendo del numero de Guia, es el titulo que voy a imprimir
                        if($numGuia == 1)
                            echo '<h5> Guia I: Cuestionario para identificar a los trabajadores que fueron sujetos a eventos traumáticos severos </h5>';
                        else if($numGuia == 2)
                            echo '<h5> Guia II: Identificación y análisis de los factores de riesgo psicosocial en los centros de trabajo</h4>';
                        else if($numGuia == 3)
                            echo '<h5> Guia III: Identificación y análisis de los factores de riesgo psicosocial y evaluación del entorno organizacional en los centros de trabajo</h4>';

                        ?>
                    </div>
                </div> <br>
            </div>
        </div>

        <div class="row" id="divEncuesta" >
            <div class="col-md-12">
                <?php
                require('cn.php');
                $preguntas = $mysqli->query("select * from preguntas where numGuia = ".$numGuia);
                $totalPregs = mysqli_num_rows($preguntas);
                
                if($numGuia == 1)
                    $totalBloques = 4;
                else if($numGuia == 2)
                    $totalBloques = 8;
                else if($numGuia == 3)
                    $totalBloques = 14;

                echo '<h5 id="tituloBloque"> Bloque 1 de '. $totalBloques .'</h5>';
                echo '<h6 id="tituloPreguntas"> _ </h6> <br>';

                $bloque = 0;
                while($row = $preguntas->fetch_assoc()){
                    //Aqui debo de imprimir el 
                    if($bloque != $row[bloque]){
                        if($row[bloque] > 1){
                            //echo '<input type="hidden" id="cierra_b'.$bloque.'">';
                            echo '</div>';
                        }
                          
                        //echo '<input type="hidden" id="b'.$row[bloque].'">';
                        echo '<div id="b'.$row[bloque].'" style="display: none;">';
                        $bloque = $row[bloque];
                    }

                    echo'<div class="card" id="p'.$row[numPreg].'" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); margin-bottom: 25px; ">
                            <div class="card-header" style="background-color: #0070c0; color: white;"><b>'.$row[numPreg].'. '.$row[pregunta].'</b> </div>
                            <div class="card-body">
                                <div class="row">';

                    //Si es la Guia 1, muestro cierto tipo de respuestas
                    if($numGuia == 1){
                        echo'<div class="col-md-2">
                                <input type="radio" id="r1p'.$row[numPreg].'" name="'.$row[numPreg].'" value="1" > Si <br>
                            </div>
                            <div class="col-md-2">
                                <input type="radio" id="r2p'.$row[numPreg].'" name="'.$row[numPreg].'" value="0" > No <br>
                            </div>';
                    }else{
                        echo'<div class="col-md-2">
                                <input type="radio" id="r1p'.$row[numPreg].'" name="'.$row[numPreg].'" value="'.$row[respuA].'" > Siempre <br>
                            </div>
                            <div class="col-md-2">
                                <input type="radio" id="r2p'.$row[numPreg].'" name="'.$row[numPreg].'" value="'.$row[respuB].'" > Casi siempre <br>
                            </div>
                            <div class="col-md-2">
                                <input type="radio" id="r3p'.$row[numPreg].'" name="'.$row[numPreg].'" value="'.$row[respuC].'" > Algunas veces <br>
                            </div>
                            <div class="col-md-2">
                                <input type="radio" id="r4p'.$row[numPreg].'" name="'.$row[numPreg].'" value="'.$row[respuD].'" > Casi nunca <br>
                            </div>
                            <div class="col-md-2">
                                <input type="radio" id="r5p'.$row[numPreg].'" name="'.$row[numPreg].'" value="'.$row[respuE].'" > Nunca <br>
                            </div>';
                    }
                              
                    echo '      </div>
                            </div>
                        </div> ';

                    if($row[numPreg] == $totalPregs)
                      echo '</div>';
                      //echo '<input type="hidden" id="cierra_b'.$bloque.'">';
                }
                ?>

                <button type="button" id="btnContinuar" class="btn btn-space btn-success float-right" onclick="continuar()">Continuar</button>
                <button type="button" id="btnAnterior" class="btn btn-space btn-outline-secondary float-right" onclick="anterior()">Anterior</button>
            </div>
        </div>

        <div class="row" id="divTerminoEncuesta" style="display: none;">
            <div class="col-md-6">
                <div class="card" style="box-shadow: 0 2px 4px 0 rgba(0,0,0,.2);">
                    <div class="card-header" style="background-color: #0070c0; color: white;"> <b> ¡Felicidades! </b> </div>
                    <div class="card-body">
                        <label> Sus respuestas han sido guardadas correctamente. </label><br><br>
                        <button type="button" class="btn btn-space btn-success" onclick="volverMenu()">Volver al menu</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estos Input me serviran para almacenar las respuestas -->
        <input type="hidden" id="r1">
        <input type="hidden" id="r2">
        <input type="hidden" id="r3">
        <input type="hidden" id="r4">
        <input type="hidden" id="r5">
        <input type="hidden" id="r6">
        <input type="hidden" id="r7">
        <input type="hidden" id="r8">
        <input type="hidden" id="r9">
        <input type="hidden" id="r10">

        <br>  

    </div><br>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

  </body>
</html>