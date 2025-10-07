<?php
require_once '../Base de Datos/conexion.php';

echo "<h2>🧪 Test de consulta productos</h2>";

// Probar diferentes variaciones de la consulta
$queries = [
    "SELECT ID_Producto, Nombre, Descripcion, Precio, Stock, Imagen_URL FROM productos WHERE Activo = 1",
    "SELECT ID_Producto, Nombre, Descripcion, Precio, Stock, Imagen_URL FROM productos",
    "SELECT * FROM productos LIMIT 1"
];

foreach($queries as $index => $query) {
    echo "<h3>Query " . ($index + 1) . ":</h3>";
    echo "<code>" . htmlspecialchars($query) . "</code><br><br>";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p style='background: #28a745; color: white; padding: 10px;'>✓ ÉXITO - " . count($result) . " productos encontrados</p>";
        
        if(count($result) > 0) {
            echo "<pre>";
            print_r($result[0]); // Mostrar primer producto
            echo "</pre>";
        }
        
    } catch(PDOException $e) {
        echo "<p style='background: #dc3545; color: white; padding: 10px;'>✗ ERROR: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

$conn = null;
?>
