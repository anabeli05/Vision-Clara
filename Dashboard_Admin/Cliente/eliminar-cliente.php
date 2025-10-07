<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que NO sea Super Admin
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/inicio/InicioSA.php');
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Verificar que se recibió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Número de afiliado no válido';
    header('Location: Gestion-Cliente.php');
    exit;
}

// Validar CSRF token
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = 'Token de seguridad inválido';
    header('Location: Gestion-Cliente.php');
    exit;
}

$no_afiliado = $_GET['id'];

try {
    // Verificar que el cliente existe
    $stmt = $conn->prepare("SELECT No_Afiliado, Nombre FROM clientes WHERE No_Afiliado = ?");
    $stmt->execute([$no_afiliado]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        $_SESSION['error_message'] = 'Cliente no encontrado';
        header('Location: Gestion-Cliente.php');
        exit;
    }
    
    // Eliminar el cliente
    $stmt = $conn->prepare("DELETE FROM clientes WHERE No_Afiliado = ?");
    $stmt->execute([$no_afiliado]);
    
    $_SESSION['success_message'] = 'Cliente "' . htmlspecialchars($cliente['Nombre']) . '" eliminado exitosamente';
    
} catch(PDOException $e) {
    $_SESSION['error_message'] = 'Error al eliminar el cliente: ' . $e->getMessage();
}

// Redirigir de vuelta a la lista
header('Location: Gestion-Cliente.php');
exit;
?>
