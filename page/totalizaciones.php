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
// Información de Tarjetas de Totales

$manzana = $conexion->prepare('SELECT COUNT(*) FROM manzana where estado =?');
$manzana->execute(["1"]);
$total_manzana = $manzana->fetchColumn();  // fetchColumn() devuelve una unica columna de todos los resultados presentes, en este caso solo disponemos de un unico 
                                           // resultado gracias a la funcion count(*) en mysql

$vivienda = $conexion->prepare('SELECT COUNT(*) FROM vivienda where estado =?');
$vivienda->execute(["1"]);
$total_vivienda = $vivienda->fetchColumn();

$habitante = $conexion->prepare('SELECT COUNT(*) FROM habitante where estado =?');
$habitante->execute(["1"]);
$total_habitante = $habitante->fetchColumn();

$jefe_hogar = $conexion->prepare('SELECT COUNT(*) FROM jefe_hogar where estado =?');
$jefe_hogar->execute(["1"]);
$total_jefe_hogar = $jefe_hogar->fetchColumn();


/* Información de gráfica Estatus Laboral
$estudiante = $conexion->prepare('SELECT COUNT(*) FROM habitante_estatus_laboral where estatus_laboral_id = ? AND estado =?');
$estudiante->execute(["1","1"]);
$total_estudiante = $estudiante->fetchColumn();

$trabajador = $conexion->prepare('SELECT COUNT(*) FROM habitante_estatus_laboral where estatus_laboral_id = ? AND estado =?');
$trabajador->execute(["2","1"]);
$total_trabajador = $trabajador->fetchColumn();

$pensionado = $conexion->prepare('SELECT COUNT(*) FROM habitante_estatus_laboral where estatus_laboral_id = ? AND estado =?');
$pensionado->execute(["3","1"]);
$total_pensionado = $pensionado->fetchColumn();
*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Totalizaciones</title>

    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">

    <!-- CSS DataTable -->
    <link rel="stylesheet" href="../src/plugins/datatables.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../src/css/all.min.css">

    <!-- CSS Estilo -->
    <link rel="stylesheet" href="../src/css/totalizacion.css">

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

        
        <div class="container mover-derecha col-10 bajar">
            

            <div class="cards d-flex flex-wrap justify-content-center container-fluid col-12">
            
                <div class="col-12 pt-3">
                   <h3 class="text-dark d-flex justify-content-center lead fs-2">Totalizaciones</h3>
                </div>

               <!-- Tarjetas de Totales -->
                <div class=" card col-xl-2 col-md-3 m-4 px-3 py-1 border-0 d-flex flex-row justify-content-between align-items-center card_manzana">
                   <div class="card-content">
                       <h6 class="number pl-1"><?php echo $total_manzana; ?></h6>
                       <h6 class="entidad pl-1 ">Manzanas</h6>
                   </div>
                   <div class="icon-box">
                       <img src="../src/img/tree-city-solid.svg" alt="Manzana">  
                   </div>
                </div>

                <div class=" card col-xl-2 col-md-3 m-4 px-3 py-1 border-0 d-flex flex-row justify-content-between align-items-center card_vivienda">
                   <div class="card-content">
                       <h6 class="number pl-1"><?php echo $total_vivienda; ?></h6>
                       <h6 class="entidad pl-1 ">Viviendas</h6>
                   </div>
                   <div class="icon-box">
                       <img src="../src/img/house-chimney-solid.svg" alt="vivienda" class="vivienda-img">  
                   </div>
                </div>

                <div class=" card col-xl-2 col-md-3 m-4 px-3 py-1 border-0 d-flex flex-row justify-content-between align-items-center card_habitante">
                   <div class="card-content">
                       <h6 class="number pl-1"><?php echo $total_habitante; ?></h6>
                       <h6 class="entidad pl-1 ">Habitantes</h6>
                   </div>
                   <div class="icon-box">
                       <img src="../src/img/person-solid.svg" alt="Manzana" class="habitante-img">  
                   </div>
                </div>

                <div class=" card col-xl-2 col-md-3 m-4 px-3 py-1 border-0 d-flex flex-row justify-content-between align-items-center card_jefe_hogar">
                   <div class="card-content">
                       <h6 class="number pl-1"><?php echo $total_jefe_hogar; ?></h6>
                       <h6 class="entidad pl-1 ">Jefes de Hogar</h6>
                   </div>
                   <div class="icon-box">
                       <img src="../src/img/house-user-solid.svg" alt="Manzana">  
                   </div>
                </div>
            </div>

            <!-- Graficas -->
            <div class="cards d-flex flex-wrap justify-content-center container-fluid col-12 mb-4">
                <div class=" card col-6 m-3 p-2 shadow-lg"><canvas id="myChart"></canvas></div>
                <div class=" card col-4 m-3 p-2 shadow-lg"><canvas id="myChartDon1"></canvas></div>
                <div class=" card col-4 m-3 p-2 shadow-lg"><canvas id="myChartIncapacidad"></canvas></div>
                <div class=" card col-11 m-3 p-2 shadow-lg"><canvas id="myChartBar1"></canvas></div>
            </div>


            



        


    </div>


        
    
  

  </div>     

    <!-- JS Bootstrap -->
    <script src="../src/js/bootstrap.bundle.min.js"></script>

    <!-------------- jquery ----------------->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <!-- JS DataTable -->
    <script src="../src/plugins/datatables.min.js"></script>

    <!-- JS Chart.js 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>-->
    <script src="../src/js/chart.js"></script>



<script>



/*-------------------------------------Gráfica de Barras--------------------------------------*/

const ctx = document.getElementById('myChart');

var myChart = new Chart(ctx, {

    type: 'bar',
    
    data: {
        labels: ['Niños', 'Adoles', 'Jóven', 'Adulto', 'Adulto Mayor'],
        datasets: [{
            // label: ,
            // data: [220, 400, 200, 150, 150, 150],
            backgroundColor:[
               'rgb(31, 200, 200)',
               'orange',
               'blueviolet',
               '#ff00ff',
               'rgb(245, 102, 150)'
            ]
        }]
      },

    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        },
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Clasificacion de Habitantes por edad'
            },
            legend: {
                display: false
            }
        }
    }
});

// ----- FETCH ------
let url_edad = "totalizaciones_edades_get.php"

fetch(url_edad)
.then(response => response.json())
.then(data_edad => mostrarEdades(data_edad))  
.catch(error_edad => console.log(error_edad)) 

const mostrarEdades = (edades) =>{
    edades.forEach(element_edad => {
        myChart.data['datasets'][0].data.push(element_edad.total);
        myChart.update();
    });
    
    //console.log(myChartDon1.data)
}

/*------------------------------ Grafica de Dona ------------------------------*/

var donut = document.getElementById('myChartDon1');
var myChartDon1 = new Chart(donut, {

    type: 'doughnut',

    data: {
        labels: ['Estudiantes', 'Trabajadores', 'Pensionados'],
        datasets: [{
            //label: 'Estatus Laboral',
            //data: [580, 254, 79],
            backgroundColor:[
               'rgb(31, 200, 200)',
               'blueviolet',
               '#ff00ff'
            ]
        }]
    },

    options: {
       /*scales: {
            y: {
                beginAtZero: true
            }
        },*/
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Clasificacion de Habitantes por Estatus Laboral'
            }
        }

    }
});


// ----- FETCH ------
let url = "totalizaciones_estatus_laboral_get.php"

fetch(url)
.then(response => response.json())
.then(data => mostrarData(data))  //datos => mostrar(datos)
.catch(error => console.log(error)) 

const mostrarData = (objects) =>{
    objects.forEach(element => {
        myChartDon1.data['datasets'][0].data.push(element.total);
        myChartDon1.update();
    });
    
    //console.log(myChartDon1.data)
}


/*------------------------------ Grafica de Dona: Incapacidad ------------------------------*/

var donut_incapacidad = document.getElementById('myChartIncapacidad');
var myChartIncapacidad = new Chart(donut_incapacidad, {

    type: 'doughnut',

    data: {
        labels: ['Embarazadas', 'Enfermos', 'Discapacitados'],
        datasets: [{
            //label: 'Estatus Laboral',
            //data: [580, 254, 79],
            backgroundColor:[
               'orange',
               'rgb(31, 200, 200)',
               'rgb(245, 102, 150)'
            ]
        }]
    },

    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Clasificacion de Habitantes por Incapacidad'
            }
        }

    }
});


// ----- FETCH ------
let url_incapacidad = "totalizaciones_incapacidad_get.php"

fetch(url_incapacidad)
.then(response => response.json())
.then(data_incapacidad => mostrarIncapacidad(data_incapacidad))  
.catch(error_incapacidad => console.log(error_incapacidad)) 

const mostrarIncapacidad = (incapacidad) =>{
    incapacidad.forEach(element_incapacidad => {
        myChartIncapacidad.data['datasets'][0].data.push(element_incapacidad.total);
        myChartIncapacidad.update();
    });
    
    //console.log(myChartDon1.data)
}




/*------------------------------------------------------------------------------------------------------------------------*/
/*----------------Grafica de Barra-----------------*/


const manzana = document.getElementById('myChartBar1');

new Chart(manzana, {

    type: 'bar',

    data: {
        labels: ['Manz 1', 'Manz 2', 'Manz 5', 'Manz 6', 'Manz 7', 'Manz 10', 'Manz 11', 'Manz 12', 'Manz 16', 'Manz 17', 'Manz 18', 'Manz 25', 'Manz 26', 'Manz 31', 'Manz 32', 'Manz 33'],
        datasets: [{
            data: [52, 77, 75, 110, 74, 62, 54, 71, 50, 99, 92, 73, 59, 85, 60, 127],
            backgroundColor:[
                'rgb(31, 200, 200)',
                'orange',
                'blueviolet',
                '#ff00ff',
                'rgb(245, 102, 150)'

            ]
        }]
    },

    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        },
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Clasificacion de Habitantes por Manzana'
            },
            legend: {
                display: false
            }
        }

    }
});

/*
let url = 'totalizaciones_datos_graficos.php';
fetch(url)
    .then( response => response.json() )
    .then( datos => mostrar(datos) )
    .catch( error => console.log(error) )

const mostrar = (habitante) =>{
    habitante.forEach(element => {
         myChart.data['datasets'][0].data.push(element.edad)
    });
    console.log(myChart.data);
}
*/

</script>

</body>
</html>