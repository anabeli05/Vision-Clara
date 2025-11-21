<?php
// Protección de sesión
require_once '../../Login/check_session.php';

// Verificar que sea Super Admin
if ($user_rol !== 'Super Admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Configurar header para JSON
header('Content-Type: application/json');

try {
    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id'])) {
        throw new Exception('ID de producto no proporcionado');
    }
    
    $productId = intval($data['product_id']);
    
    // Verificar que el producto existe
    $stmt = $conn->prepare("SELECT ID_Producto FROM productos WHERE ID_Producto = ?");
    $stmt->execute([$productId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Producto no encontrado');
    }
    
    // Soft delete - marcar como inactivo
    $stmt = $conn->prepare("UPDATE productos SET Activo = 0 WHERE ID_Producto = ?");
    $stmt->execute([$productId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto eliminado correctamente'
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>