<?php

session_start();
include 'conexion.php';
include 'validar_campo.php';

$sesion = $_SESSION['user'];

if ($sesion == null or $sesion = '') {
   // echo 'Usted no tiene autorizacion!';
    header('Location: error403.html');  
    die();

  /* 1. Verificar si ya hay una sesión activa */  
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
       
        if ($datos['rol_id'] == 2 || $datos['rol_id'] == 1 ) {    

        $id_jefe = ($datos['rol_id'] == 2)?$_SESSION['user']:$_GET['id_jefe'];

        /* 3. Obtener datos de esta sesion como nombre y apellido del jefe de calle y numero de manzana bajo su cargo*/
        $datos_sesion = $conexion->prepare('SELECT j.jefe_calle_id, j.ci_ps, j.nombre, j.apellido, j.correo, m.numero_manzana, m.manzana_id FROM jefe_calle AS j
        INNER JOIN manzana AS m
        ON j.manzana_id=m.manzana_id 
        INNER JOIN usuario AS u
        ON u.usuario_id=j.usuario_id
        WHERE j.estado=? AND u.estado=? AND u.nombre_usuario=?');
        $datos_sesion->execute(["1","1",$id_jefe]);   
                 
        foreach ($datos_sesion as $d) {
            $d['jefe_calle_id'];
            $d['ci_ps'];
            $d['nombre'];
            $d['apellido'];
            $d['correo'];
            $d['numero_manzana'];
            $d['manzana_id'];
    
        }

     //----------------------------------------- ACCION INGRESAR ---------------------------------------------//

        $nombre = '';
        $apellido = '';
        $ci_ps_pn = '';
        $fecha_nacimiento = '';
        $edad = '';
        $genero = '';
        $telefono = '';
        $vivienda_id = '';
        $grado_instruccion = '';
        $jefe_hogar = '';

        if (isset($_POST['Añadir'])){

            $nombre = valida_campo($_POST['nombre']);
            $apellido = valida_campo($_POST['apellido']);
            $ci_ps_pn = valida_campo($_POST['ci_ps_pn']);
            $fecha_nacimiento = valida_campo($_POST['fecha_nacimiento']);
            $edad = valida_campo($_POST['edad']);
            $genero = valida_campo($_POST['genero']);
            $telefono = valida_campo($_POST['telefono']);
            $vivienda_id = valida_campo($_POST['vivienda_id']);
            $grado_instruccion = valida_campo($_POST['grado_instruccion']);
            error_reporting(0);
            $jefe_hogar = valida_campo($_POST['jefe_hogar']);


            if (empty($nombre)) {
                $error_nombre_1 = 'Coloque un nombre';
            } else {
                if (strlen($nombre) > 20) {
                    $error_nombre_2 = 'El nombre es muy largo';
                }
            } 
        
            if (empty($apellido)) {
                $error_apellido_1 = 'Coloque un apellido';
            }else {
                if (strlen($apellido) > 20) {
                    $error_apellido_2 = 'El apellido es muy largo';
                }
            }   
        
            if (empty($ci_ps_pn)) {
                $error_ci_ps_pn_1 = 'Coloque una cédula';
            } else {
                if (strlen($ci_ps_pn) > 8) {
                    $error_ci_ps_pn_2 = 'La cédula no puede tener más de 8 caracteres';
                }
            }
        
            if (empty($fecha_nacimiento)) {                      
                $error_fecha_nacimiento_1 = 'Coloque la fecha de nacimiento';
            }

            if (empty($edad)) {
                $error_edad_1 = 'Coloque una edad';
            } else {
                if (strlen($edad) > 3) {
                    $error_edad_2 = 'La edad no puede tener más de 3 dígitos';
                }
            }

            if (empty($genero)) {                      
                $error_genero_1 = 'Por favor, elija una opción';
            }

            if (strlen($telefono) > 12) {
                $error_telefono_1 = 'El número de teléfono es muy largo';  
            }

            if (empty($vivienda_id)) {                      
                $error_vivienda_id_1 = 'Coloque la vivienda';
            }
        
            if (strlen($grado_instruccion) > 100) {
                $error_grado_instruccion_1 = 'El contenido es muy largo';  
            }


            /* 1. Verificar que no hay errores para insertar el registro */
            if (empty($error_nombre_1) and empty($error_nombre_2) and empty($error_apellido_1) and empty($error_apellido_2) and empty($error_ci_ps_pn_1) and empty($error_ci_ps_pn_2) 
                and empty($error_fecha_nacimiento_1) and empty($error_edad_1) and empty($error_edad_2) and empty($error_genero_1) and empty($error_telefono_1) and empty($error_vivienda_id_1)
                and empty($error_grado_instruccion_1)){

                    /* 2. Verificar si ya existe un habitante en la BD con los mismos datos ingresados */
                        
                        // 2.1 Comprobar Cédula
                        $verificar_cedula = $conexion->prepare('SELECT * FROM habitante WHERE ci_ps_pn=? AND estado=?');  
                        $verificar_cedula->execute([$ci_ps_pn,"1"]);
                
                        $r = [];
                        $r = $verificar_cedula->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($r)){
                            
                            //limpiar errores
                            $error_nombre_1 = '';
                            $error_nombre_2 = '';
                            $error_apellido_1 = '';
                            $error_apellido_2 = '';
                            $error_ci_ps_pn_1 = '';
                            $error_ci_ps_pn_2 = '';
                            $error_fecha_nacimiento_1 = '';
                            $error_edad_1 = '';
                            $error_edad_2 = '';
                            $error_genero_1 = '';
                            $error_telefono_1 = '';
                            $error_vivienda_id_1 = '';
                            $error_grado_instruccion_1 = '';

                            //limpiar valores
                            $nombre = '';
                            $apellido = '';
                            $ci_ps_pn = '';
                            $fecha_nacimiento = '';
                            $edad = '';
                            $genero = '';
                            $telefono = '';
                            $vivienda_id = '';
                            $grado_instruccion = '';
                            $jefe_hogar = '';

                            echo '
                            <script>
                                alert("Lo sentimos, el numero de cédula le corresponde a otro registro");
                            </script>
                            ';
                        } else {
                            //if (isset($_POST['jefe_hogar'])) {                        
                                if ($_POST['jefe_hogar']) {    

                                /* 3. Verificar si la cedula de la tabla Vivienda coincide con la cedula $ci_ps_pn ingresada en el formulario*/ 
                                $verificar_vivienda_cedula = $conexion->prepare('SELECT * FROM vivienda WHERE vivienda_id=? AND estado=?');  
                                $verificar_vivienda_cedula->execute([$vivienda_id,"1"]);
                            
                                $s = [];
                                $s = $verificar_vivienda_cedula->fetch(PDO::FETCH_ASSOC);
        
                                if ($s['cedula'] == $ci_ps_pn){  // existe la relación

                                    /* 4. Verificar que la vivienda seleccionada aun no exista en la Tabla Jefe Hogar, pues en ese caso significaría que el Jefe de Hogar para esa vivienda ya existe y se encuentra en esa Tabla*/ 
                                    $verificar_jefe_hogar_vivienda = $conexion->prepare('SELECT * FROM jefe_hogar WHERE vivienda_id=? AND estado=?');  
                                    $verificar_jefe_hogar_vivienda->execute([$vivienda_id,"1"]);

                                    $m = [];
                                    $m = $verificar_jefe_hogar_vivienda->fetch(PDO::FETCH_ASSOC);
            
                                    if (is_countable($m)){                            
                                        
                                        //limpiar errores
                                        $error_nombre_1 = '';
                                        $error_nombre_2 = '';
                                        $error_apellido_1 = '';
                                        $error_apellido_2 = '';
                                        $error_ci_ps_pn_1 = '';
                                        $error_ci_ps_pn_2 = '';
                                        $error_fecha_nacimiento_1 = '';
                                        $error_edad_1 = '';
                                        $error_edad_2 = '';
                                        $error_genero_1 = '';
                                        $error_telefono_1 = '';
                                        $error_vivienda_id_1 = '';
                                        $error_grado_instruccion_1 = '';
            
                                        //limpiar valores
                                        $nombre = '';
                                        $apellido = '';
                                        $ci_ps_pn = '';
                                        $fecha_nacimiento = '';
                                        $edad = '';
                                        $genero = '';
                                        $telefono = '';
                                        $vivienda_id = '';
                                        $grado_instruccion = '';
                                        $jefe_hogar = '';
            
                                        /*echo '
                                        <script>
                                            alert("Lo sentimos, esta vivienda ya tiene un Jefe de Hogar asignado");
                                        </script>
                                        ';*/

                                        echo '
                                        <script>
                                            alert("Lo sentimos, esta vivienda ya tiene un Jefe de Hogar");
                                        </script>
                                        ';

                                    } else {
                                        /* 5. Insertar los datos de habitante */ 
                                        $ingresar_habitante = $conexion->prepare("INSERT INTO habitante(nombre,apellido,ci_ps_pn,fecha_nacimiento,edad,genero,telefono,vivienda_id,grado_instruccion) VALUES (?,?,?,?,?,?,?,?,?)");
                                        $ingresar_habitante->execute([$nombre,$apellido,$ci_ps_pn,$fecha_nacimiento,$edad,$genero,$telefono,$vivienda_id,$grado_instruccion]);
                                        
                                        /* 6. Insertar los datos de Jefe de Hogar */
                                        // 6.1 Buscar el habitante_id que se genero de la anterior insercion a la BD*/ 
                                        $buscar_habitante = $conexion->prepare("SELECT * FROM habitante WHERE ci_ps_pn=? AND estado=?");
                                        $buscar_habitante->execute([$ci_ps_pn,"1"]);
                                                
                                        $ha = $buscar_habitante->fetch(PDO::FETCH_ASSOC);

                                        // 6.2 Insertar los de Jefe de Hogar con el id obtenido
                                        $ingresar_jefe_hogar = $conexion->prepare("INSERT INTO jefe_hogar(habitante_id,vivienda_id) VALUES (?,?)");
                                        $ingresar_jefe_hogar->execute([$ha['habitante_id'],$vivienda_id]); 
 
                                        //limpiar errores
                                        $error_nombre_1 = '';
                                        $error_nombre_2 = '';
                                        $error_apellido_1 = '';
                                        $error_apellido_2 = '';
                                        $error_ci_ps_pn_1 = '';
                                        $error_ci_ps_pn_2 = '';
                                        $error_fecha_nacimiento_1 = '';
                                        $error_edad_1 = '';
                                        $error_edad_2 = '';
                                        $error_genero_1 = '';
                                        $error_telefono_1 = '';
                                        $error_vivienda_id_1 = '';
                                        $error_grado_instruccion_1 = '';
            
                                        //limpiar valores
                                        $nombre = '';
                                        $apellido = '';
                                        $ci_ps_pn = '';
                                        $fecha_nacimiento = '';
                                        $edad = '';
                                        $genero = '';
                                        $telefono = '';
                                        $vivienda_id = '';
                                        $grado_instruccion = '';
                                        $jefe_hogar = '';

                                        $registrado = 'registrado';

                                    }
                                
                                } else {

                                    //limpiar errores
                                    $error_nombre_1 = '';
                                    $error_nombre_2 = '';
                                    $error_apellido_1 = '';
                                    $error_apellido_2 = '';
                                    $error_ci_ps_pn_1 = '';
                                    $error_ci_ps_pn_2 = '';
                                    $error_fecha_nacimiento_1 = '';
                                    $error_edad_1 = '';
                                    $error_edad_2 = '';
                                    $error_genero_1 = '';
                                    $error_telefono_1 = '';
                                    $error_vivienda_id_1 = '';
                                    $error_grado_instruccion_1 = '';
            
                                    //limpiar valores
                                    $nombre = '';
                                    $apellido = '';
                                    $ci_ps_pn = '';
                                    $fecha_nacimiento = '';
                                    $edad = '';
                                    $genero = '';
                                    $telefono = '';
                                    $vivienda_id = '';
                                    $grado_instruccion = '';
                                    $jefe_hogar = '';

                                    echo '
                                    <script>
                                           alert("Lo sentimos, la vivienda seleccionada tiene asignado a otro Jefe de Hogar");
                                    </script>
                                   ';
                                }



                            } elseif(empty($jefe_hogar)) {  //Insertar un habitante común

                                /* 5. Insertar los datos de habitante */ 
                                $ingresar_habitante = $conexion->prepare("INSERT INTO habitante(nombre,apellido,ci_ps_pn,fecha_nacimiento,edad,genero,telefono,vivienda_id,grado_instruccion) VALUES (?,?,?,?,?,?,?,?,?)");
                                $ingresar_habitante->execute([$nombre,$apellido,$ci_ps_pn,$fecha_nacimiento,$edad,$genero,$telefono,$vivienda_id,$grado_instruccion]);
                                         
                                //limpiar errores
                                $error_nombre_1 = '';
                                $error_nombre_2 = '';
                                $error_apellido_1 = '';
                                $error_apellido_2 = '';
                                $error_ci_ps_pn_1 = '';
                                $error_ci_ps_pn_2 = '';
                                $error_fecha_nacimiento_1 = '';
                                $error_edad_1 = '';
                                $error_edad_2 = '';
                                $error_genero_1 = '';
                                $error_telefono_1 = '';
                                $error_vivienda_id_1 = '';
                                $error_grado_instruccion_1 = '';
            
                                //limpiar valores
                                $nombre = '';
                                $apellido = '';
                                $ci_ps_pn = '';
                                $fecha_nacimiento = '';
                                $edad = '';
                                $genero = '';
                                $telefono = '';
                                $vivienda_id = '';
                                $grado_instruccion = '';
                                $jefe_hogar = '';

                                $registrado = 'registrado';
                            } 
                        }
                }

        }
        //----------------------------------------- ACCION ACTUALIZAR ---------------------------------------------//

        if (isset($_POST['Actualizar'])){

            $habitante_id2 = $_POST['habitante_id'];
            $nombre_editar = valida_campo($_POST['nombre']);
            $apellido_editar = valida_campo($_POST['apellido']);
            $ci_ps_pn_editar = valida_campo($_POST['ci_ps_pn']);
            $fecha_nacimiento_editar = valida_campo($_POST['fecha_nacimiento']);
            $edad_editar = valida_campo($_POST['edad']);
            $genero_editar = valida_campo($_POST['genero']);
            $telefono_editar = valida_campo($_POST['telefono']);
            $vivienda_id_editar = valida_campo($_POST['vivienda_id']);
            $grado_instruccion_editar = valida_campo($_POST['grado_instruccion']);
            error_reporting(0);
            $jefe_hogar_editar = valida_campo($_POST['jefe_hogar']);


            if (empty($nombre_editar)) {
                $error_nombre_editar_1 = 'Coloque un nombre';
            } else {
                if (strlen($nombre_editar) > 20) {
                    $error_nombre_editar_2 = 'El nombre es muy largo';
                }
            } 
        
            if (empty($apellido_editar)) {
                $error_apellido_editar_1 = 'Coloque un apellido';
            }else {
                if (strlen($apellido_editar) > 20) {
                    $error_apellido_editar_2 = 'El apellido es muy largo';
                }
            }   
        
            if (empty($ci_ps_pn_editar)) {
                $error_ci_ps_pn_editar_1 = 'Coloque una cédula';
            } else {
                if (strlen($ci_ps_pn_editar) > 8) {
                    $error_ci_ps_pn_editar_2 = 'La cédula no puede tener más de 8 caracteres';
                }
            }
        
            if (empty($fecha_nacimiento_editar)) {                      
                $error_fecha_nacimiento_editar_1 = 'Coloque la fecha de nacimiento';
            }

            if (empty($edad_editar)) {
                $error_edad_editar_1 = 'Coloque una edad';
            } else {
                if (strlen($edad_editar) > 3) {
                    $error_edad_editar_2 = 'La edad no puede tener más de 3 dígitos';
                }
            }

            if (empty($genero_editar)) {                      
                $error_genero_editar_1 = 'Por favor, elija una opción';
            }

            if (strlen($telefono_editar) > 12) {
                $error_telefono_editar_1 = 'El número de teléfono es muy largo';  
            }

            if (empty($vivienda_id_editar)) {                      
                $error_vivienda_id_editar_1 = 'Coloque la vivienda';
            }
        
            if (strlen($grado_instruccio_editarn) > 100) {
                $error_grado_instruccion_editar_1 = 'El contenido es muy largo';  
            }


            /* 1. Verificar que no hay errores para actualizar el registro */
            if (empty($error_nombre_editar_1) and empty($error_nombre_editar_2) and empty($error_apellido_editar_1) and empty($error_apellido_editar_2) and empty($error_ci_ps_pn_editar_1) and empty($error_ci_ps_pn_editar_2) 
                and empty($error_fecha_nacimiento_editar_1) and empty($error_edad_editar_1) and empty($error_edad_editar_2) and empty($error_genero_editar_1) and empty($error_telefono_editar_1) and empty($error_vivienda_id_editar_1)
                and empty($error_grado_instruccion_editar_1)){

                    /* 2. Verificar si ya existe un habitante en la BD con los mismos datos ingresados */
                        
                        // 2.1 Comprobar Cédula
                        $verificar_cedula_editar = $conexion->prepare('SELECT * FROM habitante WHERE md5(habitante_id) <> ? AND ci_ps_pn=? AND estado=?');  
                        $verificar_cedula_editar->execute([$habitante_id2,$ci_ps_pn_editar,"1"]);
                
                        $re = [];
                        $re = $verificar_cedula_editar->fetch(PDO::FETCH_ASSOC);

                        if (is_countable($re)){
                            
                            //limpiar errores
                            $error_nombre_editar_1 = '';
                            $error_nombre_editar_2 = '';
                            $error_apellido_editar_1 = '';
                            $error_apellido_editar_2 = '';
                            $error_ci_ps_pn_editar_1 = '';
                            $error_ci_ps_pn_editar_2 = '';
                            $error_fecha_nacimiento_editar_1 = '';
                            $error_edad_editar_1 = '';
                            $error_edad_editar_2 = '';
                            $error_genero_editar_1 = '';
                            $error_telefono_editar_1 = '';
                            $error_vivienda_id_editar_1 = '';
                            $error_grado_instruccion_editar_1 = '';

                            //limpiar valores
                            $habitante_id2 = '';
                            $nombre_editar = '';
                            $apellido_editar = '';
                            $ci_ps_pn_editar = '';
                            $fecha_nacimiento_editar = '';
                            $edad_editar = '';
                            $genero_editar = '';
                            $telefono_editar = '';
                            $vivienda_id_editar = '';
                            $grado_instruccion_editar = '';
                            $jefe_hogar_editar = '';

                            echo '
                            <script>
                                alert("Lo sentimos, el numero de cédula le corresponde a otro registro");
                            </script>
                            ';
                        } else {
                            //if (isset($_POST['jefe_hogar'])) {                        
                                if ($_POST['jefe_hogar']) {    

                                /* 3. Verificar si la cedula de la tabla Vivienda coincide con la cedula $ci_ps_pn_editar ingresada en el formulario*/ 
                                $verificar_vivienda_cedula_editar = $conexion->prepare('SELECT * FROM vivienda WHERE vivienda_id=? AND estado=?');  
                                $verificar_vivienda_cedula_editar->execute([$vivienda_id_editar,"1"]);
                            
                                $se = [];
                                $se = $verificar_vivienda_cedula_editar->fetch(PDO::FETCH_ASSOC);
        
                                if ($se['cedula'] == $ci_ps_pn_editar){  // existe la relación

                                    /* 4. Verificar que la vivienda seleccionada aun no exista en la Tabla Jefe Hogar, pues en ese caso significaría que el Jefe de Hogar para esa vivienda ya existe y se encuentra en esa Tabla*/ 
                                    $verificar_jefe_hogar_vivienda_editar = $conexion->prepare('SELECT * FROM jefe_hogar WHERE md5(habitante_id) <> ? AND vivienda_id=? AND estado=?');  
                                    $verificar_jefe_hogar_vivienda_editar->execute([$habitante_id2,$vivienda_id_editar,"1"]);

                                    $me = [];
                                    $me = $verificar_jefe_hogar_vivienda_editar->fetch(PDO::FETCH_ASSOC);
            
                                    if (is_countable($me)){                            
                                        
                                        //limpiar errores
                                        $error_nombre_editar_1 = '';
                                        $error_nombre_editar_2 = '';
                                        $error_apellido_editar_1 = '';
                                        $error_apellido_editar_2 = '';
                                        $error_ci_ps_pn_editar_1 = '';
                                        $error_ci_ps_pn_editar_2 = '';
                                        $error_fecha_nacimiento_editar_1 = '';
                                        $error_edad_editar_1 = '';
                                        $error_edad_editar_2 = '';
                                        $error_genero_editar_1 = '';
                                        $error_telefono_editar_1 = '';
                                        $error_vivienda_id_editar_1 = '';
                                        $error_grado_instruccion_editar_1 = '';

                                        //limpiar valores
                                        $habitante_id2 = '';
                                        $nombre_editar = '';
                                        $apellido_editar = '';
                                        $ci_ps_pn_editar = '';
                                        $fecha_nacimiento_editar = '';
                                        $edad_editar = '';
                                        $genero_editar = '';
                                        $telefono_editar = '';
                                        $vivienda_id_editar = '';
                                        $grado_instruccion_editar = '';
                                        $jefe_hogar_editar = '';

                                        echo '
                                        <script>
                                            alert("Lo sentimos, esta vivienda ya tiene un Jefe de Hogar");
                                        </script>
                                        ';

                                    } else {

                                        // funciones para actualizar la fecha en la tabla
                                        date_default_timezone_set('America/Caracas');
                                        setlocale(LC_TIME, 'spanish');
                                        $fecha_actualizacion = date('Y-m-d g:i:s');

                                        /* 5. Actualizar los datos de habitante */ 
                                        $actualizar_habitante = $conexion->prepare("UPDATE habitante SET nombre = ?, apellido = ?, ci_ps_pn = ?, fecha_nacimiento = ?, edad = ?, genero = ?, telefono = ?, vivienda_id = ?, grado_instruccion = ?, fecha_actualizacion = ? WHERE md5(habitante_id) = ?;");
                                        $actualizar_habitante->execute([$nombre_editar,$apellido_editar,$ci_ps_pn_editar,$fecha_nacimiento_editar,$edad_editar,$genero_editar,$telefono_editar,$vivienda_id_editar,$grado_instruccion_editar,$fecha_actualizacion,$habitante_id2]);
                                        
                                        /* 6. Actualizar los datos de Jefe de Hogar */
                                        // 6.1 Buscar el habitante_id que no este en md5 en la BD para actualizarlo */ 
                                        $buscar_habitante_editar = $conexion->prepare("SELECT * FROM habitante WHERE ci_ps_pn=? AND estado=?");
                                        $buscar_habitante_editar->execute([$ci_ps_pn_editar,"1"]);       
                                        $habi = $buscar_habitante_editar->fetch(PDO::FETCH_ASSOC);

                                        // 6.2 actualizar los datos
                                        $actualizar_jefe_hogar = $conexion->prepare("UPDATE jefe_hogar SET habitante_id = ?, vivienda_id = ?, fecha_actualizacion = ? WHERE md5(habitante_id) = ?");
                                        $actualizar_jefe_hogar->execute([$habi['habitante_id'],$vivienda_id_editar,$fecha_actualizacion,$habitante_id2]); 
 
                                        //limpiar errores
                                        $error_nombre_editar_1 = '';
                                        $error_nombre_editar_2 = '';
                                        $error_apellido_editar_1 = '';
                                        $error_apellido_editar_2 = '';
                                        $error_ci_ps_pn_editar_1 = '';
                                        $error_ci_ps_pn_editar_2 = '';
                                        $error_fecha_nacimiento_editar_1 = '';
                                        $error_edad_editar_1 = '';
                                        $error_edad_editar_2 = '';
                                        $error_genero_editar_1 = '';
                                        $error_telefono_editar_1 = '';
                                        $error_vivienda_id_editar_1 = '';
                                        $error_grado_instruccion_editar_1 = '';

                                        //limpiar valores
                                        $habitante_id2 = '';
                                        $nombre_editar = '';
                                        $apellido_editar = '';
                                        $ci_ps_pn_editar = '';
                                        $fecha_nacimiento_editar = '';
                                        $edad_editar = '';
                                        $genero_editar = '';
                                        $telefono_editar = '';
                                        $vivienda_id_editar = '';
                                        $grado_instruccion_editar = '';
                                        $jefe_hogar_editar = '';

                                        $actualizado = 'actualizado';
                                       

                                    }
                                
                                } else {

                                    //limpiar errores
                                    $error_nombre_editar_1 = '';
                                    $error_nombre_editar_2 = '';
                                    $error_apellido_editar_1 = '';
                                    $error_apellido_editar_2 = '';
                                    $error_ci_ps_pn_editar_1 = '';
                                    $error_ci_ps_pn_editar_2 = '';
                                    $error_fecha_nacimiento_editar_1 = '';
                                    $error_edad_editar_1 = '';
                                    $error_edad_editar_2 = '';
                                    $error_genero_editar_1 = '';
                                    $error_telefono_editar_1 = '';
                                    $error_vivienda_id_editar_1 = '';
                                    $error_grado_instruccion_editar_1 = '';

                                    //limpiar valores
                                    $habitante_id2 = '';
                                    $nombre_editar = '';
                                    $apellido_editar = '';
                                    $ci_ps_pn_editar = '';
                                    $fecha_nacimiento_editar = '';
                                    $edad_editar = '';
                                    $genero_editar = '';
                                    $telefono_editar = '';
                                    $vivienda_id_editar = '';
                                    $grado_instruccion_editar = '';
                                    $jefe_hogar_editar = '';

                                    echo '
                                    <script>
                                           alert("Lo sentimos, la vivienda seleccionada tiene asignado a otro Jefe de Hogar");
                                    </script>
                                   ';
                                }



                            } elseif(empty($jefe_hogar)) {  //Insertar un habitante común
                                
                                // funciones para actualizar la fecha en la tabla
                                date_default_timezone_set('America/Caracas');
                                setlocale(LC_TIME, 'spanish');
                                $fecha_actualizacion = date('Y-m-d g:i:s');

                                /* 5. Actualizar los datos de habitante */ 
                                $actualizar_habitante = $conexion->prepare("UPDATE habitante SET nombre = ?, apellido = ?, ci_ps_pn = ?, fecha_nacimiento = ?, edad = ?, genero = ?, telefono = ?, vivienda_id = ?, grado_instruccion = ?, fecha_actualizacion = ? WHERE md5(habitante_id) = ?;");
                                $actualizar_habitante->execute([$nombre_editar,$apellido_editar,$ci_ps_pn_editar,$fecha_nacimiento_editar,$edad_editar,$genero_editar,$telefono_editar,$vivienda_id_editar,$grado_instruccion_editar,$fecha_actualizacion,$habitante_id2]);
                                         
                                //limpiar errores
                                $error_nombre_editar_1 = '';
                                $error_nombre_editar_2 = '';
                                $error_apellido_editar_1 = '';
                                $error_apellido_editar_2 = '';
                                $error_ci_ps_pn_editar_1 = '';
                                $error_ci_ps_pn_editar_2 = '';
                                $error_fecha_nacimiento_editar_1 = '';
                                $error_edad_editar_1 = '';
                                $error_edad_editar_2 = '';
                                $error_genero_editar_1 = '';
                                $error_telefono_editar_1 = '';
                                $error_vivienda_id_editar_1 = '';
                                $error_grado_instruccion_editar_1 = '';

                                //limpiar valores
                                $habitante_id2 = '';
                                $nombre_editar = '';
                                $apellido_editar = '';
                                $ci_ps_pn_editar = '';
                                $fecha_nacimiento_editar = '';
                                $edad_editar = '';
                                $genero_editar = '';
                                $telefono_editar = '';
                                $vivienda_id_editar = '';
                                $grado_instruccion_editar = '';
                                $jefe_hogar_editar = '';

                                $actualizado = 'actualizado';
                                
                            } 
                        }
                }
        }

        //-------------------------------------------------- ELIMINAR ---------------------------------------------------------------//
        
        if (isset($_POST['Eliminar'])){

            $habitante_id_borrar = $_POST['habitante_id'];
            $estado = '2';
        
            /* 1. Eliminar Habitante */
            $borrar_habitante = $conexion->prepare("UPDATE habitante SET estado =? WHERE md5(habitante_id) = ?;");
            $borrar_habitante->execute([$estado, $habitante_id_borrar]);

            /* 2. Eliminar clasificacion de habitante: Incapacidad */
            $borrar_incapacidad = $conexion->prepare("UPDATE habitante_incapacidad SET estado =? WHERE md5(habitante_id) = ?;");
            $borrar_incapacidad->execute([$estado, $habitante_id_borrar]);

            /* 3. Eliminar clasificacion de habitante: Estatus Laboral */
            $borrar_estatus_laboral = $conexion->prepare("UPDATE habitante_estatus_laboral SET estado =? WHERE md5(habitante_id) = ?;");
            $borrar_estatus_laboral->execute([$estado, $habitante_id_borrar]);

            /* 4. Eliminar clasificacion de habitante: Votante */
            $borrar_votante = $conexion->prepare("UPDATE votante SET estado =? WHERE md5(habitante_id) = ?;");
            $borrar_votante->execute([$estado, $habitante_id_borrar]);

            /* 5. Eliminar clasificacion de habitante: Vocero */
            $borrar_vocero = $conexion->prepare("UPDATE vocero SET estado =? WHERE md5(habitante_id) = ?;");
            $borrar_vocero->execute([$estado, $habitante_id_borrar]);

            /* 6. Eliminar clasificacion de habitante: Jefe de Hogar */
            $borrar_jefe_hogar = $conexion->prepare("UPDATE jefe_hogar SET estado =? WHERE md5(habitante_id) = ?;");
            $borrar_jefe_hogar->execute([$estado, $habitante_id_borrar]);

            $borrado = 'borrado';

        }

         //-------------------------------------------------- RECUPERAR ---------------------------------------------------------------//
        
        if (isset($_POST['Recuperar'])){

            $habitante_id_recuperar = $_POST['habitante_id'];
            $estado_recuperar = '1';
        
            /* 1. Recuperar Habitante */
            $recuperar_habitante = $conexion->prepare("UPDATE habitante SET estado =? WHERE md5(habitante_id) = ?;");
            $recuperar_habitante->execute([$estado_recuperar, $habitante_id_recuperar]);

            /* 2. Recuperar clasificacion de habitante: Incapacidad */
            $recuperar_incapacidad = $conexion->prepare("UPDATE habitante_incapacidad SET estado =? WHERE md5(habitante_id) = ?;");
            $recuperar_incapacidad->execute([$estado_recuperar, $habitante_id_recuperar]);

            /* 3. Recuperar clasificacion de habitante: Estatus Laboral */
            $recuperar_estatus_laboral = $conexion->prepare("UPDATE habitante_estatus_laboral SET estado =? WHERE md5(habitante_id) = ?;");
            $recuperar_estatus_laboral->execute([$estado_recuperar, $habitante_id_recuperar]);

            /* 4. Recuperar clasificacion de habitante: Votante */
            $recuperar_votante = $conexion->prepare("UPDATE votante SET estado =? WHERE md5(habitante_id) = ?;");
            $recuperar_votante->execute([$estado_recuperar, $habitante_id_recuperar]);

            /* 5. Recuperar clasificacion de habitante: Vocero */
            $recuperar_vocero = $conexion->prepare("UPDATE vocero SET estado =? WHERE md5(habitante_id) = ?;");
            $recuperar_vocero->execute([$estado_recuperar, $habitante_id_recuperar]);

            /* 6. Recuperar clasificacion de habitante: Jefe de Hogar */
            $recuperar_jefe_hogar = $conexion->prepare("UPDATE jefe_hogar SET estado =? WHERE md5(habitante_id) = ?;");
            $recuperar_jefe_hogar->execute([$estado_recuperar, $habitante_id_recuperar]);

            $recuperado = 'recuperado';
            

        }

        } else {    //No puede haber otro rol que no sea especificado
        session_destroy();

       }
        

    } else {

        session_destroy();
        
    }

}

/* --------------------------------------------------------------------------------------------------------------------------------------------- */


//Informacion de la Tabla
$habitante = $conexion->prepare('SELECT h.habitante_id, h.nombre, h.apellido, h.ci_ps_pn, h.fecha_nacimiento, h.edad, h.genero, h.telefono, h.grado_instruccion, v.numero  FROM habitante as h
INNER JOIN vivienda as v
ON h.vivienda_id=v.vivienda_id
INNER JOIN manzana as m
ON v.manzana_id=m.manzana_id
WHERE h.estado=? AND v.estado=? AND m.estado=? AND m.numero_manzana=? ;');
$habitante->execute(["1","1","1",$d['numero_manzana']]);


//Viviendas de la manzana seleccionada para el formulario ingresar
$vivienda_manzana_selecciona = $conexion->prepare('SELECT vivienda_id, numero FROM vivienda WHERE manzana_id=? AND estado=?;');
$vivienda_manzana_selecciona->execute([$d['manzana_id'],"1"]);

//Viviendas de la manzana seleccionada para el formulario editar
$vivienda_manzana_selecciona_editar = $conexion->prepare('SELECT vivienda_id, numero FROM vivienda WHERE manzana_id=? AND estado=?;');
$vivienda_manzana_selecciona_editar->execute([$d['manzana_id'],"1"]);


/* ----------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------- */
// Información de la Tabla de Recuperación 
$recuperar_habitante = $conexion->prepare('SELECT h.habitante_id, h.nombre, h.apellido, h.ci_ps_pn, h.fecha_nacimiento, h.edad, h.genero, h.telefono, h.grado_instruccion, v.numero  FROM habitante as h
INNER JOIN vivienda as v
ON h.vivienda_id=v.vivienda_id
INNER JOIN manzana as m
ON v.manzana_id=m.manzana_id
WHERE h.estado=? AND m.numero_manzana=? ;');
$recuperar_habitante->execute(["2",$d['numero_manzana']]);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitantes</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">
    
    <!-- CSS SweetAtert 2 -->
    <link rel="stylesheet" href="../src/plugins/sweetAlert2/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/habitante.css">

     <!-- Links de Google Fonts para Logo-->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet"> 

</head>

<body>

<div class="container-fluid">
    <div class="row">
        <nav class="navbar bg-light shadow fixed-top d-flex justify-content-between">
            <!-- Logo -->
            <a href="#" class="navbar-brand d-flex align-items-center logo__s">
                <img src="../src/img/log.png" alt="logo" class="navbar-brand__img">
                <img src="../src/img/logo_name.jpg" class="w-25" alt="">
                <!-- <h5 class="logo-name">Sector Universitario Oeste</h5>-->
            </a>
            <!-- user -->
            <div class="user d-flex flex-column align-items-center justify-content-center px-3">
                <img src="../src/img/userleader.png" alt="userleader">
                <h6 class="lead fs-6"><?php echo $d['nombre']. " " .$d['apellido']?></h6>
            </div>
        </nav>
    </div>
</div>
    

            <!-- sidebar -->
    <div class="row g-0 row-cols-12">
        <div class="d-flex flex-column col-auto min-vh-100 bgside__bar bajar position-fixed">
            <div class="mt-4">
                <a href="inicio-lider-calle.php?id_jefe=<?php echo $id_jefe ?>" class="text-white d-none d-sm-inline text-decoration-none d-flex align-items-center ms-4" role="button">
                    <span class="fs-5">Inicio</span>
                </a>
                <hr class="text-white d-none d-sm-block"/>
                <ul class="nav nav-pills flex-column mt-2 mt-sm-0" id="menu">

                    <li class="nav-item my-sm-1 my-2" id="btn">
                        <a href="vivienda.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-house"></i>
                            <span class="ms-2 d-none d-sm-inline boton-manzana">Viviendas</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="habitante.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-user-group"></i>
                            <span class="ms-2 d-none d-sm-inline">Habitantes</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="estatus_laboral.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-briefcase"></i>
                            <span class="ms-2 d-none d-sm-inline">Estatus Laboral</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="incapacidad.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa-solid fa-wheelchair"></i>
                            <span class="ms-2 d-none d-sm-inline">Incapacidad</span>
                        </a>
                    </li> 
                    <li class="nav-item my-sm-1 my-2">
                        <a href="votante.php?id_jefe=<?php echo $id_jefe ?>" class="nav-link text-white text-center text-sm-start" aria-current="page">
                        <i class="fa-solid fa-users"></i>
                            <span class="ms-2 d-none d-sm-inline">Votantes</span>
                        </a>
                    </li>   
               
                </ul>
            </div>
            <div>
                <hr class="text-white d-none d-sm-block"/>
                <div class="dropdown open">
                    <a class="btn border-none outline-none text-white dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                                <i class="fa fa-user"></i><span class="ms-1 d-none d-sm-inline"> Mi cuenta</span>                               
                            </a>
                    <div class="dropdown-menu" aria-labelledby="triggerId">
                        <a class="dropdown-item" href="../page/perfil_jefe_calle.php?id_jefe=<?php echo $id_jefe ?>">Perfil</a>
                        <a class="dropdown-item" href="../page/cerrar_sesion.php">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>


    <!-- JS Sweet Atert 2 -->
    <script src="../src/plugins/sweetAlert2/sweetalert2.all.min.js"></script>

    <!----------------Alerta Registrado -------------->
    <?php if(!empty($registrado)){ ?>
            
            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Registrado satisfactoriamente',
                showConfirmButton: false,
                timer: 1500
                });

            </script> 

        <?php };  ?>


    <!----------------Alerta Actualizado -------------->
        <?php if(!empty($actualizado)){ ?>

            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Actualizado satisfactoriamente',
                showConfirmButton: false,
                timer: 1500
                });     

            </script> 

        <?php };  ?>

    <!----------------Alerta Eliminado -------------->
        <?php if(!empty($borrado)){ ?>

            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Eliminado satisfactoriamente',
                showConfirmButton: false,
                timer: 2000
                });     

            </script> 

        <?php };  ?>

    <!----------------Alerta Recuperado -------------->
        <?php if(!empty($recuperado)){ ?>

            <script>
                
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Recuperado satisfactoriamente',
                showConfirmButton: false,
                timer: 2000
                });     

            </script> 

        <?php };  ?>

           
            <!-- inicio -->
           <!-- Tabla Manzana -->

        <div class="container mover-derecha-tabla col-10 small">
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2"> Datos de Habitantes</h3>
                <img src="../src/img/habitante_1.png" class="img-fluid d-flex justify-content-center col-4" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4">
                
                <div class=" col-12 bg-white table-responsive">
                    <div class="col-12 d-flex justify-content-end p-2">
                        <button type="button" class="btn boton-añadir " data-bs-toggle="modal" data-bs-target="#modalHabitante" id="botonCrear">
                            <i class=" p-1 fa-solid fa-plus"></i>Añadir 
                        </button>
                    </div>
                    <div>
                        <button id="excel" class="btn btn-success"><i class="fa-solid fa-file-excel"></i></button>
                        <button id="pdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i></button>
                        <button id="print" class="btn btn-info"><i class="fa-solid fa-print"></i></button>
                    </div>
                    <table id="datos_habitante" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-info">
                                <th class="text-center ">Nombre y Apellido</th>
                                <th class="text-center">Cédula</th>
                                <th class="text-center">Nacimiento</th>
                                <th class="text-center">Edad</th>
                                <th class="text-center">Género</th>
                                <th class="text-center">Teléfono</th>
                                <th class="text-center">Vivienda</th>
                                <th class="text-center">Grado instrucción</th>
                                <th class="text-center no-exportar">Opcion</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($habitante as $h) {
		                    ?>
		                    <tr>
                                <td><?php echo $h['nombre']." ".$h['apellido']; ?></td>
			                    <td><?php echo $h['ci_ps_pn']; ?></td>
			                    <td><?php echo $h['fecha_nacimiento']; ?></td>
                                <td><?php echo $h['edad']; ?></td>
			                    <td><?php echo $h['genero']; ?></td>
			                    <td><?php echo $h['telefono']; ?></td>
                                <td><?php echo $h['numero']; ?></td> 
                                <td><?php echo $h['grado_instruccion']; ?></td>                              
                                <!--------- botones ------------> 
			                    <td class="">
                                    <a href="habitante.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarHabitante" data-bs-id="<?= md5($h['habitante_id']); ?>"> 
                                    <i class="fa-solid fa-pencil"></i></a>

                                    <a href="habitante.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminarHabitante" data-bs-id="<?= md5($h['habitante_id']); ?>"> 
                                    <i class="fa-solid fa-trash-can"></i></a>
                                </td>
		                    </tr>

	                    	<?php 
	                        }
                        	?>
                            
                        </tbody>
                    </table>
                </div> 
            </div> 
        </div>

            <!-- fin -->

    </div>


     <!--------------------------------------------------------------- MODULO DE RECUPERACION ----------------------------------------------------------------------------->
            <!-- inicio -->
           <!-- Tabla Manzana -->

           <div class="container mover-derecha-tabla col-11 pt-5 pb-3 small">
           <hr class="mt-5 line">
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Registros Eliminados</h3>
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded m-5">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="tabla_recuperacion" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead id="encabezado">
                             <tr class="table-dark">
                                <th class="text-center ">Nombre y Apellido</th>
                                <th class="text-center">Cédula</th>
                                <th class="text-center">Nacimiento</th>
                                <th class="text-center">Edad</th>
                                <th class="text-center">Género</th>
                                <th class="text-center">Teléfono</th>
                                <th class="text-center">Vivienda</th>
                                <th class="text-center">Grado instrucción</th>
                                <th class="text-center no-exportar"></th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($recuperar_habitante as $h) {
		                    ?>
		                    <tr>
                                <td><?php echo $h['nombre']." ".$h['apellido']; ?></td>
			                    <td><?php echo $h['ci_ps_pn']; ?></td>
			                    <td><?php echo $h['fecha_nacimiento']; ?></td>
                                <td><?php echo $h['edad']; ?></td>
			                    <td><?php echo $h['genero']; ?></td>
			                    <td><?php echo $h['telefono']; ?></td>
                                <td><?php echo $h['numero']; ?></td> 
                                <td><?php echo $h['grado_instruccion']; ?></td>                              
                                <!--------- boton recuperar------------> 
			                    <td class="d-flex justify-content-center">
                                    <a href="habitante.php?id_jefe=<?php echo $id_jefe ?>" class="btn btn-sm btn-secondary" data-bs-toggle="modal" 
                                    data-bs-target="#modalRecuperarHabitante" data-bs-id="<?= md5($h['habitante_id']); ?>"> 
                                    <i class="fa-solid fa-trash-arrow-up text-white"></i></a>
                                </td>
		                    </tr>

	                    	<?php 
	                        }
                        	?>
                            
                        </tbody>
                    </table>
                </div> 
            </div> 
        </div>

            <!-- fin -->

    </div>

     <!-------------------------------------------------------------------------------------------------------------------------------------------->


    
        <!-- Modal Ingresar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalHabitante" tabindex="-1" aria-labelledby="modalHabitanteLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalHabitanteLabel"> Ingresar Registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="habitante.php?id_jefe=<?php echo $id_jefe ?>" method="POST" id="formulario">
                        <div class="modal-content border-white">
                            
                            <img src="../src/img/habitante_modal.jpg" class=" w-25 mb-4 modal_imagen" alt="">
                            

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!---------------------------------->
                                    <label for="nombre" class="form-label fw-medium" > Nombre:</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo $nombre; ?>">
                                        <?php if(!empty($error_nombre_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_nombre_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_nombre_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_nombre_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
    
                                <div class="col-4"> <!---------------------------------->
                                    <label for="apellido" class="form-label fw-medium" > Apellido:</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control" value="<?php echo $apellido; ?>">
                                        <?php if(!empty($error_apellido_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_apellido_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_apellido_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_apellido_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="ci_ps_pn" class="form-label fw-medium" > Cédula:</label>
                                    <input type="text" name="ci_ps_pn" id="ci_ps_pn" class="form-control" value="<?php echo $ci_ps_pn; ?>">
                                        <?php if(!empty($error_ci_ps_pn_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_ci_ps_pn_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_ci_ps_pn_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_ci_ps_pn_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!---------------------------------->
                                    <label for="fecha_nacimiento" class="form-label fw-medium" > Fecha de Nacimiento:</label>
                                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?php echo $fecha_nacimiento; ?>">
                                        <?php if(!empty($error_fecha_nacimiento_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_fecha_nacimiento_1" ?></p>
                                        <?php }; ?>
                                </div>
    
                                <div class="col-4"> <!---------------------------------->
                                    <label for="edad" class="form-label fw-medium" > Edad:</label>
                                    <input type="number" name="edad" id="edad" class="form-control" value="<?php echo $edad; ?>">
                                        <?php if(!empty($error_edad_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_edad_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_edad_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_edad_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                                
                                <div class="col-4"> <!---------------------------------->
                                    <label for="genero" class="form-label fw-medium"> Género:</label>
                                    <select name="genero" id="genero" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="f">Femenino</option>
                                        <option value="m">Masculino</option>
                                    </select>

                                        <?php if(!empty($error_genero_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_genero_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!---------------------------------->
                                    <label for="telefono" class="form-label fw-medium" > Teléfono:</label>
                                    <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo $telefono; ?>">
                                        <?php if(!empty($error_telefono_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_telefono_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="vivienda_id" class="form-label fw-medium"> Número de Casa:</label>
                                    <select name="vivienda_id" id="vivienda_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($vivienda_manzana_selecciona as $v){
                                        ?> 
                                            <option value="<?php echo $v['vivienda_id']; ?>"><?php echo $v['numero'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                        <?php if(!empty($error_vivienda_id_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_vivienda_id_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="grado_instruccion" class="form-label fw-medium" > Grado de Instrución:</label>
                                    <input type="text" name="grado_instruccion" id="grado_instruccion" class="form-control" value="<?php echo $grado_instruccion; ?>">
                                        <?php if(!empty($error_grado_instruccion_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_grado_instruccion_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div> <!---------------------------------->
                                    <input type="checkbox" name="jefe_hogar" id="jefe_hogar" class="form-check-input" value="jefe_hogar" checked>
                                    <label for="jefe_hogar" class="form-check-label form-label fw-medium">Seleccione si es Jefe de Hogar</label>
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" name="Añadir" class="btn btn-primary"><i class=" p-1 fa-solid fa-floppy-disk"></i>Guardar</button>
                            </div>
                        </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                     
                  </div>
                </div>
              </div>
        </div> 

        
        <!-- Modal Editar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEditarHabitante" tabindex="-1" aria-labelledby="modalEditarHabitanteLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEditarHabitanteLabel">Editar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <form action="habitante.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                        <div class="modal-content border-white">

                            <img src="../src/img/habitante_modal.jpg" class=" w-25 mb-4 modal_imagen" alt="">
                            
                            <input type="hidden" id="habitante_id" name="habitante_id" >

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!---------------------------------->
                                    <label for="nombre" class="form-label fw-medium" > Nombre:</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control">
                                        <?php if(!empty($error_nombre_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_nombre_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_nombre_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_nombre_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
    
                                <div class="col-4"> <!---------------------------------->
                                    <label for="apellido" class="form-label fw-medium" > Apellido:</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control">
                                        <?php if(!empty($error_apellido_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_apellido_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_apellido_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_apellido_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="ci_ps_pn" class="form-label fw-medium" > Cédula:</label>
                                    <input type="text" name="ci_ps_pn" id="ci_ps_pn" class="form-control">
                                        <?php if(!empty($error_ci_ps_pn_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_ci_ps_pn_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_ci_ps_pn_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_ci_ps_pn_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!---------------------------------->
                                    <label for="fecha_nacimiento" class="form-label fw-medium" > Fecha de Nacimiento:</label>
                                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control">
                                        <?php if(!empty($error_fecha_nacimiento_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_fecha_nacimiento_editar_1" ?></p>
                                        <?php }; ?>
                                </div>
    
                                <div class="col-4"> <!---------------------------------->
                                    <label for="edad" class="form-label fw-medium" > Edad:</label>
                                    <input type="number" name="edad" id="edad" class="form-control">
                                        <?php if(!empty($error_edad_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_edad_editar_1" ?></p>
                                        <?php } else {
                                        if(!empty($error_edad_editar_2)){ ?>
                                            <p class='small text-danger m-0 error'><?php echo "$error_edad_editar_2" ?></p>
                                            <?php }
                                        }; ?>
                                </div>
                                
                                <div class="col-4"> <!---------------------------------->
                                    <label for="genero" class="form-label fw-medium"> Género:</label>
                                    <select name="genero" id="genero" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="f">Femenino</option>
                                        <option value="m">Masculino</option>
                                    </select>
                                        
                                        <?php if(!empty($error_genero_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_genero_editar_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div class="col-4"> <!---------------------------------->
                                    <label for="telefono" class="form-label fw-medium" > Teléfono:</label>
                                    <input type="text" name="telefono" id="telefono" class="form-control">
                                        <?php if(!empty($error_telefono_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_telefono_editar_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="vivienda_id" class="form-label fw-medium"> Número de Casa:</label>
                                    <select name="vivienda_id" id="vivienda_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        foreach ($vivienda_manzana_selecciona_editar as $v){
                                        ?> 
                                            <option value="<?php echo $v['vivienda_id']; ?>"><?php echo $v['numero'];?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                    
                                        <?php if(!empty($error_vivienda_id_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_vivienda_id_editar_1" ?></p>
                                        <?php }; ?>
                                </div>

                                <div class="col-4"> <!---------------------------------->
                                    <label for="grado_instruccion" class="form-label fw-medium" > Grado de Instrución:</label>
                                    <input type="text" name="grado_instruccion" id="grado_instruccion" class="form-control">
                                        <?php if(!empty($error_grado_instruccion_editar_1)){ ?>
                                        <p class='small text-danger error'><?php echo "$error_grado_instruccion_editar_1" ?></p>
                                        <?php }; ?>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-center row row-cols-12">
                                <div> <!---------------------------------->
                                    <input type="checkbox" name="jefe_hogar" id="jefe_hogar" class="form-check-input" value="jefe_hogar" checked>
                                    <label for="jefe_hogar" class="form-check-label form-label fw-medium">Seleccione si es Jefe de Hogar</label>
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" id= "Actualizar" name="Actualizar" class="btn btn-primary"><i class="p-1 fa-solid fa-floppy-disk"></i>Guardar cambios</button>
                            </div>
                        </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                     
                  </div>
                </div>
              </div>
        </div>    
                    
        <!-- Modal Eliminar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalEliminarHabitante" tabindex="-1" aria-labelledby="modalEliminarHabitanteLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEliminarHabitanteLabel">Eliminar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                     <p>Al eliminar el Habitante, se eliminarán también todos los datos vinculados a este.</p>
                    <h6>¿Seguro que desea eliminar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="habitante.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="habitante_id" id="habitante_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Eliminar" name="Eliminar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash"></i>Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div>   
        
                
        <!-- Modal Eliminar Registro-->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="modalRecuperarHabitante" tabindex="-1" aria-labelledby="modalRecuperarHabitanteLabel" 
        aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRecuperarHabitanteLabel">Recuperar registro</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                     <p>Al recuperar el Habitante, se recuperarán también todos los datos vinculados a éste.</p>
                    <h6>¿Seguro que desea recuperar el registro?</h6>
                  </div>
                  <div class="modal-footer">
                    <form action="habitante.php?id_jefe=<?php echo $id_jefe ?>" method="POST">
                            <input type="hidden" name="habitante_id" id="habitante_id" >
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" id= "Recuperar" name="Recuperar" class="btn btn-primary"><i class=" p-1 fa-solid fa-trash-arrow-up"></i>Recuperar</button>
                    </form>
                  </div>
                </div>
              </div>
        </div> 
     

    <!-- JS Bootstrap -->
    <script src="../src/js/bootstrap.bundle.min.js"></script>

    <!-------------- jquery ----------------->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <!-- JS DataTable -->
    <script src="../src/plugins/datatables.min.js"></script>

    <!-- JS nuestro -->
    <script src="../src/js/habitantees.js"></script>

    <script>


/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalEditarHabitante = document.getElementById('modalEditarHabitante')  //id de la ventana modal Editar Registro
    let modalEliminarHabitante = document.getElementById('modalEliminarHabitante')  //id de la ventana modal Eliminar Registro
    let modalRecuperarHabitante = document.getElementById('modalRecuperarHabitante')


    modalEditarHabitante.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let habitante_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        /*1. Seleccionamos los inputs del formulario */
        let inputHabitanteId = modalEditarHabitante.querySelector('.modal-body #habitante_id')              //selecionamos la clase y el id presentes en el formulario
        let inputNombre = modalEditarHabitante.querySelector('.modal-body #nombre')   
        let inputApellido = modalEditarHabitante.querySelector('.modal-body #apellido')
        let inputCedula = modalEditarHabitante.querySelector('.modal-body #ci_ps_pn')
        let inputFechaNacimiento = modalEditarHabitante.querySelector('.modal-body #fecha_nacimiento')
        let inputEdad = modalEditarHabitante.querySelector('.modal-body #edad')
        let inputGenero = modalEditarHabitante.querySelector('.modal-body #genero')
        let inputTelefono = modalEditarHabitante.querySelector('.modal-body #telefono')
        let inputViviendaId = modalEditarHabitante.querySelector('.modal-body #vivienda_id')
        let inputGradoInstruccion = modalEditarHabitante.querySelector('.modal-body #grado_instruccion')

        /*2. Obtenemos los datos de la BD y los enviamos en formato json */
        let url = "habitante_get.php?id_jefe=<?php echo $id_jefe ?>"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('habitante_id', habitante_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
           
            inputHabitanteId.value = data.habitante_id
            inputNombre.value = data.nombre
            inputApellido.value = data.apellido
            inputCedula.value = data.ci_ps_pn
            inputFechaNacimiento.value = data.fecha_nacimiento
            inputEdad.value = data.edad
            inputGenero.value = data.genero
            inputTelefono.value = data.telefono
            inputViviendaId.value = data.vivienda_id
            inputGradoInstruccion.value = data.grado_instruccion

            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
modalEliminarHabitante.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let habitante_id = button.getAttribute('data-bs-id') 
        
        modalEliminarHabitante.querySelector('.modal-footer #habitante_id').value = habitante_id
    })


/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
modalRecuperarHabitante.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let habitante_id = button.getAttribute('data-bs-id') 
        
        modalRecuperarHabitante.querySelector('.modal-footer #habitante_id').value = habitante_id
    })
</script>
</body>
</html>