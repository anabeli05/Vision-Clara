<?php
include("conexion.php");

$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();
$conexion = $conexion_obj->conexion;

if ($conexion) {
    echo "<h3 style='color:green;'>✅ Conectado correctamente a la base de datos Railway.</h3>";

    $query = "SELECT * FROM turnos ORDER BY id DESC LIMIT 10";
    $resultado = $conexion->query($query);

    if ($resultado && $resultado->num_rows > 0) {
        echo "<h4>📋 Ejemplo de registros:</h4>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Tipo</th><th>Número</th><th>Fecha</th><th>Atendido</th></tr>";

        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$fila['id']}</td>";
            echo "<td>{$fila['tipo']}</td>";
            echo "<td>{$fila['numero']}</td>";
            echo "<td>{$fila['fecha']}</td>";
            echo "<td>" . ($fila['atendido'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<h4 style='color:orange;'>⚠ No hay registros en la tabla 'turnos'.</h4>";
    }
} else {
    echo "<h3 style='color:red;'>❌ Error al conectar con la base de datos.</h3>";
}
?>
