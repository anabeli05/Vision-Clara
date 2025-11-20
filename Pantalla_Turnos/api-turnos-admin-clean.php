<?php
/**
 * api-turnos-admin-clean.php
 * Admin API for managing turnos with proper validation and error handling
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$logFile = __DIR__ . '/api-admin-debug.log';

// Log request
file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] METHOD=" . $_SERVER['REQUEST_METHOD'] . " POST=" . json_encode($_POST) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../Base de Datos/conexion.php';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    // HANDLE POST/PUT REQUESTS
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
        $action = $_POST['action'] ?? null;
        $numero_turno = $_POST['numero_turno'] ?? null;
        $nuevo_estado = $_POST['nuevo_estado'] ?? null;

        file_put_contents($logFile, "ACTION={$action}, NUMERO={$numero_turno}, ESTADO={$nuevo_estado}\n", FILE_APPEND);

        if (!$action) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing action']);
            exit;
        }

        $allowed_states = ['Espera', 'Atendiendo', 'Finalizado', 'Cancelado'];

        // cambiar_estado: update a turn to a new state
        if ($action === 'cambiar_estado') {
            if (!$numero_turno || !$nuevo_estado) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing numero_turno or nuevo_estado']);
                exit;
            }
            if (!in_array($nuevo_estado, $allowed_states, true)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid state: ' . $nuevo_estado]);
                exit;
            }

            $stmt = $mysqli->prepare('UPDATE turnos SET Estado = ? WHERE Numero_Turno = ?');
            if (!$stmt) {
                file_put_contents($logFile, "PREPARE ERROR: " . $mysqli->error . "\n", FILE_APPEND);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Database error']);
                exit;
            }
            $stmt->bind_param('ss', $nuevo_estado, $numero_turno);
            if (!$stmt->execute()) {
                file_put_contents($logFile, "EXECUTE ERROR: " . $stmt->error . "\n", FILE_APPEND);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
                $stmt->close();
                exit;
            }

            if ($stmt->affected_rows > 0) {
                file_put_contents($logFile, "SUCCESS: Updated {$numero_turno} to {$nuevo_estado}\n", FILE_APPEND);
                echo json_encode(['success' => true, 'message' => 'Turno actualizado', 'numero_turno' => $numero_turno]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Turno not found']);
            }
            $stmt->close();
            $conexion->cerrar_conexion();
            exit;
        }

        // llamar: get next 'Espera' and move to 'Atendiendo'
        if ($action === 'llamar') {
            $res = $mysqli->query("SELECT Numero_Turno FROM turnos WHERE Estado = 'Espera' ORDER BY Fecha ASC LIMIT 1");
            if ($res && $row = $res->fetch_assoc()) {
                $proximo = $row['Numero_Turno'];
                $stmt = $mysqli->prepare("UPDATE turnos SET Estado = 'Atendiendo' WHERE Numero_Turno = ?");
                if (!$stmt) {
                    file_put_contents($logFile, "PREPARE ERROR (llamar): " . $mysqli->error . "\n", FILE_APPEND);
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Database error']);
                    $conexion->cerrar_conexion();
                    exit;
                }
                $stmt->bind_param('s', $proximo);
                if ($stmt->execute()) {
                    file_put_contents($logFile, "SUCCESS: Called {$proximo}\n", FILE_APPEND);
                    echo json_encode(['success' => true, 'message' => 'Turno called', 'turno_llamado' => $proximo]);
                } else {
                    file_put_contents($logFile, "EXECUTE ERROR (llamar): " . $stmt->error . "\n", FILE_APPEND);
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Update failed']);
                }
                $stmt->close();
                $conexion->cerrar_conexion();
                exit;
            }
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No turnos en espera']);
            $conexion->cerrar_conexion();
            exit;
        }

        // finalizar: mark turno as Finalizado
        if ($action === 'finalizar') {
            if (!$numero_turno) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing numero_turno']);
                exit;
            }
            $stmt = $mysqli->prepare("UPDATE turnos SET Estado = 'Finalizado' WHERE Numero_Turno = ?");
            if (!$stmt) {
                file_put_contents($logFile, "PREPARE ERROR (finalizar): " . $mysqli->error . "\n", FILE_APPEND);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Database error']);
                exit;
            }
            $stmt->bind_param('s', $numero_turno);
            if (!$stmt->execute()) {
                file_put_contents($logFile, "EXECUTE ERROR (finalizar): " . $stmt->error . "\n", FILE_APPEND);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Update failed']);
                $stmt->close();
                exit;
            }
            if ($stmt->affected_rows > 0) {
                file_put_contents($logFile, "SUCCESS: Finalized {$numero_turno}\n", FILE_APPEND);
                echo json_encode(['success' => true, 'message' => 'Turno finalizado', 'numero_turno' => $numero_turno]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Turno not found']);
            }
            $stmt->close();
            $conexion->cerrar_conexion();
            exit;
        }

        // cancelar: mark turno as Cancelado
        if ($action === 'cancelar') {
            if (!$numero_turno) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing numero_turno']);
                exit;
            }
            $stmt = $mysqli->prepare("UPDATE turnos SET Estado = 'Cancelado' WHERE Numero_Turno = ?");
            if (!$stmt) {
                file_put_contents($logFile, "PREPARE ERROR (cancelar): " . $mysqli->error . "\n", FILE_APPEND);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Database error']);
                exit;
            }
            $stmt->bind_param('s', $numero_turno);
            if (!$stmt->execute()) {
                file_put_contents($logFile, "EXECUTE ERROR (cancelar): " . $stmt->error . "\n", FILE_APPEND);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Update failed']);
                $stmt->close();
                exit;
            }
            if ($stmt->affected_rows > 0) {
                file_put_contents($logFile, "SUCCESS: Cancelled {$numero_turno}\n", FILE_APPEND);
                echo json_encode(['success' => true, 'message' => 'Turno cancelado', 'numero_turno' => $numero_turno]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Turno not found']);
            }
            $stmt->close();
            $conexion->cerrar_conexion();
            exit;
        }

        // unknown action
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        $conexion->cerrar_conexion();
        exit;
    }

    // HANDLE GET REQUESTS
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql_espera = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado FROM turnos WHERE Estado = 'Espera' ORDER BY Fecha ASC LIMIT 100";
        $sql_atendiendo = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado FROM turnos WHERE Estado = 'Atendiendo' ORDER BY Fecha ASC";
        $sql_stats = "SELECT 
            COUNT(CASE WHEN Estado = 'Espera' THEN 1 END) as turnos_espera,
            COUNT(CASE WHEN Estado = 'Atendiendo' THEN 1 END) as turnos_atendiendo,
            COUNT(CASE WHEN Estado = 'Finalizado' THEN 1 END) as turnos_finalizados,
            COUNT(CASE WHEN Estado = 'Cancelado' THEN 1 END) as turnos_cancelados
        FROM turnos WHERE DATE(Fecha) = CURDATE()";

        $res_espera = $mysqli->query($sql_espera);
        $res_atendiendo = $mysqli->query($sql_atendiendo);
        $res_stats = $mysqli->query($sql_stats);

        $turnos_espera = [];
        if ($res_espera && $res_espera->num_rows > 0) {
            while ($row = $res_espera->fetch_assoc()) {
                $turnos_espera[] = $row;
            }
        }

        $turnos_atendiendo = [];
        if ($res_atendiendo && $res_atendiendo->num_rows > 0) {
            while ($row = $res_atendiendo->fetch_assoc()) {
                $turnos_atendiendo[] = $row;
            }
        }

        $stats = ['turnos_espera' => 0, 'turnos_atendiendo' => 0, 'turnos_finalizados' => 0, 'turnos_cancelados' => 0];
        if ($res_stats && $res_stats->num_rows > 0) {
            $stats = $res_stats->fetch_assoc();
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'turnos_espera' => $turnos_espera,
                'turnos_atendiendo' => $turnos_atendiendo,
                'estadisticas' => $stats
            ],
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE);
        $conexion->cerrar_conexion();
        exit;
    }

    $conexion->cerrar_conexion();
} catch (Exception $e) {
    file_put_contents($logFile, "EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
