<?php
require('cn.php'); require('reportes/nivelesRiesgo.php');
$funcion = $_GET['funcion'];

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado

//En función del parámetro que nos llegue ejecutamos una función u otra
switch ($funcion) {
	case 'getPersonalVigByCentro':
		$consulta = "SELECT claveDepto, matricula, nombreEmpleado, correo from empleados where status = 1 and claveCentro like ".$_GET['claveCentro'];

		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	case 'getCentrosTrabajo':
		$claveEmpresa = $_GET['claveEmpresa'];

		$consulta = "select idCentro, claveCentro, nombreCentro, ubicacion, 
					(select count(*) from empleados where status = 1 and claveCentro like c.claveCentro) as numEmpsVigs, 
					(select count(*) from empleados where status = 0 and claveCentro like c.claveCentro) as numEmpsBaja 
					from centrostrabajo as c where status = 1 and claveEmp = ". $claveEmpresa;

		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	case 'getAdministradores':
		$claveEmpresa = $_GET['claveEmpresa'];

		$consulta = "select idUsuario, matricula, nombre, claveEmp, claveCentro, correo from usuarios where claveEmp = ".$claveEmpresa;
		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	case 'getNumDeptos':
		$consulta = "SELECT * FROM centrotrabajo";
		$resultado = $mysqli->query($consulta);

		echo $resultado->num_rows;
		break;

	case 'getNumDeptos':
		$consulta = "SELECT * FROM centrotrabajo";
		$resultado = $mysqli->query($consulta);

		echo $resultado->num_rows;
		break;

	case 'getDepartamentos':
		$claveCentro = $_GET['claveCentro'];
		$consulta = "select claveDepto, nombreDepto, (select count(*) from empleados 
													 where status = 1 and claveDepto like d.claveDepto and claveCentro like '".$claveCentro."') as empleadosvig
						from deptos as d
						where status = 1 and claveCentro like '" . $claveCentro."'";
		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	//NUEVO ********
	case 'getListaAsistentes':
		$consulta = "SELECT idEmp, claveDepto, matricula, fecha,
					(select nombreEmpleado from empleados where matricula like l.matricula and claveCentro like l.claveCentro limit 1) as nombre
					FROM listaencuestados as l
					where claveProceso = ".$_GET['claveProceso']." and numGuia = ".$_GET['numGuia'];

		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	case 'getEncuestas':
		$claveCentro = $_GET['claveCentro'];
		$consulta = "select idProceso, nombreProceso, fechaCreacion, guia2, guia3, 
						(select count( distinct matricula) from r_".$_GET['claveEmpresa']." where numGuia = 1 and claveProceso = p.claveProceso) as numEncG1, 
						(select count( distinct matricula) from r_".$_GET['claveEmpresa']." where numGuia = 2 and claveProceso = p.claveProceso) as numEncG2, 
						(select count( distinct matricula) from r_".$_GET['claveEmpresa']." where numGuia = 3 and claveProceso = p.claveProceso) as numEncG3, status 
					from procesosencuestas as p where claveCentro like '" . $claveCentro ."'";
					
		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	case 'getDatosCentroByClave':
		$claveCentro = $_GET['claveCentro'];

		$sql = "SELECT * FROM centrotrabajo WHERE claveCentro like ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		//1. Le paso a la sentencia el valor de cada uno de los parametros
		//i" es para enteros
		//"s" es para strings
		mysqli_stmt_bind_param($stmt, 's', $claveCentro);


		//2. Ejecuto la query
		mysqli_stmt_execute($stmt);

		//3. Guardo el resultado en una variable
		$result = mysqli_stmt_get_result($stmt);

		//4. Guardo el resultado en una "fila" para poder acceder a los datos
		$row = mysqli_fetch_assoc($result);

		echo json_encode($row);
		break;

	case 'getDatosCentroByNombre':
		$nombreCentro = $_GET['nombreCentro'];

		$sql = "SELECT * FROM centrotrabajo WHERE nombreCentro = ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		//1. Le paso a la sentencia el valor de cada uno de los parametros
		mysqli_stmt_bind_param($stmt, 's', $nombreCentro);

		//2. Ejecuto la query
		mysqli_stmt_execute($stmt);

		//3. Guardo el resultado en una variable
		$result = mysqli_stmt_get_result($stmt);

		//4. Guardo el resultado en una "fila" para poder acceder a los datos
		$row = mysqli_fetch_assoc($result);

		echo json_encode($row);
		break;
	
	case 'getDatosDeptosByClave':
		$claveCentro = $_GET['claveCentro']; $claveDepto = $_GET['claveDepto'];

		$sql = "SELECT * FROM deptos WHERE claveDepto like ? and claveCentro like ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ss', $claveDepto, $claveCentro);

		//2. Ejecuto la query
		mysqli_stmt_execute($stmt);

		//3. Guardo el resultado en una variable
		$result = mysqli_stmt_get_result($stmt);

		//4. Guardo el resultado en una "fila" para poder acceder a los datos
		$row = mysqli_fetch_assoc($result);

		echo json_encode($row);
		break;

	case 'getDatosDeptoByNombre':
		$claveCentro = $_GET['claveCentro']; $nombreDepto = $_GET['nombreDepto'];

		$sql = "SELECT * FROM deptos WHERE nombreDepto like ? and claveCentro like ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ss', $nombreDepto, $claveCentro);

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		echo json_encode($row);
		break;

	case 'getDatosEmpleadosByMatricula_claveCentro':
		$matricula = $_GET['matricula']; $claveCentro = $_GET['claveCentro'];

		$sql = "SELECT * FROM empleados WHERE matricula like ? and claveCentro like ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		//1. Le paso a la sentencia el valor de cada uno de los parametros
		//i" es para enteros
		//"s" es para strings
		mysqli_stmt_bind_param($stmt, 'ss', $matricula, $claveCentro);

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		echo json_encode($row);
		break;

	case 'getDatosEmpleadosByCorreo':
		$correo = $_GET['correo'];

		$sql = "SELECT * FROM empleados WHERE correo = ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		//1. Le paso a la sentencia el valor de cada uno de los parametros
		//i" es para enteros
		//"s" es para strings
		mysqli_stmt_bind_param($stmt, 's', $correo);

		//2. Ejecuto la query
		mysqli_stmt_execute($stmt);

		//3. Guardo el resultado en una variable
		$result = mysqli_stmt_get_result($stmt);

		//4. Guardo el resultado en una "fila" para poder acceder a los datos
		$row = mysqli_fetch_assoc($result);

		echo json_encode($row);
		break;

	case 'insertarCentroTrabajo':
		$claveCentro = $_GET['claveCentro'];
		$claveEmp = $_GET['claveEmp'];
		$nombreCentro = $_GET['nombreCentro'];
		$ubicacion = $_GET['ubicacion'];

		$sql = "insert into centrotrabajo (claveCentro, claveEmp, nombreCentro, ubicacion, status) values (?, ?, ?, ?, 1)";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'iiss', $claveCentro, $claveEmp, $nombreCentro, $ubicacion);

		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo mysqli_stmt_affected_rows($stmt);
		break;

	case 'insertarDepto':
		$claveCentro = $_GET['claveCentro'];
		$claveDepto = $_GET['claveDepto'];
		$nombreDepto = $_GET['nombreDepto'];

		$sql = "insert into deptos (claveCentro, claveDepto, nombreDepto, status) values (?, ?, ?, 1)";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'iss', $claveCentro, $claveDepto, $nombreDepto);

		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo mysqli_stmt_affected_rows($stmt);
		break;

	case 'insertarEmpleados':

		$sql = "insert into empleados (claveDepto,matricula,nombreEmpleado,claveCentro,status) values(?,?,?,?,1);";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'sssi', $_GET['claveDepto'], $_GET['matricula'], $_GET['nombre'], $_GET['claveCentro']);

		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo mysqli_stmt_affected_rows($stmt);
		break;

	case 'insertarAdmin':

		if($_GET['matricula'] == "")
			$matricula = NULL;
		else
			$matricula = $_GET['matricula'];

		if($_GET['claveCentro'] == "")
			$claveCentro = NULL;
		else
			$claveCentro = $_GET['claveCentro'];

		$sql = "insert into usuarios (matricula,nombre,claveEmp,claveCentro, correo, psw) values(?,?,?,?,?,?);";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ssiiss', $matricula, $_GET['nombre'], $_GET['claveEmp'], $claveCentro, $_GET['correo'], $_GET['psw']);

		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo mysqli_stmt_affected_rows($stmt);
		break;

	//============== REVISADAS ===============

	case 'getclaveCentroExistente':
		$claveCentro = $_GET['claveCentro'];

		$sql = "select claveCentro, claveEmp, nombreCentro, (select nombreEmp from empresas where claveEmp = c.claveEmp limit 1) as nombreEmpresa
				from centrostrabajo as c where status = 1 and claveCentro like ?";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $claveCentro);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		echo json_encode($row);		
		break;

	case 'getEmpleadoByCentroTrabajo':
		$claveCentro = $_GET['claveCentro'];
		$matricula = $_GET['matricula'];

		$sql = "select * from empleados where status = 1 and claveCentro like ? and matricula like ?";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ss', $claveCentro, $matricula);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		echo json_encode($row);
		break;

	case 'getDatosAdminByCorreo':
		$correo = $_GET['correo'];

		$sql = "select idUsuario, matricula, nombre, claveEmp, (select nombreEmp from empresas where claveEmp = u.claveEmp limit 1) as nombreEmpresa, 
				claveCentro, correo, psw from usuarios as u where correo = ? limit 1";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $correo);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		echo json_encode($row);
		break;

	case 'yaExisteAdminEnCentro':
		
		$sql = "select * from usuarios where matricula like ? and claveCentro like ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ss', $_GET['matricula'], $_GET['claveCentro']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		echo json_encode($row);
		break;

	case 'getDatosAdminByIdUsuario':
		$sql = "select * from usuarios where idUsuario = ?";
		
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'i', $_GET['idUsuario']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		echo json_encode($row);
		break;

	case 'guardaRespsBloque':
		$r1; $r2; $r3; $r4;$r5; $r6; $r7; $r8; $r9;$r10; 

		//Valido si alguna de las respuestas viene vacia
		for ($i = 1; $i <= 10; $i++) {
			if($_GET['r'.$i] == "")
				${"r".$i} = NULL;
			else
				${"r".$i} = $_GET['r'.$i];
		}

		$sql = "insert into bloquesrespuestas (claveProceso, matricula, numGuia, bloque, r1, r2, r3, r4, r5, r6, r7, r8, r9, r10)  
				values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'isiiiiiiiiiiii', $_GET['claveProceso'], $_GET['matricula'], $_GET['numGuia'], $_GET['bloque'],
				$_GET['r1'], $_GET['r2'], $_GET['r3'], $_GET['r4'], $_GET['r5'], $_GET['r6'], $_GET['r7'], $_GET['r8'], $_GET['r9'], $_GET['r10']);

		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo mysqli_stmt_affected_rows($stmt);
		break;

	case 'guardaRespsGuia':
		$numGuia = $_GET['numGuia']; $numPreg = 1;
		$fechaHoy = date("Y")."-".date("m")."-".date("d");

		if($numGuia == 1){
			$numPregsPorBloque = array(6,2,7,5); $totalBloques = 4;
		}
		else if($numGuia == 2){
			$numPregsPorBloque = array(9, 4, 4, 5, 5, 13, 3, 3); $totalBloques = 8;
		}
		else if($numGuia == 3){
			$numPregsPorBloque = array(5, 3, 4, 4, 6, 6, 2, 6, 5, 5, 10, 8, 4, 4);
			$totalBloques = 14;
		}
			
		//Hago un ciclo FOR que va a dar 72 vueltas
		for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {

			for ($i = 1; $i <= $numPregsPorBloque[$bloque - 1]; $i++) {
				//Verifico si la pregunta SI fue contestada por el usuario
				if($_GET['p' . $numPreg] != ""){
					$sql = "insert into r_".$_GET['claveEmpresa']." (numPreg, matricula, respu, fecha, numGuia, bloque, claveProceso) values (?,?,?,?,?,?,?)";
					$stmt = mysqli_stmt_init($mysqli);
					mysqli_stmt_prepare($stmt, $sql);
					mysqli_stmt_bind_param($stmt, 'isisiii', $numPreg, $_GET['matricula'], $_GET['p' . $numPreg], $fechaHoy, $numGuia, $bloque, $_GET['claveProceso']);
					mysqli_stmt_execute($stmt);
				}
				$numPreg++; //Aumento el valor del numero de pregunta
			}
		}

		/*
		$sql = "insert into respsguia3 (claveProceso, matricula, p1, p2, p3, p4, p5, p6, p7, p8, p9, p10, p11, p12, p13, p14, p15, p16, p17, p18, p19, p20,
				p21, p22, p23, p24, p25, p26, p27, p28, p29, p30, p31, p32, p33, p34, p35, p36, p37, p38, p39, p40, p41, p42, p43, p44, p45, p46, p47, p48, p49, p50,
				p51, p52, p53, p54, p55, p56, p57, p58, p59, p60, p61, p62, p63, p64, p65, p66, p67, p68, p69, p70, p71, p72)  
				values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'isiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii', $_GET['claveProceso'], $_GET['matricula'],
				$p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10, $p11, $p12, $p13, $p14, $p15, $p16, $p17, $p18, $p19, $p20, $p21, $p22, $p23, $p24, $p25, $p26, $p27, $p28, $p29, $p30, 
				$p31, $p32, $p33, $p34, $p35, $p36, $p37, $p38, $p39, $p40, $p41, $p42, $p43, $p44, $p45, $p46, $p47, $p48, $p49, $p50, $p51, $p52, $p53, $p54, $p55, $p56, $p57, $p58, $p59, $p60, 
				$p61, $p62, $p63, $p64, $p65, $p66, $p67, $p68, $p69, $p70, $p71, $p72);
		*/

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo "1";
		break;

	case 'getListaCentroTrabajo':
		$claveEmp = $_GET['claveEmpresa'];

		$sql = "SELECT * from centrostrabajo where claveEmp = ? and status = 1"; //--------------- Misma estructura

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'i', $claveEmp);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$datos = mysqli_fetch_all($result);

		echo json_encode($datos);
		break;

	case 'getListaProcesosEncuestas':
		$claveCentro = $_GET['claveCentro'];

		$sql = "SELECT * from procesosencuestas where claveCentro like ?"; //--------------- Misma estructura

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $claveCentro);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$datos = mysqli_fetch_all($result);

		echo json_encode($datos);
		break;

	case 'getListaDeptosByCentroTrabajo':
		$claveCentro = $_GET['claveCentro'];

		$sql = "SELECT * from deptos where claveCentro like ?"; //--------------- Misma estructura

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $claveCentro);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$datos = mysqli_fetch_all($result);

		echo json_encode($datos);
		break;

	case 'getEmpleadosVig':
		$claveCentro = $_GET['claveCentro'];
		$consulta = "select matricula, nombreEmpleado, claveDepto, (select nombreDepto from deptos where claveDepto like e.claveDepto and claveCentro like e.claveCentro limit 1) as nombreDepto, correo
					from empleados as e where status = 1 and claveCentro like '".$claveCentro. "'";

		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	//NOTA: Esta funcion es --LA MISMA-- que la que esta arriba
	case 'getEmpleadosBaja':
		$claveCentro = $_GET['claveCentro'];
		$consulta = "select matricula, nombreEmpleado,(select nombreDepto from deptos where claveDepto like e.claveDepto and claveCentro like e.claveCentro limit 1) as nombreDepto, correo
					from empleados as e where status = 0 and claveCentro like '".$claveCentro."'";
		
		$resultado = mysqli_query($mysqli, $consulta);

		while ($data = mysqli_fetch_assoc($resultado)) {
			$arreglo["data"][] =  $data;
		}
		echo json_encode($arreglo);
		break;

	case 'getEmpleadosByCentroTrabajo':
		$claveCentro = $_GET['claveCentro'];

		$sql = "select * from empleados where status = 1 and claveCentro like ?";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $claveCentro);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$datos = mysqli_fetch_all($result); //FETCH ALL es para devolver TODAS las filas del query
		echo json_encode($datos);
		break;

	case 'generarProcesoEncuestas':
		$fechaHoy = date("Y")."-".date("m")."-".date("d");

		//Paso 1: Pongo todos los demas proceso de encuestas de este Centro como Inactivos:
		/*
		$sql = "update procesosencuestas set status = 0 where claveCentro like ?";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $claveCentro);
		mysqli_stmt_execute($stmt);
		*/

		$consulta = "update procesosencuestas set status = 0 where claveCentro like '". $_GET['claveCentro']. "'";
		$resultado = $mysqli->query($consulta);
		

		//Paso 2: Obtengo el valor de la Ultima Clave de proceso creada para poder usarla:
		$sql = "SELECT * FROM procesosencuestas order by claveProceso desc limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		$claveProceso = $row['claveProceso'];
		$claveProceso = $claveProceso+1;


		//Paso 3: Guardo en la BD el nuevo proceso ---------------
		if($_GET['numGuia'] == 2){
			$g2 = 1; $g3 = 0;
		}
		else{ $g2 = 0; $g3 = 1;}

		$sql = "insert into procesosencuestas (claveCentro,nombreProceso,fechaCreacion,guia1,guia2,guia3,status, claveProceso) values (?,?,?,1,?,?,1,?);";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'issiii', $_GET['claveCentro'], $_GET['nombreProceso'], $fechaHoy, $g2, $g3, $claveProceso);

		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo mysqli_stmt_affected_rows($stmt);
		break;

	case 'getProcesoEncuestasAbiertoByCentro':
		$sql = "SELECT * from procesosencuestas where claveCentro like ? and status = 1"; 

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $_GET['claveCentro']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		//Imprimo la fila con los datos devueltos
		echo json_encode($row);
		break;

	case 'getProcesosEncuestasAbiertosByEmpresa':
		$sql = "SELECT * FROM procesosencuestas as p
				where claveCentro in (SELECT claveCentro FROM centrostrabajo where claveEmp = ? and status = 1) and status = 1 order by claveCentro"; 

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'i', $_GET['claveEmpresa']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$datos = mysqli_fetch_all($result);
		echo json_encode($datos);
		break;

	case 'getDatosProcesoEncuestasByNombre':
		$sql = "SELECT * from procesosencuestas where claveCentro like ? and nombreProceso = ? and idProceso != ?"; 

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ssi', $_GET['claveCentro'], $_GET['nombreProceso'], $_GET['idProceso']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		//Imprimo la fila con los datos devueltos
		echo json_encode($row);
		break;

	case 'verificaNombreCentroRepetido':
		$sql = "SELECT * FROM centrostrabajo where nombreCentro = ? and claveEmp = ? and claveCentro != ?"; 

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'sii', $_GET['nombreCentro'], $_GET['claveEmp'], $_GET['claveCentro']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		//Imprimo la fila con los datos devueltos
		echo json_encode($row);
		break;

	case 'verificaCorreoRepetido':
		$sql = "SELECT * FROM usuarios where correo like ? and idUsuario != ?"; 

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'si', $_GET['correo'], $_GET['idUsuario']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		//Imprimo la fila con los datos devueltos
		echo json_encode($row);
		break;

	case 'verificaCorreoRepetido_NuevoAdmin':
		$sql = "SELECT * FROM usuarios where correo like ?"; 

		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 's', $_GET['correo']);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		//Imprimo la fila con los datos devueltos
		echo json_encode($row);
		break;

	case 'getTitulosBloques':
		$numGuia = $_GET['numGuia'];

		$sql = "select distinct bloque, titulo from preguntas where numGuia = ?";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'i', $numGuia);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$datos = mysqli_fetch_all($result); //FETCH ALL es para devolver TODAS las filas del query
		echo json_encode($datos);
		break;

	case 'verificaSiYaHizoGuia':
		require('cn.php');

		$sql = "select * from r_".$_GET['claveEmpresa']." where matricula like ? and numGuia = ? and claveProceso = ? limit 1";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'sii', $_GET['matricula'], $_GET['numGuia'], $_GET['claveProceso']);

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		echo json_encode($row);
		break;

	case 'verificaSiEmpsYaHicieronGuia':
		$sql = "select count(distinct matricula) as numEmps from r_".$_GET['claveEmpresa']." where numGuia = ? and claveProceso = ? ";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ii', $_GET['numGuia'], $_GET['claveProceso']);

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		echo $row[numEmps];
		break;

	case 'verificaSiEmpsDeptoYaHicieronGuia':
		$sql = "select count(distinct matricula) as numEmps from r_".$_GET['claveEmpresa']." where numGuia = ? and claveProceso = ? 
				and matricula in (select matricula from empleados where claveCentro like ? and claveDepto like ?)";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'iiss', $_GET['numGuia'], $_GET['claveProceso'], $_GET['claveCentro'], $_GET['claveDepto']);

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		echo $row[numEmps];
		break;

	//*** NUEVO ******
	case 'getNumEmpsCentroNoHanHechoGuia':

		$sql = "select count(distinct matricula) as numEmps from empleados as m
				where claveCentro like ? and matricula not in (SELECT distinct matricula 
															FROM r_".$_GET['claveEmpresa']."
															where numGuia = ? and claveProceso = ?)";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'sii', $_GET['claveCentro'], $_GET['numGuia'], $_GET['claveProceso']);

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		echo $row[numEmps];
		break;

	//*** NUEVO ******
	case 'getNumEmpsDeptoNoHanHechoGuia':
			
		$sql = "select count(distinct matricula) as numEmps from empleados as m
				where claveCentro like ? and claveDepto like ? and matricula not in (SELECT distinct matricula 
																				FROM r_".$_GET['claveEmpresa']." 
																				where numGuia = ? and claveProceso = ?)";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ssii', $_GET['claveCentro'], $_GET['claveDepto'], $_GET['numGuia'], $_GET['claveProceso']);

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);

		echo $row[numEmps];
		break;
	
	//Este se usa para verificar que la matricula ingresada en los reportes existe dentro de ese centro
	case 'getEmpleadoByCentroTrabajo2':
		$claveCentro = $_GET['claveCentro'];
		$matricula = $_GET['matricula'];

		$sql = "select * from empleados where claveCentro like ? and matricula like ?";
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'ss', $claveCentro, $matricula);
		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		echo json_encode($row);
		break;

	case 'cambiarPassword':
		$nuevaContra = $_GET['nuevaContra']; $idUsuario = $_GET['idUsuario'];

		$sql = "update usuarios set psw = ? where idUsuario = ?";
		
		$stmt = mysqli_stmt_init($mysqli);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, 'si', $nuevaContra, $idUsuario);

		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		//Devuelvo el numero de filas afectadas por la Query.
		//Como solo inserte 1 registro, el valor devuelto debe ser 1
		echo mysqli_stmt_affected_rows($stmt);
		break;


	//** ESTOS CASE SON PARA PODER GENERAR LAS GRAFICAS **
	//****************************************************
	case 'getAvanceGuias':
		$tipoGraficas = $_GET['tipoGraficas']; $claveCentro = $_GET['claveCentro']; $claveProceso = $_GET['claveProceso']; 
		$numGuia = $_GET['numGuia']; $claveDepto = $_GET['claveDepto']; 

		if($tipoGraficas == 2){
			$condicion1 = "and matricula in (select matricula from empleados where claveCentro like '".$claveCentro."' and claveDepto like '".$claveDepto."' and status = 1)";
			$condicion2 = "and claveDepto like '".$claveDepto."'";
		}

		/*Esta query me regresa lo siguiente:
			Numero encuestados Guia 1, Numero encuestados Guia 2 o 3, Total de empleados vigentes del centro de trabajo
		*/
		$consulta = "select (SELECT count(distinct matricula) FROM r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = 1 ".$condicion1.") as numEncsGuia1, 
					(SELECT count(distinct matricula) FROM r_".$_GET['claveEmpresa']." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." ".$condicion1.") as numEncsGuia2_3,
					(select count(matricula) from empleados where claveCentro like '".$claveCentro."' and status = 1 ".$condicion2.") as totalEmpsCentro";

		//Ejecuto la consulta y guardo el resultado
		$resultado = $mysqli->query($consulta);

		//Guardo el resultado dentro de una Fila
		$row = mysqli_fetch_assoc($resultado);

		$encuestadosGuia1 = $row['numEncsGuia1'];
		$encuestadosGuia2_3 = $row['numEncsGuia2_3'];
		$totalEmpsCentro = $row['totalEmpsCentro'];

		//Por ultimo, genero el Array que le voy a regresar al usuario
		$arrayAvanceGuias = array(
								array($encuestadosGuia1, $encuestadosGuia2_3),
								array($totalEmpsCentro-$encuestadosGuia1, $totalEmpsCentro-$encuestadosGuia2_3),
								$encuestadosGuia1, $totalEmpsCentro
							);

		echo json_encode($arrayAvanceGuias);
		break;

	case 'getNivelesRiesgoFinalEmps':
		$tipoGraficas = $_GET['tipoGraficas']; $claveDepto = $_GET['claveDepto']; 
		$claveCentro = $_GET['claveCentro']; $claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia'];

		if($tipoGraficas == 1){
			//** TEMPORAL ** Query para buscar datos de Todos los empleados
			$sql = "select (select sum(respu) from r_".$_GET['claveEmpresa']." 
					where numGuia = ? and claveProceso = ? and bloque > 0 and matricula like m.matricula) as calif
				from empleados as m
				where claveCentro like ? and matricula in (SELECT distinct matricula 
						FROM r_".$_GET['claveEmpresa']." 
						where numGuia = ? and claveProceso = ?) order by claveDepto, matricula";

			$stmt = mysqli_stmt_init($mysqli);
			mysqli_stmt_prepare($stmt, $sql);
			mysqli_stmt_bind_param($stmt, 'iisii', $numGuia, $claveProceso, $claveCentro, $numGuia, $claveProceso);
		
		}else{
			//Query para buscar datos de los empleados de UN DEPARTAMENTO
			$sql = "select (select sum(respu) from r_".$_GET['claveEmpresa']." 
				 where numGuia = ? and claveProceso = ? and bloque > 0 and matricula like m.matricula) as calif
				from empleados as m
				where claveCentro like ? and claveDepto like ? and matricula in (SELECT distinct matricula 
						FROM r_".$_GET['claveEmpresa']."  
						where numGuia = ? and claveProceso = ?) order by claveDepto, matricula";

			$stmt = mysqli_stmt_init($mysqli);
			mysqli_stmt_prepare($stmt, $sql);
			mysqli_stmt_bind_param($stmt, 'iissii', $numGuia, $claveProceso, $claveCentro, $claveDepto, $numGuia, $claveProceso);
		}
		
		mysqli_stmt_execute($stmt);

		//Guardo el resultado obtenido en una variable
		$result = mysqli_stmt_get_result($stmt);
		
		$nulo = 0; $bajo = 0; $medio = 0; $alto=0; $muy_alto=0;

		$numeroEmpleados = 0;
		while ($fila = mysqli_fetch_array($result, MYSQLI_NUM)){
			//Obtengo el Nivel de risgo final de este empleado
			$nivelRiesgo = getNivelRiesgo("Final", 0, $fila[0], $numGuia);
				
			//Ya que tengo el nivel de riesgo, lo sumo a las variables
			if($nivelRiesgo == "Nulo")
				$nulo++;
			else if($nivelRiesgo == "Bajo")
				$bajo++;
			else if($nivelRiesgo == "Medio")
				$medio++;
			else if($nivelRiesgo == "Alto")
				$alto++;
			else if($nivelRiesgo == "Muy alto")
				$muy_alto++;
        }
		//Ya que obtuve el nivel de riesgo de todos los empleado, devuelvo un array
		$empsPorNivelRiesgo = array($nulo, $bajo, $medio, $alto, $muy_alto);

		//echo $numeroEmpleados;
		echo json_encode($empsPorNivelRiesgo);
		break;

	case 'getEmpsReqAtencionMedica':
		$tipoGraficas = $_GET['tipoGraficas']; $claveDepto = $_GET['claveDepto']; 
		$claveCentro = $_GET['claveCentro']; $claveProceso = $_GET['claveProceso']; 
		$claveEmpresa = $_GET['claveEmpresa'];

		if($tipoGraficas == 1)
			//** Empleasos que requieren atencion de TOOODOS los departamentos del Centro de trabajo
			$consulta = "call getEmpsRequierenAtencionMedica__(".$claveProceso.", '".$claveCentro."', 'r_".$claveEmpresa."')";
		else
			//** Empleasos que requieren atencion de UN SOLO DEPTO del Centro de trabajo
			$consulta = "call getEmpsRequierenAtencionMedica_PorDepto__(".$claveProceso.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";

		//Ejecuto la consulta y guardo el resultado
		$resultado = $mysqli->query($consulta);
		$empsReqAtencionMedica = 0;

		//Cuento cuantos empleados requieren atencion
		while($row = $resultado->fetch_assoc()){
			$empsReqAtencionMedica++; //Aumento el numero de empleados
		}

		echo $empsReqAtencionMedica;
		break;


	case 'getNivelesRiesgoPorCategorias':
		$tipoGraficas = $_GET['tipoGraficas']; $claveDepto = $_GET['claveDepto'];  $claveEmpresa = $_GET['claveEmpresa'];
		$claveCentro = $_GET['claveCentro']; $claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia'];

		if($tipoGraficas == 1)
			//Niveles de riesgo por Categorias de ** TODOS LOS EMPLEADOS **
			$consulta = "call getRiesgoPorCategoriaByEmp__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', 'r_".$claveEmpresa."')";
		else
			//Niveles de riesgo por Categorias de todos los empleado
			$consulta = "call getRiesgoPorCategoriaByDepto__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";

		//Defino el numero total de Categorias en base al Numero de Guia
		if($numGuia == 2)   
			$totalCats = 4;
		else if($numGuia == 3)
			$totalCats = 5;

		//Ejecuto la consulta y guardo el resultado
		$resultado = $mysqli->query($consulta);

		//Creo las variables donde guardare el numero de empleados por Nivel de riesgo de cada Categoria
		for ($i = 1; $i <= $totalCats; $i++) {
			${"nulo_c" . $i} = 0; 
			${"bajo_c" . $i} = 0;
			${"medio_c" . $i} = 0;
			${"alto_c" . $i} = 0; 
			${"muy_alto_c" . $i} = 0; 
		}

		//::: Luego del paso anterior, tendre creadas todas estas variables:
		/* $nulo_c1 = 0;  $nulo_c2 = 0;  $nulo_c3 = 0;  $nulo_c4 = 0;  $nulo_c5 = 0;
			.... asi con cada una de las Categorias
		*/

		//Recorro cada una de las categorias para ir obteniendo los datos
		while($row = $resultado->fetch_assoc()){
			//Este For ira de 1 a 4  o  de 1 a 5
			for ($i = 1; $i <= $totalCats; $i++) {
				$nivelRiesgo = getNivelRiesgo("Categoria", $i, $row["e" . $i], $numGuia);
				
				//Ya que tengo el nivel de riesgo, lo sumo a las variables
				if($nivelRiesgo == "Nulo")
					${"nulo_c" . $i}++; //Aumento el valor de la variable que cuenta el numero de Nulos de ESTA categoria
				else if($nivelRiesgo == "Bajo")
					${"bajo_c" . $i}++;
				else if($nivelRiesgo == "Medio")
					${"medio_c" . $i}++;
				else if($nivelRiesgo == "Alto")
					${"alto_c" . $i}++;
				else if($nivelRiesgo == "Muy alto")
					${"muy_alto_c" . $i}++;
			}

			//Aqui haria el otro FOR para guardar el numero de empleados por cada Dominio :::
        }

		$datosNulo = array(); $datosBajo = array(); $datosMedio = array(); $datosAlto = array(); $datosMuyAlto = array();

		//Creo todos los arrays de cada tipo de Nivel de riesgo
		for ($i = 1; $i <= $totalCats; $i++) {
			array_push($datosNulo, ${"nulo_c" . $i});
			array_push($datosBajo, ${"bajo_c" . $i});
			array_push($datosMedio, ${"medio_c" . $i});
			array_push($datosAlto, ${"alto_c" . $i});
			array_push($datosMuyAlto, ${"muy_alto_c" . $i});
		}

		//Ya que tengo todos los datos guardados en las variables, creo el array
		$nivelPorCadaCategoria = array($datosNulo, $datosBajo, $datosMedio, $datosAlto, $datosMuyAlto);

		echo json_encode($nivelPorCadaCategoria);
		break;


	case 'getNivelesRiesgoPorDominios':
		$tipoGraficas = $_GET['tipoGraficas']; $claveDepto = $_GET['claveDepto']; $claveEmpresa = $_GET['claveEmpresa'];
		$claveCentro = $_GET['claveCentro']; $claveProceso = $_GET['claveProceso']; $numGuia = $_GET['numGuia'];

		if($tipoGraficas == 1)
			$consulta = "call getRiesgoPorDominioByEmp__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', 'r_".$claveEmpresa."')";
		else
			$consulta = "call getRiesgoPorDominioByDepto__(".$claveProceso.", ".$numGuia.", '".$claveCentro."', '".$claveDepto."', 'r_".$claveEmpresa."')";

		//Defino el numero total de Categorias en base al Numero de Guia
		if($numGuia == 2)   
			$totalDoms = 8;
		else if($numGuia == 3)
			$totalDoms = 10;

		//Ejecuto la consulta y guardo el resultado
		$resultado = $mysqli->query($consulta);

		//Creo las variables donde guardare el numero de empleados por Nivel de riesgo de cada Dominio
		for ($i = 1; $i <= $totalDoms; $i++) {
			${"nulo_d" . $i} = 0; 
			${"bajo_d" . $i} = 0;
			${"medio_d" . $i} = 0;
			${"alto_d" . $i} = 0; 
			${"muy_alto_d" . $i} = 0; 
		}

		//Recorro cada una de las dominios para ir obteniendo los datos
		while($row = $resultado->fetch_assoc()){
			//Este For ira de 1 a 8  o  de 1 a 10
			for ($i = 1; $i <= $totalDoms; $i++) {
				$nivelRiesgo = getNivelRiesgo("Dominio", $i, $row["e" . $i], $numGuia);
				
				//Ya que tengo el nivel de riesgo, lo sumo a las variables
				if($nivelRiesgo == "Nulo")
					${"nulo_d" . $i}++; //Aumento el valor de la variable que cuenta el numero de Nulos de ESTA categoria
				else if($nivelRiesgo == "Bajo")
					${"bajo_d" . $i}++;
				else if($nivelRiesgo == "Medio")
					${"medio_d" . $i}++;
				else if($nivelRiesgo == "Alto")
					${"alto_d" . $i}++;
				else if($nivelRiesgo == "Muy alto")
					${"muy_alto_d" . $i}++;
			}
		}

		$datosNulo = array(); $datosBajo = array(); $datosMedio = array(); $datosAlto = array(); $datosMuyAlto = array();

		//Creo todos los arrays de cada tipo de Nivel de riesgo
		for ($i = 1; $i <= $totalDoms; $i++) {
			array_push($datosNulo, ${"nulo_d" . $i});
			array_push($datosBajo, ${"bajo_d" . $i});
			array_push($datosMedio, ${"medio_d" . $i});
			array_push($datosAlto, ${"alto_d" . $i});
			array_push($datosMuyAlto, ${"muy_alto_d" . $i});
		}

		//Ya que tengo todos los datos guardados en las variables, creo el array
		$nivelPorCadaDominio = array($datosNulo, $datosBajo, $datosMedio, $datosAlto, $datosMuyAlto);

		echo json_encode($nivelPorCadaDominio);
		break;

	case 'getNivelRiesgoFinalTodosEmps':
		$claveEmpresa = $_GET['claveEmpresa']; $claveProceso = $_GET['claveProceso']; 
		$numGuia = $_GET['numGuia']; $claveCentro = $_GET['claveCentro'];

		//1. Defino cual sera la query que voy a ejecutar:
		$sql = "select round(sum(respu)/(select count( distinct matricula) from r_".$claveEmpresa." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (SELECT matricula FROM empleados where claveCentro like '".$claveCentro."'))) as calif
				from r_".$claveEmpresa." where claveProceso = ".$claveProceso." and numGuia = ".$numGuia." and matricula in (select matricula from empleados where claveCentro like '".$claveCentro."')";
		
		//2. Ejecuto la query y guardo los datos de la Fila en una variable
		$resultado = $mysqli->query($sql);
		$row = $resultado->fetch_assoc();

		//3. Obtengo el Nivel de riesgo del proceso en base a la calificacion
		$nivelRiesgo = getNivelRiesgo("Final", 0, $row[calif], $numGuia);

		//4. Ya que tengo el Nivel, regreso el numero que corresponde a ese Nivel en la tabla
		if($nivelRiesgo == "Nulo")
			$nivel = 1;
		else if($nivelRiesgo == "Bajo")
			$nivel = 2;
		else if($nivelRiesgo == "Medio")
			$nivel = 3;
		else if($nivelRiesgo == "Alto")
			$nivel = 4;
		else if($nivelRiesgo == "Muy alto")
			$nivel = 5;

		//4. Devuelvo el Nivel de riesgo
		echo $nivel;
		break;

	case 'getDatosRespuestasGuia2_3':
		$numGuia = $_GET['numGuia']; $claveProceso = $_GET['claveProceso']; $claveEmpresa = $_GET['claveEmpresa'];

		if($numGuia == 1){ 
			$consulta = "select numPreg, pregunta, bloque, titulo,
			(select count(respu) from r_".$claveEmpresa." where numPreg = p.numPreg and numGuia = 1 and respu = 1 and claveProceso = ".$claveProceso.") as si,
			(select count(respu) from r_".$claveEmpresa." where numPreg = p.numPreg and numGuia = 1 and respu = 0 and claveProceso = ".$claveProceso.") as _no
			from preguntas as p where numGuia = 1";
		}
		else if($numGuia == 2 || $numGuia == 3){
			$consulta = "call getFrecTipoRespGuia2_3_TodosDeptos__(".$numGuia.", ".$claveProceso.", 'r_".$claveEmpresa."')";
		}
		
		//Ejecuto la consulta y guardo el resultado
		$resultado = $mysqli->query($consulta);
		$datos = mysqli_fetch_all($resultado);

		echo json_encode($datos);
		break;
}
