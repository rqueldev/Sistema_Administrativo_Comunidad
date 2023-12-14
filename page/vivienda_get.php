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
            if (isset($_POST['vivienda_id'])) {
        
                $vivienda_id = $_POST['vivienda_id'];
        
                $resultado = $conexion->prepare("SELECT vivienda_id, numero, manzana_id, estatus, tipo, numero_combos_clap, cedula FROM vivienda WHERE md5(vivienda_id) =?");
                $resultado->execute([$vivienda_id]);
                $rows = $resultado->rowcount();
                $vivienda = [];
        
                if ($rows > 0){
                    $vivienda = $resultado->fetch(PDO::FETCH_ASSOC);
                   // var_dump($vivienda);
                }
        
                $vivienda["vivienda_id"] = md5($vivienda["vivienda_id"]);
        
                echo json_encode($vivienda);
            }

        } else {    //No puede haber otro rol que no sea especificado
         session_destroy();
 
        }
         
     } else {
 
         session_destroy();
         
     }
 
}


?>