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
/*
       if ($datos['rol_id'] == 1) {
        header('Location: inicio-jefe-comunidad.php');

       } else */
       
        if ($datos['rol_id'] == 2 || $datos['rol_id'] == 1 ) { 
            
        $id_jefe = ($datos['rol_id'] == 2)?$_SESSION['user']:$_GET['id_jefe'];

        /* 3. Obtener datos de esta sesion como nombre y apellido del jefe de calle y numero de manzana bajo su cargo*/
        $datos_sesion = $conexion->prepare('SELECT j.jefe_calle_id, j.ci_ps, j.nombre, j.apellido, j.correo, m.numero_manzana FROM jefe_calle AS j
        INNER JOIN manzana AS m
        ON j.manzana_id=m.manzana_id 
        INNER JOIN usuario AS u
        ON u.usuario_id=j.usuario_id
        WHERE j.estado=? AND u.estado=? AND u.nombre_usuario=?');
        $datos_sesion->execute(["1","1",$id_jefe]);   
                 
        foreach ($datos_sesion as $d) {
            $d['jefe_calle_id'];
            $d['ci_ps'];
            $d['nombre'];
            $d['apellido'];
            $d['correo'];
            $d['numero_manzana'];
    
        }
               

         //----------------------------------------- ACCION INGRESAR ---------------------------------------------//
     
        $habitante_id = '';
        $estatus_laboral_id = '';
        $observaciones = '';

        if (isset($_POST['Añadir'])){
           $habitante_id = valida_campo($_POST['habitante_id']);
           $estatus_laboral_id = valida_campo($_POST['estatus_laboral_id']);
           $observaciones = valida_campo($_POST['observaciones']);


           if (empty($habitante_id)) {
               $error_habitante_id_1 = 'Seleccione un habitante';
           } 

           if (empty($estatus_laboral_id)) {
               $error_estatus_laboral_id_1 = 'Indique un estatus laboral para el habitante';
           } 
    
           if (strlen($observaciones) > 255) {
               $error_observaciones_1 = 'El contenido es muy largo';
           }


            /* 1. Verificar que no hay errores para insertar el registro */
            if (empty($error_habitante_id_1) and empty($error_estatus_laboral_id_1) and empty($error_observaciones_1)){
            
                /* 2. Verificar que no este el mismo habitante con el mismo estatus laboral en la Tabla habitante_estatus_laboral, para que no hayan duplicados */
                $buscar_habitante_estatus_laboral = $conexion->prepare('SELECT * FROM habitante_estatus_laboral WHERE habitante_id = ? AND estatus_laboral_id = ? AND estado=?');  
                $buscar_habitante_estatus_laboral->execute([$habitante_id,$estatus_laboral_id,"1"]);
                $o = [];
                $o = $buscar_habitante_estatus_laboral->fetch(PDO::FETCH_ASSOC);

                if (is_countable($o)){  //ya existe un registro similar
                    
                    echo '
                    <script>
                        alert("Lo sentimos, el habitante seleccionado ya se encuentra registrado con ese estatus laboral");
                    </script>
                    ';

                    //limpiar errores
                    $error_habitante_id_1 = '';
                    $error_estatus_laboral_id_1 = '';
                    $error_observaciones_1 = '';
    
                    //limpiar valores
                    $habitante_id = '';
                    $estatus_laboral_id = '';
                    $observaciones = '';


                } else {
                                  
                    /* 3. Buscar habitante para verificar edades */
                    $buscar_habitante = $conexion->prepare('SELECT * FROM habitante WHERE habitante_id = ? AND estado=?');  
                    $buscar_habitante->execute([$habitante_id,"1"]);
                    $r = [];
                    $r = $buscar_habitante->fetch(PDO::FETCH_ASSOC);
    
                    /* 4. Verificar edad para tener Estatus Laboral = Pensionado */
                    if ($estatus_laboral_id == 3) {            
        
                        if ($r['edad'] < 55) {
                        
                            //limpiar errores
                            $error_habitante_id_1 = '';
                            $error_estatus_laboral_id_1 = '';
                            $error_observaciones_1 = '';
        
                            //limpiar valores
                            $habitante_id = '';
                            $estatus_laboral_id = '';
                            $observaciones = '';
        
                            echo '
                            <script>
                                alert("Lo sentimos, el habitante seleccionado no posee actualmente la edad correspondiente para estar pensionado");
                            </script>
                            ';
                        } else {
                            /* 5. Insertar los datos de Habitante_Estatus_Laboral */
                            $insertar_estatus_laboral = $conexion->prepare("INSERT INTO habitante_estatus_laboral(habitante_id,estatus_laboral_id,observaciones) VALUES (?,?,?)");
                            $insertar_estatus_laboral->execute([$habitante_id,$estatus_laboral_id,$observaciones]);
    
                            //limpiar errores
                            $error_habitante_id_1 = '';
                            $error_estatus_laboral_id_1 = '';
                            $error_observaciones_1 = '';
        
                            //limpiar valores
                            $habitante_id = '';
                            $estatus_laboral_id = '';
                            $observaciones = '';
    
                            $registrado = 'registrado';
    
                        }
                    }
    
                
                    /* 4. Verificar edad para tener Estatus Laboral = Trabajador */
                    if ($estatus_laboral_id == 2) {            
        
                        if ($r['edad'] < 16) {
                        
                            //limpiar errores
                            $error_habitante_id_1 = '';
                            $error_estatus_laboral_id_1 = '';
                            $error_observaciones_1 = '';
        
                            //limpiar valores
                            $habitante_id = '';
                            $estatus_laboral_id = '';
                            $observaciones = '';
        
                            echo '
                            <script>
                                alert("Lo sentimos, el habitante seleccionado no posee actualmente la edad correspondiente para trabajar");
                            </script>
                            ';
                        } else {
                            /* 5. Insertar los datos de Habitante_Estatus_Laboral */
                            $insertar_estatus_laboral = $conexion->prepare("INSERT INTO habitante_estatus_laboral(habitante_id,estatus_laboral_id,observaciones) VALUES (?,?,?)");
                            $insertar_estatus_laboral->execute([$habitante_id,$estatus_laboral_id,$observaciones]);
    
                            //limpiar errores
                            $error_habitante_id_1 = '';
                            $error_estatus_laboral_id_1 = '';
                            $error_observaciones_1 = '';
        
                            //limpiar valores
                            $habitante_id = '';
                            $estatus_laboral_id = '';
                            $observaciones = '';
    
                            $registrado = 'registrado';
    
                        }
                    }
    
                    if ($estatus_laboral_id == 1) {
                    
                        /* 4. Insertar los datos de Habitante_Estatus_Laboral */
                        $insertar_estatus_laboral = $conexion->prepare("INSERT INTO habitante_estatus_laboral(habitante_id,estatus_laboral_id,observaciones) VALUES (?,?,?)");
                        $insertar_estatus_laboral->execute([$habitante_id,$estatus_laboral_id,$observaciones]);
    
                        //limpiar errores
                        $error_habitante_id_1 = '';
                        $error_estatus_laboral_id_1 = '';
                        $error_observaciones_1 = '';
        
                        //limpiar valores
                        $habitante_id = '';
                        $estatus_laboral_id = '';
                        $observaciones = '';
    
                        $registrado = 'registrado';
                    }


                }

            }  

        }
    
     //----------------------------------------- ACCION ACTUALIZAR ---------------------------------------------//
     
        if (isset($_POST['Actualizar'])){

            $habitante_estatus_laboral_id2 = $_POST['habitante_estatus_laboral_id'];
            $habitante_id_editar = valida_campo($_POST['habitante_id']);
            $estatus_laboral_id_editar = valida_campo($_POST['estatus_laboral_id']);
            $observaciones_editar = valida_campo($_POST['observaciones']);


            if (empty($habitante_id_editar)) {
                $error_habitante_id_editar_1 = 'Seleccione un habitante';
            } 

            if (empty($estatus_laboral_id_editar)) {
                $error_estatus_laboral_id_editar_1 = 'Indique un estatus laboral para el habitante';
            } 
 
            if (strlen($observaciones_editar) > 255) {
                $error_observaciones_editar_1 = 'El contenido es muy largo';
            }


            /* 1. Verificar que no hay errores para insertar el registro */
            if (empty($error_habitante_id_editar_1) and empty($error_estatus_laboral_id_editar_1) and empty($error_observaciones_editar_1)){
         
                /* 2. Verificar que no este el mismo habitante con el mismo estatus laboral en la Tabla habitante_estatus_laboral, para que no hayan duplicados. 
                .   Recuerda, sólo existen 2 casos en editar: 
                .   a) Cuando se colocan datos nuevos para modificar el registro, aqui no puede haber otros registros similares al él en la Tabla.
                .   b) Cuando no se modifica ningún dato y se presiona "guardar cambios", aqui la BD puede identificar que hay un registro similar a él (el cual es el mismo XD)
                .   por ello se hacen las consultas excluyendo el registro que tiene el id que se obtuvo por POST, y asi verificar que no se vaya a duplicar otro registro*/
                
                $buscar_habitante_estatus_laboral_editar = $conexion->prepare('SELECT * FROM habitante_estatus_laboral WHERE  md5(habitante_estatus_laboral_id) <> ? AND habitante_id = ? AND estatus_laboral_id = ? AND estado=?');  
                $buscar_habitante_estatus_laboral_editar->execute([$habitante_estatus_laboral_id2,$habitante_id_editar,$estatus_laboral_id_editar,"1"]);
                $k = [];
                $k = $buscar_habitante_estatus_laboral_editar->fetch(PDO::FETCH_ASSOC);

                if (is_countable($k)){  //ya existe un registro similar
                 
                    echo '
                    <script>
                        alert("Lo sentimos, el habitante seleccionado ya se encuentra registrado con ese estatus laboral");
                    </script>
                    ';

                    //limpiar errores
                    $error_habitante_id_editar_1 = '';
                    $error_estatus_laboral_id_editar_1 = '';
                    $error_observaciones_editar_1 = '';
 
                    //limpiar valores
                    $habitante_id_editar = '';
                    $estatus_laboral_id_editar = '';
                    $observaciones_editar = '';


                } else {
                               
                    /* 3. Buscar habitante para verificar edades */
                    $buscar_habitante_editar = $conexion->prepare('SELECT * FROM habitante WHERE habitante_id = ? AND estado=?');  
                    $buscar_habitante_editar->execute([$habitante_id_editar,"1"]);
                    $f = [];
                    $f = $buscar_habitante_editar->fetch(PDO::FETCH_ASSOC);
 
                    /* 4. Verificar edad para tener Estatus Laboral = Pensionado */
                    if ($estatus_laboral_id_editar == 3) {            
     
                        if ($f['edad'] < 55) {
                     
                            //limpiar errores
                            $error_habitante_id_editar_1 = '';
                            $error_estatus_laboral_id_editar_1 = '';
                            $error_observaciones_editar_1 = '';
 
                            //limpiar valores
                            $habitante_id_editar = '';
                            $estatus_laboral_id_editar = '';
                            $observaciones_editar = '';
     
                            echo '
                            <script>
                                alert("Lo sentimos, el habitante seleccionado no posee actualmente la edad correspondiente para estar pensionado");
                            </script>
                            ';

                            } else {

                                // funciones para actualizar la fecha en la tabla
                                date_default_timezone_set('America/Caracas');
                                setlocale(LC_TIME, 'spanish');
                                $fecha_actualizacion = date('Y-m-d g:i:s');

                                /* 5. Actualizar los datos de Habitante_Estatus_Laboral */
                                $actualizar_estatus_laboral = $conexion->prepare("UPDATE habitante_estatus_laboral SET habitante_id = ?, estatus_laboral_id = ?, observaciones = ?, fecha_actualizacion = ? WHERE md5(habitante_estatus_laboral_id) = ?;");
                                $actualizar_estatus_laboral->execute([$habitante_id_editar, $estatus_laboral_id_editar, $observaciones_editar, $fecha_actualizacion, $habitante_estatus_laboral_id2]);
                         
                                //limpiar errores
                                $error_habitante_id_editar_1 = '';
                                $error_estatus_laboral_id_editar_1 = '';
                                $error_observaciones_editar_1 = '';
 
                                //limpiar valores
                                $habitante_id_editar = '';
                                $estatus_laboral_id_editar = '';
                                $observaciones_editar = '';
 
                                $actualizado = 'actualizado';
                                
 
                            }
                    }
 
             
                    /* 4. Verificar edad para tener Estatus Laboral = Trabajador */
                    if ($estatus_laboral_id_editar == 2) {            
     
                        if ($f['edad'] < 16) {
                     
                            //limpiar errores
                            $error_habitante_id_editar_1 = '';
                            $error_estatus_laboral_id_editar_1 = '';
                            $error_observaciones_editar_1 = '';
 
                            //limpiar valores
                            $habitante_id_editar = '';
                            $estatus_laboral_id_editar = '';
                            $observaciones_editar = '';
     
                            echo '
                            <script>
                                alert("Lo sentimos, el habitante seleccionado no posee actualmente la edad correspondiente para trabajar");
                            </script>
                            ';

                        } else {                                
                            
                            // funciones para actualizar la fecha en la tabla
                            date_default_timezone_set('America/Caracas');
                            setlocale(LC_TIME, 'spanish');
                            $fecha_actualizacion = date('Y-m-d g:i:s');

                            /* 5. Actualizar los datos de Habitante_Estatus_Laboral */
                            $actualizar_estatus_laboral = $conexion->prepare("UPDATE habitante_estatus_laboral SET habitante_id = ?, estatus_laboral_id = ?, observaciones = ?, fecha_actualizacion = ? WHERE md5(habitante_estatus_laboral_id) = ?;");
                            $actualizar_estatus_laboral->execute([$habitante_id_editar, $estatus_laboral_id_editar, $observaciones_editar, $fecha_actualizacion, $habitante_estatus_laboral_id2]);
                     
                            //limpiar errores
                            $error_habitante_id_editar_1 = '';
                            $error_estatus_laboral_id_editar_1 = '';
                            $error_observaciones_editar_1 = '';

                            //limpiar valores
                            $habitante_id_editar = '';
                            $estatus_laboral_id_editar = '';
                            $observaciones_editar = '';

                            $actualizado = 'actualizado';
                        }
                    }
 
                    if ($estatus_laboral_id_editar == 1) {                                
                        
                        // funciones para actualizar la fecha en la tabla
                        date_default_timezone_set('America/Caracas');
                        setlocale(LC_TIME, 'spanish');
                        $fecha_actualizacion = date('Y-m-d g:i:s');

                        /* 5. Actualizar los datos de Habitante_Estatus_Laboral */
                        $actualizar_estatus_laboral = $conexion->prepare("UPDATE habitante_estatus_laboral SET habitante_id = ?, estatus_laboral_id = ?, observaciones = ?, fecha_actualizacion = ? WHERE md5(habitante_estatus_laboral_id) = ?;");
                        $actualizar_estatus_laboral->execute([$habitante_id_editar, $estatus_laboral_id_editar, $observaciones_editar, $fecha_actualizacion, $habitante_estatus_laboral_id2]);

                        //limpiar errores
                        $error_habitante_id_editar_1 = '';
                        $error_estatus_laboral_id_editar_1 = '';
                        $error_observaciones_editar_1 = '';

                        //limpiar valores
                        $habitante_id_editar = '';
                        $estatus_laboral_id_editar = '';
                        $observaciones_editar = '';

                        $actualizado = 'actualizado';
                    
                    }
                }
            }  
        }
     
     //----------------------------------------- ACCION ELIMINAR ---------------------------------------------//
        
        if (isset($_POST['Eliminar'])){

            $habitante_estatus_laboral_id_borrar = $_POST['habitante_estatus_laboral_id'];
            $estado = '2';
            
            /* 1. Eliminar Tabla Habitante Estatus Laboral */
            $borrar = $conexion->prepare("UPDATE habitante_estatus_laboral SET estado =? WHERE md5(habitante_estatus_laboral_id) = ?;");
            $borrar->execute([$estado, $habitante_estatus_laboral_id_borrar]);

            $borrado = 'borrado';

        }
     
     
     //----------------------------------------- ACCION RECUPERAR ---------------------------------------------//

        if (isset($_POST['Recuperar'])){

            $habitante_estatus_laboral_id_recuperar = $_POST['habitante_estatus_laboral_id'];
            $estado_recuperar = '1';
        
            /* 1. Eliminar Tabla Habitante Estatus Laboral */
            $restaurar = $conexion->prepare("UPDATE habitante_estatus_laboral SET estado =? WHERE md5(habitante_estatus_laboral_id) = ?;");
            $restaurar->execute([$estado_recuperar, $habitante_estatus_laboral_id_recuperar]);

            $recuperado = 'recuperado';

        }

        } else {    //No puede haber otro rol que no sea especificado
        session_destroy();

       }
        

    } else {

        session_destroy();
        
    }

}

/* --------------------------------------------------------------------------------------------------------------------------------------------- */


//Informacion de la Tabla
$estatus = $conexion->prepare('SELECT hel.habitante_estatus_laboral_id, h.nombre, h.apellido, h.ci_ps_pn, e.estatus_nombre, hel.observaciones FROM habitante_estatus_laboral as hel
INNER JOIN habitante as h
ON hel.habitante_id=h.habitante_id
INNER JOIN estatus_laboral as e
ON hel.estatus_laboral_id=e.estatus_laboral_id
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id 
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE hel.estado=? AND h.estado=? AND e.estado=? AND m.numero_manzana=? ;');
$estatus->execute(["1","1","1",$d['numero_manzana']]);

/* ------------------------ Select de Formulario ingresar ----------------------------------- */

//habitantes de la manzana seleccionada 
$habitante_manzana_seleccionada = $conexion->prepare('SELECT h.habitante_id, h.nombre, h.apellido, h.ci_ps_pn FROM habitante as h
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE h.estado=? AND v.estado=? AND m.estado=? AND m.numero_manzana=? 
ORDER BY h.nombre;');
$habitante_manzana_seleccionada->execute(["1","1","1",$d['numero_manzana']]);

/* ------------------------ Select de Formulario editar ----------------------------------- */

//habitantes de la manzana seleccionada 
$habitante_manzana_seleccionada_editar = $conexion->prepare('SELECT h.habitante_id, h.nombre, h.apellido, h.ci_ps_pn FROM habitante as h
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE h.estado=? AND v.estado=? AND m.estado=? AND m.numero_manzana=? 
ORDER BY h.nombre;');
$habitante_manzana_seleccionada_editar->execute(["1","1","1",$d['numero_manzana']]);




/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */
$recuperar_estatus = $conexion->prepare('SELECT hel.habitante_estatus_laboral_id, h.nombre, h.apellido, h.ci_ps_pn, e.estatus_nombre, hel.observaciones FROM habitante_estatus_laboral as hel
INNER JOIN habitante as h
ON hel.habitante_id=h.habitante_id
INNER JOIN estatus_laboral as e
ON hel.estatus_laboral_id=e.estatus_laboral_id
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id 
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE hel.estado=? AND m.numero_manzana=? ;');
$recuperar_estatus->execute(["2",$d['numero_manzana']]);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatus Laboral</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/estatus_labora.css">

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
                <img src="../src/img/userleader.png" alt="userleader">
                <h6 class="lead fs-6"><?php echo $d['nombre']. " " .$d['apellido']?></h6>
            </div>
        </nav>
    </div>
</div>
    

            <!-- sidebar -->
    <div class="row g-0 row-cols-12">
        <div class="d-flex flex-column col-auto min-vh-100 bgside__bar bajar position-fixed">
            <div class="mt-4">
                <a href="inicio-lider-calle.php?id_jefe=<?php echo $id_jefe ?>" class="text-white d-none d-sm-inline text-decoration-none d-flex align-items-center ms-4" role="button">
                    <span class="fs-5">Inicio</span>
                </a>
                <hr class="text-white d-none d-sm-block"/>
                <ul class="nav nav-pills flex-column mt-2 mt-sm-0" id="menu">

                    <li class="nav-item my-sm-1 my-2" id="btn">
                        <a href="vivienda.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-house"></i>
                            <span class="ms-2 d-none d-sm-inline boton-manzana">Viviendas</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="habitante.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-user-group"></i>
                            <span class="ms-2 d-none d-sm-inline">Habitantes</span>
                        </a>
                    </li> 
                    <li class="nav-item my-sm-1 my-2">
                        <a href="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-briefcase"></i>
                            <span class="ms-2 d-none d-sm-inline">Estatus Laboral</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-wheelchair"></i>
                            <span class="ms-2 d-none d-sm-inline">Incapacidad</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="votante.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                        <i class="fa-solid fa-users"></i>
                            <span class="ms-2 d-none d-sm-inline">Votantes</span>
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
                        <a class="dropdown-item" href="../page/perfil_jefe_calle.php?id_jefe=<?php echo $id_jefe ?>">Perfil</a>
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
           <!-- Tabla Manzana -->

        <div class="container mover-derecha-tabla col-10 small">
            <div class="col-12 pt-2 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2"> Datos de Estatus Laboral</h3>
                <img src="../src/img/trabajadores.png" class="img-fluid d-flex justify-content-center col-4" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir " data-bs-toggle="modal" data-bs-target="#modalEstatus" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos_manzana" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-info">
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Apellido</th>
                                <th class="text-center">Cedula</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Observaciones</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($estatus as $e) {
		                    ?>
		                    <tr>
                                <td><?php echo $e['nombre']; ?></td>
			                    <td><?php echo $e['apellido']; ?></td>
			                    <td><?php echo $e['ci_ps_pn']; ?></td>
			                    <td><?php echo $e['estatus_nombre']; ?></td>
                                <td><?php echo $e['observaciones']; ?></td>                          
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarEstatus" data-bs-id="<?= md5($e['habitante_estatus_laboral_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminarEstatus" data-bs-id="<?= md5($e['habitante_estatus_laboral_id']); ?>"> 
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
            <div class="col-12 pt-2 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Registros Eliminados</h3>
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-5">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="tabla_recuperacion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-dark">
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Apellido</th>
                                <th class="text-center">Cedula</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Observaciones</th>
                                <th class="text-center no-exportar">Recuperar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($recuperar_estatus as $e) {
		                    ?>
		                    <tr>
                                <td><?php echo $e['nombre']; ?></td>
			                    <td><?php echo $e['apellido']; ?></td>
			                    <td><?php echo $e['ci_ps_pn']; ?></td>
			                    <td><?php echo $e['estatus_nombre']; ?></td>
                                <td><?php echo $e['observaciones']; ?></td>                          
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-center">
                                    <a href="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalRecuperarEstatus" data-bs-id="<?= md5($e['habitante_estatus_laboral_id']); ?>"> 
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEstatus" tabindex="-1" aria-labelledby="modalEstatusLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEstatusLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" method="POST" id="formulario">
                        <div class="modal-content border-white">

                            <img src="../src/img/estatus_modal.png" class="mb-4 w-25 modal_imagen" alt="">

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">
                                    <label for="habitante_id" class="form-label fw-medium"> Habitante:</label>
                                    <select name="habitante_id" id="habitante_id" class="form-select" value="<?php echo $habitante_id; ?>">
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($habitante_manzana_seleccionada as $h){
                                        ?> 
                                            <option value="<?php echo $h['habitante_id']; ?>"><?php echo $h['nombre']. " " .$h['apellido']. " | " .$h['ci_ps_pn'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                        <?php if(!empty($error_habitante_id_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_habitante_id_1" ?></p>
                                        <?php }; ?>
                                </div>

                                
                                <div class="col-6">
                                    <label for="estatus_laboral_id" class="form-label fw-medium"> Estatus Laboral:</label>
                                    <select name="estatus_laboral_id" id="estatus_laboral_id" class="form-select" value="<?php echo $estatus_laboral_id; ?>">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Estudiante</option>
                                        <option value="2">Trabajador</option>
                                        <option value="3">Pensionado</option>
                                    </select>
 
                                        <?php if(!empty($error_estatus_laboral_id_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_estatus_laboral_id_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">   

                                <div class="col-12">
                                    <label for="observaciones" class="form-label fw-medium" > Observaciones:</label>
                                    <input type="textarea" name="observaciones" id="observaciones" class="form-control" value="<?php echo $observaciones; ?>">
                                        <?php if(!empty($error_observaciones_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_observaciones_1" ?></p>
                                        <?php }; ?>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEditarEstatus" tabindex="-1" aria-labelledby="modalEditarEstatusLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEditarEstatusLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                        <div class="modal-content border-white">

                        <img src="../src/img/estatus_modal.png" class="mb-4 w-25 modal_imagen" alt="">

                            <input type="hidden" id="habitante_estatus_laboral_id" name="habitante_estatus_laboral_id" > <!--al seleccionar el registro le vamos a pasar el id para poder actualizarlo-->

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">
                                    <label for="habitante_id" class="form-label fw-medium"> Habitante:</label>
                                    <select name="habitante_id" id="habitante_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($habitante_manzana_seleccionada_editar as $h){
                                        ?> 
                                            <option value="<?php echo $h['habitante_id']; ?>"><?php echo $h['nombre']. " " .$h['apellido']. " | " .$h['ci_ps_pn'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                        <?php if(!empty($error_habitante_id_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_habitante_id_editar_1" ?></p>
                                        <?php }; ?>
                                </div>

                                
                                <div class="col-6">
                                    <label for="estatus_laboral_id" class="form-label fw-medium"> Estatus Laboral:</label>
                                    <select name="estatus_laboral_id" id="estatus_laboral_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Estudiante</option>
                                        <option value="2">Trabajador</option>
                                        <option value="3">Pensionado</option>
                                    </select>

                                        <?php if(!empty($error_estatus_laboral_id_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_estatus_laboral_id_editar_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">   

                                <div class="col-12">
                                    <label for="observaciones" class="form-label fw-medium" > Observaciones:</label>
                                    <input type="textarea" name="observaciones" id="observaciones" class="form-control">
                                        <?php if(!empty($error_observaciones_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_observaciones_editar_1" ?></p>
                                        <?php }; ?>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEliminarEstatus" tabindex="-1" aria-labelledby="modalEliminarEstatusLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEliminarEstatusLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea eliminar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="habitante_estatus_laboral_id" id="habitante_estatus_laboral_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Eliminar" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div>
        
     

        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalRecuperarEstatus" tabindex="-1" aria-labelledby="modalRecuperarEstatusLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRecuperarEstatusLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea recuperar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="habitante_estatus_laboral_id" id="habitante_estatus_laboral_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Recuperar" name="Recuperar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash-arrow-up"></i>Recuperar</button>
                    </form>
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
    <script src="../src/js/estatus-laboraal.js"></script>


    
<script>

/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalEditarEstatus = document.getElementById('modalEditarEstatus')  //id de la ventana modal Editar Registro
    let modalEliminarEstatus = document.getElementById('modalEliminarEstatus')  //id de la ventana modal Eliminar Registro
    let modalRecuperarEstatus = document.getElementById('modalRecuperarEstatus')


    modalEditarEstatus.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let habitante_estatus_laboral_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        /*1. Seleccionamos los inputs del formulario */
        let inputHabitanteEstatusLaboralId = modalEditarEstatus.querySelector('.modal-body #habitante_estatus_laboral_id')              //selecionamos la clase y el id presentes en el formulario
        let inputHabitanteId = modalEditarEstatus.querySelector('.modal-body #habitante_id')   
        let inputEstatusId = modalEditarEstatus.querySelector('.modal-body #estatus_laboral_id')
        let inputObservaciones = modalEditarEstatus.querySelector('.modal-body #observaciones')

        /*2. Obtenemos los datos de la BD y los enviamos en formato json */
        let url = "estatus_laboral_get.php?id_jefe=<?php echo $id_jefe ?>"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('habitante_estatus_laboral_id', habitante_estatus_laboral_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputHabitanteEstatusLaboralId.value = data.habitante_estatus_laboral_id
            inputHabitanteId.value = data.habitante_id
            inputEstatusId.value = data.estatus_laboral_id
            inputObservaciones.value = data.observaciones
            console.dir(data)
        }).catch(err => console.log(err))
    })


    /* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
modalEliminarEstatus.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let habitante_estatus_laboral_id = button.getAttribute('data-bs-id') 
        
        modalEliminarEstatus.querySelector('.modal-footer #habitante_estatus_laboral_id').value = habitante_estatus_laboral_id
    })


/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
modalRecuperarEstatus.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let habitante_estatus_laboral_id = button.getAttribute('data-bs-id') 
        
        modalRecuperarEstatus.querySelector('.modal-footer #habitante_estatus_laboral_id').value = habitante_estatus_laboral_id
    })
</script>
</body>
</html>