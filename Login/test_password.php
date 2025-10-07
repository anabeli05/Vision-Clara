<?php
require_once '../Base de Datos/conexion.php';

echo "<h2>🔍 Test de Contraseña Detallado</h2>";

$email = 'xcobian@ucol.mx';
$password_test = 'clara789';

// Consultar usuario
$stmt = $conexion->prepare("SELECT Usuario_ID, Nombre, Correo, Contraseña, Rol, activo FROM usuarios WHERE Correo = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();
    
    echo "<h3>Usuario encontrado:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>ID</td><td>" . $usuario['Usuario_ID'] . "</td></tr>";
    echo "<tr><td>Nombre</td><td>" . htmlspecialchars($usuario['Nombre']) . "</td></tr>";
    echo "<tr><td>Email</td><td>" . htmlspecialchars($usuario['Correo']) . "</td></tr>";
    echo "<tr><td>Rol</td><td>" . $usuario['Rol'] . "</td></tr>";
    echo "<tr><td>Activo</td><td style='background: " . ($usuario['activo'] ? '#28a745' : '#dc3545') . "; color: white;'>" . ($usuario['activo'] ? 'SÍ' : 'NO') . "</td></tr>";
    echo "</table>";
    
    echo "<h3>Análisis de Contraseña:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    
    // Contraseña en BD
    $pass_bd = $usuario['Contraseña'];
    echo "<tr><td><strong>Contraseña en BD (raw):</strong></td><td><code>" . htmlspecialchars($pass_bd) . "</code></td></tr>";
    echo "<tr><td><strong>Longitud:</strong></td><td>" . strlen($pass_bd) . " caracteres</td></tr>";
    echo "<tr><td><strong>Bytes (hex):</strong></td><td><code>" . bin2hex($pass_bd) . "</code></td></tr>";
    
    // Contraseña a probar
    echo "<tr><td><strong>Contraseña a probar:</strong></td><td><code>" . htmlspecialchars($password_test) . "</code></td></tr>";
    echo "<tr><td><strong>Longitud:</strong></td><td>" . strlen($password_test) . " caracteres</td></tr>";
    echo "<tr><td><strong>Bytes (hex):</strong></td><td><code>" . bin2hex($password_test) . "</code></td></tr>";
    
    // Comparaciones
    echo "<tr style='background: #f8f9fa;'><td colspan='2'><strong>PRUEBAS DE COMPARACIÓN:</strong></td></tr>";
    
    // 1. Comparación directa ===
    $match_strict = ($password_test === $pass_bd);
    echo "<tr><td><strong>Comparación estricta (===):</strong></td><td style='background: " . ($match_strict ? '#28a745' : '#dc3545') . "; color: white; font-weight: bold;'>" . ($match_strict ? '✓ COINCIDE' : '✗ NO COINCIDE') . "</td></tr>";
    
    // 2. Comparación ==
    $match_loose = ($password_test == $pass_bd);
    echo "<tr><td><strong>Comparación flexible (==):</strong></td><td style='background: " . ($match_loose ? '#28a745' : '#dc3545') . "; color: white; font-weight: bold;'>" . ($match_loose ? '✓ COINCIDE' : '✗ NO COINCIDE') . "</td></tr>";
    
    // 3. strcmp
    $strcmp_result = strcmp($password_test, $pass_bd);
    echo "<tr><td><strong>strcmp():</strong></td><td style='background: " . ($strcmp_result === 0 ? '#28a745' : '#dc3545') . "; color: white; font-weight: bold;'>" . ($strcmp_result === 0 ? '✓ COINCIDE (0)' : '✗ NO COINCIDE (' . $strcmp_result . ')') . "</td></tr>";
    
    // 4. password_verify
    $match_verify = password_verify($password_test, $pass_bd);
    echo "<tr><td><strong>password_verify():</strong></td><td style='background: " . ($match_verify ? '#28a745' : '#dc3545') . "; color: white; font-weight: bold;'>" . ($match_verify ? '✓ COINCIDE' : '✗ NO COINCIDE') . "</td></tr>";
    
    // 5. Detectar tipo de hash
    $is_bcrypt = (substr($pass_bd, 0, 4) === '$2y$' || substr($pass_bd, 0, 4) === '$2a$');
    echo "<tr><td><strong>¿Es hash bcrypt?:</strong></td><td>" . ($is_bcrypt ? 'SÍ' : 'NO') . "</td></tr>";
    
    // 6. Espacios ocultos
    $pass_bd_trimmed = trim($pass_bd);
    $has_spaces = ($pass_bd !== $pass_bd_trimmed);
    echo "<tr><td><strong>¿Tiene espacios al inicio/fin?:</strong></td><td style='background: " . ($has_spaces ? '#ffc107' : '#28a745') . "; color: " . ($has_spaces ? 'black' : 'white') . ";'>" . ($has_spaces ? 'SÍ - PROBLEMA DETECTADO' : 'NO') . "</td></tr>";
    
    if($has_spaces) {
        echo "<tr><td><strong>Contraseña sin espacios:</strong></td><td><code>" . htmlspecialchars($pass_bd_trimmed) . "</code></td></tr>";
        $match_trimmed = ($password_test === $pass_bd_trimmed);
        echo "<tr><td><strong>Comparación con trim():</strong></td><td style='background: " . ($match_trimmed ? '#28a745' : '#dc3545') . "; color: white; font-weight: bold;'>" . ($match_trimmed ? '✓ COINCIDE' : '✗ NO COINCIDE') . "</td></tr>";
    }
    
    echo "</table>";
    
    // Solución
    if(!$match_strict && !$match_verify) {
        echo "<hr>";
        echo "<h3 style='color: #dc3545;'>⚠️ PROBLEMA DETECTADO</h3>";
        echo "<p>La contraseña 'clara789' NO coincide con la almacenada en la base de datos.</p>";
        
        echo "<h4>Soluciones:</h4>";
        echo "<ol>";
        echo "<li><strong>Actualizar contraseña a 'clara789':</strong> <a href='?fix=plaintext&id=" . $usuario['Usuario_ID'] . "' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>Actualizar (texto plano)</a></li>";
        echo "<li><strong>Actualizar contraseña a 'clara789' con hash bcrypt (RECOMENDADO):</strong> <a href='?fix=bcrypt&id=" . $usuario['Usuario_ID'] . "' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>Actualizar (hash seguro)</a></li>";
        echo "</ol>";
    } else {
        echo "<hr>";
        echo "<p style='background: #28a745; color: white; padding: 15px; border-radius: 5px;'><strong>✓ La contraseña 'clara789' es VÁLIDA. Deberías poder iniciar sesión.</strong></p>";
    }
    
} else {
    echo "<p style='background: #dc3545; color: white; padding: 15px; border-radius: 5px;'><strong>❌ Usuario no encontrado.</strong></p>";
}

// Procesar actualización
if(isset($_GET['fix']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $new_password = 'clara789';
    
    if($_GET['fix'] === 'plaintext') {
        $stmt_update = $conexion->prepare("UPDATE usuarios SET Contraseña = ? WHERE Usuario_ID = ?");
        $stmt_update->bind_param("si", $new_password, $user_id);
    } else if($_GET['fix'] === 'bcrypt') {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt_update = $conexion->prepare("UPDATE usuarios SET Contraseña = ? WHERE Usuario_ID = ?");
        $stmt_update->bind_param("si", $hashed, $user_id);
    }
    
    if(isset($stmt_update) && $stmt_update->execute()) {
        echo "<p style='background: #28a745; color: white; padding: 15px; border-radius: 5px; margin-top: 20px;'><strong>✓ Contraseña actualizada exitosamente.</strong></p>";
        echo "<p><a href='test_password.php'>Volver a verificar</a> | <a href='inicioSecion.php'>Ir a Login</a></p>";
    }
}

// Limpiar intentos
echo "<hr>";
echo "<h3>Limpiar intentos fallidos:</h3>";
$stmt_count = $conexion->prepare("SELECT COUNT(*) as total FROM login_attempts WHERE ip_address = '127.0.0.1' AND success = 0");
$stmt_count->execute();
$count = $stmt_count->get_result()->fetch_assoc()['total'];
echo "<p>Intentos fallidos desde 127.0.0.1: <strong>" . $count . "</strong></p>";
echo "<a href='?clear=1' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Limpiar intentos fallidos</a>";

if(isset($_GET['clear'])) {
    $stmt_clear = $conexion->prepare("DELETE FROM login_attempts WHERE ip_address = '127.0.0.1'");
    if($stmt_clear->execute()) {
        echo "<p style='background: #28a745; color: white; padding: 15px; border-radius: 5px; margin-top: 10px;'><strong>✓ Intentos limpiados.</strong></p>";
        echo "<p><a href='test_password.php'>Recargar</a></p>";
    }
}

$conexion->close();
?>
