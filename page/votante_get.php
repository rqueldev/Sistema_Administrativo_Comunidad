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
 /*
        if ($datos['rol_id'] == 1) {
         header('Location: inicio-jefe-comunidad.php');
 
        } else*/
        
        if ($datos['rol_id'] == 2 || $datos['rol_id'] == 1) {   
            
            $id_jefe = ($datos['rol_id'] == 2)?$_SESSION['user']:$_GET['id_jefe'];
            
            //-------------- Obtener datos para el formulario editar ----------------//
            if (isset($_POST['votante_id'])) {
            
                $votante_id = $_POST['votante_id'];
            
                $resultado = $conexion->prepare("SELECT votante_id, habitante_id, centro_votacion FROM votante WHERE md5(votante_id) =?");
                $resultado->execute([$votante_id]);
                $rows = $resultado->rowcount();
                $votante = [];
            
                if ($rows > 0){
                    $votante = $resultado->fetch(PDO::FETCH_ASSOC);
                }
            
                $votante["votante_id"] = md5($votante["votante_id"]);
            
                echo json_encode($votante);
            }
        
        } else {   //si el nombre de usuario no se corresponde con ninguno en la BD 
         session_destroy();
 
        }
         
 
     } else {
 
         session_destroy();
         
     }
 
}



?>