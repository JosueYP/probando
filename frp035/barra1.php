
<nav class="navbar navbar-expand-lg navbar-light sticky-top" style=" box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); background-color: white;">
    <a class="navbar-brand mb-0 h1" href="index.php"><img src="frp035.png" width="140" class="d-inline-block align-top" alt=""> </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item" style="margin-right: 12px;">
          <a class="nav-link" href="menu.php" style=" font-size: 15px;"> <b>Inicio </b></a>
        </li>

        <!-- OPCION 1 -->
        <div id="tabCatalogos" >
          <li class="nav-item dropdown" style="margin-right: 12px;"> <!-- aqui va "active" -->
            <a class="nav-link dropdown-toggle" style=" font-size: 15px;" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <b> Catálogos </b>
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
              <a class="dropdown-item" href="centroTrabajo.php">Centros de trabajo</a>
              <a class="dropdown-item" href="departamentos.php">Departamentos</a>
              <a class="dropdown-item" href="empleadosVig.php">Personal vigente</a>

              <!-- 
              <a class="dropdown-item" href="empleadosBaja.php">Personal dado de baja</a> -->
            </div>
          </li>
        </div>
        
        <!-- OPCION 2 -->
        <div id="tabReportes" >
          <li class="nav-item" style="margin-right: 12px;" id="tabReportes">
            <a class="nav-link" href="reportes.php" style=" font-size: 15px;"> <b>Reportes </b></a>
          </li>
        </div>
        
        <!-- OPCION 3 -->
        <div id="tabProcsEncuestas" >
          <li class="nav-item" style="margin-right: 12px;">
            <a class="nav-link" href="procesos-encuestas.php" style=" font-size: 15px;"> <b>Procesos de encuestas </b></a>
          </li>
        </div>

        <!-- OPCION NUEVA -->
        <li class="nav-item dropdown" style="margin-right: 12px;"> <!-- aqui va "active" -->
          <a class="nav-link dropdown-toggle" style=" font-size: 15px;" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <b> Análisis gráfico </b>
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="analisis-resultados.php">Análisis de resultados</a>
            <a class="dropdown-item" href="analisis-respuestas.php">Análisis de respuestas</a>
            <a class="dropdown-item" href="comparacion-resultados.php">Comparación de resultados</a>
          </div>
        </li>

        <!-- OPCION 4 -->
        <li class="nav-item" style="margin-right: 12px;">
          <a class="nav-link" href="encuestas.php" style=" font-size: 15px;"> <b>Realizar encuestas </b></a>
        </li>

        <!-- OPCION 5 -->
        <div id="tabConfiguracion" >
          <li class="nav-item" style="margin-right: 12px;">
            <a class="nav-link" href="configuracion.php" style=" font-size: 15px;"> <b>Configuración </b></a>
          </li>
        </div>
        
      </ul>

      <ul class="nav navbar-nav ml-auto">
        <!-- Dependiendo de si ya inicio sesion o no, se mostrara un texto u otro -->
        <button id="btnCerrarSesion" onclick="cerrarSesion()" style="" class="btn btn-outline-secondary navbar-btn btn-sm" type="button"> Cerrar sesión </button>
      </ul>
      
    </div>
</nav>