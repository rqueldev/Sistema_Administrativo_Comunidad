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

     //----------------------------------------- ACCION ACTUALIZAR ---------------------------------------------//
            $usuario_id2 = '';
            $user_editar = '';
            $rol_id_editar = '';
            
            if (isset($_POST['Actualizar'])){

                $usuario_id2 = $_POST['usuario_id'];
                $user_editar = valida_campo($_POST['user']);
                $rol_id_editar = valida_campo($_POST['rol_id']);


                if (empty($user_editar)) {
                    $error_user_editar_1 = 'Coloque un usuario';
                } else {
                    if (strlen($user_editar) > 10) {
                        $error_user_editar_2 = 'El nombre de usuario es muy largo';
                    }
                }

                if (empty($rol_id_editar)) {                      // Igual el select ya trae una alarma automatica para que se elija una opcion
                    $error_rol_id_editar_1 = 'Asigne un rol ';
                }
                

                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_user_editar_1) and empty($error_user_editar_2) and empty($error_rol_id_editar_1)){
                    
                    /* 2. Verificamos que el nombre de usuario "nuevo" no pertenezcan a algun otro usuario */
                    $buscar_usuario_editar = $conexion->prepare("SELECT * FROM usuario WHERE md5(usuario_id) <> ? AND nombre_usuario = ? AND estado=?");
                    $buscar_usuario_editar->execute([$usuario_id2,$user_editar,"1"]);
                    $rows_editar = $buscar_usuario_editar->rowcount();
                                
                    $res = [];
                    $res = $buscar_usuario_editar->fetch(PDO::FETCH_ASSOC);

                    if ($rows_editar > 0){
                        echo '
                        <script>
                                alert("Lo sentimos, el nombre de usuario le pertenece a otro usuario");
                        </script>
                        ';

                        //limpiar errores
                        $error_user_editar_1 = '';
                        $error_user_editar_2 = '';
                        $error_rol_id_editar_1 = '';

                        //limpiar valores
                        $user_editar = '';
                        $rol_id_editar = '';

                    } else {

                        date_default_timezone_set('America/Caracas');
                        setlocale(LC_TIME, 'spanish');
                        $fecha_actualizacion = date('Y-m-d g:i:s');

                        /* 3. Actualizamos los datos de usuario */
                        $actualizar_usuario = $conexion->prepare("UPDATE usuario SET nombre_usuario = ?, rol_id = ?, fecha_actualizacion = ? WHERE md5(usuario_id) = ?;");
                        $actualizar_usuario->execute([$user_editar,$rol_id_editar,$fecha_actualizacion,$usuario_id2]);

                        $actualizado = 'actualizado';

                        //limpiar errores
                        $error_user_editar_1 = '';
                        $error_user_editar_2 = '';
                        $error_rol_id_editar_1 = '';

                        //limpiar valores
                        $user_editar = '';
                        $rol_id_editar = '';

                    }
                }
            }


         //----------------------------------------- ACCION ELIMINAR ---------------------------------------------//

            if (isset($_POST['Eliminar'])) {    
                
                $usuario_id_borrar = $_POST['usuario_id'];
                $estado = '2';


                /* 1. Eliminar Usuario */
                $borrar_usuario = $conexion->prepare("UPDATE usuario SET estado =? WHERE md5(usuario_id) = ?;");
                $borrar_usuario->execute([$estado, $usuario_id_borrar]);

                /* 2. Eliminar Tabla involucrada: Jefe Calle*/
                $borrar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET estado =? WHERE md5(usuario_id) = ?;");
                $borrar_jefe_calle->execute([$estado, $usuario_id_borrar]);

                $borrado = 'borrado';
            }


         //----------------------------------------- ACCION RECUPERAR ---------------------------------------------//

            if (isset($_POST['Recuperar'])) {    
                
                $usuario_id_recuperar = $_POST['usuario_id'];
                $estado_recuperar = '1';


                /* 1. Eliminar Usuario */
                $recuperar_usuario = $conexion->prepare("UPDATE usuario SET estado =? WHERE md5(usuario_id) = ?;");
                $recuperar_usuario->execute([$estado_recuperar, $usuario_id_recuperar]);

                /* 2. Eliminar Tabla involucrada: Jefe Calle*/
                $borrar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET estado =? WHERE md5(usuario_id) = ?;");
                $borrar_jefe_calle->execute([$estado_recuperar, $usuario_id_recuperar]);

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


// Informacion de la Tabla Usuario
$usuario = $conexion->prepare('SELECT u.usuario_id, u.nombre_usuario, r.descripcion, u.fecha_creacion, u.fecha_actualizacion FROM usuario AS u
INNER JOIN rol AS r
ON u.rol_id=r.rol_id WHERE u.estado=? AND r.estado=?;');
$usuario->execute(["1","1"]);

/* ------------------------ Select de Formulario editar ----------------------------------- */
//Roles de usuario
$rol_editar = $conexion->prepare('SELECT rol_id, descripcion FROM rol WHERE estado = ?');
$rol_editar->execute(["1"]);



/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */
// Informacion de la Tabla de Recuperación
$recuperar_usuario = $conexion->prepare('SELECT u.usuario_id, u.nombre_usuario, r.descripcion, u.fecha_creacion, u.fecha_actualizacion FROM usuario AS u
INNER JOIN rol AS r
ON u.rol_id=r.rol_id WHERE u.estado=?;');
$recuperar_usuario->execute(["2"]);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">
    
    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/usuarios.css">

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
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Usuarios</h3>
                <img src="../src/img/usuario1.png" class="img-fluid d-flex justify-content-center col-4" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos-usuario" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-info">
                                <th class="text-center">Usuario</th>
                                <th class="text-center ">Rol</th>
                                <th class="text-center">Creación</th>
                                <th class="text-center ">Actualización</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($usuario as $u) {
		                    ?>
		                    <tr>
			                    <td><?php echo $u['nombre_usuario']; ?></td>
			                    <td><?php echo $u['descripcion']; ?></td>
                                <td><?php echo $u['fecha_creacion']; ?></td>
			                    <td><?php echo $u['fecha_actualizacion']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="usuarios.php" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalUsuarioEditar" data-bs-id="<?= md5($u['usuario_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="usuarios.php" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalUsuarioEliminar" data-bs-id="<?= md5($u['usuario_id']); ?>"> 
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
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-5">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="tabla_recuperacion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-dark">
                                <th class="text-center">Usuario</th>
                                <th class="text-center ">Rol</th>
                                <th class="text-center">Creación</th>
                                <th class="text-center ">Actualización</th>
                                <th class="text-center no-exportar">Recuperar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($recuperar_usuario as $u) {
		                    ?>
		                    <tr>
			                    <td><?php echo $u['nombre_usuario']; ?></td>
			                    <td><?php echo $u['descripcion']; ?></td>
                                <td><?php echo $u['fecha_creacion']; ?></td>
			                    <td><?php echo $u['fecha_actualizacion']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-center">
                                    <a href="usuarios.php" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalUsuarioRecuperar" data-bs-id="<?= md5($u['usuario_id']); ?>"> 
                                    <i class="fa-solid fa-trash-arrow-up"></i></a>

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
        
        <!-- Modal Editar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalUsuarioEditar" tabindex="-1" aria-labelledby="modalUsuarioEditarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalUsuarioEditarLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="usuarios.php" method="POST">
                        <div class="modal-content border-white">

                        <img src="../src/img/habitante_modal.jpg" class="w-25 mb-4 modal_imagen" alt="">
                        
                            <input type="hidden" id="usuario_id" name="usuario_id">

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">  <!---------------------------------->
                                    <label for="user" class="form-label fw-medium" > Nombre de Usuario:</label>
                                    <input type="text" name="user" id="user" class="form-control" >
                                        <?php if(!empty($error_user_editar_1)){ ?>
                                        <p class='small text-danger error m-0'><?php echo "$error_user_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_user_editar_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_user_editar_2" ?></p>
                                        <?php }
                                        }; ?> 
                                </div>

                                <div class="col-6">  <!---------------------------------->
                                    <label for="rol_id" class="form-label fw-medium">Rol:</label>
                                    <select name="rol_id" id="rol_id" class="form-select" required >
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($rol_editar as $r){
                                        ?> 
                                            <option value="<?php echo $r['rol_id']; ?>"><?php echo $r['descripcion'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                        <?php if(!empty($error_rol_id_editar_1)): ?>
                                        <p class='small text-danger error'><?php echo "$error_rol_id_editar_1" ?></p>
                                        <?php endif; ?> 
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalUsuarioEliminar" tabindex="-1" aria-labelledby="modalUsuarioEliminarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalUsuarioEliminarLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Al eliminar el usuario, se eliminarán también los datos del Jefe de Calle vinculado.</p>
                    <h6>¿Seguro que desea eliminar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="usuarios.php" method="POST">
                            <input type="hidden" name="usuario_id" id="usuario_id">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 


        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalUsuarioRecuperar" tabindex="-1" aria-labelledby="modalUsuarioRecuperarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalUsuarioRecuperarLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Al recuperar el usuario, se recuperaran también los datos del Jefe de Calle vinculado.</p>
                    <h6>¿Seguro que desea recuperar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="usuarios.php" method="POST">
                            <input type="hidden" name="usuario_id" id="usuario_id">
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
    <script src="../src/js/usuariooos.js"></script>

    <script>

/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalUsuarioEditar = document.getElementById('modalUsuarioEditar')  //id de la ventana modal Editar Registro
    let modalUsuarioEliminar = document.getElementById('modalUsuarioEliminar')  //id de la ventana modal Eliminar Registro
    let modalUsuarioRecuperar = document.getElementById('modalUsuarioRecuperar')

    modalUsuarioEditar.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let usuario_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputUsuarioId = modalUsuarioEditar.querySelector('.modal-body #usuario_id')              //selecionamos la clase y el id presentes en el formulario
        let inputUser = modalUsuarioEditar.querySelector('.modal-body #user')
        let inputRolId = modalUsuarioEditar.querySelector('.modal-body #rol_id')


        let url = "usuario_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('usuario_id', usuario_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputUsuarioId.value = data.usuario_id
            inputUser.value = data.nombre_usuario
            inputRolId.value = data.rol_id
             
            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
    modalUsuarioRecuperar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let usuario_id = button.getAttribute('data-bs-id') 
        
        modalUsuarioRecuperar.querySelector('.modal-footer #usuario_id').value = usuario_id
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalUsuarioEliminar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let usuario_id = button.getAttribute('data-bs-id') 
        
        modalUsuarioEliminar.querySelector('.modal-footer #usuario_id').value = usuario_id
    })

</script>

</body>
</html>