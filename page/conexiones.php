<?php
session_start();
include 'conexion.php';

$sesion = $_SESSION['user'];

if ($sesion == null or $sesion = '') {
   // echo 'Usted no tiene autorizacion!';
    header('Location: error403.html');   //cual sea tu gusto 
    die();

  /* 1. Verificar si ya hay una sesión activa */  
} else if(isset($_SESSION['user'])){

    $rescatar_sesion = $conexion->prepare('SELECT usuario_id, nombre_usuario, contraseña, rol_id FROM usuario WHERE nombre_usuario=? ');   
    $rescatar_sesion->execute([$_SESSION['user']]);

    $datos = $rescatar_sesion->fetch(PDO::FETCH_ASSOC); 
    
    /* 2. Si el usuario esxiste verificar roles */
    if (is_countable($datos)) {   

       if ($datos['rol_id'] == 1) {
            /* 3. Obtener datos de esta sesion como nombre y apellido del jefe comunidad*/
            $datos_sesion = $conexion->prepare('SELECT j.jefe_comunidad_id, j.nombre, j.apellido FROM jefe_comunidad AS j
            INNER JOIN usuario AS u
            ON u.usuario_id=j.usuario_id
            WHERE j.estado=? AND u.estado=? AND u.nombre_usuario=?');
            $datos_sesion->execute(["1","1",$_SESSION['user']]);   
                 
            foreach ($datos_sesion as $d) {
                $d['jefe_comunidad_id'];
                $d['nombre'];
                $d['apellido'];
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



/* --------------------------------------------------------------------------------------------------------------------------------------------- */


// Informacion de la Tabla Conexiones
$usuario_conexion = $conexion->prepare('SELECT u.nombre_usuario, r.descripcion, u.entrada, u.salida, u.posicion FROM usuario AS u
INNER JOIN rol AS r
ON u.rol_id=r.rol_id WHERE u.estado=? AND r.estado=?;');
$usuario_conexion->execute(["1","1"]);




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexiones</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/conexiones.css">

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
                <img src="../src/img/user.png" alt="userleader">
                <h6 class="lead fs-6"><?php echo $d['nombre']. " " .$d['apellido']?></h6>
            </div>
        </nav>
    </div>
</div>
    

            <!-- sidebar -->
    <div class="row g-0 row-cols-12">
        <div class="d-flex flex-column col-auto min-vh-100 bgside__bar bajar position-fixed">
            <div class="mt-4">
                <a href="inicio-jefe-comunidad.php" class="text-white d-none d-sm-inline text-decoration-none d-flex align-items-center ms-4" role="button">
                    <span class="fs-5">Inicio</span>
                </a>
                <hr class="text-white d-none d-sm-block"/>
                <ul class="nav nav-pills flex-column mt-2 mt-sm-0" id="menu">

                    <li class="nav-item my-sm-1 my-2">
                        <a href="manzana.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-apple-whole "></i>
                            <span class="ms-2 d-none d-sm-inline">Manzanas</span>
                        </a>
                    </li>  
                    <li class="nav-item my-sm-1 my-2">
                        <a href="lider_calle.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-address-book"></i>
                            <span class="ms-2 d-none d-sm-inline">Lideres</span>
                        </a>
                    </li>  

                    <li class="nav-item dropdown my-sm-1 my-2">
                        <a href="#sidemenu" data-bs-toggle="dropdown" class="nav-link dropdown-toggle text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-bullhorn"></i>
                            <span class="ms-2 d-none d-sm-inline">Jornadas</span>
                        </a>
                        <ul class="dropdown-menu ms-1 flex-column" id="sidemenu" data-bs-parent="#menu">
                            <li class="nav-item">
                                <a class="dropdown-item text-dark" href="jornada.php#categoria">Categoria</a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-dark" href="jornada.php#atencion" aria-current="page">Atenciones</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item my-sm-1 my-2">
                        <a href="comite.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-comments"></i>
                            <span class="ms-2 d-none d-sm-inline">Comites</span>
                        </a>
                    </li> 
                    <li class="nav-item my-sm-1 my-2">
                        <a href="totalizaciones.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-chart-column"></i>
                            <span class="ms-2 d-none d-sm-inline">Totalizaciones</span>
                        </a>
                    </li>   
                    <li class="nav-item my-sm-1 my-2">
                        <a href="conexiones.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-mobile-screen"></i>
                            <span class="ms-2 d-none d-sm-inline">Conexiones</span>
                        </a>
                    </li>            
                    <li class="nav-item my-sm-1 my-2">
                        <a href="usuarios.php" class="nav-link text-white text-center text-sm-start" aria-current="page">
                            <i class="fa fa-users"></i>
                            <span class="ms-2 d-none d-sm-inline">Usuarios</span>
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
                        <a class="dropdown-item" href="../page/perfil_jefe_comunidad.php">Perfil</a>
                        <a class="dropdown-item" href="../page/cerrar_sesion.php">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>



            <!-- inicio -->
           <!-- Tabla Manzana -->

        <div class="container mover-derecha-tabla col-10 small">
            <div class="col-12 pt-5 d-flex align-items-center justify-content-center">
                <h3 class="text-dark d-flex justify-content-center lead fs-2">Registro de Conexiones</h3>
                <img src="../src/img/Conexiones.png" class="img-fluid d-flex justify-content-center col-4" alt="">
            </div>
            <div class="row row-cols-12 tablita shadow-lg bg-white rounded mx-4 mb-4 pt-5">
                
                <div class=" col-12 bg-white table-responsive">
                    <table id="datos-conexiones" class="table table-bordered table-hover display nowrap table-striped" cellspacing="0" width="100%">
                        <thead>
                             <tr class="table-info">
                                <th class="text-center">Usuario</th>
                                <th class="text-center ">Rol</th>
                                <th class="text-center">Entrada</th>
                                <th class="text-center ">Salida</th>
                                <th class="text-center ">Posición</th>
                             </tr>
                        </thead>
                        <tbody>

                            <?php
		                    foreach ($usuario_conexion as $u) {
		                    ?>
		                    <tr>
			                    <td><?php echo $u['nombre_usuario']; ?></td>
			                    <td><?php echo $u['descripcion']; ?></td>
                                <td><?php echo $u['entrada']; ?></td>
			                    <td><?php echo $u['salida']; ?></td>
			                    <td><?php echo $u['posicion']; ?></td>
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

    </div>


        
    
  

  </div>     

    <!-- JS Bootstrap -->
    <script src="../src/js/bootstrap.bundle.min.js"></script>

    <!-------------- jquery ----------------->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <!-- JS DataTable -->
    <script src="../src/plugins/datatables.min.js"></script>

    <!-- JS nuestro -->
    <script src="../src/js/conexiones.js"></script>

    <script>

/* ---------------------------------------------------- EDITAR REGISTRO ------------------------------------------------------------------------- */
    let modalUsuarioEditar = document.getElementById('modalUsuarioEditar')  //id de la ventana modal Editar Registro
    let modalUsuarioEliminar = document.getElementById('modalUsuarioEliminar')  //id de la ventana modal Eliminar Registro
    let modalUsuarioRecuperar = document.getElementById('modalUsuarioRecuperar')

    modalUsuarioEditar.addEventListener('show.bs.modal', event => {         //le añadimos el evento que permita abrir la ventana modal gracias a shown.bs.modal
        let button = event.relatedTarget                                        //me permite saber a cual botón se le ha dado de click, recuerda que cada registro tiene un boton de editar
        let usuario_id = button.getAttribute('data-bs-id')                      //el atributo "data-bs-id" debe estar incluido en el boton para poder pasarle el "manzana_id" del registro que quiero modificar

        let inputUsuarioId = modalUsuarioEditar.querySelector('.modal-body #usuario_id')              //selecionamos la clase y el id presentes en el formulario
        let inputUser = modalUsuarioEditar.querySelector('.modal-body #user')
        //let inputPassword = modalUsuarioEditar.querySelector('.modal-body #password')
        let inputRolId = modalUsuarioEditar.querySelector('.modal-body #rol_id')


        let url = "usuario_get.php"                 //definimos la ruta donde vamos a realizar la petición
        let formData = new FormData()
        formData.append('usuario_id', usuario_id)

        fetch(url, {
            method: "POST",
            body: formData                        //informacion que estamos enviandole
        }).then(response => response.json())
        .then(data => {                           //la variable data contendra todos los datos del registro
            inputUsuarioId.value = data.usuario_id
            inputUser.value = data.nombre_usuario
            //inputPassword.value = data.contraseña
            inputRolId.value = data.rol_id
             
            console.dir(data)
        }).catch(err => console.log(err))
    })

/* ---------------------------------------------------- RECUPERAR REGISTRO ------------------------------------------------------------------------- */
    modalUsuarioRecuperar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let usuario_id = button.getAttribute('data-bs-id') 
        
        modalUsuarioRecuperar.querySelector('.modal-footer #usuario_id').value = usuario_id
    })

/* ---------------------------------------------------- ELIMINAR REGISTRO ------------------------------------------------------------------------- */
    modalUsuarioEliminar.addEventListener('show.bs.modal', event => { 
        let button = event.relatedTarget                                        
        let usuario_id = button.getAttribute('data-bs-id') 
        
        modalUsuarioEliminar.querySelector('.modal-footer #usuario_id').value = usuario_id
    })

</script>

</body>
</html>