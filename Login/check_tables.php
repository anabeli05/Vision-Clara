<?php
require_once '../Base de Datos/conexion.php';

echo "<h2>📋 Tablas en la Base de Datos</h2>";

try {
    // Obtener todas las tablas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tablas encontradas (" . count($tables) . "):</h3>";
    echo "<ul style='font-family: monospace; font-size: 16px;'>";
    foreach($tables as $table) {
        echo "<li><strong>$table</strong></li>";
    }
    echo "</ul>";
    
    // Mostrar estructura de cada tabla
    echo "<hr>";
    echo "<h3>Estructura de las tablas:</h3>";
    
    foreach($tables as $table) {
        echo "<h4 style='background: #007bff; color: white; padding: 10px;'>Tabla: $table</h4>";
        
        $stmt = $conn->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin-bottom: 20px; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
        echo "</tr>";
        
        foreach($columns as $col) {
            echo "<tr>";
            echo "<td><strong>" . $col['Field'] . "</strong></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch(PDOException $e) {
    echo "<p style='background: #dc3545; color: white; padding: 15px; border-radius: 5px;'>";
    echo "Error: " . $e->getMessage();
    echo "</p>";
}

$conn = null;
?>
