<?php
session_start(); //reanuda la sesion!
include 'conexion.php';


/* 4. Registrar su fecha de salida */
date_default_timezone_set('America/Caracas');
setlocale(LC_TIME, 'spanish');
$ultima_conexion = date('Y-m-d g:i:s a');
$actualizar_usuario = $conexion->prepare('UPDATE usuario SET salida = ?, posicion = ? WHERE nombre_usuario=?');
$actualizar_usuario->execute([$ultima_conexion,"desconectado",$_SESSION['user']]);


session_unset();  //limpiar variables de sesion
session_destroy();  //destruir la sesion

header('Location: principal.html');

?> 