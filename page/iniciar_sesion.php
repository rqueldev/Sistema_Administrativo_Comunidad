<?php

session_start();   // esto ayuda a reanudar la sesion en caso de que ya este una sesion activa!

include 'conexion.php';
include 'validar_campo.php';
//limpiar valores del formulario
$user = '';
$password = '';

//patron admitido para contraseñas, contiene lo que requiere una contraseña fuerte 
$pattern = '(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W)[a-zA-Z0-9\W]';  

if (isset($_POST['submit'])) {

    $user = valida_campo($_POST['user']);
    $password = valida_campo($_POST['password']);
    /*echo '
    <script>
           alert("Usuario: '.$user.'");
    </script>
   ';*/

    if (empty($user)) {
        $error_usuario_1 = 'Coloca un usuario';
    } else {
        if (strlen($user) > 10) {
            $error_user_2 = 'El nombre de usuario es muy largo';
        }
    }

    if (empty($password)) {
        $error_password_1 = 'Coloca tu contraseña';
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
    }

}

/* 1. Verificamos que no hay errores */
if (empty($error_usuario_1) and empty($error_usuario_2) and empty($error_password_1) and empty($error_password_2) and empty($error_password_3) 
    and empty($error_password_4)) {
    
    /* 2. Verificamos si ya hay una sesion activa */  
    if (isset($_SESSION['user'])) {

        $rescatar_sesion = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña, rol_id FROM usuario WHERE nombre_usuario=? ');   
        $rescatar_sesion->execute([$_SESSION['user']]);

        $datos = $rescatar_sesion->fetch(PDO::FETCH_ASSOC); 

        if (is_countable($datos)) {   //si el usuario existe verificar roles

           if ($datos['rol_id'] == 1) {
            header('Location: inicio-jefe-comunidad.php');

           } elseif ($datos['rol_id'] == 2) {
            header('Location: inicio-lider-calle.php');

           } else {   //si el nombre de usuario no se corresponde con ninguno en la BD 
            session_destroy();

           }
            

        } else {

            session_destroy();
            
        }
       

    /* 3. Verificamos que el usuario y contraseña se corresponden con usuario en la BD*/
    } else if (!empty($_POST['user']) && !empty($_POST['password'])) {    
        
        $md5password = md5($password); //encriptar la contraseña, ya que en la BD esta encriptada

        $buscar_usuario = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña, rol_id FROM usuario WHERE nombre_usuario=? AND contraseña=? AND estado=?');   
        $buscar_usuario->execute([$user,$md5password,"1"]);

        $resultado = $buscar_usuario->fetch(PDO::FETCH_ASSOC); 

        if (is_countable($resultado)) {   //si el usuario existe
            session_destroy();
            
            /* 4. Registrar su fecha de entrada y cambiar su posición = 'conectado'*/
            date_default_timezone_set('America/Caracas');
            setlocale(LC_TIME, 'spanish');
            $ultima_conexion = date('Y-m-d g:i:s a');
            $actualizar_usuario = $conexion->prepare('UPDATE usuario SET entrada = ?, posicion = ? WHERE nombre_usuario=?');
            $actualizar_usuario->execute([$ultima_conexion,"conectado",$user]);


            session_start();
            $_SESSION['user'] = $user;   //se le asigna un valor a la sesion actual, en este caso el valor es el nombre de usuario
            /*
            echo '
            <script>
                   alert("Usuario '.$_SESSION['user'].' validado ♥");
            </script>
           ';*/

           if ($resultado['rol_id'] == 1) {
            header('Location: inicio-jefe-comunidad.php');

           } elseif ($resultado['rol_id'] == 2) {
            header('Location: inicio-lider-calle.php');

           } else {
            $error_login = 'Error de autenticacion';
           }
            

        } else {

            $error_login = 'Datos incorrectos';
            
        }
    } 

} else if (empty($error_usuario_1) and empty($error_password_1)) {
       
    $error_login = 'Datos incorrectos';
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicia Sesión</title>

       <!-- CSS Bootstrap -->
       <link rel="stylesheet" href="../src/css/bootstrap.min.css">
   
       <!-- Font Awesome -->
       <link rel="stylesheet" href="../src/css/all.min.css">
   
       <!-- CSS Estilo -->
       <link rel="stylesheet" href="../src/css/iniciar_sesioon.css">
   
        <!-- Links de Google Fonts para Logo-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet"> 
</head>


<body>
    
    <header>
        <div class="container-fluid">
            <div class="row">
                <div class="navbar shadow d-flex justify-content-between">
    
                    <!-- Logo -->
                    <a href="nosotros.html" class="navbar-brand d-flex align-items-center px-2 logo__s">
                        <img src="../src/img/log.png" alt="logo" class="navbar-brand__img">
                        <img src="../src/img/logo_name_color1.png" class="navbar-brand__logo" alt="">
                        <!-- <h5 class="logo-name">Sector Universitario Oeste</h5>-->
                    </a>
    
                    <!-- Volver-->
                    <nav class="pt-1">
                        <i class="fa-solid fa-circle-left p-1"></i>
                        <a class="lead fs-6 " href="../page/principal.html">Volver</a>
                    </nav>
    
                </div>
            </div>
        </div>
    </header>

    <?php if(!empty($message)): ?>
        <p><?php echo "$message" ?></p>
    <?php endif; ?> 

    <main class="bajar">
       
        <div class="img-inicio">
            <img src="../src/img/inicio.jpg" alt="imagen">
        </div>

        <div class="login-card-container">
            <div class="login-card">
                <div class="login-card-logo">
                    <img src="../src/img/user-solid.svg" alt="logo">
                </div>
                <div class="login-card-header">
                    <h1>Iniciar Sesión</h1>
                    <h3>Ingresa si eres jefe de comunidad o si ya fuiste aceptado(a) como líder de calle</h3>
                </div>
                <form class="login-card-form" method="POST" action="iniciar_sesion.php">

                    <div class="form-item">

                        <div class="mb-3">
                           <input type="text" name="user" id="usuario" placeholder="Usuario" value="<?php echo $user; ?>"> 
                          
                             <?php if(!empty($error_usuario_1)): ?>
                             <p class='small text-danger error'><?php echo "$error_usuario_1" ?></p>
                             <?php endif; ?>
                        </div> 

                        <div class="mb-3">
                            <input type="password" name="password" id="contrasenia" placeholder="Contraseña" value="<?php echo $password; ?>">

                             <?php if(!empty($error_password_1)): ?>
                             <p class='small text-danger error'><?php echo "$error_password_1" ?></p>
                             <?php endif; ?> 
                        </div>

                        <?php if(!empty($error_login)){ ?>
                             <p class='small text-danger text-center error_login'><?php echo "$error_login" ?></p>
                        <?php }; ?> 

                        <button type="submit" name="submit">Iniciar Sesión</button>
                    </div>
                </form>
                <div class="login-card-footer">
                    <!--<a href="#">¿Olvidaste tu contraseña?</a> -->
                    <hr class="line">
                    ¿No tienes una cuenta? <a href="../page/registrarse.php">Haz click para crear un usuario</a>
                </div>
            </div>
        </div> 


    </main>
</body>
</html>