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
            if (isset($_POST['jornada_id'])) {

                $jornada_id = $_POST['jornada_id'];
            
                $resultado = $conexion->prepare("SELECT jornada_id, categoria, jefe_comunidad_id FROM jornada WHERE md5(jornada_id)=?");
                $resultado->execute([$jornada_id]);
                $rows = $resultado->rowcount();
                $jornada = [];
            
                if ($rows > 0){
                    $jornada = $resultado->fetch(PDO::FETCH_ASSOC);
                   // var_dump($manzana);
                }
                
                $jornada["jornada_id"] = md5($jornada["jornada_id"]);
                echo json_encode($jornada);
            }

            //-------------- Obtener datos para el formulario editar ATENCION  ----------------//
            if (isset($_POST['atencion_id'])) {

                $atencion_id = $_POST['atencion_id'];
            
                $resultado_atencion = $conexion->prepare("SELECT atencion_id, jornada_id, fecha_entrega, cantidad FROM atencion WHERE md5(atencion_id)=?");
                $resultado_atencion->execute([$atencion_id]);
                $rows_atencion = $resultado_atencion->rowcount();
                $atencion = [];
            
                if ($rows_atencion > 0){
                    $atencion = $resultado_atencion->fetch(PDO::FETCH_ASSOC);
                   // var_dump($manzana);
                }
                
                $atencion["atencion_id"] = md5($atencion["atencion_id"]);
                echo json_encode($atencion);
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