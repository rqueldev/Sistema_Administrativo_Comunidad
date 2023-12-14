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

      /* if ($datos['rol_id'] == 1) {
            header('Location: inicio-jefe-comunidad.php');

       } else*/
       
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
            $incapacidad_id = '';
            $observaciones = '';

            if (isset($_POST['Añadir'])){

               $habitante_id = valida_campo($_POST['habitante_id']);
               $incapacidad_id = valida_campo($_POST['incapacidad_id']);
               $observaciones = valida_campo($_POST['observaciones']);


                if (empty($habitante_id)) {
                    $error_habitante_id_1 = 'Seleccione un habitante';
                } 

                if (empty($incapacidad_id)) {
                    $error_incapacidad_id_1 = 'Indique el tipo de incapacidad';
                } 
    
                if (strlen($observaciones) > 255) {
                    $error_observaciones_1 = 'El contenido es muy largo';
                }


                /* 1. Verificar que no hay errores para insertar el registro */
                if (empty($error_habitante_id_1) and empty($error_incapacidad_id_1) and empty($error_observaciones_1)){
            
                    /* 2. Verificar que no este el mismo habitante con la misma incapacidad en la Tabla incapacidad_estatus_laboral, para que no hayan duplicados */
                    $buscar_habitante_incapacidad = $conexion->prepare('SELECT * FROM habitante_incapacidad WHERE habitante_id = ? AND incapacidad_id = ? AND estado=?');  
                    $buscar_habitante_incapacidad->execute([$habitante_id,$incapacidad_id,"1"]);
                    $o = [];
                    $o = $buscar_habitante_incapacidad->fetch(PDO::FETCH_ASSOC);

                    if (is_countable($o)){  //ya existe un registro similar
                    
                        echo '
                        <script>
                            alert("Lo sentimos, el habitante seleccionado ya se encuentra registrado con esa incapacidad");
                        </script>
                        ';

                        //limpiar errores
                        $error_habitante_id_1 = '';
                        $error_incapacidad_id_1 = '';
                        $error_observaciones_1 = '';
    
                        //limpiar valores
                        $habitante_id = '';
                        $incapacidad_id = '';
                        $observaciones = '';

                    } else {

                        /* 3. Buscar habitante para verificar el genero */
                        $buscar_habitante = $conexion->prepare('SELECT * FROM habitante WHERE habitante_id = ? AND estado=?');  
                        $buscar_habitante->execute([$habitante_id,"1"]);
                        $r = [];
                        $r = $buscar_habitante->fetch(PDO::FETCH_ASSOC);                     
                        
                        /* 4. Verificar genero para tener Incapacidad = Embarazada */
                        if ($incapacidad_id == 1) {            
            
                            if ($r['genero'] == 'm') {
                            
                                //limpiar errores
                                $error_habitante_id_1 = '';
                                $error_incapacidad_id_1 = '';
                                $error_observaciones_1 = '';
            
                                //limpiar valores
                                $habitante_id = '';
                                $incapacidad_id = '';
                                $observaciones = '';
            
                                echo '
                                <script>
                                    alert("Lo sentimos, el habitante seleccionado es de género masculino");
                                </script>
                                ';
                            } elseif ($r['genero'] == 'f') {                            
                                
                                /* 5. Insertar los datos de Habitante_Incapacidad */
                                $insertar_estatus_incapacidad = $conexion->prepare("INSERT INTO habitante_incapacidad(habitante_id,incapacidad_id,observaciones) VALUES (?,?,?)");
                                $insertar_estatus_incapacidad->execute([$habitante_id,$incapacidad_id,$observaciones]);
        
                                //limpiar errores
                                $error_habitante_id_1 = '';
                                $error_incapacidad_id_1 = '';
                                $error_observaciones_1 = '';
            
                                //limpiar valores
                                $habitante_id = '';
                                $incapacidad_id = '';
                                $observaciones = '';
        
                                $registrado = 'registrado';

                            }
                        }

                        /* 4. Insertar los datos de Habitante_Incapacidad = Enfermo, Discapacitado */
                        if ($incapacidad_id == 2 || $incapacidad_id == 3){                                
                            
                            $insertar_estatus_incapacidad = $conexion->prepare("INSERT INTO habitante_incapacidad(habitante_id,incapacidad_id,observaciones) VALUES (?,?,?)");
                            $insertar_estatus_incapacidad->execute([$habitante_id,$incapacidad_id,$observaciones]);
    
                            //limpiar errores
                            $error_habitante_id_1 = '';
                            $error_incapacidad_id_1 = '';
                            $error_observaciones_1 = '';
        
                            //limpiar valores
                            $habitante_id = '';
                            $incapacidad_id = '';
                            $observaciones = '';
    
                            $registrado = 'registrado';

                        }
                        
                    }
                }  
            }   
            
            
         //----------------------------------------- ACCION ACTUALIZAR ---------------------------------------------//

            if (isset($_POST['Actualizar'])){

               $habitante_incapacidad_id2 = $_POST['habitante_incapacidad_id'];
               $habitante_id_editar = valida_campo($_POST['habitante_id']);
               $incapacidad_id_editar = valida_campo($_POST['incapacidad_id']);
               $observaciones_editar = valida_campo($_POST['observaciones']);


                if (empty($habitante_id_editar)) {
                    $error_habitante_id_editar_1 = 'Seleccione un habitante';
                } 

                if (empty($incapacidad_id_editar)) {
                    $error_incapacidad_id_editar_1 = 'Indique el tipo de incapacidad';
                } 
    
                if (strlen($observaciones_editar) > 255) {
                    $error_observaciones_editar_1 = 'El contenido es muy largo';
                }


                /* 1. Verificar que no hay errores para actualizar el registro */
                if (empty($error_habitante_id_editar_1) and empty($error_incapacidad_id_editar_1) and empty($error_observaciones_editar_1)){
            
                    /* 2. Verificar que no este el mismo habitante con la misma incapacidad en la Tabla habitante_incapacidad, para que no hayan duplicados */
                    $buscar_habitante_incapacidad_editar = $conexion->prepare('SELECT * FROM habitante_incapacidad WHERE md5(habitante_incapacidad_id) <> ? AND habitante_id = ? AND incapacidad_id = ? AND estado=?');  
                    $buscar_habitante_incapacidad_editar->execute([$habitante_incapacidad_id2,$habitante_id_editar,$incapacidad_id_editar,"1"]);
                    $k = [];
                    $k = $buscar_habitante_incapacidad_editar->fetch(PDO::FETCH_ASSOC);

                    if (is_countable($k)){  //ya existe un registro similar
                    
                        echo '
                        <script>
                            alert("Lo sentimos, el habitante seleccionado ya se encuentra registrado con esa incapacidad");
                        </script>
                        ';

                        //limpiar errores
                        $error_habitante_id_editar_1 = '';
                        $error_incapacidad_id_editar_1 = '';
                        $error_observaciones_editar_1 = '';
    
                        //limpiar valores
                        $habitante_id_editar = '';
                        $incapacidad_id_editar = '';
                        $observaciones_editar = '';

                    } else {

                        /* 3. Buscar habitante para verificar el genero */
                        $buscar_habitante_editar = $conexion->prepare('SELECT * FROM habitante WHERE habitante_id = ? AND estado=?');  
                        $buscar_habitante_editar->execute([$habitante_id_editar,"1"]);
                        $f = [];
                        $f = $buscar_habitante_editar->fetch(PDO::FETCH_ASSOC);                     
                        
                        /* 4. Verificar genero para tener Incapacidad = Embarazada */
                        if ($incapacidad_id_editar == 1) {            
            
                            if ($f['genero'] == 'm') {
                            
                                //limpiar errores
                                $error_habitante_id_editar_1 = '';
                                $error_incapacidad_id_editar_1 = '';
                                $error_observaciones_editar_1 = '';
            
                                //limpiar valores
                                $habitante_id_editar = '';
                                $incapacidad_id_editar = '';
                                $observaciones_editar = '';
            
                                echo '
                                <script>
                                    alert("Lo sentimos, el habitante seleccionado es de género masculino");
                                </script>
                                ';
                            } elseif ($f['genero'] == 'f') {                            
                                
                                // funciones para actualizar la fecha en la tabla
                                date_default_timezone_set('America/Caracas');
                                setlocale(LC_TIME, 'spanish');
                                $fecha_actualizacion = date('Y-m-d g:i:s');

                                /* 5. Actualizar los datos de Habitante_Incapacidad */
                                $actualizar = $conexion->prepare("UPDATE habitante_incapacidad SET habitante_id = ?, incapacidad_id = ?, observaciones = ?, fecha_actualizacion = ? WHERE md5(habitante_incapacidad_id) = ?;");
                                $actualizar->execute([$habitante_id_editar, $incapacidad_id_editar, $observaciones_editar, $fecha_actualizacion, $habitante_incapacidad_id2]);
                         
                                //limpiar errores
                                $error_habitante_id_editar_1 = '';
                                $error_incapacidad_id_editar_1 = '';
                                $error_observaciones_editar_1 = '';
            
                                //limpiar valores
                                $habitante_id_editar = '';
                                $incapacidad_id_editar = '';
                                $observaciones_editar = '';
        
                                $actualizado = 'actualizado';

                            }
                        }

                        /* 4. Actualizar los datos de Habitante_Incapacidad = Enfermo, Discapacitado */
                        if ($incapacidad_id_editar == 2 || $incapacidad_id_editar == 3){                                
                            
                            // funciones para actualizar la fecha en la tabla
                            date_default_timezone_set('America/Caracas');
                            setlocale(LC_TIME, 'spanish');
                            $fecha_actualizacion = date('Y-m-d g:i:s');

                            /* 5. Actualizar los datos de Habitante_Incapacidad */
                            $actualizar = $conexion->prepare("UPDATE habitante_incapacidad SET habitante_id = ?, incapacidad_id = ?, observaciones = ?, fecha_actualizacion = ? WHERE md5(habitante_incapacidad_id) = ?;");
                            $actualizar->execute([$habitante_id_editar, $incapacidad_id_editar, $observaciones_editar, $fecha_actualizacion, $habitante_incapacidad_id2]);
                         
                            //limpiar errores
                            $error_habitante_id_editar_1 = '';
                            $error_incapacidad_id_editar_1 = '';
                            $error_observaciones_editar_1 = '';
        
                            //limpiar valores
                            $habitante_id_editar = '';
                            $incapacidad_id_editar = '';
                            $observaciones_editar = '';
    
                            $actualizado = 'actualizado';

                        }
                        
                    }
                }  
            } 


            //----------------------------------------- ACCION ELIMINAR ---------------------------------------------//
            if (isset($_POST['Eliminar'])){

                $habitante_incapacidad_id_borrar = $_POST['habitante_incapacidad_id'];
                $estado = '2';

                $borrar_habitante_incapacidad = $conexion->prepare("UPDATE habitante_incapacidad SET estado =? WHERE md5(habitante_incapacidad_id) = ?;");
                $borrar_habitante_incapacidad->execute([$estado, $habitante_incapacidad_id_borrar]);

                $borrado = 'borrado';

            }

            //----------------------------------------- ACCION RECUPERAR ---------------------------------------------//
            if (isset($_POST['Recuperar'])){

                $habitante_incapacidad_id_recuperar = $_POST['habitante_incapacidad_id'];
                $estado_recuperar = '1';

                $recuperar_habitante_incapacidad = $conexion->prepare("UPDATE habitante_incapacidad SET estado =? WHERE md5(habitante_incapacidad_id) = ?;");
                $recuperar_habitante_incapacidad->execute([$estado_recuperar, $habitante_incapacidad_id_recuperar]);

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


//Información Tabla
$incapacidad = $conexion->prepare('SELECT hi.habitante_incapacidad_id, h.nombre, h.apellido, h.ci_ps_pn, i.incapacidad_nombre, hi.observaciones FROM habitante_incapacidad as hi
INNER JOIN habitante as h
ON hi.habitante_id=h.habitante_id
INNER JOIN incapacidad as i
ON hi.incapacidad_id=i.incapacidad_id
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE hi.estado=? AND h.estado=? AND i.estado=? AND m.numero_manzana=?;');
$incapacidad->execute(["1","1","1",$d['numero_manzana']]);

/* ------------------------------ Select de formulario ingresar --------------------------- */

// habitantes de la manzana seleccionada
$habitante_manzana_seleccionada = $conexion->prepare('SELECT h.habitante_id, h.nombre, h.apellido, h.ci_ps_pn FROM habitante as h
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE h.estado=? AND v.estado=? AND m.estado=? AND m.numero_manzana=?
ORDER BY h.nombre;');
$habitante_manzana_seleccionada->execute(["1","1","1",$d['numero_manzana']]);

/* ------------------------------ Select de formulario editar --------------------------- */

// habitantes de la manzana seleccionada
$habitante_manzana_seleccionada_editar = $conexion->prepare('SELECT h.habitante_id, h.nombre, h.apellido, h.ci_ps_pn FROM habitante as h
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE h.estado=? AND v.estado=? AND m.estado=? AND m.numero_manzana=?
ORDER BY h.nombre;');
$habitante_manzana_seleccionada_editar->execute(["1","1","1",$d['numero_manzana']]);




/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */

$recuperar_incapacidad = $conexion->prepare('SELECT hi.habitante_incapacidad_id, h.nombre, h.apellido, h.ci_ps_pn, i.incapacidad_nombre, hi.observaciones FROM habitante_incapacidad as hi
INNER JOIN habitante as h
ON hi.habitante_id=h.habitante_id
INNER JOIN incapacidad as i
ON hi.incapacidad_id=i.incapacidad_id
INNER JOIN vivienda as v
ON v.vivienda_id=h.vivienda_id
INNER JOIN manzana as m
ON m.manzana_id=v.manzana_id
WHERE hi.estado=? AND m.numero_manzana=?;');
$recuperar_incapacidad->execute(["2",$d['numero_manzana']]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incapacidad</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/incapacidad.css">

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
           <!-- Tabla Incapacidad -->

        <div class="container mover-derecha-tabla col-10 small">
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2"> Datos de Incapacidad</h3>
                <img src="../src/img/enfermedad.png" class="img-fluid d-flex justify-content-center col-3" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir " data-bs-toggle="modal" data-bs-target="#modalIncapacidad" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos_incapacidad" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-info">
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Apellido</th>
                                <th class="text-center">Cedula</th>
                                <th class="text-center">Incapacidad</th>
                                <th class="text-center">Observaciones</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($incapacidad as $i) {
		                    ?>
		                    <tr>
                                <td><?php echo $i['nombre']; ?></td>
			                    <td><?php echo $i['apellido']; ?></td>
			                    <td><?php echo $i['ci_ps_pn']; ?></td>
			                    <td><?php echo $i['incapacidad_nombre']; ?></td>
                                <td><?php echo $i['observaciones']; ?></td>                          
                                <!--------- Botones ------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarIncapacidad" data-bs-id="<?= md5($i['habitante_incapacidad_id']) ; ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminarIncapacidad" data-bs-id="<?= md5($i['habitante_incapacidad_id']) ?>"> 
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
           <!-- Tabla Incapacidad -->

           <div class="container mover-derecha-tabla col-10 pt-5 pb-3 small">
           <hr class="mt-5 line">
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2"> Registros Eliminados</h3>
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-5">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="tabla_recuperacion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-dark">
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Apellido</th>
                                <th class="text-center">Cedula</th>
                                <th class="text-center">Incapacidad</th>
                                <th class="text-center">Observaciones</th>
                                <th class="text-center no-exportar">Recuperar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($recuperar_incapacidad as $i) {
		                    ?>
		                    <tr>
                                <td><?php echo $i['nombre']; ?></td>
			                    <td><?php echo $i['apellido']; ?></td>
			                    <td><?php echo $i['ci_ps_pn']; ?></td>
			                    <td><?php echo $i['incapacidad_nombre']; ?></td>
                                <td><?php echo $i['observaciones']; ?></td>                          
                                <!--------- Botones ------------> 
			                    <td class="d-flex justify-content-center">
                                    <a href="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalRecuperarIncapacidad" data-bs-id="<?= md5($i['habitante_incapacidad_id']) ; ?>"> 
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalIncapacidad" tabindex="-1" aria-labelledby="modalIncapacidadLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalIncapacidadLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" method="POST" id="formulario">
                        <div class="modal-content border-white">

                            <img src="../src/img/incapacidad_modal.png" class="mb-2 modal_imagen" alt="">

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
                                    <label for="incapacidad_id" class="form-label fw-medium"> Incapacidad:</label>
                                    <select name="incapacidad_id" id="incapacidad_id" class="form-select" value="<?php echo $incapacidad_id; ?>">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Embarazada</option>
                                        <option value="2">Enfermo</option>
                                        <option value="3">Discapacitado</option>
                                    </select>

                                        <?php if(!empty($error_incapacidad_id_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_incapacidad_id_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label fw-medium" >Observaciones:</label>
                                <input type="textarea" name="observaciones" id="observaciones" class="form-control" value="<?php echo $observaciones; ?>">
                                    <?php if(!empty($error_observaciones_1)){ ?>
                                    <p class='small text-danger error'><?php echo "$error_observaciones_1" ?></p>
                                    <?php }; ?>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEditarIncapacidad" tabindex="-1" aria-labelledby="modalEditarIncapacidadLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEditarIncapacidadLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                        <div class="modal-content border-white">

                        <img src="../src/img/incapacidad_modal.png" class="mb-2 modal_imagen" alt="">

                            <input type="hidden" id="habitante_incapacidad_id" name="habitante_incapacidad_id" > <!--al seleccionar el registro le vamos a pasar el id para poder actualizarlo-->

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
                                    <label for="incapacidad_id" class="form-label fw-medium"> Incapacidad:</label>
                                    <select name="incapacidad_id" id="incapacidad_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Embarazada</option>
                                        <option value="2">Enfermo</option>
                                        <option value="3">Discapacitado</option>
                                    </select>
                                    
                                        <?php if(!empty($error_incapacidad_id_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_incapacidad_id_editar_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label fw-medium" >Observaciones:</label>
                                <input type="textarea" name="observaciones" id="observaciones" class="form-control">
                                    <?php if(!empty($error_observaciones_editar_1)){ ?>
                                    <p class='small text-danger error'><?php echo "$error_observaciones_editar_1" ?></p>
                                    <?php }; ?>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEliminarIncapacidad" tabindex="-1" aria-labelledby="modalEliminarIncapacidadLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEliminarIncapacidadLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea eliminar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="habitante_incapacidad_id" id="habitante_incapacidad_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Eliminar" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div>

        
        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalRecuperarIncapacidad" tabindex="-1" aria-labelledby="modalRecuperarIncapacidadLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRecuperarIncapacidadLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Seguro que desea recuperar el registro?
                  </div>
                  <div class="modal-footer">
                    <form action="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="habitante_incapacidad_id" id="habitante_incapacidad_id" >
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
    <script src="../src/js/incapacidad_manzana.js"></script>

    <script>

/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalEditarIncapacidad = document.getElementById('modalEditarIncapacidad')  //id de la ventana modal Editar Registro
    let modalEliminarIncapacidad = document.getElementById('modalEliminarIncapacidad')  //id de la ventana modal Eliminar Registro
    let modalRecuperarIncapacidad = document.getElementById('modalRecuperarIncapacidad')


    modalEditarIncapacidad.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let habitante_incapacidad_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        /*1. Seleccionamos los inputs del formulario */
        let inputHabitanteIncapacidadId = modalEditarIncapacidad.querySelector('.modal-body #habitante_incapacidad_id')              //selecionamos la clase y el id presentes en el formulario
        let inputHabitanteId = modalEditarIncapacidad.querySelector('.modal-body #habitante_id')   
        let inputIncapacidadId = modalEditarIncapacidad.querySelector('.modal-body #incapacidad_id')
        let inputObservaciones = modalEditarIncapacidad.querySelector('.modal-body #observaciones')

        /*2. Obtenemos los datos de la BD y los enviamos en formato json */
        let url = "incapacidad_get.php?id_jefe=<?php echo $id_jefe ?>"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('habitante_incapacidad_id', habitante_incapacidad_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputHabitanteIncapacidadId.value = data.habitante_incapacidad_id
            inputHabitanteId.value = data.habitante_id
            inputIncapacidadId.value = data.incapacidad_id
            inputObservaciones.value = data.observaciones
            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
modalEliminarIncapacidad.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let habitante_incapacidad_id = button.getAttribute('data-bs-id') 
        
        modalEliminarIncapacidad.querySelector('.modal-footer #habitante_incapacidad_id').value = habitante_incapacidad_id
    })



    
/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
modalRecuperarIncapacidad.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let habitante_incapacidad_id = button.getAttribute('data-bs-id') 
        
        modalRecuperarIncapacidad.querySelector('.modal-footer #habitante_incapacidad_id').value = habitante_incapacidad_id
    })
</script>

</body>
</html>