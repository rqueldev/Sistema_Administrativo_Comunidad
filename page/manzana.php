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

    $rescatar_sesion = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña, rol_id FROM usuario WHERE nombre_usuario=? AND estado=?');   
    $rescatar_sesion->execute([$_SESSION['user'],"1"]);

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
            $numero_manzana = '';
            $cedula = '';
            $calle = '';

            if (isset($_POST['Añadir'])){

                $numero_manzana = valida_campo($_POST['numero_manzana']);
                $cedula = valida_campo($_POST['cedula']);
                $calle = valida_campo($_POST['calle']);

                if (empty($numero_manzana)) {
                    $error_numero_manzana_1 = 'Coloque una manzana';
                } else {
                    if (strlen($numero_manzana) > 4) {
                        $error_numero_manzana_2 = 'El numero de manzana es muy largo';
                    }
                } 

                if (empty($cedula)) {
                    $error_cedula_1 = 'Coloque una cédula';
                } else {
                    if (strlen($cedula) > 8) {
                        $error_cedula_2 = 'La cédula no puede tener más de 8 caracteres';
                    }
                }

                if (empty($calle)) {
                    $error_calle_1 = 'Coloque una calle';
                }else {
                    if (strlen($calle) > 90) {
                        $error_calle_2 = 'El nombre de la calle es muy largo';
                    }
                } 


                /* 1. Verificar que no hay errores para insertar el registro */
                if (empty($error_numero_manzana_1) and empty($error_numero_manzana_2) and empty($error_cedula_1) and empty($error_cedula_2) 
                    and empty($error_calle_1) and empty($error_calle_2)){

                        /* 2. Se verifica si existe una manzana en la BD con los mismos datos ingresados */
                        if (!empty($_POST['numero_manzana']) && !empty($_POST['cedula'])) {  
                            
                            // 2.1 Comprobar Numero de Manzana
                            $verificar_numero = $conexion->prepare('SELECT * FROM manzana WHERE numero_manzana=? AND estado=?');  
                            $verificar_numero->execute([$numero_manzana,"1"]);
                    
                            $r = [];
                            $r = $verificar_numero->fetch(PDO::FETCH_ASSOC);

                            if (is_countable($r)) {
                                
                                //limpiar errores
                                $error_numero_manzana_1 = '';
                                $error_numero_manzana_2 = '';
                                $error_cedula_1 = '';
                                $error_cedula_2 = '';
                                $error_calle_1 = '';
                                $error_calle_2 = '';

                                //limpiar valores
                                $numero_manzana = '';
                                $cedula = '';
                                $calle = '';

                                echo '
                                <script>
                                    alert("Lo sentimos, el numero de manzana le corresponde a otro registro");
                                </script>
                                ';
                            }
                            
                            // 2.2 Comprobar Cedula
                            $verificar_cedula = $conexion->prepare('SELECT * FROM manzana WHERE cedula=? AND estado=?');  
                            $verificar_cedula->execute([$cedula,"1"]);
                    
                            $s = [];
                            $s = $verificar_cedula->fetch(PDO::FETCH_ASSOC);

                            if (is_countable($s)) {
                                
                                //limpiar errores
                                $error_numero_manzana_1 = '';
                                $error_numero_manzana_2 = '';
                                $error_cedula_1 = '';
                                $error_cedula_2 = '';
                                $error_calle_1 = '';
                                $error_calle_2 = '';

                                //limpiar valores
                                $numero_manzana = '';
                                $cedula = '';
                                $calle = '';

                                echo '
                                <script>
                                    alert("Lo sentimos, la cédula ingresada le corresponde a otro registro");
                                </script>
                                ';
                            }

                            if (!is_countable($r) and !is_countable($s)) {
                               /* echo '
                                <script>
                                    alert("EXCELENTE, la cedula y manzana ingresada no le corresponden a mas nadie");
                                </script>
                                ';*/

                                /* 3. Se insertan los datos en la Tabla Mnazana */
                                $introducir = $conexion->prepare("INSERT INTO manzana(numero_manzana,cedula,calle) VALUES (?,?,?)");
                                $introducir->execute([$numero_manzana,$cedula,$calle]);

                                //limpiar errores
                                $error_numero_manzana_1 = '';
                                $error_numero_manzana_2 = '';
                                $error_cedula_1 = '';
                                $error_cedula_2 = '';
                                $error_calle_1 = '';
                                $error_calle_2 = '';

                                //limpiar valores
                                $numero_manzana = '';
                                $cedula = '';
                                $calle = '';

                                $registrado = 'registrado';
                            }
                        }    
                    }
            }



          //----------------------------------------- ACCION EDITAR ---------------------------------------------//


            if (isset($_POST['Actualizar'])){

                $manzana_id2 = $_POST['manzana_id'];
                $numero_manzana_editar = valida_campo($_POST['numero_manzana']);
                $cedula_editar = valida_campo($_POST['cedula']);
                $calle_editar = valida_campo($_POST['calle']);

                if (empty($numero_manzana_editar)) {
                    $error_numero_manzana_editar_1 = 'Coloque una manzana';
                } else {
                    if (strlen($numero_manzana_editar) > 4) {
                        $error_numero_manzana_editar_2 = 'El numero de manzana es muy largo';
                    }
                } 

                if (empty($cedula_editar)) {
                    $error_cedula_editar_1 = 'Coloque una cédula';
                } else {
                    if (strlen($cedula_editar) > 8) {
                        $error_cedula_editar_2 = 'La cédula no puede tener más de 8 caracteres';
                    }
                }

                if (empty($calle_editar)) {
                    $error_calle_editar_1 = 'Coloque una calle';
                }else {
                    if (strlen($calle_editar) > 90) {
                        $error_calle_editar_2 = 'El nombre de la calle es muy largo';
                    }
                } 


                /* 1. Verificar que no hay errores para actualizar el registro */
                if (empty($error_numero_manzana_editar_1) and empty($error_numero_manzana_editar_2) and empty($error_cedula_editar_1) and empty($error_cedula_editar_2) 
                    and empty($error_calle_editar_1) and empty($error_calle_editar_2)){

                        /* 2. Se verifica si existe una manzana en la BD con los mismos datos ingresados */
                        if (!empty($_POST['numero_manzana']) && !empty($_POST['cedula'])) {  
                    
                            // 2.1 Comprobar Numero de Manzana
                            $verificar_numero_editar = $conexion->prepare('SELECT * FROM manzana WHERE md5(manzana_id) <> ? AND numero_manzana=? AND estado=?');  
                            $verificar_numero_editar->execute([$manzana_id2,$numero_manzana_editar,"1"]);
            
                            $n = [];
                            $n = $verificar_numero_editar->fetch(PDO::FETCH_ASSOC);

                            if (is_countable($n)) {
                        
                                //limpiar errores
                                $error_numero_manzana_editar_1 = '';
                                $error_numero_manzana_editar_2 = '';
                                $error_cedula_editar_1 = '';
                                $error_cedula_editar_2 = '';
                                $error_calle_editar_1 = '';
                                $error_calle_editar_2 = '';

                                //limpiar valores
                                $numero_manzana_editar = '';
                                $cedula_editar = '';
                                $calle_editar = '';

                                echo '
                                <script>
                                    alert("Lo sentimos, el numero de manzana le corresponde a otro registro");
                                </script>
                                ';
                            }
                    
                            // 2.2 Comprobar Cedula
                            $verificar_cedula_editar = $conexion->prepare('SELECT * FROM manzana WHERE md5(manzana_id) <> ? AND cedula=? AND estado=?');  
                            $verificar_cedula_editar->execute([$manzana_id2,$cedula_editar,"1"]);
            
                            $t = [];
                            $t = $verificar_cedula_editar->fetch(PDO::FETCH_ASSOC);

                            if (is_countable($t)) {
                        
                                //limpiar errores
                                $error_numero_manzana_editar_1 = '';
                                $error_numero_manzana_editar_2 = '';
                                $error_cedula_editar_1 = '';
                                $error_cedula_editar_2 = '';
                                $error_calle_editar_1 = '';
                                $error_calle_editar_2 = '';

                                //limpiar valores
                                $numero_manzana_editar = '';
                                $cedula_editar = '';
                                $calle_editar = '';

                                echo '
                                <script>
                                    alert("Lo sentimos, la cedula ingresada le corresponde a otro registro");
                                </script>
                                ';
                            }

                            if (!is_countable($n) and !is_countable($t)) {
                                
                                // funciones para actualizar la fecha en la tabla
                                date_default_timezone_set('America/Caracas');
                                setlocale(LC_TIME, 'spanish');
                                $fecha_actualizacion = date('Y-m-d g:i:s');

                                /* 3. Se actualiza el registro con los datos "nuevos" */
                                $actualizar = $conexion->prepare("UPDATE manzana SET numero_manzana = ?, cedula = ?, calle = ?, fecha_actualizacion = ? WHERE md5(manzana_id) = ?;");
                                $actualizar->execute([$numero_manzana_editar, $cedula_editar, $calle_editar,$fecha_actualizacion,$manzana_id2]);
                                

                                /* 4. Editar tambien la cedula en la tabla Jefe de calle con la cedula "nueva" que se desea actualizar en la Tabla Manzana. Esto sse realiza porque la tabla manzana es como una guia que valida la existencia de los jefes de calle con sus manzanas a cargo*/
                                $actualizar_cedula_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET ci_ps = ? WHERE md5(manzana_id) = ?;");
                                $actualizar_cedula_jefe_calle->execute([$cedula_editar, $manzana_id2]);

                                
                                //limpiar errores
                                $error_numero_manzana_editar_1 = '';
                                $error_numero_manzana_editar_2 = '';
                                $error_cedula_editar_1 = '';
                                $error_cedula_editar_2 = '';
                                $error_calle_editar_1 = '';
                                $error_calle_editar_2 = '';

                                //limpiar valores
                                $numero_manzana_editar = '';
                                $cedula_editar = '';
                                $calle_editar = '';

                                $actualizado = 'actualizado';
                            }
                        }    
                    }
            }

         //----------------------------------------- ACCION ELIMINAR ---------------------------------------------//
            if (isset($_POST['Eliminar'])) {
            
                $manzana_id_borrar = $_POST['manzana_id'];
                $estado = '2';

                /* 1. Eliminamos manzana */
                $borrar_manzana = $conexion->prepare("UPDATE manzana SET estado =? WHERE md5(manzana_id) = ?;");
                $borrar_manzana->execute([$estado, $manzana_id_borrar]);

                /* 2. Eliminamos tabla involucrada: Jefe de Calle*/
                $borrar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET estado =? WHERE md5(manzana_id) = ?;");
                $borrar_jefe_calle->execute([$estado,$manzana_id_borrar]);


                /* 3. Eliminamos tabla involucrada: Usuario por medio del id solicitado */
                // Buscamos el id de usuario en la tabla Jefe de Calle 
                $buscar_jefe_calle = $conexion->prepare("SELECT * FROM jefe_calle WHERE md5(manzana_id) = ?;");
                $buscar_jefe_calle->execute([$manzana_id_borrar]);
                $o = $buscar_jefe_calle->fetch(PDO::FETCH_ASSOC);
                
                if (is_countable($o)) {
                    $borrar_usuario = $conexion->prepare("UPDATE usuario SET estado =? WHERE usuario_id = ?;");
                    $borrar_usuario->execute([$estado,$o['usuario_id']]);
                }


                /* 4. Eliminamos tabla involucrada: vivienda */
                $borrar_vivienda = $conexion->prepare("UPDATE vivienda SET estado =? WHERE md5(manzana_id) = ?;");
                $borrar_vivienda->execute([$estado, $manzana_id_borrar]);
    

                /* 5. Eliminamos tabla involucrada: habitante */
                $borrar_habitante = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h
                SET h.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id
                AND md5(m.manzana_id)=?;");
                $borrar_habitante->execute([$estado,$manzana_id_borrar]);
                

                /* 6. Eliminamos clasificacion de habitante: Estatus Laboral */
                $borrar_estatus_laboral = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, habitante_estatus_laboral as hes
                SET hes.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=hes.habitante_id AND md5(m.manzana_id)=?;");
                $borrar_estatus_laboral->execute([$estado,$manzana_id_borrar]);

                
                /* 7. Eliminamos clasificacion de habitante: Incapacidad */
                $borrar_incapacidad = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, habitante_incapacidad as hi
                SET hi.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=hi.habitante_id AND md5(m.manzana_id)=?;");
                $borrar_incapacidad->execute([$estado,$manzana_id_borrar]);


                /* 8. Eliminamos clasificacion de habitante: Votante */
                $borrar_votante = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, votante as vt
                SET vt.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=vt.habitante_id AND md5(m.manzana_id)=?;");
                $borrar_votante->execute([$estado,$manzana_id_borrar]);
                
                /* 9. Eliminamos clasificacion de habitante: Vocero */
                $borrar_vocero = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, vocero as vo
                SET vo.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=vo.habitante_id AND md5(m.manzana_id)=?;");
                $borrar_vocero->execute([$estado,$manzana_id_borrar]);

                /* 10. Eliminamos clasificacion de habitante: Jefe de Hogar */
                $borrar_jefe_hogar = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, jefe_hogar as jh
                SET jh.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=jh.habitante_id AND md5(m.manzana_id)=?;");
                $borrar_jefe_hogar->execute([$estado,$manzana_id_borrar]);

                $borrado = 'borrado';
            } 

            //----------------------------------------- ACCION RESTAURAR ---------------------------------------------//
            if (isset($_POST['Recuperar'])) {
            
                $manzana_id_recuperar = $_POST['manzana_id'];
                $estado_recuperar = '1';

                /* 1. Recuperar manzana */
                $recuperar_manzana = $conexion->prepare("UPDATE manzana SET estado =? WHERE md5(manzana_id) = ?;");
                $recuperar_manzana->execute([$estado_recuperar, $manzana_id_recuperar]);

                /* 2. Recuperar tabla involucrada: Jefe de Calle*/
                $recuperar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET estado =? WHERE md5(manzana_id) = ?;");
                $recuperar_jefe_calle->execute([$estado_recuperar,$manzana_id_recuperar]);


                /* 3. Recuperar tabla involucrada: Usuario por medio del id solicitado */
                // Buscamos el id de usuario en la tabla Jefe de Calle 
                $buscar_jefe_calle_recuperar = $conexion->prepare("SELECT * FROM jefe_calle WHERE md5(manzana_id) = ?;");
                $buscar_jefe_calle_recuperar->execute([$manzana_id_recuperar]);
                $o = $buscar_jefe_calle_recuperar->fetch(PDO::FETCH_ASSOC);
                
                if (is_countable($o)) {
                    $recuperar_usuario = $conexion->prepare("UPDATE usuario SET estado =? WHERE usuario_id = ?;");
                    $recuperar_usuario->execute([$estado_recuperar,$o['usuario_id']]);
                }


                /* 4. Recuperar tabla involucrada: vivienda */
                $recuperar_vivienda = $conexion->prepare("UPDATE vivienda SET estado =? WHERE md5(manzana_id) = ?;");
                $recuperar_vivienda->execute([$estado_recuperar, $manzana_id_recuperar]);
    
                /* 5. Recuperar tabla involucrada: habitante */
                $recuperar_habitante = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h
                SET h.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id
                AND md5(m.manzana_id)=?;");
                $recuperar_habitante->execute([$estado_recuperar,$manzana_id_recuperar]);
                
                /* 6. Recuperar clasificacion de habitante: Estatus Laboral */
                $recuperar_estatus_laboral = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, habitante_estatus_laboral as hes
                SET hes.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=hes.habitante_id AND md5(m.manzana_id)=?;");
                $recuperar_estatus_laboral->execute([$estado_recuperar,$manzana_id_recuperar]);

                /* 7. Recuperar clasificacion de habitante: Incapacidad */
                $recuperar_incapacidad = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, habitante_incapacidad as hi
                SET hi.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=hi.habitante_id AND md5(m.manzana_id)=?;");
                $recuperar_incapacidad->execute([$estado_recuperar,$manzana_id_recuperar]);

                /* 8. Recuperar clasificacion de habitante: Votante */
                $recuperar_votante = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, votante as vt
                SET vt.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=vt.habitante_id AND md5(m.manzana_id)=?;");
                $recuperar_votante->execute([$estado_recuperar,$manzana_id_recuperar]);
                
                /* 9. Recuperar clasificacion de habitante: Vocero */
                $recuperar_vocero = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, vocero as vo
                SET vo.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=vo.habitante_id AND md5(m.manzana_id)=?;");
                $recuperar_vocero->execute([$estado_recuperar,$manzana_id_recuperar]);

                /* 10. Recuperar clasificacion de habitante: Jefe de Hogar */
                $recuperar_jefe_hogar = $conexion->prepare("UPDATE manzana as m, vivienda as v, habitante as h, jefe_hogar as jh
                SET jh.estado =?
                WHERE m.manzana_id=v.manzana_id AND v.vivienda_id=h.vivienda_id AND h.habitante_id=jh.habitante_id AND md5(m.manzana_id)=?;");
                $recuperar_jefe_hogar->execute([$estado_recuperar,$manzana_id_recuperar]);
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


// Informacion de la Tabla manzana
$manzana = $conexion->prepare('SELECT manzana_id, numero_manzana, cedula, calle,
fecha_creacion, fecha_actualizacion FROM manzana where estado =?');
$manzana->execute(["1"]);

//Informacion de la tarjetas
$card_jefe_calle = $conexion->prepare('SELECT m.numero_manzana, j.nombre, j.apellido, (SELECT nombre_usuario FROM usuario WHERE usuario_id = j.usuario_id) as nb_usuario FROM jefe_calle AS j
INNER JOIN manzana AS m
ON j.manzana_id=m.manzana_id WHERE j.estado=? AND m.estado=?
ORDER BY m.numero_manzana');
$card_jefe_calle->execute(["1","1"]);

/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */
// Informacion de la Tabla de Recuperación
$recuperar_manzana = $conexion->prepare('SELECT manzana_id, numero_manzana, cedula, calle,
fecha_creacion, fecha_actualizacion FROM manzana where estado =?');
$recuperar_manzana->execute(["2"]);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge">-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manzana</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/manzanas.css">

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

                    <li class="nav-item my-sm-1 my-2" id="btn">
                        <a href="manzana.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-apple-whole "></i>
                            <span class="ms-2 d-none d-sm-inline boton-manzana">Manzanas</span>
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


                    <!-- cards -->
        <div class=" container-fluid col-11 bajar">

            <div class="col-10">
                <h3 class="text-dark d-flex justify-content-center m-3 mover-derecha lead fs-2"> Manzanas</h3>
            </div>

            <div class="cards d-flex flex-wrap col-11 tarjeta mover-derecha">
                
             <!-- inicio -->
               <?php
               foreach ($card_jefe_calle as $j){
               ?> 
                 <a href="inicio-lider-calle.php?id_jefe=<?php echo $j['nb_usuario'] ?>" class="card col-2 px-3 py-1 m-3 d-flex flex-row text-decoration-none justify-content-between align-items-center shadow-sm tarjetita ">
                   <div class="card-content">
                       <h6 class="number pl-1 "><?php echo $j['numero_manzana']; ?></h6>
                       <h6 class="lider-name pl-1  "><?php echo $j['nombre']." ".$j['apellido']; ?></h6>
                   </div>
                   <div class="icon-box">
                       <img src="../src/img/tree-city-solid.svg" alt="Manzana">  
                   </div>
                 </a>
               
               <?php
               }
               ?>
             <!-- fin -->

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
           <!-- Tabla Manzana -->

        
           <div class="container mover-derecha-tabla col-12 pb-3  small">
            <div class="col-12 pt-0 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2"> Datos de las Manzanas</h3>
                <img src="../src/img/manzana.png" class="img-fluid d-flex justify-content-center col-4" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive ">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir " data-bs-toggle="modal" data-bs-target="#modalManzana" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success" type="button" aria-label="generar-excel"><i class="fa-solid fa-file-excel"></i></button>               <!--aria-label permite indicar el propósto que cumplira el boton-->
                        <button id="pdf" class="btn btn-danger" type="button" aria-label="generar-pdf"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info" type="button" aria-label="generar-print"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos_manzana" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-info">
                                <th class="text-center">Nº Manzana</th>
                                <th class="text-center">Cédula</th>
                                <th class="text-center">Calle</th>
                                <th class="text-center">Creacion</th>
                                <th class="text-center">Actualización</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($manzana as $m) {
		                    ?>
		                    <tr>
			                    <td><?php echo $m['numero_manzana']; ?></td>
			                    <td><?php echo $m['cedula']; ?></td>
			                    <td><?php echo $m['calle']; ?></td>
			                    <td><?php echo $m['fecha_creacion']; ?></td>
			                    <td><?php echo $m['fecha_actualizacion']; ?></td>
                                <!--------- botones ------------> 
                                <td class="d-flex justify-content-around">
                                    <a href="manzana.php" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarManzana" data-bs-id="<?= md5($m['manzana_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="manzana.php" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminarManzana" data-bs-id="<?= md5($m['manzana_id']); ?>"> 
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
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-5">
                
                <div class=" col-12 bg-white table-responsive ">
                    <table id="tabla_recuperacion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-dark">
                                <th class="text-center">Nº Manzana</th>
                                <th class="text-center">Cédula</th>
                                <th class="text-center">Calle</th>
                                <th class="text-center">Creacion</th>
                                <th class="text-center">Actualización</th>
                                <th class="text-center no-exportar">Restaurar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($recuperar_manzana as $r) {
		                    ?>
		                    <tr>
			                    <td><?php echo $r['numero_manzana']; ?></td>
			                    <td><?php echo $r['cedula']; ?></td>
			                    <td><?php echo $r['calle']; ?></td>
			                    <td><?php echo $r['fecha_creacion']; ?></td>
			                    <td><?php echo $r['fecha_actualizacion']; ?></td>
                                <!--------- botones ------------> 
                                <td class="d-flex justify-content-center">
                                    <a href="manzana.php" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalRecuperarManzana" data-bs-id="<?= md5($r['manzana_id']); ?>"> 
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



        <!-- Modal Ingresar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalManzana" tabindex="-1" aria-labelledby="modalManzanaLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalManzanaLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="manzana.php" method="POST" id="formulario">
                        <div class="modal-content border-white">

                            <img src="../src/img/manzana_modal.png" class=" mb-4 modal_imagen" alt="">

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">                             
                                <div class="col-6">  <!---------------------------------->
                                    <label for="numero" class="form-label fw-medium">Número de manzana:</label>
                                    <input type="number" name="numero_manzana" id="numero_manzana" class="form-control" value="<?php echo $numero_manzana; ?>">
                                        <?php if(!empty($error_numero_manzana_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_numero_manzana_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_numero_manzana_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_numero_manzana_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-6"> <!---------------------------------->
                                    <label for="cedula" class="form-label fw-medium">Cedula del Lider:</label>
                                    <input type="number" name="cedula" id="cedula"  class="form-control" value="<?php echo $cedula; ?>">
                                        <?php if(!empty($error_cedula_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_cedula_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_cedula_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_cedula_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-12"> <!---------------------------------->
                                    <label for="calle" class="form-label fw-medium">Calle:</label>
                                    <input type="text" name="calle" id="calle" class="form-control" value="<?php echo $calle; ?>">
                                        <?php if(!empty($error_calle_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_calle_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_calle_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_calle_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
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



        <!-- Modal Editar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEditarManzana" tabindex="-1" aria-labelledby="modalEditarManzanaLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEditarManzanaLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="manzana.php" method="POST">
                        <div class="modal-content border-white">

                            <img src="../src/img/manzana_modal.png" class=" mb-4 modal_imagen" alt="">
                            
                            <input type="hidden" id="manzana_id" name="manzana_id" > <!--al seleccionar el registro le vamos a pasar el id para poder actualizarlo-->

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">                             
                                <div class="col-6"> <!---------------------------------->
                                    <label for="numero" class="form-label fw-medium">Número de manzana:</label>
                                    <input type="number" name="numero_manzana" id="numero_manzana" class="form-control">
                                        <?php if(!empty($error_numero_manzana_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_numero_manzana_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_numero_manzana_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_numero_manzana_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-6"> <!---------------------------------->
                                    <label for="cedula" class="form-label fw-medium">Cedula del Lider:</label>
                                    <input type="number" name="cedula" id="cedula"  class="form-control">
                                        <?php if(!empty($error_cedula_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_cedula_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_cedula_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_cedula_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-12"> <!---------------------------------->
                                    <label for="calle" class="form-label fw-medium">Calle:</label>
                                    <input type="text" name="calle" id="calle" class="form-control">
                                        <?php if(!empty($error_calle_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_calle_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_calle_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_calle_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" id= "Actualizar" name="Actualizar" class="btn btn-primary"><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar cambios</button>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEliminarManzana" tabindex="-1" aria-labelledby="modalEliminarManzanaLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEliminarManzanaLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Al eliminar la manzana, se eliminarán también los datos del Jefe de Calle y Usuario vinculados.</p>
                    <h6>¿Seguro que desea eliminar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="manzana.php" method="POST">
                            <input type="hidden" name="manzana_id" id="manzana-id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Eliminar" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div>



        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalRecuperarManzana" tabindex="-1" aria-labelledby="modalRecuperarManzanaLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRecuperarManzanaLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Al recuperar la manzana, se recuperaran también los datos del Jefe de Calle y Usuario vinculados.</p>
                    <h6>¿Seguro que desea Recuperar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="manzana.php" method="POST">
                            <input type="hidden" name="manzana_id" id="manzana-id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Recuperar" name="Recuperar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash-arrow-up"></i>Recuperar</button>
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
    <script src="../src/js/manzana.js"></script>

<script>


/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalEditarManzana = document.getElementById('modalEditarManzana')  //id de la ventana modal Editar Registro
    let modalEliminarManzana = document.getElementById('modalEliminarManzana')  //id de la ventana modal Eliminar Registro

    modalEditarManzana.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let manzana_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputManzanaId = modalEditarManzana.querySelector('.modal-body #manzana_id')              //selecionamos la clase y el id presentes en el formulario
        let inputNumero_manzana = modalEditarManzana.querySelector('.modal-body #numero_manzana')
        let inputCedula = modalEditarManzana.querySelector('.modal-body #cedula')
        let inputCalle = modalEditarManzana.querySelector('.modal-body #calle')


        let url = "manzana_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('manzana_id', manzana_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputManzanaId.value = data.manzana_id
            inputNumero_manzana.value = data.numero_manzana
            inputCedula.value = data.cedula
            inputCalle.value = data.calle
             
            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalEliminarManzana.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let manzana_id = button.getAttribute('data-bs-id') 
        
        modalEliminarManzana.querySelector('.modal-footer #manzana-id').value = manzana_id
    })

/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
    modalRecuperarManzana.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let manzana_id = button.getAttribute('data-bs-id') 
        
        modalRecuperarManzana.querySelector('.modal-footer #manzana-id').value = manzana_id
    })

</script>


</body>
</html>