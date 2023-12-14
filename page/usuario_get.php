<?php

include 'conexion.php';
session_start();
$sesion = $_SESSION['user'];

if ($sesion == null or $sesion = '') {
    // echo 'Usted no tiene autorizacion!';
     header('Location: error403.html');   //cual sea tu gusto 
     die();
 
   /* 1. Verificar si ya hay una sesión activa para continuar con el proceso*/  
} else if(isset($_SESSION['user'])){
 
     $rescatar_sesion = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña, rol_id FROM usuario WHERE nombre_usuario=? ');   
     $rescatar_sesion->execute([$_SESSION['user']]);
 
     $datos = $rescatar_sesion->fetch(PDO::FETCH_ASSOC); 
     
     /* 2. Si el usuario esxiste verificar roles */
     if (is_countable($datos)) {   
 
        if ($datos['rol_id'] == 1) {            
            
            //-------------- Obtener datos para el formulario editar ----------------//
            if (isset($_POST['usuario_id'])) {

                $usuario_id = $_POST['usuario_id'];
            
                $resultado = $conexion->prepare("SELECT usuario_id, nombre_usuario, rol_id FROM usuario WHERE md5(usuario_id)=?");
                $resultado->execute([$usuario_id]);
                $rows = $resultado->rowcount();
                $usuario = [];
            
                if ($rows > 0){
                    $usuario = $resultado->fetch(PDO::FETCH_ASSOC);
                   // var_dump($manzana);
                }
                
                $usuario["usuario_id"] = md5($usuario["usuario_id"]);
                echo json_encode($usuario);
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


?>