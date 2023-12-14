<?php

function valida_campo($campo){
    $campo = trim($campo);                          // eliminar espacios en blanco del inicio y final de la cadena
    $campo = stripcslashes($campo);                 // quita la barra diagonal invertida
    $campo = htmlspecialchars($campo);              // escapar o convertir caracteres especiales que tienen un significado por si solos en HTML
 
    $campo = str_ireplace("<script>","",$campo);    // reemplazar un texto especificado
    $campo = str_ireplace("</script>","",$campo);
    $campo = str_ireplace("<script src","",$campo);
    $campo = str_ireplace("<script type=","",$campo);
    $campo = str_ireplace("SELECT * FROM","",$campo);
    $campo = str_ireplace("DELETE FROM","",$campo);
    $campo = str_ireplace("INSERT INTO","",$campo);
    $campo = str_ireplace("DROP TABLE","",$campo);
    $campo = str_ireplace("DROP DATABASE","",$campo);
    $campo = str_ireplace("TRUNCATE TABLE","",$campo);
    $campo = str_ireplace("SHOW TABLES;","",$campo);
    $campo = str_ireplace("SHOW DATABASES;","",$campo);
    $campo = str_ireplace("<?php","",$campo);
    $campo = str_ireplace("?>","",$campo);
    
    return $campo;
}
?>