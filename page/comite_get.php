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
            if (isset($_POST['comite_id'])) {

                $comite_id = $_POST['comite_id'];
            
                $resultado = $conexion->prepare("SELECT c.comite_id, c.nombre, c.descripcion, h.ci_ps_pn FROM comite AS c
                INNER JOIN vocero AS v
                ON c.comite_id=v.comite_id 
                INNER JOIN habitante as h
                ON v.habitante_id=h.habitante_id WHERE md5(c.comite_id)=? AND c.estado=? AND v.estado=? AND h.estado=?;");
                $resultado->execute([$comite_id,"1","1","1"]);
                $rows = $resultado->rowcount();
                $comite = [];
            
                if ($rows > 0){
                    $comite = $resultado->fetch(PDO::FETCH_ASSOC);
                   // var_dump($manzana);
                }
                
                $comite["comite_id"] = md5($comite["comite_id"]);
                echo json_encode($comite);
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