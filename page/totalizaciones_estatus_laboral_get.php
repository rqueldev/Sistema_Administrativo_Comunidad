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
            $estudiante = $conexion->prepare('SELECT COUNT(*) AS estudiante FROM habitante_estatus_laboral where estatus_laboral_id = ? AND estado =?');
            $estudiante->execute(["1","1"]);
            $total_estudiante = $estudiante->fetchColumn();
            
            $trabajador = $conexion->prepare('SELECT COUNT(*) AS trabajador FROM habitante_estatus_laboral where estatus_laboral_id = ? AND estado =?');
            $trabajador->execute(["2","1"]);
            $total_trabajador = $trabajador->fetchColumn();
            
            $pensionado = $conexion->prepare('SELECT COUNT(*) AS pensionado FROM habitante_estatus_laboral where estatus_laboral_id = ? AND estado =?');
            $pensionado->execute(["3","1"]);
            $total_pensionado = $pensionado->fetchColumn();

            /*$estatus_laboral_donut = array(
                'estudiante' => $total_estudiante,
                'trabajador' => $total_trabajador,
                'pensionado' => $total_pensionado
            );*/
            
            /*$estatus_laboral_json = '
            {
                "estatus_nombre": "estudiante", 
                "total": "'.$total_estudiante.'"
            },
            {
                "estatus_nombre": "trabajador", 
                "total": "'.$total_trabajador.'"
            },
            {
                "estatus_nombre": "pensionado", 
                "total": "'.$total_pensionado.'"
            }';*/

            //$estatus_laboral_donut = json_decode($estatus_laboral_json);


           // $estatus_laboral_donut = array($total_estudiante,$total_trabajador,$total_pensionado);

           $array = array($total_estudiante,$total_trabajador,$total_pensionado);
           $objects = array();

           for($i=0; $i<count($array); $i++){
            $myObj = new stdClass();
            $myObj->total = $array[$i];
            $objects[] = $myObj;
           }
           
           echo json_encode($objects);

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