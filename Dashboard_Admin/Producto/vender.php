<?php
require_once '../../Login/check_session.php';
require_once '../../Base de Datos/conexion.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = intval($data['product_id']);

$stmt = $conexion->prepare("UPDATE productos SET Stock = Stock - 1 WHERE ID_Producto = ? AND Stock > 0");
$stmt->bind_param("i", $product_id);
$stmt->execute();

echo json_encode(['success' => $stmt->affected_rows > 0]);

$stmt->close();
?>