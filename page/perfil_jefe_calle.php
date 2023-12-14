<?php

session_start();
include 'conexion.php';
include 'validar_campo.php';

$sesion = $_SESSION['user'];

if ($sesion == null or $sesion = '') {
   // echo 'Usted no tiene autorizacion!';
    header('Location: principal.html');   //cual sea tu gusto 
    die();

  /* 1. Verificar si ya hay una sesión activa */  
} else if(isset($_SESSION['user'])){

    $rescatar_sesion = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña, rol_id FROM usuario WHERE nombre_usuario=? ');   
    $rescatar_sesion->execute([$_SESSION['user']]);

    $datos = $rescatar_sesion->fetch(PDO::FETCH_ASSOC); 
    
    /* 2. Si el usuario esxiste verificar roles */
    if (is_countable($datos)) {   

       if ($datos['rol_id'] == 2) {

        $id_jefe = $_SESSION['user'];

        /* 3. Obtener datos de esta sesion como nombre y apellido del jefe de calle y numero de manzana bajo su cargo*/
        $datos_sesion = $conexion->prepare('SELECT j.jefe_calle_id, j.usuario_id, j.ci_ps, j.nombre, j.apellido, j.correo, u.nombre_usuario FROM jefe_calle AS j
        INNER JOIN usuario AS u
        ON u.usuario_id=j.usuario_id
        WHERE j.estado=? AND u.estado=? AND u.nombre_usuario=?');
        $datos_sesion->execute(["1","1",$id_jefe]);   
                 
        foreach ($datos_sesion as $d) {
            $d['jefe_calle_id'];
            $d['usuario_id'];
            $d['ci_ps'];
            $d['nombre'];
            $d['apellido'];
            $d['correo'];
            $d['nombre_usuario'];
    
        }


          //------------------------------------------- ACCION EDITAR ------------------------------------------//

          if (isset($_POST['EditarPerfil'])){

            $jefe_calle_id2 = $_POST['jefe_calle_id'];

            $usuario_id_editar = valida_campo($_POST['usuario_id']);
            $ci_ps_editar = valida_campo($_POST['ci_ps']);
            $nombre_editar = valida_campo($_POST['nombre']);
            $apellido_editar = valida_campo($_POST['apellido']);
            $correo_editar = valida_campo($_POST['correo']);
            $user_editar = valida_campo($_POST['user']);


            if (empty($ci_ps_editar)) {
                $error_ci_ps_editar_1 = 'Coloque una cédula';
            } else {
                if (strlen($ci_ps_editar) > 8) {
                    $error_ci_ps_editar_2 = 'La cédula no puede tener más de 8 caracteres';
                }
            }

            if (empty($nombre_editar)) {
                $error_nombre_editar_1 = 'Coloque un nombre';
            } else {
                if (strlen($nombre_editar) > 15) {
                    $error_nombre_editar_2 = 'El nombre es muy largo';
                }
            } 
        
            if (empty($apellido_editar)) {
                $error_apellido_editar_1 = 'Coloque un apellido';
            }else {
                if (strlen($apellido_editar) > 15) {
                    $error_apellido_editar_2 = 'El apellido es muy largo';
                }
            }   
    
        
            if (empty($correo_editar)) {
                $error_correo_editar_1 = 'Coloca un email';
            } else {                                    
                if (!filter_var($correo_editar, FILTER_VALIDATE_EMAIL)) {    // El campo email ya trae por defecto una alarma para esto
                    $error_correo_editar_2 = 'Ingresa un correo válido';    
                }
            }  
        
            if (empty($user_editar)) {
                $error_user_editar_1 = 'Coloca un usuario';
            } else {
                if (strlen($user_editar) > 10) {
                    $error_user_editar_2 = 'El nombre de usuario es muy largo';
                }
            }


            /* 1. Verificamos que no hay errores para actualizar el registro */
            if (empty($error_nombre_editar_1) and empty($error_nombre_editar_2) and empty($error_apellido_editar_1) and empty($error_apellido_editar_2) 
                and empty($error_ci_ps_editar_1) and empty($error_ci_ps_editar_2) and empty($error_correo_editar_1) and empty($error_correo_editar_2)
                and empty($error_user_editar_1) and empty($error_user_editar_2)){

                    /* 2. Verificamos que el nombre de usuario "nuevo" no pertenezcan a algun otro usuario */
                    $buscar_usuario_editar = $conexion->prepare("SELECT * FROM usuario WHERE md5(usuario_id) <> ? AND nombre_usuario = ? AND estado=?");
                    $buscar_usuario_editar->execute([$usuario_id_editar,$user_editar,"1"]);
                    $rows_editar = $buscar_usuario_editar->rowcount();
                                
                    $res = [];
                    $res = $buscar_usuario_editar->fetch(PDO::FETCH_ASSOC);

                    if ($rows_editar > 0) {

                        echo '
                        <script>
                                alert("Lo sentimos, el nombre de usuario le pertenece a otro usuario");
                        </script>
                        ';

                        //limpiar errores
                        $error_nombre_editar_1 = '';
                        $error_nombre_editar_2 = '';
                        $error_apellido_editar_1 = '';
                        $error_apellido_editar_2 = '';
                        $error_ci_ps_editar_1 = '';
                        $error_ci_ps_editar_2 = '';
                        $error_correo_editar_1 = '';
                        $error_correo_editar_2 = '';
                        $error_user_editar_1 = '';
                        $error_user_editar_2 = '';

                        //limpiar valores
                        $ci_ps_editar = '';
                        $nombre_editar = '';
                        $apellido_editar = '';
                        $correo_editar = '';
                        $user_editar = '';
                        $usuario_id_editar = '';

                    } else {                     
                        
                        /* 3. Actualizar los datos */

                        // funciones para actualizar la fecha en la tabla
                        date_default_timezone_set('America/Caracas');
                        setlocale(LC_TIME, 'spanish');
                        $fecha_actualizacion = date('Y-m-d g:i:s'); 
    
                        /* 4. Actualizamos los datos de Jefe de Calle */
                        $actualizar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET ci_ps = ?, nombre = ?, apellido = ?, correo = ?, fecha_actualizacion = ?  WHERE md5(jefe_calle_id) = ?;");
                        $actualizar_jefe_calle->execute([$ci_ps_editar,$nombre_editar,$apellido_editar,$correo_editar,$fecha_actualizacion,$jefe_calle_id2]);
                                            
                        /* 5. Actualizamos los datos de usuario */
                        $actualizar_usuario = $conexion->prepare("UPDATE usuario SET nombre_usuario = ?, fecha_actualizacion = ?  WHERE md5(usuario_id) = ?;");
                        $actualizar_usuario->execute([$user_editar,$fecha_actualizacion,$usuario_id_editar]);
    
    
                        //limpiar errores
                        $error_nombre_editar_1 = '';
                        $error_nombre_editar_2 = '';
                        $error_apellido_editar_1 = '';
                        $error_apellido_editar_2 = '';
                        $error_ci_ps_editar_1 = '';
                        $error_ci_ps_editar_2 = '';
                        $error_correo_editar_1 = '';
                        $error_correo_editar_2 = '';
                        $error_user_editar_1 = '';
                        $error_user_editar_2 = '';
    
                        session_unset();  //limpiar variables de sesion
                        session_destroy();  //destruir la sesion
                        session_start(); //reanuda la sesion
                        $_SESSION['user'] = $user_editar;           
                        
                        /* 3. Obtener datos de esta sesion como nombre y apellido del jefe de calle y numero de manzana bajo su cargo*/
                        $datos_sesion = $conexion->prepare('SELECT j.jefe_calle_id, j.usuario_id, j.ci_ps, j.nombre, j.apellido, j.correo, u.nombre_usuario FROM jefe_calle AS j
                        INNER JOIN usuario AS u
                        ON u.usuario_id=j.usuario_id
                        WHERE j.estado=? AND u.estado=? AND u.nombre_usuario=?');
                        $datos_sesion->execute(["1","1",$_SESSION['user']]);   
                                 
                        foreach ($datos_sesion as $d) {
                            $d['jefe_calle_id'];
                            $d['usuario_id'];
                            $d['ci_ps'];
                            $d['nombre'];
                            $d['apellido'];
                            $d['correo'];
                            $d['nombre_usuario'];
                    
                        }          
    
                        //limpiar valores
                        $ci_ps_editar = '';
                        $nombre_editar = '';
                        $apellido_editar = '';
                        $correo_editar = '';
                        $user_editar = '';
                        $usuario_id_editar = '';
    
    
                        $actualizado = 'actualizado';
                        
                    }
            }
          }

            //------------------------------------------- ACCION CAMBIAR CONSTRASEÑA ------------------------------------------//

            $password = '';

            //patron admitido para contraseñas, contiene lo que requiere una contraseña fuerte 
            $pattern = '(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W)[a-zA-Z0-9\W]';  
            

            if (isset($_POST['CambiarContraseña'])){

                $usuario_id = $_POST['usuario_id'];
                $password = valida_campo($_POST['password']);

                if (empty($password)) {
                    $error_password_1 = 'Coloca la contraseña';
                } else {
                    if (strlen($password) < 6) {
                        $error_password_2 = 'Contraseña no puede tener menos de 6 carácteres';
                    }
                    if (strlen($password) > 10) {
                        $error_password_3 = 'Contraseña no puede tener mas de 10 carácteres';
                    }
                    if (!preg_match('/^'.$pattern.'+$/', $password)) {
                        $error_password_4 = 'No es una contraseña fuerte';
                    }
                    else {
                       // $error_password_4 = 'Es una contraseña fuerte';
                    }

                    /* 1. Verificamos que no hay errores para insertar el registro */
                    if (empty($error_password_1) and empty($error_password_2) and empty($error_password_3) 
                        and empty($error_password_4)){   

                            /* 5. Actualizamos los datos de usuario */

                            // funciones para actualizar la fecha en la tabla
                            date_default_timezone_set('America/Caracas');
                            setlocale(LC_TIME, 'spanish');
                            $fecha_actualizacion_password = date('Y-m-d g:i:s'); 

                            $md5password = md5($password);
                            $cambiar_contraseña = $conexion->prepare("UPDATE usuario SET contraseña = ?, fecha_actualizacion = ?  WHERE md5(usuario_id) = ?;");
                            $cambiar_contraseña->execute([$md5password,$fecha_actualizacion_password,$usuario_id]);

                                //limpiar errores
                                $error_password_1 = '';
                                $error_password_2 = '';
                                $error_password_3 = '';
                                $error_password_4 = '';

                                //limpiar valore
                                $password = '';

                            $actualizado = 'actualizado';
    
                        } 
                     

                }
            }


        } elseif ($datos['rol_id'] == 1) { 
            header('Location: inicio-jefe-comunidad.php');

        } else {    //No puede haber otro rol que no sea especificado 
            session_destroy();

       }
        
    } else {

        session_destroy();
        
    }

}





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/perfil-lider-calle.css">

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
                <a href="inicio-lider-calle.php" class="text-white d-none d-sm-inline text-decoration-none d-flex align-items-center ms-4" role="button">
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

            <!-- Bienvenida -->

        <div class="container col-10  bajar">
            <h3 class="text-dark d-flex justify-content-center pt-4 lead fs-2">Mi Perfil</h3>
            <div class="d-flex pt-4 contenido">            
                
                <img src="../src/img//mi_perfil.png" class="mx-3 img_perfil" alt="">
                
                <div class="d-flex flex-column">
                    <div class="d-flex flex-row">
                        <h6 class="lead fs-6 fw-normal mt-2 mx-3">Nombre:</h6> 
                        <p class="mt-1 lead fs-6"><?php echo $d['nombre'];?></p>
                    </div>
                    <div class="d-flex flex-row">
                        <h6 class="lead fs-6 fw-normal mt-2 mx-3">Apellido:</h6> 
                        <p class="mt-1 lead fs-6"><?php echo $d['apellido'];?></p>
                    </div>
                    <div class="d-flex flex-row">
                        <h6 class="lead fs-6 fw-normal mt-2 mx-3">Cédula:</h6> 
                        <p class="mt-1 lead fs-6"><?php echo $d['ci_ps'];?></p>
                    </div>                    
                    <div class="d-flex flex-row">
                        <h6 class="lead fs-6 fw-normal mt-2 mx-3">Correo:</h6> 
                        <p class="mt-1 lead fs-6"><?php echo $d['correo'];?></p>
                    </div>
                    <div class="d-flex flex-row">
                        <h6 class="lead fs-6 fw-normal mt-2 mx-3">Nombre de Usuario:</h6> 
                        <p class="mt-1 lead fs-6"><?php echo $d['nombre_usuario'];?></p>
                    </div>
                </div>

            </div>
            <div class="contenido col-7 mt-5 d-flex justify-content-center">
                <button class="px-3 py-2 btn boton_perfil" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil" data-bs-id="<?= md5($d['jefe_calle_id']); ?>">Editar perfil</button>
                <button class="mx-3 px-3 py-2 btn boton_contraseña" data-bs-toggle="modal" data-bs-target="#modalCambiarContraseña" data-bs-id="<?= md5($d['usuario_id']); ?>">Cambiar contraseña</button>
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

        <!-- Modal Editar Perfil -->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false"  id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                  <div class="modal-header modal_perfil">
                    <h1 class="modal-title fs-5" id="modalEditarPerfilLabel">Editar Perfil</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="perfil_jefe_calle.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                        <div class="modal-content border-white">

                            <img src="../src/img/mi_perfil.png" class="w-25 mb-4 modal_imagen" alt="">
                            
                            <input type="hidden" id="jefe_comunidad_id" name="jefe_calle_id" value="<?php echo md5($d['jefe_calle_id']); ?>">
                            <input type="hidden" id="usuario_id" name="usuario_id" value="<?php echo md5($d['usuario_id']); ?>">

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4">
                                    <label for="ci_ps" class="form-label fw-medium" > Cédula:</label>
                                    <input type="number" name="ci_ps" id="ci_ps" class="form-control"  value="<?php echo $d['ci_ps']; ?>">
                                        <?php if(!empty($error_ci_ps_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_ci_ps_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_ci_ps_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_ci_ps_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                                <div class="col-4">
                                    <label for="nombre" class="form-label fw-medium" > Nombre:</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control"  value="<?php echo $d['nombre']; ?>">
                                        <?php if(!empty($error_nombre_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_nombre_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_nombre_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_nombre_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
    
                                <div class="col-4">
                                    <label for="apellido" class="form-label fw-medium" > Apellido:</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control"  value="<?php echo $d['apellido'];?>">
                                        <?php if(!empty($error_apellido_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_apellido_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_apellido_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_apellido_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">
                                    <label for="correo" class="form-label fw-medium">Correo Electrónico:</label>
                                    <input type="email" name="correo" id="correo" class="form-control" value="<?php echo $d['correo']; ?>">
                                        <?php if(!empty($error_correo_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_correo_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_correo_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_correo_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-6">
                                    <label for="user" class="form-label fw-medium" > Nombre de Usuario:</label>
                                    <input type="text" name="user" id="user" class="form-control" value="<?php echo $d['nombre_usuario']; ?>">
                                    <?php if(!empty($error_user_editar_1)){ ?>
                                        <p class='small text-danger error m-0'><?php echo "$error_user_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_user_editar_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_user_editar_2" ?></p>
                                        <?php }
                                        }; ?> 
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" id= "EditarPerfil" name="EditarPerfil" class="btn btn-primary"><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar cambios</button>
                            </div>
                        </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                     
                  </div>
                </div>
              </div>
        </div>
        
     
    <!-- Modal Cambiar contraseña -->
    <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false"  id="modalCambiarContraseña" tabindex="-1" aria-labelledby="modalCambiarContraseñaLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content ">
                  <div class="modal-header modal_contraseña">
                    <h1 class="modal-title fs-5" id="modalCambiarContraseñaLabel">Editar Perfil</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="perfil_jefe_calle.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                        <div class="modal-content border-white">

                            <img src="../src/img/password.png" class="w-25 mb-4 modal_imagen" alt="">
                            
                            <input type="hidden" id="usuario_id" name="usuario_id" value="<?php echo md5($d['usuario_id']); ?>">

                            <div class="mb-3 ">
                                <label for="password" class="form-label fw-medium">Ingrese su nueva contraseña:</label>
                                <input type="password" name="password" id="password" class="form-control" value="<?php echo $password; ?>">
                                   <?php if(!empty($error_password_1)){ ?>
                                   <p class='small text-danger error m-0'><?php echo "$error_password_1" ?></p>
                                   <?php } else {
                                   if(!empty($error_password_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_password_2" ?></p>
                                   <?php }
                                   if(!empty($error_password_3)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_password_3" ?></p> 
                                   <?php } 
                                   if (!empty($error_password_4)) { ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_password_4" ?></p> 
                                   <?php } 
                                }; ?> 

                            <div>

                                <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" id= "CambiarContraseña" name="CambiarContraseña" class="btn btn-primary mt-3" ><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar</button>
                            </div>
                        </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                     
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
 /*   let modalEditarPerfil = document.getElementById('modalEditarPerfil')  //id de la ventana modal Editar Registro

    modalEditarPerfil.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let jefe_calle_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputJefeCalleId = modalEditarPerfil.querySelector('.modal-body #jefe_calle_id')              //selecionamos la clase y el id presentes en el formulario
        let inputUserId = modalEditarPerfil.querySelector('.modal-body #usuario_id')
        let inputCedula = modalEditarPerfil.querySelector('.modal-body #ci_ps')
        let inputNombre = modalEditarPerfil.querySelector('.modal-body #nombre')
        let inputApellido = modalEditarPerfil.querySelector('.modal-body #apellido')
        let inputCorreo = modalEditarPerfil.querySelector('.modal-body #correo')
        let inputUser = modalEditarPerfil.querySelector('.modal-body #user')


        let url = "perfil_jefe_calle_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('jefe_calle_id', jefe_calle_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputJefeCalleId.value = data.jefe_calle_id      //lo que viene a continuacion de data. es el campo al cual se llama en la consulta a la BD
            inputUserId.value = data.usuario_id
            inputCedula.value = data.ci_ps
            inputNombre.value = data.nombre
            inputApellido.value = data.apellido
            inputCorreo.value = data.correo
            inputUser.value = data.nombre_usuario

            console.dir(data)
        }).catch(err => console.log(err))
    })
    </script>
</body>
</html>