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
 
        /*if ($datos['rol_id'] == 1) {
         header('Location: inicio-jefe-comunidad.php');
 
        } else*/
        
        if ($datos['rol_id'] == 2 || $datos['rol_id'] == 1) {  
            
            $id_jefe = ($datos['rol_id'] == 2)?$_SESSION['user']:$_GET['id_jefe'];
            
            //-------------- Obtener datos para el formulario editar ----------------//
            if (isset($_POST['habitante_incapacidad_id'])) {
            
                $habitante_incapacidad_id = $_POST['habitante_incapacidad_id'];
            
                $resultado = $conexion->prepare("SELECT habitante_incapacidad_id, habitante_id, incapacidad_id, observaciones FROM habitante_incapacidad WHERE md5(habitante_incapacidad_id) =?");
                $resultado->execute([$habitante_incapacidad_id]);
                $rows = $resultado->rowcount();
                $habitante_incapacidad = [];
            
                if ($rows > 0){
                    $habitante_incapacidad = $resultado->fetch(PDO::FETCH_ASSOC);
                }
            
                $habitante_incapacidad["habitante_incapacidad_id"] = md5($habitante_incapacidad["habitante_incapacidad_id"]);
            
                echo json_encode($habitante_incapacidad);
            }
        
        } else {    //No puede haber otro rol que no sea especificado
         session_destroy();
 
        }
         
     } else {
 
         session_destroy();
         
     }
 
}



?>