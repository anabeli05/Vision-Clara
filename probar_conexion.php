<?php
include("conexion.php");

$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();

if ($conexion_obj->conexion) {
    echo "<h2 style='color:green;'>✅ Conectado correctamente a la base de datos en Railway.</h2>";
} else {
    echo "<h2 style='color:red;'>❌ No se pudo conectar a la base de datos.</h2>";
}
?>
