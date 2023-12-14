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
            
            // Información de gráfica Incapacidad
            $embarazadas = $conexion->prepare('SELECT COUNT(*) AS embarazadas FROM habitante_incapacidad where incapacidad_id = ? AND estado =?');
            $embarazadas->execute(["1","1"]);
            $total_embarazadas = $embarazadas->fetchColumn();
            
            $enfermos = $conexion->prepare('SELECT COUNT(*) AS enfermos FROM habitante_incapacidad where incapacidad_id = ? AND estado =?');
            $enfermos->execute(["2","1"]);
            $total_enfermos = $enfermos->fetchColumn();

            $pensionados = $conexion->prepare('SELECT COUNT(*) AS pensionados FROM habitante_incapacidad where incapacidad_id = ? AND estado =?');
            $pensionados->execute(["3","1"]);
            $total_pensionados = $pensionados->fetchColumn();
            

           $array = array($total_embarazadas,$total_enfermos,$total_pensionados);
           $incapacidad = array();

           for($i=0; $i<count($array); $i++){
            $myObj_incapacidad = new stdClass();
            $myObj_incapacidad->total = $array[$i];
            $incapacidad[] = $myObj_incapacidad;
           }
           
           echo json_encode($incapacidad);

            //echo json_encode($estatus_laboral_donut);
        
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