<?php
session_start();
include 'conexion.php';
include 'validar_campo.php';

$sesion = $_SESSION['user'];

if ($sesion == null or $sesion = '') {
   // echo 'Usted no tiene autorizacion!';
    header('Location: error403.html');   //cual sea tu gusto 
    die();

  /* 1. Verificar si ya hay una sesión activa */  
} else if(isset($_SESSION['user'])){

    $rescatar_sesion = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña, rol_id FROM usuario WHERE nombre_usuario=? ');   
    $rescatar_sesion->execute([$_SESSION['user']]);

    $datos = $rescatar_sesion->fetch(PDO::FETCH_ASSOC); 
    
    /* 2. Si el usuario esxiste verificar roles */
    if (is_countable($datos)) {   

       if ($datos['rol_id'] == 1) {
            /* 3. Obtener datos de esta sesion como nombre y apellido del jefe comunidad*/
            $datos_sesion = $conexion->prepare('SELECT j.jefe_comunidad_id, j.nombre, j.apellido FROM jefe_comunidad AS j
            INNER JOIN usuario AS u
            ON u.usuario_id=j.usuario_id
            WHERE j.estado=? AND u.estado=? AND u.nombre_usuario=?');
            $datos_sesion->execute(["1","1",$_SESSION['user']]);   
                 
            foreach ($datos_sesion as $d) {
                $d['jefe_comunidad_id'];
                $d['nombre'];
                $d['apellido'];
            }


         //----------------------------------------- ACCION INGRESAR ---------------------------------------------//

            $nombre = '';
            $descripcion = '';
            $cedula_vocero = '';

            if (isset($_POST['Añadir'])){

                $nombre = valida_campo($_POST['nombre']);
                $descripcion = valida_campo($_POST['descripcion']);
                $cedula_vocero = valida_campo($_POST['cedula_vocero']);

                if (empty($nombre)) {
                    $error_nombre_1 = 'Coloque un nombre';
                } else {
                    if (strlen($nombre) > 60) {
                        $error_nombre_2 = 'El nombre del comite es muy largo';
                    }
                } 

                if (strlen($descripcion) > 200) {
                        $error_descripcion_1 = 'La descripcion es muy larga';
                }
 

                if (empty($cedula_vocero)) {
                    $error_cedula_vocero_1 = 'Coloque una cédula';
                } else {
                    if (strlen($cedula_vocero) > 8) {
                        $error_cedula_vocero_2 = 'La cédula no puede tener más de 8 caracteres';
                    }
                }

                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_nombre_1) and empty($error_nombre_2) and empty($error_descripcion_1)
                    and empty($error_cedula_vocero_1) and empty($error_cedula_vocero_2)){

                        /* 2. Verificar que los datos son únicos */
                        if (!empty($_POST['nombre']) && !empty($_POST['cedula_vocero'])){
                            
                            // 2.1 Verificar que el nombre del Comité no se este repitiendo 
                            $verificar_nombre = $conexion->prepare('SELECT * FROM comite WHERE nombre = ? AND estado=?'); 
                            $verificar_nombre->execute([$nombre,"1"]);
                    
                            $resultado = [];
                            $resultado = $verificar_nombre->fetch(PDO::FETCH_ASSOC);

                            if (is_countable($resultado)) {
                                
                            //limpiar errores
                            $error_nombre_1 = '';
                            $error_nombre_2 = '';
                            $error_descripcion_1 = '';
                            $error_cedula_vocero_1 = '';
                            $error_cedula_vocero_2 = '';

                            //limpiar valores
                            $nombre = '';
                            $descripcion = '';
                            $cedula_vocero = '';
                    
                            echo '
                            <script>
                                alert("Lo sentimos, este comite le pertenece a otro registro");
                            </script>
                            ';
                            } else {                            
                                
                                // 2.2 Verificar que la cedula le pertenece a un habitante real de la comunidad 
                                $verificar_cedula = $conexion->prepare('SELECT * FROM habitante WHERE ci_ps_pn = ? AND estado=?'); 
                                $verificar_cedula->execute([$cedula_vocero,"1"]);
                                
                                $r = [];
                                $r = $verificar_cedula->fetch(PDO::FETCH_ASSOC); // Se obtuvo el id de habitante para la Tabla Vocero
    
                                if (is_countable($r)){  // la cedula existe
                                    
                                    // 2.3 Verificar que el id de habitante proporcionado por la consulta anterior no este repetido en la tabla Vocero
                                    $verificar_habitante_id = $conexion->prepare('SELECT * FROM vocero WHERE habitante_id = ? AND estado=?'); 
                                    $verificar_habitante_id->execute([$r['habitante_id'],"1"]);
                        
                                    $t = [];
                                    $t = $verificar_habitante_id->fetch(PDO::FETCH_ASSOC);
    
                                    if (is_countable($t)) {  
                                        
                                        //limpiar errores
                                        $error_nombre_1 = '';
                                        $error_nombre_2 = '';
                                        $error_descripcion_1 = '';
                                        $error_cedula_vocero_1 = '';
                                        $error_cedula_vocero_2 = '';
    
                                        //limpiar valores
                                        $nombre = '';
                                        $descripcion = '';
                                        $cedula_vocero = '';
                        
                                        echo '
                                        <script>
                                            alert("Lo sentimos, la cédula ingresada ya le pertenece a un vocero de un Comité");
                                        </script>
                                        ';
                                    } else {
                                        /* 3. Insertar los datos en la Tabla Comite */

                                        if (!empty($descripcion)) {
                                            
                                            // 3.1 Insertar en la Tabla Comite
                                            $insertar_comite = $conexion->prepare("INSERT INTO comite(nombre,descripcion) VALUES (?,?)");
                                            $insertar_comite->execute([$nombre,$descripcion]);
        
                                            // 3.2 Buscar el id del comite generado en la anterior inserción a la BD
                                            $bucar_comite = $conexion->prepare('SELECT * FROM comite WHERE nombre = ? AND estado=?'); 
                                            $bucar_comite->execute([$nombre,"1"]);
                                            $c = $bucar_comite->fetch(PDO::FETCH_ASSOC);
        
                                            // 3.3 Insertar en la Tabla Vocero el id de comite y de habitante
                                            $insertar_vocero = $conexion->prepare("INSERT INTO vocero(habitante_id,comite_id) VALUES (?,?)");
                                            $insertar_vocero->execute([$r['habitante_id'],$c['comite_id']]);
        
                                            $registrado = 'registrado';
        
                                            //limpiar errores
                                            $error_nombre_1 = '';
                                            $error_nombre_2 = '';
                                            $error_descripcion_1 = '';
                                            $error_cedula_vocero_1 = '';
                                            $error_cedula_vocero_2 = '';
        
                                            //limpiar valores
                                            $nombre = '';
                                            $descripcion = '';
                                            $cedula_vocero = '';
                                        
                                        } else {                                            
                                            
                                            // 3.1 Insertar en la Tabla Comite
                                            $insertar_comite = $conexion->prepare("INSERT INTO comite(nombre) VALUES (?)");
                                            $insertar_comite->execute([$nombre]);
        
                                            // 3.2 Buscar el id del comite generado en la anterior inserción a la BD
                                            $bucar_comite = $conexion->prepare('SELECT * FROM comite WHERE nombre = ? AND estado=?'); 
                                            $bucar_comite->execute([$nombre,"1"]);
                                            $c = $bucar_comite->fetch(PDO::FETCH_ASSOC);
        
                                            // 3.3 Insertar en la Tabla Vocero el id de comite y de habitante
                                            $insertar_vocero = $conexion->prepare("INSERT INTO vocero(habitante_id,comite_id) VALUES (?,?)");
                                            $insertar_vocero->execute([$r['habitante_id'],$c['comite_id']]);
        
                                            $registrado = 'registrado';
        
                                            //limpiar errores
                                            $error_nombre_1 = '';
                                            $error_nombre_2 = '';
                                            $error_descripcion_1 = '';
                                            $error_cedula_vocero_1 = '';
                                            $error_cedula_vocero_2 = '';
        
                                            //limpiar valores
                                            $nombre = '';
                                            $descripcion = '';
                                            $cedula_vocero = '';}
                                        
    
                                    }
    
                                } else {
                                    
                                    //limpiar errores
                                    $error_nombre_1 = '';
                                    $error_nombre_2 = '';
                                    $error_descripcion_1 = '';
                                    $error_cedula_vocero_1 = '';
                                    $error_cedula_vocero_2 = '';
    
                                    //limpiar valores
                                    $nombre = '';
                                    $descripcion = '';
                                    $cedula_vocero = '';
                        
                                    echo '
                                    <script>
                                        alert("Lo sentimos, la cédula ingresada no le pertenece a ningún habitante en esta Comunidad");
                                    </script>
                                    ';
                                }
                            }
                        }
                }
            }


         //----------------------------------------- ACCION ACTUALIZAR ---------------------------------------------//
         
            $comite_id2 = '';
            $nombre_editar = '';
            $descripcion_editar = '';
            $cedula_vocero_editar = '';

            if (isset($_POST['Actualizar'])){

                $comite_id2 = $_POST['comite_id'];
                $nombre_editar = valida_campo($_POST['nombre']);
                $descripcion_editar = valida_campo($_POST['descripcion']);
                $cedula_vocero_editar = valida_campo($_POST['cedula_vocero']);

                if (empty($nombre_editar)) {
                    $error_nombre_editar_1 = 'Coloque un nombre';
                } else {
                    if (strlen($nombre_editar) > 60) {
                        $error_nombr_editare_2 = 'El nombre del comite es muy largo';
                    }
                } 

                if (strlen($descripcion_editar) > 200) {
                        $error_descripcion_editar_1 = 'La descripcion es muy larga';
                }
 

                if (empty($cedula_vocero_editar)) {
                    $error_cedula_vocero_editar_1 = 'Coloque una cédula';
                } else {
                    if (strlen($cedula_vocero_editar) > 8) {
                        $error_cedula_vocero_editar_2 = 'La cédula no puede tener más de 8 caracteres';
                    }
                }

                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_nombre_editar_1) and empty($error_nombre_editar_2) and empty($error_descripcion_editar_1)
                    and empty($error_cedula_vocero_editar_1) and empty($error_cedula_vocero_editar_2)){

                        /* 2. Verificar que los datos son únicos */
                        if (!empty($_POST['nombre']) && !empty($_POST['cedula_vocero'])){
                            
                            // 2.1 Verificar que el nombre del Comité no se este repitiendo 
                            $verificar_nombre_editar = $conexion->prepare('SELECT * FROM comite WHERE md5(comite_id) <> ? AND nombre = ? AND estado=?'); 
                            $verificar_nombre_editar->execute([$comite_id2,$nombre_editar,"1"]);
                    
                            $e = [];
                            $e = $verificar_nombre_editar->fetch(PDO::FETCH_ASSOC);

                            if (is_countable($e)) {
                                
                            //limpiar errores
                            $error_nombre_editar_1 = '';
                            $error_nombre_editar_2 = '';
                            $error_descripcion_editar_1 = '';
                            $error_cedula_vocero_editar_1 = '';
                            $error_cedula_vocero_editar_2 = '';
                    
                            echo '
                            <script>
                                alert("Lo sentimos, este comite le pertenece a otro registro");
                            </script>
                            ';
                            } else {                            
                                
                                // 2.2 Verificar que la cedula le pertenece a un habitante real de la comunidad 
                                $verificar_cedula_editar = $conexion->prepare('SELECT * FROM habitante WHERE ci_ps_pn = ? AND estado=?'); 
                                $verificar_cedula_editar->execute([$cedula_vocero_editar,"1"]);
                                
                                $x = [];
                                $x = $verificar_cedula_editar->fetch(PDO::FETCH_ASSOC); // Se obtuvo el id de habitante para la Tabla Vocero
    
                                if (is_countable($x)){  // la cedula existe
                                    
                                    // 2.3 Verificar que el id de habitante proporcionado por la consulta anterior no este repetido en la tabla Vocero
                                    $verificar_habitante_id_editar = $conexion->prepare('SELECT * FROM vocero WHERE md5(comite_id) <> ? AND habitante_id = ? AND estado=?'); 
                                    $verificar_habitante_id_editar->execute([$comite_id2,$x['habitante_id'],"1"]);
                        
                                    $y = [];
                                    $y = $verificar_habitante_id_editar->fetch(PDO::FETCH_ASSOC);
    
                                    if (is_countable($y)) {  
                                        
                                        //limpiar errores
                                        $error_nombre_1 = '';
                                        $error_nombre_2 = '';
                                        $error_descripcion_1 = '';
                                        $error_cedula_vocero_1 = '';
                                        $error_cedula_vocero_2 = '';
                        
                                        echo '
                                        <script>
                                            alert("Lo sentimos, la cédula ingresada ya le pertenece a un vocero de un Comité");
                                        </script>
                                        ';
                                    } else {
                                        /* 3. Actualizar los datos en la Tabla Comite */

                                        if (!empty($descripcion_editar)) {
                                            
                                            // funciones para actualizar la fecha en la tabla
                                            date_default_timezone_set('America/Caracas');
                                            setlocale(LC_TIME, 'spanish');
                                            $fecha_actualizacion = date('Y-m-d g:i:s');

                                            // 3.1 Actualizar en la Tabla Comite
                                            $actualizar_comite = $conexion->prepare("UPDATE comite SET nombre = ?, descripcion = ?, fecha_actualizacion = ? WHERE md5(comite_id) = ?;");
                                            $actualizar_comite->execute([$nombre_editar,$descripcion_editar,$fecha_actualizacion,$comite_id2]);
        
                                            // 3.2 Actualizar en la Tabla Vocero el id de habitante obtenido anteriormente
                                            $actualizar_vocero = $conexion->prepare("UPDATE vocero SET habitante_id = ?, fecha_actualizacion = ? WHERE md5(comite_id) = ?;");
                                            $actualizar_vocero->execute([$x['habitante_id'],$fecha_actualizacion,$comite_id2]);
        
        
                                            //limpiar errores
                                            $error_nombre_editar_1 = '';
                                            $error_nombr_editare_2 = '';
                                            $error_descripcion_editar_1 = '';
                                            $error_cedula_vocero_editar_1 = '';
                                            $error_cedula_vocero_editar_2 = '';

                                            $actualizado = 'actualizado';

                                        } else {                                            
                                            
                                            // funciones para actualizar la fecha en la tabla
                                            date_default_timezone_set('America/Caracas');
                                            setlocale(LC_TIME, 'spanish');
                                            $fecha_actualizacion = date('Y-m-d g:i:s');

                                            // 3.1 Actualizar en la Tabla Comite
                                            $actualizar_comite = $conexion->prepare("UPDATE comite SET nombre = ?, fecha_actualizacion = ? WHERE md5(comite_id) = ?;");
                                            $actualizar_comite->execute([$nombre_editar,$fecha_actualizacion,$comite_id2]);
        
                                            // 3.2 Actualizar en la Tabla Vocero el id de habitante obtenido anteriormente
                                            $actualizar_vocero = $conexion->prepare("UPDATE vocero SET habitante_id = ?, fecha_actualizacion = ? WHERE md5(comite_id) = ?;");
                                            $actualizar_vocero->execute([$x['habitante_id'],$fecha_actualizacion,$comite_id2]);
        
        
                                            //limpiar errores
                                            $error_nombre_editar_1 = '';
                                            $error_nombr_editare_2 = '';
                                            $error_descripcion_editar_1 = '';
                                            $error_cedula_vocero_editar_1 = '';
                                            $error_cedula_vocero_editar_2 = '';

                                            $actualizado = 'actualizado';
                                        
                                        }
                                        
    
                                    }
    
                                } else {
                                    
                                    //limpiar errores
                                    $error_nombre_1 = '';
                                    $error_nombre_2 = '';
                                    $error_descripcion_1 = '';
                                    $error_cedula_vocero_1 = '';
                                    $error_cedula_vocero_2 = '';
    
                                    //limpiar valores
                                    $nombre = '';
                                    $descripcion = '';
                                    $cedula_vocero = '';
                        
                                    echo '
                                    <script>
                                        alert("Lo sentimos, la cédula ingresada no le pertenece a ningún habitante en esta Comunidad");
                                    </script>
                                    ';
                                }
                            }
                        }
                }
            }
         
         //----------------------------------------- ACCION ELIMINAR ---------------------------------------------//
            if (isset($_POST['Eliminar'])){
                $comite_id_borrar = $_POST['comite_id'];
                $estado = '2';

                /* 1. Eliminar Comite */
                $borrar_comite = $conexion->prepare("UPDATE comite SET estado =? WHERE md5(comite_id) = ?;");
                $borrar_comite->execute([$estado, $comite_id_borrar]);

                /* 2. Eliminar Tabla involucrada: Vocero*/
                $borrar_vocero = $conexion->prepare("UPDATE vocero SET estado =? WHERE md5(comite_id) = ?;");
                $borrar_vocero->execute([$estado,$comite_id_borrar]);

                $borrado = 'borrado';

            }

                     
         //----------------------------------------- ACCION RECUPERAR ---------------------------------------------//
            if (isset($_POST['Recuperar'])){
                $comite_id_recuperar = $_POST['comite_id'];
                $estado_recuperar = '1';

                /* 1. Recuperar Comite */
                $recuperar_comite = $conexion->prepare("UPDATE comite SET estado =? WHERE md5(comite_id) = ?;");
                $recuperar_comite->execute([$estado_recuperar, $comite_id_recuperar]);

                /* 2. Recuperar Tabla involucrada: Vocero*/
                $recuperar_vocero = $conexion->prepare("UPDATE vocero SET estado =? WHERE md5(comite_id) = ?;");
                $recuperar_vocero->execute([$estado_recuperar,$comite_id_recuperar]);


                $recuperado = 'recuperado';


            }


        } elseif ($datos['rol_id'] == 2) { 
            header('Location: inicio-lider-calle.php');

        } else {    //No puede haber otro rol que no sea especificado 
            session_destroy();

       }
        
    } else {

        session_destroy();
        
    } 

}


/* --------------------------------------------------------------------------------------------------------------------------------------------- */


//Informacion de la Tabla Comite
$comite = $conexion->prepare('SELECT c.comite_id, c.nombre as comite, c.descripcion, h.nombre, h.apellido, h.ci_ps_pn FROM comite AS c
INNER JOIN vocero AS v
ON c.comite_id=v.comite_id 
INNER JOIN habitante as h
ON v.habitante_id=h.habitante_id WHERE c.estado=?;');
$comite->execute(["1"]);


/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */
// Información de la Tabla de Recuperación 
$comite_recuperar = $conexion->prepare('SELECT c.comite_id, c.nombre as comite, c.descripcion, h.nombre, h.apellido, h.ci_ps_pn FROM comite AS c
INNER JOIN vocero AS v
ON c.comite_id=v.comite_id 
INNER JOIN habitante as h
ON v.habitante_id=h.habitante_id WHERE c.estado=?;');
$comite_recuperar->execute(["2"]);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comites del Consejo Comunal</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">
    
    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/comite.css">

     <!-- Links de Google Fonts para Logo-->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet"> 

</head>

<body>

<div class="container-fluid">
    <div class="row">
        <nav class="navbar bg-light shadow fixed-top d-flex justify-content-between">
            <!-- Logo -->
            <a href="#" class="navbar-brand d-flex align-items-center logo__s">
                <img src="../src/img/log.png" alt="logo" class="navbar-brand__img">
                <img src="../src/img/logo_name.jpg" class="w-25" alt="">
                <!-- <h5 class="logo-name">Sector Universitario Oeste</h5>-->
            </a>
            <!-- user -->
            <div class="user d-flex flex-column align-items-center justify-content-center px-3">
                <img src="../src/img/user.png" alt="userleader">
                <h6 class="lead fs-6"><?php echo $d['nombre']. " " .$d['apellido']?></h6>
            </div>
        </nav>
    </div>
</div>
    

            <!-- sidebar -->
    <div class="row g-0 row-cols-12">
        <div class="d-flex flex-column col-auto min-vh-100 bgside__bar bajar position-fixed">
            <div class="mt-4">
                <a href="inicio-jefe-comunidad.php" class="text-white d-none d-sm-inline text-decoration-none d-flex align-items-center ms-4" role="button">
                    <span class="fs-5">Inicio</span>
                </a>
                <hr class="text-white d-none d-sm-block"/>
                <ul class="nav nav-pills flex-column mt-2 mt-sm-0" id="menu">

                    <li class="nav-item my-sm-1 my-2">
                        <a href="manzana.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-apple-whole "></i>
                            <span class="ms-2 d-none d-sm-inline">Manzanas</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="lider_calle.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-address-book"></i>
                            <span class="ms-2 d-none d-sm-inline">Lideres</span>
                        </a>
                    </li>  

                    <li class="nav-item dropdown my-sm-1 my-2">
                        <a href="#sidemenu" data-bs-toggle="dropdown" class="nav-link dropdown-toggle text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-bullhorn"></i>
                            <span class="ms-2 d-none d-sm-inline">Jornadas</span>
                        </a>
                        <ul class="dropdown-menu ms-1 flex-column" id="sidemenu" data-bs-parent="#menu">
                            <li class="nav-item">
                                <a class="dropdown-item text-dark" href="jornada.php#categoria">Categoria</a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-dark" href="jornada.php#atencion" aria-current="page">Atenciones</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item my-sm-1 my-2">
                        <a href="comite.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-comments"></i>
                            <span class="ms-2 d-none d-sm-inline">Comites</span>
                        </a>
                    </li> 
                    <li class="nav-item my-sm-1 my-2">
                        <a href="totalizaciones.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-chart-column"></i>
                            <span class="ms-2 d-none d-sm-inline">Totalizaciones</span>
                        </a>
                    </li>   
                    <li class="nav-item my-sm-1 my-2">
                        <a href="conexiones.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-mobile-screen"></i>
                            <span class="ms-2 d-none d-sm-inline">Conexiones</span>
                        </a>
                    </li>            
                    <li class="nav-item my-sm-1 my-2">
                        <a href="usuarios.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-users"></i>
                            <span class="ms-2 d-none d-sm-inline">Usuarios</span>
                        </a>
                    </li>                 
                </ul>
            </div>
            <div>
                <hr class="text-white d-none d-sm-block"/>
                <div class="dropdown open">
                    <a class="btn border-none outline-none text-white dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                                <i class="fa fa-user"></i><span class="ms-1 d-none d-sm-inline"> Mi cuenta</span>                               
                            </a>
                    <div class="dropdown-menu" aria-labelledby="triggerId">
                        <a class="dropdown-item" href="../page/perfil_jefe_comunidad.php">Perfil</a>
                        <a class="dropdown-item" href="../page/cerrar_sesion.php">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>


            <!-- inicio -->
           <!-- Tabla Manzana -->

        <div class="container mover-derecha-tabla col-10 small">
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Comites del Consejo Comunal</h3>
                <img src="../src/img/comite2.png" class="img-fluid d-flex justify-content-center col-4" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir" data-bs-toggle="modal" data-bs-target="#modalComiteAñadir" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos-comite" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-info">
                                <th class="text-center">Comite</th>
                                <th class="text-center">Voceros</th>
                                <th class="text-center ">Descripcion</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($comite as $c) {
		                    ?>
		                    <tr>
			                    <td><?php echo $c['comite']; ?></td>
                                <td><?php echo $c['nombre']. " " .$c['apellido']. " | " .$c['ci_ps_pn']; ?></td>
                                <td class="tamaño_columna"><?php echo $c['descripcion']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="comite.php" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarComite" data-bs-id="<?= md5($c['comite_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="comite.php" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminarComite" data-bs-id="<?= md5($c['comite_id']); ?>"> 
                                    <i class="fa-solid fa-trash-can"></i></a>

		                    </tr>	
	                    	<?php 
	                        }
                        	?>
                            
                        </tbody>
                    </table>
                </div> 
            </div> 

        </div>

            <!-- fin -->

        </div>


     <!--------------------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------------------->

            <!-- inicio -->
           <!-- Tabla Manzana -->

           <div class="container mover-derecha-tabla col-10 pt-5 pb-3 small">
            <hr class="mt-5 line"> 
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Registros Eliminados</h3>
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="tabla_recuperacion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-dark">
                                <th class="text-center">Comite</th>
                                <th class="text-center">Voceros</th>
                                <th class="text-center ">Descripcion</th>
                                <th class="text-center no-exportar">Recuperar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($comite_recuperar as $c) {
		                    ?>
		                    <tr>
			                    <td><?php echo $c['comite']; ?></td>
                                <td><?php echo $c['nombre']. " " .$c['apellido']. " | " .$c['ci_ps_pn']; ?></td>
                                <td class="tamaño_columna"><?php echo $c['descripcion']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-center">
                                    <a href="comite.php" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalRecuperarComite" data-bs-id="<?= md5($c['comite_id']); ?>"> 
                                    <i class="fa-solid fa-trash-arrow-up text-white"></i></a>
                                </td>
		                    </tr>	
	                    	<?php 
	                        }
                        	?>
                            
                        </tbody>
                    </table>
                </div> 
            </div> 

        </div>

            <!-- fin -->

        </div>

     <!-------------------------------------------------------------------------------------------------------------------------------------------->




    <!-- JS Sweet Atert 2 -->
    <script src="../src/plugins/sweetAlert2/sweetalert2.all.min.js"></script>


    <!----------------Alerta Registrado -------------->
        <?php if(!empty($registrado)){ ?>
            
            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Registrado satisfactoriamente',
                showConfirmButton: false,
                timer: 1500
                });

            </script> 

        <?php };  ?>


    <!----------------Alerta Actualizado -------------->
        <?php if(!empty($actualizado)){ ?>

            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Actualizado satisfactoriamente',
                showConfirmButton: false,
                timer: 1500
                });     

            </script> 

        <?php };  ?>

    <!----------------Alerta Eliminado -------------->
        <?php if(!empty($borrado)){ ?>

            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Eliminado satisfactoriamente',
                showConfirmButton: false,
                timer: 2000
                });     

            </script> 

        <?php };  ?>        

     <!----------------Alerta Recuperado -------------->
        <?php if(!empty($recuperado)){ ?>

            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Recuperado satisfactoriamente',
                showConfirmButton: false,
                timer: 2000
                });     

            </script> 

        <?php };  ?>





        <!-- Modal Ingresar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalComiteAñadir" tabindex="-1" aria-labelledby="modalComiteAñadirLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalComiteAñadirLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="comite.php" method="POST" id="formulario">
                        <div class="modal-content border-white">

                        <img class="w-25 mb-4 modal_imagen" src="../src/img/comite_modal.png" alt="">
                           

                           <div class="mb-3 d-flex justify-content-center row row-cols-12">
                               <div class="col-6">  <!---------------------------------->
                                   <label for="nombre" class="form-label fw-medium">Nombre del Comite:</label>
                                   <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo $nombre; ?>">
                                        <?php if(!empty($error_nombre_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_nombre_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_nombre_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_nombre_2" ?></p>
                                            <?php }
                                        }; ?>
                               </div>

                               <div class="col-6">  <!---------------------------------->
                                   <label for="descripcion" class="form-label fw-medium">Descripción:</label>
                                   <input type="textarea" name="descripcion" id="descripcion" class="form-control" value="<?php echo $descripcion; ?>">
                                       <?php if(!empty($error_descripcion_1)){ ?>
                                       <p class='small text-danger error'><?php echo "$error_descripcion_1" ?></p>
                                       <?php } ; ?>
                               </div>
                           </div>

                           <div class="mb-3">
                               <!-------------------------------------------------------->
                               <label for="cedula_vocero" class="form-label fw-medium">Cédula del Vocero:</label>
                               <input type="number" name="cedula_vocero" id="cedula_vocero" class="form-control" value="<?php echo $cedula_vocero; ?>">
                                    <?php if(!empty($error_cedula_vocero_1)){ ?>
                                    <p class='small text-danger error'><?php echo "$error_cedula_vocero_1" ?></p>
                                    <?php } else {
                                    if(!empty($error_cedula_vocero_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_cedula_vocero_2" ?></p>
                                        <?php }
                                    }; ?>
                           </div>


                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" name="Añadir" class="btn btn-primary"><i class=" p-1 fa-solid fa-floppy-disk"></i>Guardar</button>
                            </div>
                        </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                     
                  </div>
                </div>
              </div>
        </div> 

        <?php
                             
        ?>
        
        <!-- Modal Editar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEditarComite" tabindex="-1" aria-labelledby="modalEditarComiteLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEditarComiteLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="comite.php" method="POST">
                        <div class="modal-content border-white">

                            <input type="hidden" id="comite_id" name="comite_id" >

                            <img class="w-25 mb-4 modal_imagen" src="../src/img/comite_modal.png" alt="">
                           

                           <div class="mb-3 d-flex justify-content-center row row-cols-12">
                               <div class="col-6">  <!---------------------------------->
                                   <label for="nombre" class="form-label fw-medium">Nombre del Comite:</label>
                                   <input type="text" name="nombre" id="nombre" class="form-control" >
                                        <?php if(!empty($error_nombre_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_nombre_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_nombre_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_nombre_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                               </div>

                               <div class="col-6">  <!---------------------------------->
                                   <label for="descripcion" class="form-label fw-medium">Descripción:</label>
                                   <input type="textarea" name="descripcion" id="descripcion" class="form-control">
                                       <?php if(!empty($error_descripcion_editar_1)){ ?>
                                       <p class='small text-danger error'><?php echo "$error_descripcion_editar_1" ?></p>
                                       <?php } else {
                                       if(!empty($error_descripcion_editar_2)){ ?>
                                           <p class='small text-danger m-0 error'><?php echo "$error_descripcion_editar_2" ?></p>
                                           <?php }
                                       }; ?>
                               </div>
                           </div>

                           <div class="mb-3">
                               <!-------------------------------------------------------->
                               <label for="cedula_vocero" class="form-label fw-medium">Cédula del Vocero:</label>
                               <input type="number" name="cedula_vocero" id="cedula_vocero" class="form-control">
                                    <?php if(!empty($error_cedula_vocero_editar_1)){ ?>
                                    <p class='small text-danger error'><?php echo "$error_cedula_vocero_editar_1" ?></p>
                                    <?php } else {
                                    if(!empty($error_cedula_vocero_editar_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_cedula_vocero_editar_2" ?></p>
                                        <?php }
                                    }; ?>
                           </div>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" id= "Acttualizar" name="Actualizar" class="btn btn-primary"><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar cambios</button>
                            </div>
                        </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                     
                  </div>
                </div>
              </div>
        </div>    

        <!-- Modal Eliminar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEliminarComite" tabindex="-1" aria-labelledby="modalEliminarComiteLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEliminarComiteLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea eliminar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="comite.php" method="POST">
                            <input type="hidden" name="comite_id" id="comite_id">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 


        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalRecuperarComite" tabindex="-1" aria-labelledby="modalRecuperarComiteLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRecuperarComiteLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea recuperar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="comite.php" method="POST">
                            <input type="hidden" name="comite_id" id="comite_id">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="Recuperar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash-arrow-up text-white"></i>Recuperar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 


    </div>


        
    
  

  </div>     

    <!-- JS Bootstrap -->
    <script src="../src/js/bootstrap.bundle.min.js"></script>

    <!-------------- jquery ----------------->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <!-- JS DataTable -->
    <script src="../src/plugins/datatables.min.js"></script>

    <!-- JS nuestro -->
    <script src="../src/js/comite.js"></script>

    <script>

/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalEditarComite = document.getElementById('modalEditarComite')  //id de la ventana modal Editar Registro
    let modalEliminarComite = document.getElementById('modalEliminarComite')  //id de la ventana modal Eliminar Registro

    modalEditarComite.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let comite_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputComiteId = modalEditarComite.querySelector('.modal-body #comite_id')              //selecionamos la clase y el id presentes en el formulario
        let inputNombre = modalEditarComite.querySelector('.modal-body #nombre')
        let inputDescripcion = modalEditarComite.querySelector('.modal-body #descripcion')
        let inputCedulaVocero = modalEditarComite.querySelector('.modal-body #cedula_vocero')


        let url = "comite_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('comite_id', comite_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputComiteId.value = data.comite_id
            inputNombre.value = data.nombre
            inputDescripcion.value = data.descripcion
            inputCedulaVocero.value = data.ci_ps_pn
             
            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalEliminarComite.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let comite_id = button.getAttribute('data-bs-id') 
        
        modalEliminarComite.querySelector('.modal-footer #comite_id').value = comite_id
    })


/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
    modalRecuperarComite.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let comite_id = button.getAttribute('data-bs-id') 
        
        modalRecuperarComite.querySelector('.modal-footer #comite_id').value = comite_id
    })
</script>

</body>
</html>