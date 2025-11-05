<?php
require_once '../Base de Datos/conexion.php';

echo "<h2>🧹 Limpiar Intentos Fallidos</h2>";

// Limpiar todos los intentos de 127.0.0.1
$stmt = $conexion->prepare("DELETE FROM login_attempts WHERE ip_address = '127.0.0.1'");

if($stmt->execute()) {
    $deleted = $stmt->affected_rows;
    echo "<p style='background: #28a745; color: white; padding: 20px; border-radius: 5px; font-size: 18px;'>";
    echo "<strong>✓ INTENTOS LIMPIADOS</strong><br><br>";
    echo "Se eliminaron <strong>$deleted</strong> intentos fallidos de tu IP.";
    echo "</p>";
} else {
    echo "<p style='background: #dc3545; color: white; padding: 15px; border-radius: 5px;'>";
    echo "❌ Error: " . $stmt->error;
    echo "</p>";
}

// Verificar estado actual
$stmt_check = $conexion->prepare("SELECT COUNT(*) as total FROM login_attempts WHERE ip_address = '127.0.0.1' AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)");
$stmt_check->execute();
$result = $stmt_check->get_result();
$count = $result->fetch_assoc()['total'];

echo "<p style='background: #17a2b8; color: white; padding: 15px; border-radius: 5px;'>";
echo "Intentos fallidos recientes: <strong>$count</strong>";
echo "</p>";

echo "<hr>";
echo "<h3>✓ Ahora puedes iniciar sesión</h3>";
echo "<p><strong>Email:</strong> xcobian@ucol.mx</p>";
echo "<p><strong>Contraseña:</strong> clara789</p>";
echo "<p><a href='inicioSecion.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 20px; display: inline-block; margin-top: 20px;'>IR A LOGIN</a></p>";

$conexion->close();
?>
