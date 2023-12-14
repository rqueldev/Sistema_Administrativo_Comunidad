<?php

session_start();
include 'conexion.php';


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

      /* if ($datos['rol_id'] == 1 ) {
        header('Location: inicio-jefe-comunidad.php');

       } else*/ 
       
       if ($datos['rol_id'] == 1 || $datos['rol_id'] == 2) {   
        
        $id_jefe = ($datos['rol_id'] == 2)?$_SESSION['user']:$_GET['id_jefe'];

        /* 3. Obtener datos de esta sesion como nombre y apellido del jefe de calle y numero de manzana bajo su cargo*/
        $datos_sesion = $conexion->prepare('SELECT j.jefe_calle_id, j.ci_ps, j.nombre, j.apellido, (SELECT nombre_usuario FROM usuario WHERE usuario_id = j.usuario_id) as nb_usuario, j.correo, m.numero_manzana, u.nombre_usuario FROM jefe_calle AS j
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
            $d['nb_usuario'];
            $d['correo'];
            $d['numero_manzana'];
    
        }
               
        } else {    //No puede haber otro rol que no sea especificado 
        session_destroy();

       }
        

    } else {

        session_destroy();
        
    }

  /*  echo '
    <script>
           alert("Usuario: '.$_SESSION['user'].'");
    </script>
   '; 
*/
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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/inicio-lider-call.css">

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


            <!-- Bienvenida -->

        <div class="container col-10 bajar">
            <h3 class="text-dark d-flex justify-content-center pt-2 lead fs-1">Comunidad Sector Universitario Oeste</h3>

            <div class="d-flex contenido">

                <div class="d-flex justify-content-center position-relative pb-5">
                    <div>
                        <h1 class="text-dark text-center position-absolute bienvenido pt-5 lead fs-2">¡Bienvenido!</h1>
                    </div>
                    <div>
                        <img src="../src/img/inicio.png" alt="" class="img-fluid inicio-img" height="100%">
                    </div>
                </div>

                <div>
                <div class="d-flex flex-column justify-content-between align-items-center" height="20%">
                    <div class="card mx-5 px-3 p-1 shadow-sm border-0 bg-info">
                        <div class="card-content d-flex flex-column ">
                           <h6 class="entidad pl-1 text-center text-white lead fs-5">Manzana</h6>
                           <h3 class="number pl-1 text-center text-white lead fs-2"><?php echo $d['numero_manzana']?></h3>
                        </div>
                    </div>
                    
                    <?php 
                    // Se muestra el botón volver si el usuario es administrador
                    if ($datos['rol_id'] == 1) { ?>
                    <div class="mt-5 shadow-sm border-0 ">                                
                        <a href="manzana.php" class="btn btn-sm p-2 boton_volver"> 
                        <i class="fa-solid fa-circle-left "></i><strong class="fw-normal fs-6 lead px-1">Volver</strong></a>
                    </div>
                    <?php } if($datos['rol_id'] == 2) {?>
                    <div class="mt-5 shadow-sm border-0 " style="visibility:hidden;">                                
                        <a href="manzana.php" class="btn btn-sm p-2 boton_volver"> 
                        <i class="fa-solid fa-circle-left "></i><strong class="fw-normal fs-6 lead px-1">Volver</strong></a>
                    </div>
                    <?php }?>
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
</body>
</html>