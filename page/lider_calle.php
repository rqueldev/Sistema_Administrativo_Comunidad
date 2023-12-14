<?php

session_start();
include 'conexion.php';
include 'validar_campo.php';

//limpiar valores de formulario ingresar cada vez que se recargue la página
$ci_ps = '';
$nombre = '';
$apellido = '';
$correo = '';
$manzana_id = '';
$user = '';
$password = '';
$rol_id = '';


$sesion = $_SESSION['user'];

if ($sesion == null or $sesion = '') {
   // echo 'Usted no tiene autorizacion';
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
            $ci_ps = '';
            $nombre = '';
            $apellido = '';
            $correo = '';
            $manzana_id = '';
            $user = '';
            $password = '';
            $rol_id = '';
            
            //patron admitido para contraseñas, contiene lo que requiere una contraseña fuerte 
            $pattern = '(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W)[a-zA-Z0-9\W]';  
            
            if (isset($_POST['Añadir'])) {    
                
                //limpiar valores del formulario
                $ci_ps = valida_campo($_POST['ci_ps']);
                $nombre = valida_campo($_POST['nombre']);
                $apellido = valida_campo($_POST['apellido']);
                $correo = valida_campo($_POST['correo']);
                $manzana_id = valida_campo($_POST['manzana_id']);
                $user = valida_campo($_POST['user']);
                $password = valida_campo($_POST['password']);
                $rol_id = valida_campo($_POST['rol_id']);


                if (empty($nombre)) {
                    $error_nombre_1 = 'Coloca un nombre';
                } else {
                    if (strlen($nombre) > 15) {
                        $error_nombre_2 = 'El nombre es muy largo';
                    }
                } 
            
                if (empty($apellido)) {
                    $error_apellido_1 = 'Coloca un apellido';
                }else {
                    if (strlen($apellido) > 15) {
                        $error_apellido_2 = 'El apellido es muy largo';
                    }
                }   
            
                if (empty($ci_ps)) {
                    $error_ci_ps_1 = 'Coloca una cédula';
                } else {
                    if (strlen($ci_ps) > 8) {
                        $error_ci_ps_2 = 'La cédula no puede tener más de 8 caracteres';
                    }
                }
            
                if (empty($manzana_id)) {                      
                    $error_manzana_id_1 = 'Coloca la manzana';
                }
            
                if (empty($correo)) {
                    $error_correo_1 = 'Coloca un email';
                } else {                                    
                    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {    // El campo email ya trae por defecto una alarma para esto
                        $error_correo_2 = 'Ingresa un correo válido';    
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

                if (empty($rol_id)) {                      // Igual el select ya trae una alarma automatica para que se elija una opcion
                        $error_rol_id_1 = 'Asigne un rol ';
                    }


                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_nombre_1) and empty($error_nombre_2) and empty($error_apellido_1) and empty($error_apellido_2) 
                    and empty($error_ci_ps_1) and empty($error_ci_ps_2) and empty($error_manzana_id_1) and empty($error_correo_1) and empty($error_correo_2)
                    and empty($error_user_1) and empty($error_user_2) and empty($error_password_1) and empty($error_password_2) and empty($error_password_3) 
                    and empty($error_password_4) and empty($error_rol_id_1)){    
                        
                        /* 2. Verificamos si los datos ya le pertenecen a un usuario ya registrado */
                        if (!empty($_POST['user']) && !empty($_POST['password'])) {  
                            $verificar = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña FROM usuario WHERE nombre_usuario=? AND estado=?');  //consulta en donde el nombre de usuario del formulario es igual a uno existente en la BD.
                            $verificar->execute([$user,"1"]);
                    
                            $resultado = [];
                            $resultado = $verificar->fetch(PDO::FETCH_ASSOC);   //obtener los datos de tu consulta a la BD
                                                                                // permite que mi resultado de la query este en formato array.
                    
                            if (is_countable($resultado)) {      //si ya existe un usuario con ese mismo nombre

                                //limpiar errores
                                $error_nombre_1 = '';
                                $error_nombre_2 = '';
                                $error_apellido_1 = '';
                                $error_apellido_2 = '';
                                $error_ci_ps_1 = '';
                                $error_ci_ps_2 = '';
                                $error_manzana_id_1 = '';
                                $error_correo_1 = '';
                                $error_correo_2 = '';
                                $error_user_1 = '';
                                $error_user_2 = '';
                                $error_password_1 = '';
                                $error_password_2 = '';
                                $error_password_3 = '';
                                $error_password_4 = '';
                                $error_rol_id_1 = '';

                                //limpiar valores
                                $ci_ps = '';
                                $nombre = '';
                                $apellido = '';
                                $correo = '';
                                $manzana_id = '';
                                $user = '';
                                $password = '';
                                $rol_id = '';
                    
                              echo '
                              <script>
                                  alert("Lo sentimos, el usuario ya le pertenece a otra cuenta");
                              </script>
                              ';
                            } else {
                                
                                /* 3. Verificamos si la cedula de la tabla Manzana coincide con la cedula $ci_ps ingresada en el formulario*/ 
                                if (isset($manzana_id)) {
                                    $buscar_cedula = $conexion->prepare("SELECT manzana_id, numero_manzana, cedula FROM manzana WHERE manzana_id=? AND estado=?");
                                    $buscar_cedula->execute([$manzana_id,"1"]);
                    
                                    $x = [];
                                    $x = $buscar_cedula->fetch(PDO::FETCH_ASSOC);
                        
                                    if ($x['cedula'] == $ci_ps) {
                                        /*
                                        echo '
                                        <script>
                                               alert("No hay problemas con la manzana :)");
                                        </script>
                                       ';*/

                                        /* 4. Verificamos si los datos ya le corresponden a un jefe de calle */   
                                        if (!empty($_POST['ci_ps']) and !empty($_POST['nombre']) and !empty($_POST['apellido']) and !empty($_POST['correo'])
                                           and !empty($_POST['manzana_id'])) {                                    
                                            
                                            $buscar_lider = $conexion->prepare("SELECT ci_ps, nombre, apellido, correo, manzana_id FROM jefe_calle WHERE ci_ps = ? AND manzana_id = ? AND estado=?");
                                            $buscar_lider->execute([$ci_ps, $manzana_id,"1"]);
                            
                                            $y = [];
                                            $y = $buscar_lider->fetch(PDO::FETCH_ASSOC);
                                
                                            if (is_countable($y)){                                
                                                
                                                //limpiar errores
                                                $error_nombre_1 = '';
                                                $error_nombre_2 = '';
                                                $error_apellido_1 = '';
                                                $error_apellido_2 = '';
                                                $error_ci_ps_1 = '';
                                                $error_ci_ps_2 = '';
                                                $error_manzana_id_1 = '';
                                                $error_correo_1 = '';
                                                $error_correo_2 = '';
                                                $error_user_1 = '';
                                                $error_user_2 = '';
                                                $error_password_1 = '';
                                                $error_password_2 = '';
                                                $error_password_3 = '';
                                                $error_password_4 = '';
                                                $error_rol_id_1 = '';
                
                                                //limpiar valores
                                                $ci_ps = '';
                                                $nombre = '';
                                                $apellido = '';
                                                $correo = '';
                                                $manzana_id = '';
                                                $user = '';
                                                $password = '';
                                                $rol_id = '';
                                    
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
                                                $buscar_usuario = $conexion->prepare("SELECT usuario_id, nombre_usuario, contraseña FROM usuario WHERE nombre_usuario=? AND estado=?");
                                                $buscar_usuario->execute([$user,"1"]);
                                                
                                                $r = $buscar_usuario->fetch(PDO::FETCH_ASSOC);
                                        
                                                /* 7. Insertamos los datos de jefe de calle*/ 
                                                if (isset($manzana_id)) {
                                                    $introducir_jefe_calle = $conexion->prepare("INSERT INTO jefe_calle(ci_ps,nombre,apellido,correo,manzana_id,usuario_id) VALUES (?,?,?,?,?,?)");
                                                    $introducir_jefe_calle->execute([$ci_ps,$nombre,$apellido,$correo,$manzana_id,$r['usuario_id']]);          
                                
                                                    //limpiar errores
                                                    $error_nombre_1 = '';
                                                    $error_nombre_2 = '';
                                                    $error_apellido_1 = '';
                                                    $error_apellido_2 = '';
                                                    $error_ci_ps_1 = '';
                                                    $error_ci_ps_2 = '';
                                                    $error_manzana_id_1 = '';
                                                    $error_correo_1 = '';
                                                    $error_correo_2 = '';
                                                    $error_user_1 = '';
                                                    $error_user_2 = '';
                                                    $error_password_1 = '';
                                                    $error_password_2 = '';
                                                    $error_password_3 = '';
                                                    $error_password_4 = '';
                                                    $error_rol_id_1 = '';
        
                                                    //limpiar valores
                                                    $ci_ps = '';
                                                    $nombre = '';
                                                    $apellido = '';
                                                    $correo = '';
                                                    $manzana_id = '';
                                                    $user = '';
                                                    $password = '';
                                                    $rol_id = '';
                                                    
                                                    $registrado = 'registrado';

                                                  /*  echo '
                                                    <script>
                                                       alert("Registrado satisfactoriamente");
                                                    </script>
                                                    ';*/

                                                   
                                                    
                                                }


                                            }


                                        
                                        } 
                    
                                    } else {
                    
                                            //limpiar errores
                                            $error_nombre_1 = '';
                                            $error_nombre_2 = '';
                                            $error_apellido_1 = '';
                                            $error_apellido_2 = '';
                                            $error_ci_ps_1 = '';
                                            $error_ci_ps_2 = '';
                                            $error_manzana_id_1 = '';
                                            $error_correo_1 = '';
                                            $error_correo_2 = '';
                                            $error_user_1 = '';
                                            $error_user_2 = '';
                                            $error_password_1 = '';
                                            $error_password_2 = '';
                                            $error_password_3 = '';
                                            $error_password_4 = '';
                                            $error_rol_id_1 = '';

                                            //limpiar valores
                                            $ci_ps = '';
                                            $nombre = '';
                                            $apellido = '';
                                            $correo = '';
                                            $manzana_id = '';
                                            $user = '';
                                            $password = '';
                                            $rol_id = '';
                                        
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
                } 
            } 

        //------------------------------------------- ACCION EDITAR ------------------------------------------//
            
            
            if (isset($_POST['Actualizar'])) {    

                $jefe_calle_id2 = $_POST['jefe_calle_id'];

                $ci_ps_editar = valida_campo($_POST['ci_ps']);
                $nombre_editar = valida_campo($_POST['nombre']);
                $apellido_editar = valida_campo($_POST['apellido']);
                $correo_editar = valida_campo($_POST['correo']);
                $manzana_id_editar = valida_campo($_POST['manzana_id']);
                $usuario_id_editar = valida_campo($_POST['usuario_id']);
                $user_editar = valida_campo($_POST['user']);
                $rol_id_editar = valida_campo($_POST['rol_id']);


                if (empty($nombre_editar)) {
                    $error_nombre_editar_1 = 'Coloca un nombre';
                } else {
                    if (strlen($nombre_editar) > 15) {
                        $error_nombre_editar_2 = 'El nombre es muy largo';
                    }
                } 
            
                if (empty($apellido_editar)) {
                    $error_apellido_editar_1 = 'Coloca un apellido';
                }else {
                    if (strlen($apellido_editar) > 15) {
                        $error_apellido_editar_2 = 'El apellido es muy largo';
                    }
                }   
            
                if (empty($ci_ps_editar)) {
                    $error_ci_ps_editar_1 = 'Coloca una cédula';
                } else {
                    if (strlen($ci_ps_editar) > 8) {
                        $error_ci_ps_editar_2 = 'La cédula no puede tener más de 8 caracteres';
                    }
                }
            
                if (empty($manzana_id_editar)) {                      
                    $error_manzana_id_editar_1 = 'Coloca la manzana';
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

                if (empty($rol_id_editar)) {                      // Igual el select ya trae una alarma automatica para que se elija una opcion
                        $error_rol_id_editar_1 = 'Asigne un rol ';
                    }


                /* 1. Verificamos que no hay errores para insertar el registro */
                if (empty($error_nombre_editar_1) and empty($error_nombre_editar_2) and empty($error_apellido_editar_1) and empty($error_apellido_editar_2) 
                    and empty($error_ci_ps_editar_1) and empty($error_ci_ps_editar_2) and empty($error_manzana_id_editar_1) and empty($error_correo_editar_1) and empty($error_correo_editar_2)
                    and empty($error_user_editar_1) and empty($error_user_editar_2) and empty($error_rol_id_editar_1)){    
                        /* 
                        echo '
                        <script>
                                alert("No hay errores");
                        </script>
                        ';
                         */
                        /* 2. Verificamos si la cedula de la tabla Manzana coincide con la cedula $ci_ps ingresada en el formulario*/ 
                        if (isset($manzana_id_editar)) {
                            $buscar_cedula_editar = $conexion->prepare("SELECT manzana_id, numero_manzana, cedula FROM manzana WHERE manzana_id=? AND estado=?");
                            $buscar_cedula_editar->execute([$manzana_id_editar,"1"]);
 
                            $x = [];
                            $x = $buscar_cedula_editar->fetch(PDO::FETCH_ASSOC);
                        
                            //error_reporting(0);  /*me salia un  error  "Trying to access array offset on value of type bool in C:\xampp\htdocs\NuevoProyecto\page\lider_calle.php on line 469"
                                                             // lo acomodaba colocando un if para contar las filas de la consulta pero no validaba bien y daba falso :(  */

                            if (@$x['cedula'] == $ci_ps_editar){                                               
                                /*            
                                echo '
                                <script>
                                        alert("No hay problemas con la manzana :)");
                                </script>
                                ';
                                */
                                /* 3. Verificamos que la cedula y manzana "nuevas" no pertenezcan a algun otro jefe de calle */
                                $buscar_lider_editar = $conexion->prepare("SELECT * FROM jefe_calle WHERE md5(jefe_calle_id) <> ? AND manzana_id = ? AND estado=?");
                                $buscar_lider_editar->execute([$jefe_calle_id2,$manzana_id_editar,"1"]);
                                $rows = $buscar_lider_editar->rowcount();
                                
                                $re = [];
                                $re = $buscar_lider_editar->fetch(PDO::FETCH_ASSOC);
                                
                                if ($rows > 0) {

                                    if ($re['ci_ps'] == $ci_ps_editar) {
                                        echo '
                                        <script>
                                                alert("Lo sentimos, manzana y cedula le pertenecen a otro jefe de calle");
                                        </script>
                                        ';

                                        //limpiar errores
                                        $error_nombre_editar_1 = '';
                                        $error_nombre_editar_2 = '';
                                        $error_apellido_editar_1 = '';
                                        $error_apellido_editar_2 = '';
                                        $error_ci_ps_editar_1 = '';
                                        $error_ci_ps_editar_2 = '';
                                        $error_manzana_id_editar_1 = '';
                                        $error_correo_editar_1 = '';
                                        $error_correo_editar_2 = '';
                                        $error_user_editar_1 = '';
                                        $error_user_editar_2 = '';
                                        $error_rol_id_editar_1 = '';

                                        //limpiar valores
                                        $ci_ps_editar = '';
                                        $nombre_editar = '';
                                        $apellido_editar = '';
                                        $correo_editar = '';
                                        $manzana_id_editar = '';
                                        $user_editar = '';
                                        $rol_id_editar = '';
                                    } 

                                } else {
                                   /* echo '
                                    <script>
                                            alert("Excelente, manzana y cedula no le pertenece a mas nadie");
                                    </script>
                                    ';*/

                                    /* 4. Verificamos que el nombre de usuario "nuevo" no pertenezcan a algun otro usuario */
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
                                        $error_manzana_id_editar_1 = '';
                                        $error_correo_editar_1 = '';
                                        $error_correo_editar_2 = '';
                                        $error_user_editar_1 = '';
                                        $error_user_editar_2 = '';
                                        $error_rol_id_editar_1 = '';

                                        //limpiar valores
                                        $ci_ps_editar = '';
                                        $nombre_editar = '';
                                        $apellido_editar = '';
                                        $correo_editar = '';
                                        $manzana_id_editar = '';
                                        $user_editar = '';
                                        $rol_id_editar = '';
                                        
                                    } else {
                                       /* echo '
                                        <script>
                                                alert("Excelente, el nombre de usuario no le pertenece a más nadie");
                                        </script>
                                        ';*/
                                        // funciones para actualizar la fecha en la tabla
                                        date_default_timezone_set('America/Caracas');
                                        setlocale(LC_TIME, 'spanish');
                                        $fecha_actualizacion = date('Y-m-d g:i:s');

                                        /* 5. Actualizamos los datos de jefe de calle */
                                        $actualizar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET ci_ps = ?, nombre = ?, apellido = ?, correo = ?, manzana_id = ?, fecha_actualizacion = ?  WHERE md5(jefe_calle_id) = ?;");
                                        $actualizar_jefe_calle->execute([$ci_ps_editar,$nombre_editar,$apellido_editar,$correo_editar,$manzana_id_editar,$fecha_actualizacion,$jefe_calle_id2]);
                                        
                                        /* 6. Actualizamos los datos de usuario */
                                        $actualizar_usuario = $conexion->prepare("UPDATE usuario SET nombre_usuario = ?, rol_id = ?, fecha_actualizacion = ?  WHERE md5(usuario_id) = ?;");
                                        $actualizar_usuario->execute([$user_editar,$rol_id_editar,$fecha_actualizacion,$usuario_id_editar]);

                                        /*
                                        echo '
                                        <script>
                                                alert("Registro actualizado satisfactoriamente");
                                        </script>
                                        ';
                                        */

                                        $actualizado = 'actualizado';

                                        //limpiar errores
                                        $error_nombre_editar_1 = '';
                                        $error_nombre_editar_2 = '';
                                        $error_apellido_editar_1 = '';
                                        $error_apellido_editar_2 = '';
                                        $error_ci_ps_editar_1 = '';
                                        $error_ci_ps_editar_2 = '';
                                        $error_manzana_id_editar_1 = '';
                                        $error_correo_editar_1 = '';
                                        $error_correo_editar_2 = '';
                                        $error_user_editar_1 = '';
                                        $error_user_editar_2 = '';
                                        $error_rol_id_editar_1 = '';

                                        //limpiar valores
                                        $ci_ps_editar = '';
                                        $nombre_editar = '';
                                        $apellido_editar = '';
                                        $correo_editar = '';
                                        $manzana_id_editar = '';
                                        $user_editar = '';
                                        $rol_id_editar = '';
                                    }
                                }

                            } else {
                    
                                //limpiar errores
                                $error_nombre_editar_1 = '';
                                $error_nombre_editar_2 = '';
                                $error_apellido_editar_1 = '';
                                $error_apellido_editar_2 = '';
                                $error_ci_ps_editar_1 = '';
                                $error_ci_ps_editar_2 = '';
                                $error_manzana_id_editar_1 = '';
                                $error_correo_editar_1 = '';
                                $error_correo_editar_2 = '';
                                $error_user_editar_1 = '';
                                $error_user_editar_2 = '';
                                $error_rol_id_editar_1 = '';

                                //limpiar valores
                                $ci_ps_editar = '';
                                $nombre_editar = '';
                                $apellido_editar = '';
                                $correo_editar = '';
                                $manzana_id_editar = '';
                                $user_editar = '';
                                $rol_id_editar = '';
                                        
                                echo '
                                <script>
                                        alert("Lo sentimos, la manzana selecionada esta bajo la direccion de otro jefe de calle");
                                </script>
                                ';
                            }  
                              
                        
                        }
            
                    }  //si hay errores no hacer nada solo mostrarlos 
            }

         //-------------------------------------------------- ELIMINAR ---------------------------------------------------------------//
        
            if (isset($_POST['Eliminar'])) {    
                
                $jefe_calle_id_borrar = $_POST['jefe_calle_id'];
                $estado = '2';
            

                /* 1. Eliminar Tabla Jefe de Calle */
                $borrar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET estado =? WHERE md5(jefe_calle_id) = ?;");
                $borrar_jefe_calle->execute([$estado, $jefe_calle_id_borrar]);

                /* 2. Eliminar Tabla vinculada: Usuario */
                //buscar id de usario de la tabla jefe de calle
                $buscar_usuario_borrar =  $conexion->prepare("SELECT * FROM jefe_calle WHERE md5(jefe_calle_id) = ?;");
                $buscar_usuario_borrar->execute([$jefe_calle_id_borrar]);
                $u = $buscar_usuario_borrar->fetch(PDO::FETCH_ASSOC); 
                
                if (is_countable($u)) {
                    $borrar_usuario = $conexion->prepare("UPDATE usuario SET estado =? WHERE usuario_id = ?;");
                    $borrar_usuario->execute([$estado, $u['usuario_id']]);
                }


                $borrado = 'borrado';
            }

         //----------------------------------------- ACCION RESTAURAR ---------------------------------------------//


        
            if (isset($_POST['Recuperar'])) {    
                
                $jefe_calle_id_recuperar = $_POST['jefe_calle_id'];
                $estado_recuperar = '1';
            
                //buscar id de manzana y de usuario de la tabla jefe de calle
                $buscar_jefe_calle_recuperar = $conexion->prepare("SELECT * FROM jefe_calle WHERE md5(jefe_calle_id) = ?;");
                $buscar_jefe_calle_recuperar->execute([$jefe_calle_id_recuperar]);
                $us = $buscar_jefe_calle_recuperar->fetch(PDO::FETCH_ASSOC); 

                //Verificar si la manzana esta activa '1' mediante el id obtenido de la anterior consulta
                $verificar_manzana_recuperar = $conexion->prepare("SELECT * FROM manzana WHERE estado=? AND manzana_id = ?;");
                $verificar_manzana_recuperar->execute(["1",$us['manzana_id']]);
                $man = $verificar_manzana_recuperar->fetch(PDO::FETCH_ASSOC); 

                if (is_countable($man)) {  
                    /* 1. Recuperar Tabla Jefe de Calle */
                    $recuperar_jefe_calle = $conexion->prepare("UPDATE jefe_calle SET estado =? WHERE md5(jefe_calle_id) = ?;");
                    $recuperar_jefe_calle->execute([$estado_recuperar, $jefe_calle_id_recuperar]);
                    
                    /* 2. Recuperar Tabla vinculada: Usuario */
                    $recuperar_usuario = $conexion->prepare("UPDATE usuario SET estado =? WHERE usuario_id = ?;");
                    $recuperar_usuario->execute([$estado_recuperar, $us['usuario_id']]);
                    
                    $recuperado = 'recuperado';
                    
                } else {
                    echo '
                    <script>
                            alert("Lo sentimos, no se puede restaurar el líder de calle debido a que la manzana esta deshabilitada");
                    </script>
                    ';
                }


            }


            
        } elseif ($datos['rol_id'] == 2) { 
            header('Location: inicio-lider-calle.php');

        } else {    //No puede haber otro rol que no sea especificado 
            session_destroy();

        }
        
    } else {   //hay valor en la sesion pero no es de la BD, procedencia extraña 
        session_destroy();
        
    }
}




/* --------------------------------------------------------------------------------------------------------------------------------------------- */




// Información de la Tabla Jefe de Calle
$jefe_calle = $conexion->prepare('SELECT j.jefe_calle_id, j.ci_ps, j.nombre, j.apellido, j.correo, m.numero_manzana FROM jefe_calle AS j
INNER JOIN manzana AS m
ON j.manzana_id=m.manzana_id WHERE j.estado=? AND m.estado=?');
$jefe_calle->execute(["1","1"]);


/* ------------------------ Select de Formulario ingresar ----------------------------------- */
//Numeros de manzana y sus id 
$row_manzana = $conexion->prepare('SELECT manzana_id, numero_manzana FROM manzana WHERE estado = ?');
$row_manzana->execute(["1"]);

//Roles de usuario
$rol_ingresar = $conexion->prepare('SELECT rol_id, descripcion FROM rol WHERE estado = ?');
$rol_ingresar->execute(["1"]);

/* ------------------------ Select de Formulario editar ----------------------------------- */
//Numeros de manzana y sus id 
$row_manzana_editar = $conexion->prepare('SELECT manzana_id, numero_manzana FROM manzana WHERE estado = ?');
$row_manzana_editar->execute(["1"]);

//Roles de usuario
$rol_editar = $conexion->prepare('SELECT rol_id, descripcion FROM rol WHERE estado = ?');
$rol_editar->execute(["1"]);





/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */
// Información de la Tabla de Recuperación 
$recuperar_jefe_calle = $conexion->prepare('SELECT j.jefe_calle_id, j.ci_ps, j.nombre, j.apellido, j.correo, m.numero_manzana FROM jefe_calle AS j
INNER JOIN manzana AS m
ON j.manzana_id=m.manzana_id WHERE j.estado=?');
$recuperar_jefe_calle->execute(["2"]);


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lideres</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/lider_calle.css">

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
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Líderes de Calle</h3>
                <img src="../src/img/lideres.png" class="img-fluid d-flex justify-content-center col-5" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir" data-bs-toggle="modal" data-bs-target="#modalLiderCalleAñadir" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos-lider-calle" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-info">
                                <th class="text-center">Cédula</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Apellido</th>
                                <th class="text-center">correo</th>
                                <th class="text-center">Nº Manzana</th>
                                <th class="text-center no-exportar">Opciones</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($jefe_calle as $j) {
		                    ?>
		                    <tr>
			                    <td><?php echo $j['ci_ps']; ?></td>
			                    <td><?php echo $j['nombre']; ?></td>
			                    <td><?php echo $j['apellido']; ?></td>
			                    <td><?php echo $j['correo']; ?></td>
			                    <td><?php echo $j['numero_manzana']; ?></td>
                                <!--------- en proceso ya va------------> 
			                    <td class="d-flex justify-content-around">
                                    <a href="lider_calle.php" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalLiderCalleEditar" data-bs-id="<?= md5($j['jefe_calle_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="lider_calle_borrar.php" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalLiderCalleEliminar" data-bs-id="<?= md5($j['jefe_calle_id']); ?>"> 
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
                                <th class="text-center">Cédula</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Apellido</th>
                                <th class="text-center">correo</th>
                                <th class="text-center">Nº Manzana</th>
                                <th class="text-center no-exportar">Recuperar</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($recuperar_jefe_calle as $j) {
		                    ?>
		                    <tr>
			                    <td><?php echo $j['ci_ps']; ?></td>
			                    <td><?php echo $j['nombre']; ?></td>
			                    <td><?php echo $j['apellido']; ?></td>
			                    <td><?php echo $j['correo']; ?></td>
			                    <td><?php echo $j['numero_manzana']; ?></td>
                                <!--------- botones ------------> 
			                    <td class="d-flex justify-content-center">
                                    <a href="lider_calle.php" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalLiderCalleRecuperar" data-bs-id="<?= md5($j['jefe_calle_id']); ?>"> 
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalLiderCalleAñadir" tabindex="-1" aria-labelledby="modalLiderCalleAñadirLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLiderCalleAñadirLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="lider_calle.php" method="POST" id="formulario">
                        <div class="modal-content border-white">

                            <img src="../src/img/lider-calle_modal.jpg" class="w-25 mb-4 modal_imagen" alt="">
                            
                            
                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4">  <!---------------------------------->
                                    <label for="ci_ps" class="form-label fw-medium" > Cédula:</label>
                                    <input type="number" name="ci_ps" id="ci_ps" class="form-control" value="<?php echo $ci_ps; ?>">
                                        <?php if(!empty($error_ci_ps_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_ci_ps_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_ci_ps_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_ci_ps_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                                <div class="col-4">  <!---------------------------------->
                                    <label for="nombre" class="form-label fw-medium" > Nombre:</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo $nombre; ?>">
                                        <?php if(!empty($error_nombre_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_nombre_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_nombre_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_nombre_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
    
                                <div class="col-4">  <!---------------------------------->
                                    <label for="apellido" class="form-_label fw-medium" > Apellido:</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control" value="<?php echo $apellido; ?>">
                                        <?php if(!empty($error_apellido_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_apellido_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_apellido_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_apellido_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>
                            

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">  <!---------------------------------->
                                    <label for="correo" class="form-label fw-medium">Correo Electrónico:</label>
                                    <input type="email" name="correo" id="correo" class="form-control" value="<?php echo $correo; ?>">
                                        <?php if(!empty($error_correo_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_correo_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_correo_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_correo_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-6">  <!---------------------------------->
                                    <label for="manzana_id" class="form-label fw-medium">Nro. de Manzana que lidera:</label>
                                    <select name="manzana_id" id="manzana_id" class="form-select" required>
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($row_manzana as $m){
                                        ?> 
                                            <option value="<?php echo $m['manzana_id']; ?>"><?php echo $m['numero_manzana'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                        <?php if(!empty($error_manzana_id_1)): ?>
                                        <p class='small text-danger error'><?php echo "$error_manzana_id_1" ?></p>
                                        <?php endif; ?> 
                                </div>
                            </div>

                            
                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4">  <!---------------------------------->
                                    <label for="user" class="form-label fw-medium" > Nombre de Usuario:</label>
                                    <input type="text" name="user" id="user" class="form-control" value="<?php echo $user; ?>">
                                        <?php if(!empty($error_user_1)){ ?>
                                        <p class='small text-danger error m-0'><?php echo "$error_user_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_user_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_user_2" ?></p>
                                        <?php }
                                        }; ?> 
                                </div>

                                <div class="col-4">  <!---------------------------------->
                                    <label for="password" class="form-label fw-medium">Contraseña:</label>
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
                                </div>

                                <div class="col-4">  <!---------------------------------->
                                    <label for="rol_id" class="form-label fw-medium">Rol:</label>
                                    <select name="rol_id" id="rol_id" class="form-select" required value="<?php echo $rol_id; ?>">
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($rol_ingresar as $r){
                                        ?> 
                                            <option value="<?php echo $r['rol_id']; ?>"><?php echo $r['descripcion'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                        <?php if(!empty($error_rol_id_1)): ?>
                                        <p class='small text-danger error'><?php echo "$error_rol_id_1" ?></p>
                                        <?php endif; ?> 
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="button" name="Añadir" id="Añadir" class="btn btn-primary"><i class=" p-1 fa-solid fa-floppy-disk"></i>Guardar</button>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false"  id="modalLiderCalleEditar" tabindex="-1" aria-labelledby="modalLiderCalleEditarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLiderCalleEditarLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="lider_calle.php" method="POST">
                        <div class="modal-content border-white">

                            <img src="../src/img/lider-calle_modal.jpg" class="w-25 mb-4 modal_imagen" alt="">
                            
                            <input type="hidden" id="jefe_calle_id" name="jefe_calle_id">
                            <input type="hidden" id="usuario_id" name="usuario_id">

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4">
                                    <label for="ci_ps" class="form-label fw-medium" > Cédula:</label>
                                    <input type="number" name="ci_ps" id="ci_ps" class="form-control">
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
                                    <input type="text" name="nombre" id="nombre" class="form-control">
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
                                    <input type="text" name="apellido" id="apellido" class="form-control">
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
                                    <input type="email" name="correo" id="correo" class="form-control">
                                        <?php if(!empty($error_correo_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_correo_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_correo_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_correo_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-6">
                                    <label for="manzana_id" class="form-label fw-medium">Nro. de Manzana que lidera:</label>
                                    <select name="manzana_id" id="manzana_id" class="form-select" required>
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($row_manzana_editar as $m){
                                        ?> 
                                            <option value="<?php echo $m['manzana_id']; ?>"><?php echo $m['numero_manzana'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                        <?php if(!empty($error_manzana_id_editar_1)): ?>
                                        <p class='small text-danger error'><?php echo "$error_manzana_id_editar_1" ?></p>
                                        <?php endif; ?> 
                                </div>
                            </div>

                           
                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-6">
                                    <label for="user" class="form-label fw-medium" > Nombre de Usuario:</label>
                                    <input type="text" name="user" id="user" class="form-control">
                                    <?php if(!empty($error_user_editar_1)){ ?>
                                        <p class='small text-danger error m-0'><?php echo "$error_user_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_user_editar_2)){ ?>
                                        <p class='small text-danger m-0 error'><?php echo "$error_user_editar_2" ?></p>
                                        <?php }
                                        }; ?> 
                                </div>

                                <div class="col-6">
                                    <label for="rol_id" class="form-label fw-medium">Rol:</label>
                                    <select name="rol_id" id="rol_id" class="form-select" required>
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
                                <button type="submit" id= "Acttualizar" name="Actualizar" class="btn btn-primary"><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar cambios</button>
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
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalLiderCalleEliminar" tabindex="-1" aria-labelledby="modalLiderCalleEliminarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLiderCalleEliminarLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                  <p>Al eliminar el Jefe de Calle, se eliminaran también los datos del Usuario vinculado.</p>
                    <h6>¿Seguro que desea eliminar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="lider_calle.php" method="POST">
                            <input type="hidden" name="jefe_calle_id" id="jefe_calle_id">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 


        <!-- Modal Recuperar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalLiderCalleRecuperar" tabindex="-1" aria-labelledby="modalLiderCalleRecuperarLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLiderCalleRecuperarLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Al recuperar el Jefe de Calle, se recuperaran también los datos del Usuario vinculado.</p>
                    <h6>¿Seguro que desea Recuperar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="lider_calle.php" method="POST">
                            <input type="hidden" name="jefe_calle_id" id="jefe_calle_id">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="Recuperar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash-arrow-up"></i>Recuperar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 


    </div>


        

  </div>     

    <!-- JS Bootstrap -->
    <script src="../src/js/bootstrap.bundle.min.js"></script>

    <!-- jquery -------->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <!-- JS DataTable -->
    <script src="../src/plugins/datatables.min.js"></script>



    <!-- JS nuestro ---->
    <script src="../src/js/lider_calles.js"></script>


<script>
    
 
                                                
/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalLiderCalleEditar = document.getElementById('modalLiderCalleEditar')  //id de la ventana modal Editar Registro
    let modalLiderCalleEliminar = document.getElementById('modalLiderCalleEliminar')  //id de la ventana modal Eliminar Registro
    let modalLiderCalleRecuperar = document.getElementById('modalLiderCalleRecuperar')

    modalLiderCalleEditar.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let jefe_calle_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputJefeCalleId = modalLiderCalleEditar.querySelector('.modal-body #jefe_calle_id')              //selecionamos la clase y el id presentes en el formulario
        let inputCedula = modalLiderCalleEditar.querySelector('.modal-body #ci_ps')
        let inputNombre = modalLiderCalleEditar.querySelector('.modal-body #nombre')
        let inputApellido = modalLiderCalleEditar.querySelector('.modal-body #apellido')
        let inputCorreo = modalLiderCalleEditar.querySelector('.modal-body #correo')
        let inputManzanaId = modalLiderCalleEditar.querySelector('.modal-body #manzana_id')
        let inputUserId = modalLiderCalleEditar.querySelector('.modal-body #usuario_id')
        let inputUser = modalLiderCalleEditar.querySelector('.modal-body #user')
        let inputRolID = modalLiderCalleEditar.querySelector('.modal-body #rol_id')


        let url = "lider_calle_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('jefe_calle_id', jefe_calle_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputJefeCalleId.value = data.jefe_calle_id      //lo que viene a continuacion de data. es el campo al cual se llama en la consulta a la BD
            inputCedula.value = data.ci_ps
            inputNombre.value = data.nombre
            inputApellido.value = data.apellido
            inputCorreo.value = data.correo
            inputManzanaId.value = data.manzana_id
            inputUserId.value = data.usuario_id
            inputUser.value = data.nombre_usuario
            inputRolID.value = data.rol_id

            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalLiderCalleEliminar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let jefe_calle_id = button.getAttribute('data-bs-id') 
        
        modalLiderCalleEliminar.querySelector('.modal-footer #jefe_calle_id').value = jefe_calle_id

    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalLiderCalleRecuperar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let jefe_calle_id = button.getAttribute('data-bs-id') 
        
        modalLiderCalleRecuperar.querySelector('.modal-footer #jefe_calle_id').value = jefe_calle_id

    })


</script>
</body>
</html>