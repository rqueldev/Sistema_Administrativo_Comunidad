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
            
            // Información de gráfica Estatus Laboral
            $niños = $conexion->prepare('SELECT COUNT(*) AS niños FROM habitante WHERE edad BETWEEN ? AND ? AND estado =?');
            $niños->execute(["1","12","1"]);
            $total_niños = $niños->fetchColumn();
            
            $adolescentes = $conexion->prepare('SELECT COUNT(*) AS adolescentes FROM habitante WHERE edad BETWEEN ? AND ? AND estado =?');
            $adolescentes->execute(["13","18","1"]);
            $total_adolescentes = $adolescentes->fetchColumn();

            $jovenes = $conexion->prepare('SELECT COUNT(*) AS jovenes FROM habitante WHERE edad BETWEEN ? AND ? AND estado =?');
            $jovenes->execute(["19","25","1"]);
            $total_jovenes = $jovenes->fetchColumn();

            $adultos = $conexion->prepare('SELECT COUNT(*) AS adultos FROM habitante WHERE edad BETWEEN ? AND ? AND estado =?');
            $adultos->execute(["26","55","1"]);
            $total_adultos = $adultos->fetchColumn();

            $adultos_mayores = $conexion->prepare('SELECT COUNT(*) AS adultos_mayores FROM habitante WHERE edad BETWEEN ? AND ? AND estado =?');
            $adultos_mayores->execute(["56","120","1"]);
            $total_adultos_mayores = $adultos_mayores->fetchColumn();
            

           $array = array($total_niños,$total_adolescentes,$total_jovenes,$total_adultos,$total_adultos_mayores);
           $edades = array();

           for($i=0; $i<count($array); $i++){
            $myObj_edades = new stdClass();
            $myObj_edades->total = $array[$i];
            $edades[] = $myObj_edades;
           }
           
           echo json_encode($edades);

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