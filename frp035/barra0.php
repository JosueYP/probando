<nav class="navbar navbar-expand-lg navbar-light sticky-top" style=" box-shadow: 0 2px 4px 0 rgba(0,0,0,.2); background-color: white;">
    <a class="navbar-brand mb-0 h1" href="#"><img src="logoNuevo.png" width="140" class="d-inline-block align-top" alt=""> </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      
      <ul class="navbar-nav">
        <!-- OPCION 4 -->
        <li class="nav-item" style="margin-right: 15px;">
        <a class="nav-link" href="menu.php"> <b>Inicio </b></a>
      </li>
      </ul>

      <ul class="navbar-nav">
        <!-- OPCION 4 -->
        <li class="nav-item" style="margin-right: 15px;">
          <a class="nav-link" href="encuestas.php"> <b>Realizar encuestas </b></a>
        </li>
      </ul>

      <ul class="nav navbar-nav ml-auto">
        <!-- Dependiendo de si ya inicio sesion o no, se mostrara un texto u otro -->
        <button id="btnCerrarSesion" onclick="cerrarSesion()" class="btn btn-outline-secondary navbar-btn btn-sm" type="button"> Cerrar sesión </button>
      </ul>
      
    </div>
</nav>