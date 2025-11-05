<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar solicitudes OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../Base de Datos/conexion.php';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    // Si es POST, crear turno (Cliente o Visitante)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;
        if ($tipo === 'Visitante') {
            // Generar siguiente número de turno Visitante: N-XXX
            $sql = "SELECT Numero_Turno FROM turnos WHERE Tipo='Visitante' ORDER BY Fecha DESC, Numero_Turno DESC LIMIT 1";
            $res = $mysqli->query($sql);
            $next = 1;
            if ($res && $row = $res->fetch_assoc()) {
                if (preg_match('/N-(\d{3})/', $row['Numero_Turno'], $m)) {
                    $next = intval($m[1]) + 1;
                }
            }
            // Formato ajustado a 4 caracteres para la columna char(4): 'N001'
            $turno_num = 'N' . str_pad($next, 3, '0', STR_PAD_LEFT);

            $stmt = $mysqli->prepare("INSERT INTO turnos (Numero_Turno, Tipo, Estado, Fecha) VALUES (?, 'Visitante', 'Espera', NOW())");
            if (!$stmt) {
                $err = $mysqli->error;
                error_log("Prepare failed (visitante): " . $err);
                echo json_encode(['success' => false, 'error' => 'Error interno (prepare): ' . $err]);
                $conexion->cerrar_conexion();
                exit;
            }
            $stmt->bind_param('s', $turno_num);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'turno' => $turno_num]);
            } else {
                $err = $stmt->error ?: $mysqli->error;
                error_log("Execute failed (visitante): " . $err);
                echo json_encode(['success' => false, 'error' => 'Error interno (execute): ' . $err]);
            }
            $stmt->close();
            $conexion->cerrar_conexion();
            exit;
        } elseif (isset($_POST['afiliado'])) {
            $afiliado = preg_replace('/\D/', '', $_POST['afiliado']);
            if (strlen($afiliado) !== 6) {
                echo json_encode(['success' => false, 'error' => 'Número de afiliado inválido']);
                exit;
            }

            // Generar siguiente número de turno tipo Cliente: C-XXX
            $sql = "SELECT Numero_Turno FROM turnos WHERE Tipo='Cliente' ORDER BY Fecha DESC, Numero_Turno DESC LIMIT 1";
            $res = $mysqli->query($sql);
            $next = 1;
            if ($res && $row = $res->fetch_assoc()) {
                if (preg_match('/C-(\d{3})/', $row['Numero_Turno'], $m)) {
                    $next = intval($m[1]) + 1;
                }
            }
            // Formato ajustado a 4 caracteres para la columna char(4): 'C001'
            $turno_num = 'C' . str_pad($next, 3, '0', STR_PAD_LEFT);

            $stmt = $mysqli->prepare("INSERT INTO turnos (Numero_Turno, Tipo, Estado, Fecha, No_Afiliado) VALUES (?, 'Cliente', 'Espera', NOW(), ?)");
            if (!$stmt) {
                $err = $mysqli->error;
                error_log("Prepare failed (cliente): " . $err);
                echo json_encode(['success' => false, 'error' => 'Error interno (prepare): ' . $err]);
                $conexion->cerrar_conexion();
                exit;
            }
            $stmt->bind_param('ss', $turno_num, $afiliado);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'turno' => $turno_num]);
            } else {
                $err = $stmt->error ?: $mysqli->error;
                error_log("Execute failed (cliente): " . $err);
                echo json_encode(['success' => false, 'error' => 'Error interno (execute): ' . $err]);
            }
            $stmt->close();
            $conexion->cerrar_conexion();
            exit;
        }
    }

    // --- RESPUESTA NORMAL (GET) ---
    // Funciones de ejemplo (puedes reemplazar por consultas reales si lo deseas)
    function obtenerTurnosEnEspera($mysqli) {
        $turnos = [];
        $sql = "SELECT Numero_Turno as numero, Tipo as tipo FROM turnos WHERE Estado = 'Espera' ORDER BY Fecha ASC LIMIT 100";
        $res = $mysqli->query($sql);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $turnos[] = [
                    'numero' => $row['numero'],
                    'tipo' => $row['tipo']
                ];
            }
        } else {
            // Fallback
            $turnos = [
                ['numero' => 'C-800', 'tipo' => 'Cliente'],
                ['numero' => 'N-564', 'tipo' => 'Visitante'],
                ['numero' => 'C-959', 'tipo' => 'Cliente'],
                ['numero' => 'N-645', 'tipo' => 'Visitante'],
                ['numero' => 'C-123', 'tipo' => 'Cliente'],
                ['numero' => 'N-456', 'tipo' => 'Visitante']
            ];
        }
        return $turnos;
    }
    function obtenerVentanillas($mysqli) {
        // Ficticio, igual que antes
        return [
            ['numero' => 1, 'estado' => 'libre', 'turno_actual' => 'C-897'],
            ['numero' => 2, 'estado' => 'ocupada', 'turno_actual' => 'N-789'],
            ['numero' => 3, 'estado' => 'libre', 'turno_actual' => 'C-777'],
            ['numero' => 4, 'estado' => 'ocupada', 'turno_actual' => 'N-234']
        ];
    }
    function obtenerEstadisticas($mysqli) {
        $ventanillas = obtenerVentanillas($mysqli);
        $turnosEnEspera = obtenerTurnosEnEspera($mysqli);
        $ventanillasLibres = count(array_filter($ventanillas, function($v) { return $v['estado'] === 'libre'; }));
        $ventanillasOcupadas = count(array_filter($ventanillas, function($v) { return $v['estado'] === 'ocupada'; }));
        return [
            'ventanillas_libres' => $ventanillasLibres,
            'ventanillas_ocupadas' => $ventanillasOcupadas,
            'total_ventanillas' => count($ventanillas),
            'turnos_en_espera' => count($turnosEnEspera),
            'ultima_actualizacion' => date('Y-m-d H:i:s')
        ];
    }
    $turnosEnEspera = obtenerTurnosEnEspera($mysqli);
    $ventanillas = obtenerVentanillas($mysqli);
    $estadisticas = obtenerEstadisticas($mysqli);
    $response = [
        'success' => true,
        'data' => [
            'turnos_en_espera' => $turnosEnEspera,
            'ventanillas' => $ventanillas,
            'estadisticas' => $estadisticas
        ],
        'timestamp' => time(),
        'message' => 'Datos obtenidos correctamente'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    $conexion->cerrar_conexion();
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>