<?php
// Script web/CLI para comprobar la conexión a la base de datos y dar mensajes JSON amigables.
// Seguridad: por defecto permite ejecución CLI o peticiones desde localhost. Si lo pones en un servidor público, protéjelo.

// Permitir ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    // Permitir solo desde localhost en entorno web
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($remote !== '127.0.0.1' && $remote !== '::1') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Acceso denegado. Solo localhost.']);
        exit;
    }
}

require_once __DIR__ . '/../Base de Datos/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$result = [
    'success' => false,
    'checks' => [],
];

// 1) Intentar usar la clase Conexion
try {
    $c = new Conexion();
    try {
        $c->abrir_conexion();
        $result['checks'][] = ['name' => 'conexion_clase', 'ok' => true, 'message' => 'Conexión mysqli via Conexion::abrir_conexion() OK'];
        $c->cerrar_conexion();
    } catch (Exception $e) {
        $result['checks'][] = ['name' => 'conexion_clase', 'ok' => false, 'message' => $e->getMessage()];
    }
} catch (Exception $e) {
    $result['checks'][] = ['name' => 'crear_instancia_clase', 'ok' => false, 'message' => $e->getMessage()];
}

// 2) Comprobar variable mysqli global (si existe)
if (isset($conexion) && $conexion instanceof mysqli) {
    if ($conexion->connect_error) {
        $result['checks'][] = ['name' => 'mysqli_global', 'ok' => false, 'message' => $conexion->connect_error];
    } else {
        $r = $conexion->query('SELECT 1');
        if ($r) {
            $result['checks'][] = ['name' => 'mysqli_global', 'ok' => true, 'message' => 'OK'];
        } else {
            $result['checks'][] = ['name' => 'mysqli_global', 'ok' => false, 'message' => $conexion->error];
        }
    }
} else {
    $result['checks'][] = ['name' => 'mysqli_global', 'ok' => false, 'message' => 'Variable $conexion no definida'];
}

// 3) Comprobar PDO global (si existe)
if (isset($conn) && $conn instanceof PDO) {
    try {
        $stmt = $conn->query('SELECT 1');
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            $result['checks'][] = ['name' => 'pdo_global', 'ok' => true, 'message' => 'OK'];
        } else {
            $result['checks'][] = ['name' => 'pdo_global', 'ok' => false, 'message' => 'La consulta no devolvió resultados'];
        }
    } catch (Exception $e) {
        $result['checks'][] = ['name' => 'pdo_global', 'ok' => false, 'message' => $e->getMessage()];
    }
} else {
    $result['checks'][] = ['name' => 'pdo_global', 'ok' => false, 'message' => 'Variable $conn no definida'];
}

// 4) Informar configuración detectada (sin exponer password)
$host = getenv('DB_HOST') ?: (isset($host) ? $host : null);
$port = getenv('DB_PORT') ?: (isset($port) ? $port : null);
$user = getenv('DB_USER') ?: (isset($usuario) ? $usuario : null);
$dbname = getenv('DB_NAME') ?: (isset($base) ? $base : null);

$result['config'] = [
    'host' => $host,
    'port' => $port,
    'user' => $user,
    'database' => $dbname
];

// Resultado final
$anyOk = false;
foreach ($result['checks'] as $c) {
    if (!empty($c['ok'])) { $anyOk = true; break; }
}

$result['success'] = $anyOk;

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>