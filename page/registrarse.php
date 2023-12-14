<?php
include 'conexion.php';
include 'validar_campo.php';

// Listar manzanas en el formulario
$row_manzana = $conexion->query('SELECT manzana_id, numero_manzana FROM manzana WHERE estado = "1"');

//limpiar valores del formulario
$name = '';
$surname = '';
$ci = '';
$email = '';
$user = '';
$password = '';

//patron admitido para contraseñas, contiene lo que requiere una contraseña fuerte 
$pattern = '(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W)[a-zA-Z0-9\W]';  


if (isset($_POST['submit'])) {

    $name = valida_campo($_POST['name']);
    $surname = valida_campo($_POST['surname']);
    $ci = valida_campo($_POST['ci']);
    $apple = valida_campo($_POST['apple']);   
    $email = valida_campo($_POST['email']);
    $user = valida_campo($_POST['user']);
    $password = valida_campo($_POST['password']);


    if (empty($name)) {
        $error_name_1 = 'Coloca un nombre';
    } else {
        if (strlen($name) > 15) {
            $error_name_2 = 'El nombre es muy largo';
        }
    } 

    if (empty($surname)) {
        $error_surname_1 = 'Coloca tu apellido';
    }else {
        if (strlen($surname) > 15) {
            $error_surname_2 = 'El apellido es muy largo';
        }
    }   

    if (empty($ci)) {
        $error_ci_1 = 'Coloca tu cédula';
    } else {
        if (strlen($ci) > 8) {
            $error_ci_2 = 'La cédula no puede tener más de 8 caracteres';
        }
    }

    if (empty($apple)) {                      // Igual el select ya trae una alarma automatica para que se elija una opcion
        $error_apple_1 = 'Coloca la manzana';
    }

    if (empty($email)) {
        $error_email_1 = 'Coloca un email';
    } else {                                    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {    // El campo email ya trae por defecto una alarma para esto
            $error_email_2 = 'Ingresa un correo válido';    
        }
    }  

    if (empty($user)) {
        $error_user_1 = 'Coloca un usuario';
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
        else {
           // $error_password_4 = 'Es una contraseña fuerte';
        }
    }
}


 /* 1. Verificamos que no hay errores para insertar el registro */
if (empty($error_name_1) and empty($error_name_2) and empty($error_surname_1) and empty($error_surname_2) 
    and empty($error_ci_1) and empty($error_ci_2) and empty($error_apple_1) and empty($error_email_1) and empty($error_email_2)
    and empty($error_user_1) and empty($error_user_2) and empty($error_password_1) and empty($error_password_2) and empty($error_password_3) and empty($error_password_4)) {

    /* 2. Verificamos si los datos ya le pertenecen a un usuario ya registrado */
    if (!empty($_POST['user']) && !empty($_POST['password'])) {  //sin este if marca error
        $verificar = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña FROM usuario WHERE nombre_usuario=:usuario');  //consulta en donde el nombre de usuario del formulario es igual a uno existente en la BD.
        $verificar->bindParam(':usuario', $user);
        $verificar->execute();

        $resultado = [];
        $resultado = $verificar->fetch(PDO::FETCH_ASSOC);   //obtener los datos de tu consulta a la BD
                                                            // permite que mi resultado de la query este en formato array.

        if (is_countable($resultado)) {    
            //limpiar errores
            $error_name_1 = '';
            $error_name_2 = '';
            $error_surname_1 = '';
            $error_surname_2 = '';
            $error_ci_1 = '';
            $error_ci_2 = '';
            $error_apple_1 = '';
            $error_email_1 = '';
            $error_email_2 = '';
            $error_user_1 = '';
            $error_user_2 = '';
            $error_password_1 = '';
            $error_password_2 = '';
            $error_password_3 = '';

            //limpiar valores
            $name = '';
            $surname = '';
            $ci = '';
            $email = '';
            $user = '';
            $password = '';

          echo '
          <script>
              alert("Lo sentimos, el usuario ya le pertenece a otra cuenta");
          </script>
          ';
        } else {
            
            /* 3. Verificamos si la cedula de la tabla Manzana coincide con la cedula $ci ingresada en el formulario*/ 
            if (isset($apple)) {
                $buscar_cedula = $conexion->prepare("SELECT manzana_id, numero_manzana, cedula FROM manzana WHERE manzana_id=?");
                $buscar_cedula->execute([$apple]);

                $x = [];
                $x = $buscar_cedula->fetch(PDO::FETCH_ASSOC);

                if ($x['cedula'] == $ci) {
                   /*                     
                    echo '
                    <script>
                           alert("No hay problemas con la manzana :)");
                    </script>
                   ';*/

                    /* 4. Verificamos si los datos ya le corresponden a un jefe de calle */   
                    if (!empty($_POST['ci']) and !empty($_POST['name']) and !empty($_POST['surname']) and !empty($_POST['email'])
                        and !empty($_POST['apple'])) {                                    
                        
                        $buscar_lider = $conexion->prepare("SELECT ci_ps, nombre, apellido, correo, manzana_id FROM jefe_calle WHERE ci_ps = ? AND manzana_id = ?");
                        $buscar_lider->execute([$ci, $apple]);
        
                        $y = [];
                        $y = $buscar_lider->fetch(PDO::FETCH_ASSOC);
            
                        if (is_countable($y)){                                
                            
                            //limpiar errores
                            $error_name_1 = '';
                            $error_name_2 = '';
                            $error_surname_1 = '';
                            $error_surname_2 = '';
                            $error_ci_1 = '';
                            $error_ci_2 = '';
                            $error_apple_1 = '';
                            $error_email_1 = '';
                            $error_email_2 = '';
                            $error_user_1 = '';
                            $error_user_2 = '';
                            $error_password_1 = '';
                            $error_password_2 = '';
                            $error_password_3 = '';

                            //limpiar valores
                            $name = '';
                            $surname = '';
                            $ci = '';
                            $email = '';
                            $user = '';
                            $password = '';
                
                            echo '
                            <script>
                                alert("Lo sentimos, los datos ya pertenecen a un jefe de calle");
                            </script>
                            '; 

                        } else {                                        
                            
                            /* 5. Insertamos los datos de usuario*/   
                            $md5password = md5($password);            
                
                            $introducir_usuario = $conexion->prepare("INSERT INTO usuario(nombre_usuario,contraseña) VALUES (?,?)");
                            $introducir_usuario->execute([$user,$md5password]);
        
                            /* 6. Buscamos el usuario_id que se genero de la anterior insercion a la BD*/ 
                            $buscar_usuario = $conexion->prepare("SELECT usuario_id, nombre_usuario, contraseña FROM usuario WHERE nombre_usuario=?");
                            $buscar_usuario->execute([$user]);
                            
                            $r = $buscar_usuario->fetch(PDO::FETCH_ASSOC);
                    
                            /* 7. Insertamos los datos de jefe de calle*/ 
                            if (isset($apple)) {
                                $introducir_jefe_calle = $conexion->prepare("INSERT INTO jefe_calle(ci_ps,nombre,apellido,correo,manzana_id,usuario_id) VALUES (?,?,?,?,?,?)");
                                $introducir_jefe_calle->execute([$ci,$name,$surname,$email,$apple,$r['usuario_id']]);          
            
                                //limpiar errores
                                $error_name_1 = '';
                                $error_name_2 = '';
                                $error_surname_1 = '';
                                $error_surname_2 = '';
                                $error_ci_1 = '';
                                $error_ci_2 = '';
                                $error_apple_1 = '';
                                $error_email_1 = '';
                                $error_email_2 = '';
                                $error_user_1 = '';
                                $error_user_2 = '';
                                $error_password_1 = '';
                                $error_password_2 = '';
                                $error_password_3 = '';

                                //limpiar valores
                                $name = '';
                                $surname = '';
                                $ci = '';
                                $email = '';
                                $user = '';
                                $password = '';
                                
        
                                header('Location:popup.html');
                               /* echo '
                                <script>
                                alert("Registrado satisfactoriamente");
                                </script>
                                ';
                                */
                            }


                        }


                    
                    } 

                } else {

                    //limpiar errores
                    $error_name_1 = '';
                    $error_name_2 = '';
                    $error_surname_1 = '';
                    $error_surname_2 = '';
                    $error_ci_1 = '';
                    $error_ci_2 = '';
                    $error_apple_1 = '';
                    $error_email_1 = '';
                    $error_email_2 = '';
                    $error_user_1 = '';
                    $error_user_2 = '';
                    $error_password_1 = '';
                    $error_password_2 = '';
                    $error_password_3 = '';

                    //limpiar valores
                    $name = '';
                    $surname = '';
                    $ci = '';
                    $email = '';
                    $user = '';
                    $password = '';
                    
                    echo '
                    <script>
                           alert("Lo sentimos, la manzana selecionada esta bajo la direccion de otro jefe de calle");
                    </script>
                   ';
                }  

            }
                   
        }
    }
}
    

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>

    
       <!-- CSS Bootstrap -->
       <link rel="stylesheet" href="../src/css/bootstrap.min.css">
   
       <!-- Font Awesome -->
       <link rel="stylesheet" href="../src/css/all.min.css">
   
       <!-- CSS Estilo -->
       <link rel="stylesheet" href="../src/css/registrarse.css">
   
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

    <main class="bajar ajustar">

        <div class="img-inicio">
            <img src="../src/img/registro.jpg" alt="imagen" class="img-fluid">
        </div>

        <div class="signup-card-container">
            <div class="signup-card">
                <div class="signup-card-logo">
                    <img src="../src/img/address-book-solid.svg" alt="logo">
                </div>
                <div class="signup-card-header">
                    <h1>Registrarse</h1>
                    <h3>Si eres líder de calle por favor registrate</h3>
                </div>
                <form class="signup-card-form" method="POST" action="registrarse.php">
                     
                    <div class="form-item">

                        <div class="form-item-item mb-3">
                            <div>  <!---------------------------------->
                               <input class="items-izquierda" type="text" name="name" id="nombre" placeholder="Nombre" value="<?php echo $name; ?>">
                                   <?php if(!empty($error_name_1)){ ?>
                                   <p class='small text-danger m-0 error'><?php echo "$error_name_1" ?></p>
                                   <?php } else {
                                     if(!empty($error_name_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_name_2" ?></p>
                                        <?php }
                                    }; ?>
                            </div>

                            <div> <!---------------------------------->
                               <input class="items-derecha" type="text" name="surname" id="apellido" placeholder="Apellido" value="<?php echo $surname; ?>">
                                   <?php if(!empty($error_surname_1)){ ?>
                                   <p class='small text-danger m-0 error'><?php echo "$error_surname_1" ?></p>
                                   <?php } else {
                                    if(!empty($error_surname_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_surname_2" ?></p>
                                        <?php }
                                   }; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3"> <!---------------------------------->
                            <input class="items" type="number" name="ci" id="cedula" placeholder="Cédula" value="<?php echo $ci; ?>">
                                <?php if(!empty($error_ci_1)){ ?>
                                <p class='small text-danger error'><?php echo "$error_ci_1" ?></p>
                                <?php } else {
                                 if(!empty($error_ci_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_ci_2" ?></p>
                                        <?php }
                                }; ?> 
                        </div>

                        <div class="mb-3"> <!---------------------------------->
                           <select name="apple" id="manzana_id" class="form-select items" placeholder="¿Que Manzana lideras?" required>
                                    <option value="">¿Que Manzana lideras?</option>
                                    <?php
                                    foreach ($row_manzana as $m){
                                    ?> 
                                        <option value="<?php echo $m['manzana_id']; ?>"><?php echo $m['numero_manzana'];?></option>
                                    <?php
                                    }
                                    ?>
                            </select>
                        
                                <?php if(!empty($error_apple_1)): ?>
                                <p class='small text-danger error'><?php echo "$error_apple_1" ?></p>
                                <?php endif; ?> 

                        </div>

                        <div class="mb-3"> <!---------------------------------->
                            <input class="items" type="email" name="email" id="correo" placeholder="Correo" value="<?php echo $email; ?>">
                                <?php if(!empty($error_email_1)){ ?>
                                <p class='small text-danger error'><?php echo "$error_email_1" ?></p>
                                <?php } else {
                                 if(!empty($error_email_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_email_2" ?></p>
                                        <?php }
                                }; ?> 
                        </div>

                        <div class="form-item-item mb-3"> <!---------------------------------->
                            <div>
                               <input class="items-izquierda"type="text" name="user" id="usuario" placeholder="Usuario" value="<?php echo $user; ?>">
                                   <?php if(!empty($error_user_1)){ ?>
                                   <p class='small text-danger error m-0'><?php echo "$error_user_1" ?></p>
                                   <?php } else {
                                   if(!empty($error_user_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_user_2" ?></p>
                                        <?php }
                                }; ?> 
                            </div>
                            <div>
                               <input class="items-derecha"type="password" name="password" id="contrasenia" placeholder="Contraseña" value="<?php echo $password; ?>">
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
                            </div>



                        </div>

                        <button type="submit" name="submit">Registrarse</button>    

                    </div>
                </form>
                <div class="signup-card-footer">
                    ¿Ya tienes una cuenta? <a href="../page/iniciar_sesion.php">Haz click para iniciar sesión</a>
                </div>
            </div>
        </div>


        
    </main>  
    

    
    <!-- JS Bootstrap -->
    <script src="../src/js/bootstrap.bundle.min.js"></script>

</body>
</html>