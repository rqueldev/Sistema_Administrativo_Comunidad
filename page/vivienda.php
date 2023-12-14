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

       /*if ($datos['rol_id'] == 1) {
        header('Location: inicio-jefe-comunidad.php');

       } else */ 
         
        
       /* if ($datos['rol_id'] == 2 ) {
            $id_jefe = $_SESSION['user'];
            
        } else {
            $id_jefe = $_GET['id_jefe'];
        }*/

        if ($datos['rol_id'] == 2 || $datos['rol_id'] == 1 ) {  
            
        $id_jefe = ($datos['rol_id'] == 2)?$_SESSION['user']:$_GET['id_jefe'];
       
        /* 3. Obtener datos de esta sesion como nombre y apellido del jefe de calle y numero de manzana bajo su cargo*/
        $datos_sesion = $conexion->prepare('SELECT j.jefe_calle_id, j.ci_ps, j.nombre, j.apellido, j.correo, m.numero_manzana, m.manzana_id FROM jefe_calle AS j
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
            $d['manzana_id'];
        }
               
     //----------------------------------------- ACCION INGRESAR ---------------------------------------------//

        $manzana_id = '';
        $numero = '';
        $estatus = '';
        $tipo = '';
        $cedula = '';
        $numero_combos_clap = ''; 
            
        if (isset($_POST['Añadir'])){


            $manzana_id = $_POST['manzana_id'];
            $numero = valida_campo($_POST['numero']);
            $estatus = valida_campo($_POST['estatus']);
            $tipo = valida_campo($_POST['tipo']);
            $cedula = valida_campo($_POST['cedula']);
            $numero_combos_clap = valida_campo($_POST['numero_combos_clap']);


            if (empty($numero)) {
                $error_numero_1 = 'Coloque un número';
            } else {
                if (strlen($numero) > 6) {
                    $error_numero_2 = 'El numero es muy largo';
                }
            } 
        
            if (empty($estatus)) {
                $error_estatus_1 = 'Indique un estatus';
            } 

            if (empty($tipo)) {
                $error_tipo_1 = 'Indique un tipo de vivienda';
            } 
        
            if (empty($cedula)) {
                $error_cedula_1 = 'Coloque una cédula';
            } else {
                if (strlen($cedula) > 8) {
                    $error_cedula_2 = 'La cédula no puede tener más de 8 caracteres';
                }
            }

            if (empty($numero_combos_clap)) {
                $error_numero_combos_clap_1 = 'Coloque un número';
            } else {
                if (strlen($numero_combos_clap) > 1) {
                    $error_numero_combos_clap_2 = 'El numero es muy grande';
                }
            } 


            /* 1. Verificar que no hay errores para insertar el registro */
            if (empty($error_numero_1) and empty($error_numero_2) and empty($error_estatus_1) and empty($error_tipo_1) 
                and empty($error_cedula_1) and empty($error_cedula_2) and empty($error_numero_combos_clap_1) and 
                empty($error_numero_combos_clap_2)){

                    /* 2. Verificar si ya existe una vivienda en la BD con los mismos datos ingresados */
                    if (!empty($_POST['numero']) && !empty($_POST['cedula'])) {

                        // 2.1 Comprobar Numero de Vivienda
                        $verificar_numero = $conexion->prepare('SELECT * FROM vivienda WHERE numero=? AND estado=?');  
                        $verificar_numero->execute([$numero,"1"]);
                
                        $r = [];
                        $r = $verificar_numero->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($r)){

                            //limpiar errores
                            $error_numero_1 = '';
                            $error_numero_2 = '';
                            $error_estatus_1 = '';
                            $error_tipo_2 = '';
                            $error_cedula_1 = '';
                            $error_cedula_2 = '';
                            $error_numero_combos_clap_1 = '';
                            $error_numero_combos_clap_2 = '';

                            //limpiar valores
                            $manzana_id = '';
                            $numero = '';
                            $estatus = '';
                            $tipo = '';
                            $cedula = '';
                            $numero_combos_clap = '';

                            echo '
                            <script>
                                alert("Lo sentimos, el numero de vivienda le corresponde a otro registro");
                            </script>
                            ';
                            
                        }

                        // 2.2 Comprobar Cedula
                        $verificar_cedula = $conexion->prepare('SELECT * FROM vivienda WHERE cedula=? AND estado=?');  
                        $verificar_cedula->execute([$cedula,"1"]);
                    
                        $s = [];
                        $s = $verificar_cedula->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($s)){

                            //limpiar errores
                            $error_numero_1 = '';
                            $error_numero_2 = '';
                            $error_estatus_1 = '';
                            $error_tipo_2 = '';
                            $error_cedula_1 = '';
                            $error_cedula_2 = '';
                            $error_numero_combos_clap_1 = '';
                            $error_numero_combos_clap_2 = '';

                            //limpiar valores
                            $manzana_id = '';
                            $numero = '';
                            $estatus = '';
                            $tipo = '';
                            $cedula = '';
                            $numero_combos_clap = '';

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

                            /* 3. Se insertan los datos en la Tabla Vivienda */
                            $ingresar = $conexion->prepare("INSERT INTO vivienda(numero,manzana_id,estatus,tipo,numero_combos_clap,cedula) VALUES (?,?,?,?,?,?)");
                            $ingresar->execute([$numero,$manzana_id,$estatus,$tipo,$numero_combos_clap,$cedula]);

                            //limpiar errores
                            $error_numero_1 = '';
                            $error_numero_2 = '';
                            $error_estatus_1 = '';
                            $error_tipo_2 = '';
                            $error_cedula_1 = '';
                            $error_cedula_2 = '';
                            $error_numero_combos_clap_1 = '';
                            $error_numero_combos_clap_2 = '';

                            //limpiar valores
                            $manzana_id = '';
                            $numero = '';
                            $estatus = '';
                            $tipo = '';
                            $cedula = '';
                            $numero_combos_clap = '';

                            $registrado = 'registrado';
                        }    
                    }
                }
        }

        //----------------------------------------- ACCION EDITAR ---------------------------------------------//

        if (isset($_POST['Actualizar'])){

            $vivienda_id2 = $_POST['vivienda_id'];

            $manzana_id_editar = $_POST['manzana_id'];
            $numero_editar = valida_campo($_POST['numero']);
            $estatus_editar = valida_campo($_POST['estatus']);
            $tipo_editar = valida_campo($_POST['tipo']);
            $cedula_editar = valida_campo($_POST['cedula']);
            $numero_combos_clap_editar = valida_campo($_POST['numero_combos_clap']);


            if (empty($numero_editar)) {
                $error_numero_editar_1 = 'Coloque un número';
            } else {
                if (strlen($numero_editar) > 6) {
                    $error_numero_editar_2 = 'El numero es muy largo';
                }
            } 
        
            if (empty($estatus_editar)) {
                $error_estatus_editar_1 = 'Indique un estatus';
            } 

            if (empty($tipo_editar)) {
                $error_tipo_editar_1 = 'Indique un tipo de vivienda';
            } 
        
            if (empty($cedula_editar)) {
                $error_cedula_editar_1 = 'Coloque una cédula';
            } else {
                if (strlen($cedula_editar) > 8) {
                    $error_cedula_editar_2 = 'La cédula no puede tener más de 8 caracteres';
                }
            }

            if (empty($numero_combos_clap_editar)) {
                $error_numero_combos_clap_editar_1 = 'Coloque un número';
            } else {
                if (strlen($numero_combos_clap_editar) > 1) {
                    $error_numero_combos_clap_editar_2 = 'El numero es muy grande';
                }
            } 


            /* 1. Verificar que no hay errores para actualizar el registro */
            if (empty($error_numero_editar_1) and empty($error_numero_editar_2) and empty($error_estatus_editar_1) and empty($error_tipo_editar_1) 
                and empty($error_cedula_editar_1) and empty($error_cedula_editar_2) and empty($error_numero_combos_clap_editar_1) and 
                empty($error_numero_combos_clap_editar_2)){

                    /* 2. Verificar si ya existe una vivienda en la BD con los mismos datos ingresados */
                    if (!empty($_POST['numero']) && !empty($_POST['cedula'])) {

                        // 2.1 Comprobar Numero de Vivienda
                        $verificar_numero_editar = $conexion->prepare('SELECT * FROM vivienda WHERE md5(vivienda_id) <> ? AND numero=? AND estado=?');  
                        $verificar_numero_editar->execute([$vivienda_id2,$numero_editar,"1"]);
                
                        $re = [];
                        $re = $verificar_numero_editar->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($re)){

                            //limpiar errores
                            $error_numero_editar_1 = '';
                            $error_numero_editar_2 = '';
                            $error_estatus_editar_1 = '';
                            $error_tipo_editar_2 = '';
                            $error_cedula_editar_1 = '';
                            $error_cedula_editar_2 = '';
                            $error_numero_combos_clap_editar_1 = '';
                            $error_numero_combos_clap_editar_2 = '';

                            //limpiar valores
                            $manzana_id_editar = '';
                            $numero_editar = '';
                            $estatus_editar = '';
                            $tipo_editar = '';
                            $cedula_editar = '';
                            $numero_combos_clap_editar = '';

                            echo '
                            <script>
                                alert("Lo sentimos, el numero de vivienda le corresponde a otro registro");
                            </script>
                            ';
                            
                        }

                        // 2.2 Comprobar Cedula
                        $verificar_cedula_editar = $conexion->prepare('SELECT * FROM vivienda WHERE md5(vivienda_id) <> ? AND cedula=? AND estado=?');  
                        $verificar_cedula_editar->execute([$vivienda_id2,$cedula_editar,"1"]);
                    
                        $se = [];
                        $se = $verificar_cedula_editar->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($se)){

                            //limpiar errores
                            $error_numero_editar_1 = '';
                            $error_numero_editar_2 = '';
                            $error_estatus_editar_1 = '';
                            $error_tipo_editar_2 = '';
                            $error_cedula_editar_1 = '';
                            $error_cedula_editar_2 = '';
                            $error_numero_combos_clap_editar_1 = '';
                            $error_numero_combos_clap_editar_2 = '';

                            //limpiar valores
                            $manzana_id_editar = '';
                            $numero_editar = '';
                            $estatus_editar = '';
                            $tipo_editar = '';
                            $cedula_editar = '';
                            $numero_combos_clap_editar = '';

                            echo '
                            <script>
                                alert("Lo sentimos, la cédula ingresada le corresponde a otro registro");
                            </script>
                            ';

                        }

                        if (!is_countable($re) and !is_countable($se)) {
                            /* echo '
                             <script>
                                 alert("EXCELENTE, la cedula y manzana ingresada no le corresponden a mas nadie");
                             </script>
                             ';*/


                            // funciones para actualizar la fecha en la tabla
                            date_default_timezone_set('America/Caracas');
                            setlocale(LC_TIME, 'spanish');
                            $fecha_actualizacion = date('Y-m-d g:i:s');

                            /* 3. Se actualizan los datos en la Tabla Vivienda */
                            $actualizar = $conexion->prepare("UPDATE vivienda SET manzana_id = ?, numero = ?, estatus = ?, tipo = ?, numero_combos_clap = ?, fecha_actualizacion = ?, cedula=? WHERE md5(vivienda_id) = ?;");
                            $actualizar->execute([$manzana_id_editar, $numero_editar, $estatus_editar, $tipo_editar, $numero_combos_clap_editar, $fecha_actualizacion, $cedula_editar, $vivienda_id2]);

                            //limpiar errores
                            $error_numero_1 = '';
                            $error_numero_2 = '';
                            $error_estatus_1 = '';
                            $error_tipo_2 = '';
                            $error_cedula_1 = '';
                            $error_cedula_2 = '';
                            $error_numero_combos_clap_1 = '';
                            $error_numero_combos_clap_2 = '';

                            //limpiar valores
                            $manzana_id = '';
                            $numero = '';
                            $estatus = '';
                            $tipo = '';
                            $cedula = '';
                            $numero_combos_clap = '';

                            $actualizado = 'actualizado';

                        }    
                    }
                }
        }

        //----------------------------------------- ACCION ELIMINAR ---------------------------------------------//
        if (isset($_POST['Eliminar'])){

            $vivienda_id_borrar = $_POST['vivienda_id'];
            $estado = '2';

            /* 1. Eliminar Vivienda */
            $borrar_vivienda = $conexion->prepare("UPDATE vivienda SET estado = ? WHERE md5(vivienda_id) = ?;");
            $borrar_vivienda->execute([$estado, $vivienda_id_borrar]);

            /* 2. Eliminar tabla involucrada: habitante */
            $borrar_habitante = $conexion->prepare("UPDATE habitante SET estado = ? WHERE md5(vivienda_id)=?;");
            $borrar_habitante->execute([$estado,$vivienda_id_borrar]);

            /* 3. Eliminar clasificacion de habitante: Estatus Laboral */
            $borrar_estatus_laboral = $conexion->prepare("UPDATE vivienda as v, habitante as h, habitante_estatus_laboral as hes
            SET hes.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=hes.habitante_id AND md5(v.vivienda_id)=?;");
            $borrar_estatus_laboral->execute([$estado,$vivienda_id_borrar]);

            /* 4. Eliminar clasificacion de habitante: Incapacidad */
            $borrar_incapacidad = $conexion->prepare("UPDATE vivienda as v, habitante as h, habitante_incapacidad as hi
            SET hi.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=hi.habitante_id AND md5(v.vivienda_id)=?;");
            $borrar_incapacidad->execute([$estado,$vivienda_id_borrar]);

            /* 5. Eliminar clasificacion de habitante: Votante */
            $borrar_votante = $conexion->prepare("UPDATE vivienda as v, habitante as h, votante as vt
            SET vt.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=vt.habitante_id AND md5(v.vivienda_id)=?;");
            $borrar_votante->execute([$estado,$vivienda_id_borrar]);

            /* 6. Eliminar clasificacion de habitante: Vocero */
            $borrar_vocero = $conexion->prepare("UPDATE vivienda as v, habitante as h, vocero as vo
            SET vo.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=vo.habitante_id AND md5(v.vivienda_id)=?;");
            $borrar_vocero->execute([$estado,$vivienda_id_borrar]);

            /* 7. Eliminar clasificacion de habitante: Jefe de Hogar */
            $borrar_jefe_hogar = $conexion->prepare("UPDATE vivienda as v, habitante as h, jefe_hogar as jh
            SET jh.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=jh.habitante_id AND md5(v.vivienda_id)=?;");
            $borrar_jefe_hogar->execute([$estado,$vivienda_id_borrar]);


            $borrado = 'borrado';
        }


        //----------------------------------------- ACCION RECUPERAR ---------------------------------------------//
        if (isset($_POST['Recuperar'])){

            $vivienda_id_recuperar = $_POST['vivienda_id'];
            $estado_recuperar = '1';

            /* 1. Recuperar Vivienda */
            $recuperar_vivienda = $conexion->prepare("UPDATE vivienda SET estado = ? WHERE md5(vivienda_id) = ?;");
            $recuperar_vivienda->execute([$estado_recuperar, $vivienda_id_recuperar]);

            /* 2. Recuperar tabla involucrada: habitante */
            $recuperar_habitante = $conexion->prepare("UPDATE habitante SET estado = ? WHERE md5(vivienda_id)=?;");
            $recuperar_habitante->execute([$estado_recuperar,$vivienda_id_recuperar]);

            /* 3. Recuperar clasificacion de habitante: Estatus Laboral */
            $recuperar_estatus_laboral = $conexion->prepare("UPDATE vivienda as v, habitante as h, habitante_estatus_laboral as hes
            SET hes.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=hes.habitante_id AND md5(v.vivienda_id)=?;");
            $recuperar_estatus_laboral->execute([$estado_recuperar,$vivienda_id_recuperar]);

            /* 4. Recuperar clasificacion de habitante: Incapacidad */
            $recuperar_incapacidad = $conexion->prepare("UPDATE vivienda as v, habitante as h, habitante_incapacidad as hi
            SET hi.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=hi.habitante_id AND md5(v.vivienda_id)=?;");
            $recuperar_incapacidad->execute([$estado_recuperar,$vivienda_id_recuperar]);

            /* 5. Recuperar clasificacion de habitante: Votante */
            $recuperar_votante = $conexion->prepare("UPDATE vivienda as v, habitante as h, votante as vt
            SET vt.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=vt.habitante_id AND md5(v.vivienda_id)=?;");
            $recuperar_votante->execute([$estado_recuperar,$vivienda_id_recuperar]);

            /* 6. Recuperar clasificacion de habitante: Vocero */
            $recuperar_vocero = $conexion->prepare("UPDATE vivienda as v, habitante as h, vocero as vo
            SET vo.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=vo.habitante_id AND md5(v.vivienda_id)=?;");
            $recuperar_vocero->execute([$estado_recuperar,$vivienda_id_recuperar]);

            /* 7. Recuperar clasificacion de habitante: Jefe de Hogar */
            $recuperar_jefe_hogar = $conexion->prepare("UPDATE vivienda as v, habitante as h, jefe_hogar as jh
            SET jh.estado =?
            WHERE v.vivienda_id=h.vivienda_id AND h.habitante_id=jh.habitante_id AND md5(v.vivienda_id)=?;");
            $recuperar_jefe_hogar->execute([$estado_recuperar,$vivienda_id_recuperar]);

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
$vivienda = $conexion->prepare('SELECT v.vivienda_id, v.numero, m.numero_manzana, v.estatus, v.tipo, v.cedula, v.numero_combos_clap FROM vivienda as v
INNER JOIN manzana as m
ON v.manzana_id=m.manzana_id 
WHERE v.estado =? AND m.estado=? AND m.numero_manzana=?
ORDER BY v.vivienda_id;');
$vivienda->execute(["1","1",$d['numero_manzana']]);


/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */
// Informacion de la Tabla de Recuperación
$recuperar_vivienda = $conexion->prepare('SELECT v.vivienda_id, v.numero, m.numero_manzana, v.estatus, v.tipo, v.cedula, v.numero_combos_clap FROM vivienda as v
INNER JOIN manzana as m
ON v.manzana_id=m.manzana_id 
WHERE v.estado =? AND m.numero_manzana=?
ORDER BY v.vivienda_id;');
$recuperar_vivienda->execute(["2",$d['numero_manzana']]);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vivienda</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/vivienda.css">

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
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2"> Datos de Viviendas</h3>
                <img src="../src/img/house.png" class="img-fluid d-flex justify-content-center col-5" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir " data-bs-toggle="modal" data-bs-target="#modalVivienda" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos_vivienda" class="table table-bordered table-hover display nowrap table-striped " cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-info">
                                <th class="text-center">Nº Vivienda</th>
                                <th class="text-center">Manzana</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Jefe de Hogar</th>
                                <th class="text-center">Combos CLAP</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($vivienda as $v) {
		                    ?>
		                    <tr>
                                <td><?php echo $v['numero']; ?></td>
			                    <td><?php echo $v['numero_manzana']; ?></td>
			                    <td><?php echo $v['estatus']; ?></td>
			                    <td><?php echo $v['tipo']; ?></td>
			                    <td><?php echo $v['cedula']; ?></td>
                                <td><?php echo $v['numero_combos_clap']; ?></td>
                                <!--------- Botones ------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="vivienda.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarVivienda" data-bs-id="<?= md5($v['vivienda_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="vivienda.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminarVivienda" data-bs-id="<?= md5($v['vivienda_id']); ?>"> 
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
                <h3 class="text-dark d-flex justify-content-center lead fs-2"> Registros Eliminados</h3>
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-5">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="tabla_recuperacion" class="table table-bordered table-hover display nowrap table-striped " cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-dark">
                                <th class="text-center">Nº Vivienda</th>
                                <th class="text-center">Manzana</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Jefe de Hogar</th>
                                <th class="text-center">Combos CLAP</th>
                                <th class="text-center no-exportar">Recuperar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($recuperar_vivienda as $v) {
		                    ?>
		                    <tr>
                                <td><?php echo $v['numero']; ?></td>
			                    <td><?php echo $v['numero_manzana']; ?></td>
			                    <td><?php echo $v['estatus']; ?></td>
			                    <td><?php echo $v['tipo']; ?></td>
			                    <td><?php echo $v['cedula']; ?></td>
                                <td><?php echo $v['numero_combos_clap']; ?></td>
                                <!--------- Botones ------------> 
			                    <td class="d-flex justify-content-center">
                                    <a href="vivienda.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalRecuperarVivienda" data-bs-id="<?= md5($v['vivienda_id']); ?>"> 
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalVivienda" tabindex="-1" aria-labelledby="modalViviendaLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalViviendaLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="vivienda.php?id_jefe=<?php echo $id_jefe ?>" method="POST" id="formulario">
                        <div class="modal-content border-white">

                            <img src="../src/img/casa_modal.png" class="w-25 mb-4 modal_imagen" alt="">

                            <input type="hidden" name="manzana_id" id="manzana_id" value="<?php echo $d['manzana_id']?>">

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!----------------------------------> 
                                    <label for="numero" class="form-label fw-medium" >Número de vivienda:</label>
                                    <input type="text" name="numero" id="numero" class="form-control" value="<?php echo $numero; ?>">
                                        <?php if(!empty($error_numero_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_numero_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_numero_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_numero_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            
                            
                                <div class="col-4"> <!---------------------------------->
                                    <label for="estatus" class="form-label fw-medium">Estatus:</label>
                                    <select name="estatus" id="estatus" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="propia">Propia</option>
                                        <option value="alquilada">Alquilada</option>
                                        <option value="prestada">Prestada</option>
                                    </select>

                                        <?php if(!empty($error_estatus_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_estatus_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="tipo" class="form-label fw-medium">Tipo:</label>
                                    <select name="tipo" id="tipo" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="casa">Casa</option>
                                        <option value="apartamento">Apartamento</option>
                                        <option value="anexo">Anexo</option>
                                        <option value="rancho">Rancho</option>
                                    </select>

                                        <?php if(!empty($error_tipo_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_tipo_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6"> <!---------------------------------->
                                    <label for="cedula" class="form-label fw-medium" > Cédula del Jefe de Hogar:</label>
                                    <input type="number" name="cedula" id="cedula" class="form-control" value="<?php echo $cedula; ?>">
                                        <?php if(!empty($error_cedula_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_cedula_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_cedula_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_cedula_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-6"> <!---------------------------------->
                                    <label for="numero_combos_clap" class="form-label fw-medium" >Número de Combos CLAP:</label>
                                    <input type="number" name="numero_combos_clap" id="numero_combos_clap" class="form-control" value="<?php echo $numero_combos_clap; ?>">
                                        <?php if(!empty($error_numero_combos_clap_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_numero_combos_clap_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_numero_combos_clap_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_numero_combos_clap_2" ?></p>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEditarVivienda" tabindex="-1" aria-labelledby="modalEditarViviendaLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEditarViviendaLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="vivienda.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                        <div class="modal-content border-white">

                        <img src="../src/img/casa_modal.png" class=" w-25 mb-4 modal_imagen" alt="">
                            <input type="hidden" id="vivienda_id" name="vivienda_id" > <!--al seleccionar el registro le vamos a pasar el id para poder actualizarlo-->
                            <input type="hidden" id="manzana_id" name="manzana_id" >

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!---------------------------------->
                                    <label for="numero" class="form-label fw-medium" >Número de vivienda:</label>
                                    <input type="text" name="numero" id="numero" class="form-control">
                                        <?php if(!empty($error_numero_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_numero_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_numero_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_numero_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            
                            
                                <div class="col-4"> <!---------------------------------->
                                    <label for="estatus" class="form-label fw-medium">Estatus:</label>
                                    <select name="estatus" id="estatus" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="propia">Propia</option>
                                        <option value="alquilada">Alquilada</option>
                                        <option value="prestada">Prestada</option>
                                    </select>

                                        <?php if(!empty($error_estatus_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_estatus_editar_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="tipo" class="form-label fw-medium">Tipo:</label>
                                    <select name="tipo" id="tipo" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="casa">Casa</option>
                                        <option value="apartamento">Apartamento</option>
                                        <option value="anexo">Anexo</option>
                                        <option value="rancho">Rancho</option>
                                    </select>

                                        <?php if(!empty($error_tipo_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_tipo_editar_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6"> <!---------------------------------->
                                    <label for="cedula" class="form-label fw-medium" > Cédula del Jefe de Hogar:</label>
                                    <input type="number" name="cedula" id="cedula" class="form-control">
                                        <?php if(!empty($error_cedula_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_cedula_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_cedula_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_cedula_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-6"> <!---------------------------------->
                                    <label for="numero_combos_clap" class="form-label fw-medium" >Número de Combos CLAP:</label>
                                    <input type="number" name="numero_combos_clap" id="numero_combos_clap" class="form-control">
                                        <?php if(!empty($error_numero_combos_clap_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_numero_combos_clap_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_numero_combos_clap_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_numero_combos_clap_editar_2" ?></p>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEliminarVivienda" tabindex="-1" aria-labelledby="modalEliminarViviendaLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEliminarViviendaLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Al eliminar la vivienda, se aliminarán también los datos de los habitantes que viven allí.</p>
                    <h6>¿Seguro que desea eliminar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="vivienda.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="vivienda_id" id="vivienda_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Eliminar" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div>


        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalRecuperarVivienda" tabindex="-1" aria-labelledby="modalRecuperarViviendaLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRecuperarViviendaLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Al recuperar la vivienda, se recuperarán también los datos de los habitantes que viven allí.</p>
                    <h6>¿Seguro que desea Recuperar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="vivienda.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="vivienda_id" id="vivienda_id" >
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
    <script src="../src/js/vivienda.js"></script>

<script>

/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalEditarVivienda = document.getElementById('modalEditarVivienda')  //id de la ventana modal Editar Registro
    let modalEliminarVivienda = document.getElementById('modalEliminarVivienda')  //id de la ventana modal Eliminar Registro
    let modalRecuperarVivienda = document.getElementById('modalRecuperarVivienda')

    modalEditarVivienda.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let vivienda_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        /*1. Seleccionamos los inputs del formulario */
        let inputViviendaId = modalEditarVivienda.querySelector('.modal-body #vivienda_id')              //selecionamos la clase y el id presentes en el formulario
        let inputManzanaId = modalEditarVivienda.querySelector('.modal-body #manzana_id')   
        let inputNumero = modalEditarVivienda.querySelector('.modal-body #numero')
        let inputEstatus = modalEditarVivienda.querySelector('.modal-body #estatus')
        let inputTipo = modalEditarVivienda.querySelector('.modal-body #tipo')
        let inputCedula = modalEditarVivienda.querySelector('.modal-body #cedula')
        let inputNumeroCombosClap = modalEditarVivienda.querySelector('.modal-body #numero_combos_clap')

        /*2. Obtenemos los datos de la BD y los enviamos en formato json */
        let url = "vivienda_get.php?id_jefe=<?php echo $id_jefe ?>"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('vivienda_id', vivienda_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputViviendaId.value = data.vivienda_id
            inputManzanaId.value = data.manzana_id
            inputNumero.value = data.numero
            inputEstatus.value = data.estatus
            inputTipo.value = data.tipo
            inputCedula.value = data.cedula
            inputNumeroCombosClap.value = data.numero_combos_clap
            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
modalEliminarVivienda.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let vivienda_id = button.getAttribute('data-bs-id') 
        
        modalEliminarVivienda.querySelector('.modal-footer #vivienda_id').value = vivienda_id
    })

/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
modalRecuperarVivienda.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let vivienda_id = button.getAttribute('data-bs-id') 
        
        modalRecuperarVivienda.querySelector('.modal-footer #vivienda_id').value = vivienda_id
    })
</script>

</body>
</html>