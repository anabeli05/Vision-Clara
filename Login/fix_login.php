<?php
require_once '../Base de Datos/conexion.php';

echo "<h2>🔧 Herramienta de Corrección de Login</h2>";

// 1. Limpiar intentos fallidos
$stmt_clear = $conexion->prepare("DELETE FROM login_attempts WHERE ip_address = '127.0.0.1'");
if($stmt_clear->execute()) {
    echo "<p style='background: #28a745; color: white; padding: 15px; border-radius: 5px;'>✓ Intentos fallidos limpiados para IP 127.0.0.1</p>";
} else {
    echo "<p style='background: #dc3545; color: white; padding: 15px; border-radius: 5px;'>✗ Error al limpiar intentos</p>";
}

// 2. Verificar usuarios
echo "<h3>Usuarios en la base de datos:</h3>";
$stmt = $conexion->query("SELECT Usuario_ID, Nombre, Correo, Contraseña, Rol, activo FROM usuarios");
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #007bff; color: white;'>
        <th>ID</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Contraseña</th>
        <th>Longitud</th>
        <th>Tipo</th>
        <th>Rol</th>
        <th>Activo</th>
      </tr>";

while($user = $stmt->fetch_assoc()) {
    $pass_type = 'Texto plano';
    if(substr($user['Contraseña'], 0, 4) === '$2y$' || substr($user['Contraseña'], 0, 4) === '$2a$') {
        $pass_type = 'Hash bcrypt';
    }
    
    $activo_color = $user['activo'] ? '#28a745' : '#dc3545';
    
    echo "<tr>";
    echo "<td>" . $user['Usuario_ID'] . "</td>";
    echo "<td>" . htmlspecialchars($user['Nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($user['Correo']) . "</td>";
    echo "<td><code>" . htmlspecialchars(substr($user['Contraseña'], 0, 20)) . "...</code></td>";
    echo "<td>" . strlen($user['Contraseña']) . "</td>";
    echo "<td><strong>" . $pass_type . "</strong></td>";
    echo "<td>" . $user['Rol'] . "</td>";
    echo "<td style='background: $activo_color; color: white;'>" . ($user['activo'] ? 'Sí' : 'No') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Verificar intentos recientes
echo "<h3>Intentos de login recientes:</h3>";
$stmt_attempts = $conexion->query("SELECT * FROM login_attempts ORDER BY attempted_at DESC LIMIT 10");
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #007bff; color: white;'>
        <th>Email</th>
        <th>IP</th>
        <th>Éxito</th>
        <th>Fecha/Hora</th>
      </tr>";

while($attempt = $stmt_attempts->fetch_assoc()) {
    $success_color = $attempt['success'] ? '#28a745' : '#dc3545';
    echo "<tr>";
    echo "<td>" . htmlspecialchars($attempt['email']) . "</td>";
    echo "<td>" . $attempt['ip_address'] . "</td>";
    echo "<td style='background: $success_color; color: white;'>" . ($attempt['success'] ? 'Sí' : 'No') . "</td>";
    echo "<td>" . $attempt['attempted_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p style='background: #17a2b8; color: white; padding: 15px; border-radius: 5px;'>";
echo "<strong>✓ Ahora puedes intentar iniciar sesión nuevamente:</strong><br>";
echo "<a href='inicioSecion.php' style='color: white; font-size: 18px; text-decoration: underline;'>Ir a Login</a>";
echo "</p>";

$conexion->close();
?>
