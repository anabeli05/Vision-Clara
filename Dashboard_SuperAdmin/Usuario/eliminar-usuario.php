<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que sea Super Admin
if ($user_rol !== 'Super Admin') {
    header('Location: ../../Login/inicioSecion.php');
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Verificar que se recibió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID de usuario no válido';
    header('Location: Gestion-Usuarios.php');
    exit;
}

// Validar CSRF token
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = 'Token de seguridad inválido';
    header('Location: Gestion-Usuarios.php');
    exit;
}

$usuario_id = intval($_GET['id']);

try {
    // Verificar que el usuario existe y no es Super Admin
    $stmt = $conn->prepare("SELECT Usuario_ID, Nombre, Rol FROM usuarios WHERE Usuario_ID = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $_SESSION['error_message'] = 'Usuario no encontrado';
        header('Location: Gestion-Usuarios.php');
        exit;
    }
    
    // No permitir eliminar Super Admins
    if ($usuario['Rol'] === 'Super Admin') {
        $_SESSION['error_message'] = 'No se puede eliminar un Super Admin';
        header('Location: Gestion-Usuarios.php');
        exit;
    }
    
    // Eliminar el usuario
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE Usuario_ID = ?");
    $stmt->execute([$usuario_id]);
    
    $_SESSION['success_message'] = 'Usuario "' . htmlspecialchars($usuario['Nombre']) . '" eliminado exitosamente';
    
} catch(PDOException $e) {
    $_SESSION['error_message'] = 'Error al eliminar el usuario: ' . $e->getMessage();
}

// Redirigir de vuelta a la lista
header('Location: Gestion-Usuarios.php');
exit;
?>
