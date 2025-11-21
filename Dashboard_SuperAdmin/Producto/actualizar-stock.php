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
    
    if (!isset($data['product_id']) || !isset($data['action']) || !isset($data['amount'])) {
        throw new Exception('Datos incompletos');
    }
    
    $productId = intval($data['product_id']);
    $action = $data['action'];
    $amount = intval($data['amount']);
    
    if ($amount <= 0) {
        throw new Exception('La cantidad debe ser mayor a 0');
    }
    
    // Obtener stock actual
    $stmt = $conn->prepare("SELECT Stock FROM productos WHERE ID_Producto = ? AND Activo = 1");
    $stmt->execute([$productId]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    
    $stockActual = intval($producto['Stock']);
    $nuevoStock = $stockActual;
    
    // Calcular nuevo stock según la acción
    switch ($action) {
        case 'add':
            $nuevoStock = $stockActual + $amount;
            break;
        case 'subtract':
            $nuevoStock = $stockActual - $amount;
            if ($nuevoStock < 0) {
                throw new Exception('El stock no puede ser negativo');
            }
            break;
        case 'set':
            $nuevoStock = $amount;
            break;
        default:
            throw new Exception('Acción no válida');
    }
    
    // Actualizar stock
    $stmt = $conn->prepare("UPDATE productos SET Stock = ? WHERE ID_Producto = ?");
    $stmt->execute([$nuevoStock, $productId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock actualizado correctamente',
        'new_stock' => $nuevoStock
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>