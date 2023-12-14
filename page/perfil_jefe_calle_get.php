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
 
        if ($datos['rol_id'] == 2) {            
            
            //-------------- Obtener datos para el formulario editar ----------------//
            if (isset($_POST['jefe_calle_id'])) {

                $jefe_calle_id = $_POST['jefe_calle_id'];
            
                $resultado = $conexion->prepare('SELECT j.jefe_calle_id, j.usuario_id, j.ci_ps, j.nombre, j.apellido, j.correo, u.nombre_usuario FROM jefe_calle as j
                INNER JOIN usuario as u
                ON u.usuario_id=j.usuario_id
                WHERE j.estado=? AND u.estado=? AND md5(jefe_calle_id)=?');
                $resultado->execute(["1","1",$jefe_calle_id]);
                $rows = $resultado->rowcount();
                $jefe_calle = [];
            
                if ($rows > 0){
                    $jefe_calle = $resultado->fetch(PDO::FETCH_ASSOC);
                   // var_dump($jefe_calle);
                }
                
                $jefe_calle["jefe_calle_id"] = md5($jefe_calle["jefe_calle_id"]);
                $jefe_calle["usuario_id"] = md5($jefe_calle["usuario_id"]);
                echo json_encode($jefe_calle);
                }



            /*-----------------------------------
                $jefe_calle_id = $_POST['jefe_calle_id'];
            
                $resultado = $conexion->prepare("SELECT jefe_calle_id, ci_ps, nombre, apellido, correo, manzana_id, usuario_id FROM jefe_calle WHERE md5(jefe_calle_id)=?");
                $resultado->execute([$jefe_calle_id]);
                $rows = $resultado->rowcount();
                $jefe_calle = [];
            
                if ($rows > 0){
                    $jefe_calle = $resultado->fetch(PDO::FETCH_ASSOC);

                }
                
                $jefe_calle["jefe_calle_id"] = md5($jefe_calle["jefe_calle_id"]);
                echo json_encode($jefe_calle);
                }
            */
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