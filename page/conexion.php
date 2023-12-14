<?php

//--------CONEXION PDO----------//

try{
    $conexion = new PDO('mysql:host=localhost; dbname=comunidad', 'root', '');
} catch(Excepcion $e){
    echo "A ocurrido un error".$e->getMessage();
}
?>