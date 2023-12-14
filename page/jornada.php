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

     //----------------------------------------- JORNADA : ACCION INGRESAR ---------------------------------------------//
            $categoria = '';
            $jefe_comunidad_id = '';

            if (isset($_POST['AñadirJornada'])){

                $categoria = valida_campo($_POST['categoria']);
                $jefe_comunidad_id = valida_campo($_POST['jefe_comunidad_id']);  


                if (empty($categoria)) {
                    $error_categoria_1 = 'Coloque una categoria';
                } else {
                    if (strlen($categoria) > 60) {
                        $error_categoria_2 = 'El nombre de la cattegoria es muy largo';
                    }
                } 
            
                if (empty($jefe_comunidad_id)) {
                    $error_jefe_comunidad_id_1 = 'Por favor, indique un responsable';
                }  

                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_categoria_1) and empty($error_categoria_2) and empty($error_jefe_comunidad_id_1)){

                    /* 2. Verificamos si los datos ya le pertenecen a una jornada */
                    if (!empty($_POST['categoria'])) {
                        $verificar_jornada = $conexion->prepare('SELECT * FROM jornada WHERE categoria=? AND estado=?');  
                        $verificar_jornada->execute([$categoria,"1"]);
                
                        $resultado = [];
                        $resultado = $verificar_jornada->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($resultado)) {

                            //limpiar errores
                            $error_categoria_1 = '';
                            $error_categoria_2 = '';
                            $error_jefe_comunidad_id_1 = '';


                            //limpiar valores
                            $categoria = '';
                            $jefe_comunidad_id = '';

                    
                            echo '
                            <script>
                                alert("Lo sentimos, la categoria especificada pertenece a otro registro");
                            </script>
                            ';

                        } elseif(!is_countable($resultado)) {

                            /* 3. Inserción de los datos */
                            $ingresar_jornada = $conexion->prepare("INSERT INTO jornada(categoria,jefe_comunidad_id) VALUES (?,?)");
                            $ingresar_jornada->execute([$categoria,$jefe_comunidad_id]);

                            $registrado = 'registrado';
                        }
                    }
                }
            }

     //----------------------------------------- JORNADA : ACCION EDITAR ---------------------------------------------//

            $categoria_editar = '';
            $jefe_comunidad_id_editar = '';

            if (isset($_POST['ActualizarJornada'])){

                $jornada_id2 = $_POST['jornada_id'];

                $categoria_editar = valida_campo($_POST['categoria']);
                $jefe_comunidad_id_editar = valida_campo($_POST['jefe_comunidad_id']);  


                if (empty($categoria_editar)) {
                    $error_categoria_editar_1 = 'Coloque una categoria';
                } else {
                    if (strlen($categoria_editar) > 60) {
                        $error_categoria_editar_2 = 'El nombre de la cattegoria es muy largo';
                    }
                } 
            
                if (empty($jefe_comunidad_id_editar)) {
                    $error_jefe_comunidad_id_editar_1 = 'Por favor, indique un responsable';
                }  

                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_categoria_editar_1) and empty($error_categoria_editar_2) and empty($error_jefe_comunidad_id_editar_1)){

                    /* 2. Verificamos si los datos ya le pertenecen a una jornada */
                    if (!empty($_POST['categoria'])) {
                        $verificar_jornada_editar = $conexion->prepare('SELECT * FROM jornada WHERE categoria=? AND estado=?');  
                        $verificar_jornada_editar->execute([$categoria_editar,"1"]);
                
                        $r = [];
                        $r = $verificar_jornada_editar->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($r)) {

                            //limpiar errores
                            $error_categoria_editar_1 = '';
                            $error_categoria_editar_2 = '';
                            $error_jefe_comunidad_id_editar_1 = '';

                            //limpiar valores
                            $categoria_editar = '';
                            $jefe_comunidad_id_editar = '';

                    
                            echo '
                            <script>
                                alert("Lo sentimos, la categoria especificada pertenece a otro registro");
                            </script>
                            ';

                        } elseif(!is_countable($r)) {

                            // funciones para actualizar la fecha en la tabla
                            date_default_timezone_set('America/Caracas');
                            setlocale(LC_TIME, 'spanish');
                            $fecha_actualizacion = date('Y-m-d g:i:s');

                            /* 3. Inserción de los datos */
                            $actualizar_jornada = $conexion->prepare("UPDATE jornada SET categoria = ?, jefe_comunidad_id = ?, fecha_actualizacion = ? WHERE md5(jornada_id) = ?;");
                            $actualizar_jornada->execute(array($categoria_editar, $jefe_comunidad_id_editar, $fecha_actualizacion, $jornada_id2));

                            $actualizado = 'actualizado';

                        }
                    }
                }
            }

         //----------------------------------------- JORNADA : ACCION ELIMINAR ---------------------------------------------//
            if (isset($_POST['EliminarJornada'])){

                $jornada_id_borrar = $_POST['jornada_id'];
                $estado = '2';
                
                /* 1. Eliminar Jornada*/
                $borrar_jornada = $conexion->prepare("UPDATE jornada SET estado =? WHERE md5(jornada_id) = ?;");
                $borrar_jornada->execute([$estado, $jornada_id_borrar]);

                /* 2. Eliminar Tabla involucrada: Atencion */
                $borrar_atencion = $conexion->prepare("UPDATE atencion SET estado =? WHERE md5(jornada_id) = ?;");
                $borrar_atencion->execute([$estado, $jornada_id_borrar]);

                $borrado = 'borrado';

            }


         //----------------------------------------- JORNADA : ACCION RECUPERAR ---------------------------------------------//
            if (isset($_POST['RecuperarJornada'])){

                $jornada_id_recuperar = $_POST['jornada_id'];
                $estado_recuperar = '1';
                
                /* 1. Eliminar Jornada*/
                $recuperar_jornada = $conexion->prepare("UPDATE jornada SET estado =? WHERE md5(jornada_id) = ?;");
                $recuperar_jornada->execute([$estado_recuperar, $jornada_id_recuperar]);

                /* 2. Eliminar Tabla involucrada: Atencion */
                $recuperar_atencion = $conexion->prepare("UPDATE atencion SET estado =? WHERE md5(jornada_id) = ?;");
                $recuperar_atencion->execute([$estado_recuperar, $jornada_id_recuperar]);

                $recuperado = 'recuperado';
        

            }

         //----------------------------------------- ATENCION : ACCION INGRESAR ---------------------------------------------//
            $jornada_id = '';
            $fecha_entrega = '';
            $cantidad = '';

            if (isset($_POST['AñadirAtencion'])){

                $jornada_id = valida_campo($_POST['jornada_id']);
                $fecha_entrega = valida_campo($_POST['fecha_entrega']);
                $cantidad = valida_campo($_POST['cantidad']);

                
                if (empty($jornada_id)) {
                    $error_jornada_id_1 = 'Coloque una categoria';
                } 
            
                if (empty($fecha_entrega)) {
                    $error_fecha_entrega_1 = 'Coloque una fecha de entrega';
                }   
            
                if (empty($cantidad)) {
                    $error_cantidad_1 = 'Indique la cantidad';
                } else {
                    if (strlen($cantidad) > 4) {
                        $error_cantidad_2 = 'El numero especificado es muy largo';
                    }
                }

                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_jornada_id_1) and empty($error_fecha_entrega_1) and empty($error_cantidad_1)
                    and empty($error_cantidad_2)){

                        /* 2. Verificamos si la fecha de la atencion le pertenece a otra ya registrada */
                        if (!empty($_POST['fecha_entrega'])) {
                            $verificar_fecha = $conexion->prepare('SELECT * FROM atencion WHERE jornada_id=? AND fecha_entrega=? AND estado=?'); 
                            $verificar_fecha->execute([$jornada_id,$fecha_entrega,"1"]);
                    
                            $f = [];
                            $f = $verificar_fecha->fetch(PDO::FETCH_ASSOC);

                            if(is_countable($f)){  //lamentablemente el campo de fecha de HTML parece ser distinto al de la BD, asi que esta validacion no funciona 

                                //limpiar errores
                                $error_jornada_id_1 = '';
                                $error_fecha_entrega_1 = '';
                                $error_cantidad_1 = '';
                                $error_cantidad_2 = '';

                                //limpiar valores
                                $jornada_id = '';
                                $fecha_entrega = '';
                                $cantidad = '';
                    
                                echo '
                                <script>
                                    alert("Lo sentimos, los datos de la atencion especificada le pertenecen a otro registro");
                                </script>
                                ';
                            } else {

                                /* 3. Insertar los datos en la Tabla Atencion*/
                                $ingresar_atencion = $conexion->prepare("INSERT INTO atencion(jornada_id,fecha_entrega,cantidad) VALUES (?,?,?)");
                                $ingresar_atencion->execute([$jornada_id,$fecha_entrega,$cantidad]);

                                //limpiar errores
                                $error_jornada_id_1 = '';
                                $error_fecha_entrega_1 = '';
                                $error_cantidad_1 = '';
                                $error_cantidad_2 = '';

                                //limpiar valores
                                $jornada_id = '';
                                $fecha_entrega = '';
                                $cantidad = '';

                                $registrado = 'registrado';

                            }
                        }
                    }
            }


            
         //----------------------------------------- ATENCION : ACCION ACTUALIZAR ---------------------------------------------//
            $atencion_id2 = '';
            $jornada_id_editar = '';
            $fecha_entrega_editar = '';
            $cantidad_editar = '';

            if (isset($_POST['ActualizarAtencion'])){

                $atencion_id2 = $_POST['atencion_id'];
                $jornada_id_editar = valida_campo($_POST['jornada_id']);
                $fecha_entrega_editar = valida_campo($_POST['fecha_entrega']);
                $cantidad_editar = valida_campo($_POST['cantidad']);

                
                if (empty($jornada_id_editar)) {
                    $error_jornada_id_editar_1 = 'Coloque una categoria';
                } 
            
                if (empty($fecha_entrega_editar)) {
                    $error_fecha_entrega_editar_1 = 'Coloque una fecha de entrega';
                }   
            
                if (empty($cantidad_editar)) {
                    $error_cantidad_editar_1 = 'Indique la cantidad';
                } else {
                    if (strlen($cantidad_editar) > 4) {
                        $error_cantidad_editar_2 = 'El numero especificado es muy largo';
                    }
                }

                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_jornada_id_editar_1) and empty($error_fecha_entrega_editar_1) and empty($error_cantidad_editar_1)
                    and empty($error_cantidad_editar_2)){

                        /* 2. Verificamos si la fecha y la categoria de la atencion le pertenecen a otra registrada */
                        if (!empty($_POST['fecha_entrega'])) {
                            $verificar_fecha = $conexion->prepare('SELECT * FROM atencion WHERE md5(atencion_id) <> ? AND jornada_id=? AND fecha_entrega=? AND estado=?'); 
                            $verificar_fecha->execute([$atencion_id2,$jornada_id_editar,$fecha_entrega_editar,"1"]);
                    
                            $fe = [];
                            $fe = $verificar_fecha->fetch(PDO::FETCH_ASSOC);

                            if(is_countable($fe)){  //lamentablemente el campo de fecha de HTML parece ser distinto al de la BD, asi que esta validacion no funciona 

                                //limpiar errores
                                $error_jornada_id_editar_1 = '';
                                $error_fecha_entrega_editar_1 = '';
                                $error_cantidad_editar_1 = '';
                                $error_cantidad_editar_2 = '';

                                //limpiar valores
                                $jornada_id_editar = '';
                                $fecha_entrega_editar = '';
                                $cantidad_editar = '';
                    
                                echo '
                                <script>
                                    alert("Lo sentimos, los datos de la atencion especificada le pertenecen a otro registro");
                                </script>
                                ';
                            } else {

                                // funciones para actualizar la fecha en la tabla
                                date_default_timezone_set('America/Caracas');
                                setlocale(LC_TIME, 'spanish');
                                $fecha_actualizacion_atencion = date('Y-m-d g:i:s');

                                /* 3. Actualizar los datos en la Tabla Atencion */
                                $actualizar_atencion = $conexion->prepare("UPDATE atencion SET jornada_id = ?, fecha_entrega = ?, cantidad = ?, fecha_actualizacion = ? WHERE md5(atencion_id) = ?;");
                                $actualizar_atencion->execute(array($jornada_id_editar, $fecha_entrega_editar, $cantidad_editar, $fecha_actualizacion_atencion, $atencion_id2));

                                //limpiar errores
                                $error_jornada_id_1 = '';
                                $error_fecha_entrega_1 = '';
                                $error_cantidad_1 = '';
                                $error_cantidad_2 = '';

                                //limpiar valores
                                $jornada_id = '';
                                $fecha_entrega = '';
                                $cantidad = '';

                                $actualizado = 'actualizado';

                            }
                        }
                    }
            }


         //----------------------------------------- ATENCION : ACCION ELIMINAR ---------------------------------------------//

            if (isset($_POST['EliminarAtencion'])){
                
                $atencion_id_borrar = $_POST['atencion_id'];
                $estado = '2';

                $borrar_atencion = $conexion->prepare("UPDATE atencion SET estado =? WHERE md5(atencion_id) = ?;");
                $borrar_atencion->execute([$estado, $atencion_id_borrar]);

                $borrado = 'borrado';

            }


            
         //----------------------------------------- ATENCION : ACCION RECUPERAR ---------------------------------------------//

         if (isset($_POST['RecuperarAtencion'])){
                
            $atencion_id_recuperar = $_POST['atencion_id'];
            $estado_recuperar = '1';

            /* 1. Restaurar Atencion siempre y cuando la Jornada este Activa */
            // 1.1 Buscar el id de jornada de la atención
            $buscar_atencion = $conexion->prepare("SELECT * FROM atencion WHERE md5(atencion_id) = ?;");
            $buscar_atencion->execute([$atencion_id_recuperar]);
            $at = $buscar_atencion->fetch(PDO::FETCH_ASSOC);  

            // 1.2 Verificar mediante el id obtenido que la Jornada al cual pertenece la Atención que se deseea restaurar esta activa '1'
            $verificar_jornada_recuperar = $conexion->prepare("SELECT * FROM jornada WHERE estado = ? AND jornada_id = ?;");
            $verificar_jornada_recuperar->execute(["1",$at['jornada_id']]);
            $jor = $verificar_jornada_recuperar->fetch(PDO::FETCH_ASSOC); 

            if (is_countable($jor)) {

                // 1.3 Restaurar el registro
                $recuperar_atencion = $conexion->prepare("UPDATE atencion SET estado =? WHERE md5(atencion_id) = ?;");
                $recuperar_atencion->execute([$estado_recuperar, $atencion_id_recuperar]);

                $recuperado = 'recuperado';
            } else {
                echo '
                <script>
                        alert("Lo sentimos, no se puede restaurar esta atención porque la jornada está deshabilitada");
                </script>
                ';
            }


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



//Información de la Tabla Jornada
$jornada = $conexion->prepare('SELECT j.jornada_id, j.categoria, jc.nombre, jc.apellido, jc.ci_ps FROM jornada AS j
INNER JOIN jefe_comunidad AS jc
ON j.jefe_comunidad_id=jc.jefe_comunidad_id WHERE j.estado=? AND jc.estado=?');
$jornada->execute(["1","1"]);

//Información de la Tabla Atencion
$atencion = $conexion->prepare('SELECT a.atencion_id, j.categoria, a.fecha_entrega, a.cantidad, c.nombre, c.apellido, c.ci_ps FROM atencion AS a 
INNER JOIN jornada AS j
ON a.jornada_id=j.jornada_id 
INNER JOIN jefe_comunidad AS c
ON j.jefe_comunidad_id=c.jefe_comunidad_id 
WHERE a.estado=? AND j.estado=? AND c.estado=?
ORDER BY a.fecha_entrega');
$atencion->execute(["1","1","1"]);

/* ------------------------ Select de Formulario ingresar ----------------------------------- */
//Datos del Jefe de Comunidad -- Jornada
$row_jornada = $conexion->prepare('SELECT jefe_comunidad_id, nombre, apellido, ci_ps FROM jefe_comunidad WHERE estado=?');
$row_jornada->execute(["1"]);

//Obtener las categorias de Jornadas -- Atencion
$row_atencion = $conexion->prepare('SELECT jornada_id, categoria FROM jornada WHERE estado=?');
$row_atencion->execute(["1"]);

/* ------------------------ Select de Formulario editar ----------------------------------- */
//Datos del Jefe de Comunidad -- Jornada
$row_jornada_editar = $conexion->prepare('SELECT jefe_comunidad_id, nombre, apellido, ci_ps FROM jefe_comunidad WHERE estado=?');
$row_jornada_editar->execute(["1"]);

//Obtener las categorias de Jornadas -- Atencion
$row_atencion_editar = $conexion->prepare('SELECT jornada_id, categoria FROM jornada WHERE estado=?');
$row_atencion_editar->execute(["1"]);



/* ----------------------------------------------------- MODULO DE RECUPERACION DE JORNDADA ----------------------------------------------------------------- */
// Información de la Tabla de Recuperación 
$jornada_recuperar = $conexion->prepare('SELECT j.jornada_id, j.categoria, jc.nombre, jc.apellido, jc.ci_ps FROM jornada AS j
INNER JOIN jefe_comunidad AS jc
ON j.jefe_comunidad_id=jc.jefe_comunidad_id WHERE j.estado=?');
$jornada_recuperar->execute(["2"]);


/* ----------------------------------------------------- MODULO DE RECUPERACION DE ATENCIONES ----------------------------------------------------------------- */
// Información de la Tabla de Recuperación 
$atencion_recuperar = $conexion->prepare('SELECT a.atencion_id, j.categoria, a.fecha_entrega, a.cantidad, c.nombre, c.apellido, c.ci_ps FROM atencion AS a 
INNER JOIN jornada AS j
ON a.jornada_id=j.jornada_id 
INNER JOIN jefe_comunidad AS c
ON j.jefe_comunidad_id=c.jefe_comunidad_id 
WHERE a.estado=?
ORDER BY a.fecha_entrega');
$atencion_recuperar->execute(["2"]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jornadas</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">
    
    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/jornada.css">

     <!-- Links de Google Fonts para Logo-->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet"> 

</head>

<body>

<div class="container-fluid">
    <div class="row">
        <nav class="navbar bg-light shadow fixed-top d-flex justify-content-between" id="categoria">
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


            <!-- inicio -->
           <!-- Tabla Categorias de Jornada -->

        <div class="container mover-derecha-tabla col-8 small" >
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Jornadas</h3>
                <img src="../src/img/jornada_2.png" class="img-fluid d-flex justify-content-center col-5 jornada_img" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir" data-bs-toggle="modal" data-bs-target="#modalJornadaAñadir" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos-jornada" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-info">
                                <th class="text-center">Categoria</th>
                                <th class="text-center">Responsable</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($jornada as $j) {
		                    ?>
		                    <tr>
			                    <td><?php echo $j['categoria']; ?></td>
			                    <td><?php echo $j['nombre']. " " .$j['apellido']. " | " .$j['ci_ps']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="jornada.php" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalJornadaEditar" data-bs-id="<?= md5($j['jornada_id']); ?>"> 
                                    <i class=" fa-solid fa-pencil"></i></a>

                                    <a href="jornada.php" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalJornadaEliminar" data-bs-id="<?= md5($j['jornada_id']); ?>"> 
                                    <i class="fa-solid fa-trash-can"></i></a>
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



     <!--------------------------------------------------------------- MODULO DE RECUPERACION JORNADA ----------------------------------------------------------------------------->
     
                 <!-- inicio -->
           <!-- Tabla Recuperacion Jornada -->

           <div class="container mover-derecha-tabla col-8 pt-5 pb-3 small" >
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Registros Eliminados</h3>
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="tabla_recuperacion_jornada" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-dark">
                                <th class="text-center">Categoria</th>
                                <th class="text-center">Responsable</th>
                                <th class="text-center no-exportar">Restaurar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($jornada_recuperar as $j) {
		                    ?>
		                    <tr>
			                    <td><?php echo $j['categoria']; ?></td>
			                    <td><?php echo $j['nombre']. " " .$j['apellido']. " | " .$j['ci_ps']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="jornada.php" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalJornadaRecuperar" data-bs-id="<?= md5($j['jornada_id']); ?>"> 
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
     

     <!-------------------------------------------------------------------------------------------------------------------------------------------->




            <!-- fin -->

           <!-- Tabla Registros de Atenciones -->

        <div class="container mover-derecha-tabla col-10 small" id="atencion">
               
        <hr class="mt-5 line">
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark text-center lead fs-2">Registro de Atenciones</h3>
                <img src="../src/img/atencion.png" class="img-fluid d-flex justify-content-center col-4" alt="">
            </div>
            
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">

                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir" data-bs-toggle="modal" data-bs-target="#modalAtencionAñadir" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir
                        </button>
                    </div>
                    <div>
                        <button id="excel-atencion" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf-atencion" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print-atencion" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos-atencion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-info">
                                <th class="text-center">Categoria</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Responsable</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($atencion as $a) {
		                    ?>
		                    <tr>
			                    <td><?php echo $a['categoria']; ?></td>
                                <td><?php echo $a['fecha_entrega']; ?></td>
			                    <td><?php echo $a['cantidad']; ?></td>
			                    <td><?php echo $a['nombre']. " " .$a['apellido']. " | " .$a['ci_ps']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="jornada.php" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalAtencionEditar" data-bs-id="<?= md5($a['atencion_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="jornada.php" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalAtencionEliminar" data-bs-id="<?= md5($a['atencion_id']); ?>"> 
                                    <i class="fa-solid fa-trash-can"></i></a>
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

        </div>






     <!--------------------------------------------------------------- MODULO DE RECUPERACION ATENCION ----------------------------------------------------------------------------->
        
            <!-- fin -->

           <!-- Tabla Recuperación de Atenciones -->

           <div class="container mover-derecha-tabla col-10 pt-5 pb-3 small" id="atencion">
                   <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                       <h3 class="text-dark text-center lead fs-2">Registro Eliminados</h3>
                    </div>
                   
                   <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-5">
       
                       <div class=" col-12 bg-white table-responsive">
                           <table id="tabla_recuperacion_atencion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                               <thead>
                                    <tr class="table-dark">
                                       <th class="text-center">Categoria</th>
                                       <th class="text-center">Fecha</th>
                                       <th class="text-center">Cantidad</th>
                                       <th class="text-center">Responsable</th>
                                       <th class="text-center no-exportar">Restaurar</th>
                                    </tr>
                               </thead>
                               <tbody>
       
                                   <?php
                                   foreach ($atencion_recuperar as $a) {
                                   ?>
                                   <tr>
                                       <td><?php echo $a['categoria']; ?></td>
                                       <td><?php echo $a['fecha_entrega']; ?></td>
                                       <td><?php echo $a['cantidad']; ?></td>
                                       <td><?php echo $a['nombre']. " " .$a['apellido']. " | " .$a['ci_ps']; ?></td>
                                       <!--------- botones ------------> 
                                       <td class="d-flex justify-content-center">
                                           <a href="jornada.php" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                           data-bs-target="#modalAtencionRecuperar" data-bs-id="<?= md5($a['atencion_id']); ?>"> 
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
       
               </div>


     <!-------------------------------------------------------------------------------------------------------------------------------------------->



      <!----------------------------------------- MODAL JORNADA ---------------------------------------->

        <!-- Modal Ingresar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalJornadaAñadir" tabindex="-1" aria-labelledby="modalJornadaAñadirLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalJornadaAñadirLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="jornada.php" method="POST" id="formulario">
                        <div class="modal-content border-white">

                            <img src="../src/img/jornada_modal.png" class="w-25 mb-4 modal_imagen" alt="">
                            

                            <div class="mb-3">
                                <label for="categoria" class="form-label fw-medium">Categoria:</label>
                                <input type="text" name="categoria" id="categoria" class="form-control" value="<?php echo $categoria; ?>">
                                       <?php if(!empty($error_categoria_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_categoria_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_categoria_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_categoria_2" ?></p>
                                            <?php }
                                        }; ?>
                            </div>

                            <div class="mb-3">
                                <label for="Jefe_comunidad_id" class="form-label fw-medium">Responsable:</label>
                                <select name="jefe_comunidad_id" id="jefe_comunidad_id" class="form-select" required value="<?php echo $jefe_comunidad_id; ?>">
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    foreach ($row_jornada as $r){
                                    ?> 
                                        <option value="<?php echo $r['jefe_comunidad_id']; ?>"><?php echo $r['nombre']. " " .$r['apellido']. " | " .$r['ci_ps']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                
                                    <?php if(!empty($error_jefe_comunidad_id_1)): ?>
                                    <p class='small text-danger error'><?php echo "$error_jefe_comunidad_id_1" ?></p>
                                    <?php endif; ?> 
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" name="AñadirJornada" class="btn btn-primary"><i class=" p-1 fa-solid fa-floppy-disk"></i>Guardar</button>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalJornadaEditar" tabindex="-1" aria-labelledby="modalJornadaEditarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalJornadaEditarLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="jornada.php" method="POST">
                        <div class="modal-content border-white">

                            <img src="../src/img/jornada_modal.png" class="w-25 mb-4 modal_imagen" alt="">

                            <input type="hidden" id="jornada_id" name="jornada_id">

                            <div class="mb-3">
                                <label for="categoria" class="form-label fw-medium">Categoria:</label>
                                <input type="text" name="categoria" id="categoria" class="form-control">
                                    <?php if(!empty($error_categoria_editar_1)){ ?>
                                    <p class='small text-danger error'><?php echo "$error_categoria_editar_1" ?></p>
                                    <?php } else {
                                    if(!empty($error_categoria_editar_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_categoria_editar_2" ?></p>
                                        <?php }
                                    }; ?>
                            </div>

                            <div class="mb-3">
                                <label for="Jefe_comunidad_id" class="form-label fw-medium">Responsable:</label>
                                <select name="jefe_comunidad_id" id="jefe_comunidad_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    foreach ($row_jornada_editar as $r){
                                    ?> 
                                        <option value="<?php echo $r['jefe_comunidad_id']; ?>"><?php echo $r['nombre']. " " .$r['apellido']. " | " .$r['ci_ps']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>

                                    <?php if(!empty($error_jefe_comunidad_id_editar_1)): ?>
                                    <p class='small text-danger error'><?php echo "$error_jefe_comunidad_id_editar_1" ?></p>
                                    <?php endif; ?> 
                            </div>
                            
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" id= "ActualizarJornada" name="ActualizarJornada" class="btn btn-primary"><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar cambios</button>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalJornadaEliminar" tabindex="-1" aria-labelledby="modalJornadaEliminarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalJornadaEliminarLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                  <p>Al eliminar la Jornada, se eliminarán también los datos de las Atenciones.</p>
                    <h6>¿Seguro que desea eliminar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="jornada.php" method="POST">
                            <input type="hidden" name="jornada_id" id="jornada_id" >
                            
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="EliminarJornada" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 

        <!-- Modal Eliminar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalJornadaRecuperar" tabindex="-1" aria-labelledby="modalJornadaRecuperarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalJornadaRecuperarLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                  <p>Al recuperar la Jornada, se recuperaran también los datos de las Atenciones.</p>
                    <h6>¿Seguro que desea Recuperar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="jornada.php" method="POST">
                            <input type="hidden" name="jornada_id" id="jornada_id" >
                            
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="RecuperarJornada" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash-arrow-up"></i>Recuperar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 



      <!----------------------------------------- MODAL ATENCIÓN ---------------------------------------->

        <!-- Modal Ingresar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalAtencionAñadir" tabindex="-1" aria-labelledby="modalAtencionAñadirLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalAtencionAñadirLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body"> 
                    <form action="jornada.php" method="POST" id="formulario">
                        <div class="modal-content border-white">
                           
                            <img class="w-25 mb-4 modal_imagen" src="../src/img/atencion-modal.png" alt="">
                           
                            <div class="mb-3">
                                <!-------------------------------------------------------->
                                <label for="Jornada_id" class="form-label fw-medium">Categoria:</label>
                                <select name="jornada_id" id="jornada_id" class="form-select" required value="<?php echo $jornada_id; ?>">
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    foreach ($row_atencion as $r){
                                    ?> 
                                        <option value="<?php echo $r['jornada_id']; ?>"><?php echo $r['categoria']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>

                                    <?php if(!empty($error_jornada_id_1)){ ?>
                                    <p class='small text-danger error'><?php echo "$error_jornada_id_1" ?></p>
                                    <?php }; ?>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">  <!---------------------------------->
                                    <label for="fecha_entrega" class="form-label fw-medium">Fecha de Entrega:</label>
                                    <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" value="<?php echo $fecha_entrega; ?>">
                                        <?php if(!empty($error_fecha_entrega_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_fecha_entrega_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-6">  <!---------------------------------->
                                    <label for="cantidad" class="form-label fw-medium">Cantidad:</label>
                                    <input type="number" name="cantidad" id="cantidad" class="form-control" value="<?php echo $cantidad; ?>">
                                        <?php if(!empty($error_cantidad_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_cantidad_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_cantidad_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_cantidad_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" name="AñadirAtencion" class="btn btn-primary"><i class=" p-1 fa-solid fa-floppy-disk"></i>Guardar</button>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalAtencionEditar" tabindex="-1" aria-labelledby="modalAtencionEditarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalAtencionEditarLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="jornada.php" method="POST">
                        <div class="modal-content border-white">

                            <input type="hidden" id="atencion_id" name="atencion_id">
                            
                            <img class="w-25 mb-4 modal_imagen" src="../src/img/atencion-modal.png" alt="">
                           
                            <div class="mb-3">
                                <!-------------------------------------------------------->
                                <label for="Jornada_id" class="form-label fw-medium">Categoria:</label>
                                <select name="jornada_id" id="jornada_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    foreach ($row_atencion_editar as $r){
                                    ?> 
                                        <option value="<?php echo $r['jornada_id']; ?>"><?php echo $r['categoria']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>

                                    <?php if(!empty($error_jornada_id_editar_1)){ ?>
                                    <p class='small text-danger error'><?php echo "$error_jornada_id_editar_1" ?></p>
                                    <?php }; ?>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">  <!---------------------------------->
                                    <label for="fecha_entrega" class="form-label fw-medium">Fecha de Entrega:</label>
                                    <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" >
                                        <?php if(!empty($error_fecha_entrega_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_fecha_entrega_editar_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-6">  <!---------------------------------->
                                    <label for="cantidad" class="form-label fw-medium">Cantidad:</label>
                                    <input type="number" name="cantidad" id="cantidad" class="form-control">
                                        <?php if(!empty($error_cantidad_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_cantidad_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_cantidad_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_cantidad_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>
                            
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" id= "ActualizarAtencion" name="ActualizarAtencion" class="btn btn-primary"><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar cambios</button>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalAtencionEliminar" tabindex="-1" aria-labelledby="modalAtencionEliminarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalAtencionEliminarLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea eliminar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="jornada.php" method="POST">
                            <input type="hidden" name="atencion_id" id="atencion_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="EliminarAtencion" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 

        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalAtencionRecuperar" tabindex="-1" aria-labelledby="modalAtencionRecuperarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalAtencionRecuperarLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea recuperar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="jornada.php" method="POST">
                            <input type="hidden" name="atencion_id" id="atencion_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="RecuperarAtencion" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash-arrow-up"></i>Recuperar</button>
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
    <script src="../src/js/jornadas_atenciones.js"></script>


    <script>                                           
/* ---------------------------------------------------- JORNADA: EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalJornadaEditar = document.getElementById('modalJornadaEditar')  //id de la ventana modal Editar Registro
    let modalJornadaEliminar = document.getElementById('modalJornadaEliminar')  //id de la ventana modal Eliminar Registro
    let modalJornadaRecuperar = document.getElementById('modalJornadaRecuperar')


    modalJornadaEditar.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let jornada_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputJornadaId = modalJornadaEditar.querySelector('.modal-body #jornada_id')              //selecionamos la clase y el id presentes en el formulario
        let inputCategoria = modalJornadaEditar.querySelector('.modal-body #categoria')
        let inputJefeComunidadId = modalJornadaEditar.querySelector('.modal-body #jefe_comunidad_id')

        let url = "jornada_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('jornada_id', jornada_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputJornadaId.value = data.jornada_id      //lo que viene a continuacion de data. es el campo al cual se llama en la consulta a la BD
            inputCategoria.value = data.categoria
            inputJefeComunidadId.value = data.jefe_comunidad_id

            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- JORNADA: ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalJornadaEliminar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let jornada_id = button.getAttribute('data-bs-id') 
        
        modalJornadaEliminar.querySelector('.modal-footer #jornada_id').value = jornada_id

    })

/* ---------------------------------------------------- JORNADA: RECUPERAR REGISTRO ------------------------------------------------------------------------- */
modalJornadaRecuperar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let jornada_id = button.getAttribute('data-bs-id') 
        
        modalJornadaRecuperar.querySelector('.modal-footer #jornada_id').value = jornada_id

    })








/* ---------------------------------------------------- AENCION: EDITAR REGISTRO ------------------------------------------------------------------------- */
let modalAtencionEditar = document.getElementById('modalAtencionEditar')  //id de la ventana modal Editar Registro
    let modalAtencionEliminar = document.getElementById('modalAtencionEliminar')  //id de la ventana modal Eliminar Registro
    let modalAtencionRecuperar = document.getElementById('modalAtencionRecuperar')


    modalAtencionEditar.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let atencion_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputAtencionId = modalAtencionEditar.querySelector('.modal-body #atencion_id')              //selecionamos la clase y el id presentes en el formulario
        let inputJornadaId = modalAtencionEditar.querySelector('.modal-body #jornada_id')
        let inputFechaEntrega = modalAtencionEditar.querySelector('.modal-body #fecha_entrega')
        let inputCantidad = modalAtencionEditar.querySelector('.modal-body #cantidad')

        let url = "jornada_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('atencion_id', atencion_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputAtencionId.value = data.atencion_id      //lo que viene a continuacion de data. es el campo al cual se llama en la consulta a la BD
            inputJornadaId.value = data.jornada_id
            inputFechaEntrega.value = data.fecha_entrega
            inputCantidad.value = data.cantidad

            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- ATENCION: ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalAtencionEliminar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let atencion_id = button.getAttribute('data-bs-id') 
        
        modalAtencionEliminar.querySelector('.modal-footer #atencion_id').value = atencion_id

    })

    /* ---------------------------------------------------- ATENCION: ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalAtencionRecuperar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let atencion_id = button.getAttribute('data-bs-id') 
        
        modalAtencionRecuperar.querySelector('.modal-footer #atencion_id').value = atencion_id

    })


</script>
</body>
</html>