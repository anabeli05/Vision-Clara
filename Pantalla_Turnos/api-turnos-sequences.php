<?php
/**
 * api-turnos-sequences.php
 * API segura para crear turnos usando tabla de secuencias
 * Sin race conditions, sin duplicados
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../Base de Datos/conexion.php';

/**
 * Obtiene el próximo número de turno de forma segura (sin race conditions)
 * Usa transacción para garantizar unicidad
 */
function obtener_proximo_turno($mysqli, $tipo) {
    $tipo_letra = ($tipo === 'Visitante') ? 'N' : 'C';
    $hoy = date('Y-m-d');
    
    // Iniciar transacción
    $mysqli->begin_transaction();
    
    try {
        // 1. LOCK: Buscar o crear registro de secuencia para hoy
        $sql_select = "SELECT id, ultimo_numero FROM turno_sequences 
                       WHERE tipo = ? AND fecha_secuencia = ? 
                       FOR UPDATE";
        
        $stmt = $mysqli->prepare($sql_select);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        
        $stmt->bind_param('ss', $tipo, $hoy);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            // Existe, incrementar
            $nuevo_numero = $row['ultimo_numero'] + 1;
            $seq_id = $row['id'];
            
            $sql_update = "UPDATE turno_sequences SET ultimo_numero = ? 
                          WHERE id = ?";
            $stmt = $mysqli->prepare($sql_update);
            $stmt->bind_param('ii', $nuevo_numero, $seq_id);
            if (!$stmt->execute()) {
                throw new Exception("Update failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            // No existe, crear con 1
            $nuevo_numero = 1;
            $sql_insert = "INSERT INTO turno_sequences (tipo, fecha_secuencia, ultimo_numero) 
                          VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql_insert);
            $stmt->bind_param('ssi', $tipo, $hoy, $nuevo_numero);
            if (!$stmt->execute()) {
                throw new Exception("Insert failed: " . $stmt->error);
            }
            $stmt->close();
        }
        
        // Confirmar transacción
        $mysqli->commit();
        
        // Generar número de turno formateado
        $numero_turno = $tipo_letra . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);
        return ['success' => true, 'numero' => $numero_turno];
        
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    // POST: Crear nuevo turno
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;
        $afiliado = isset($_POST['afiliado']) ? $_POST['afiliado'] : null;

        // Validar tipo
        if (!in_array($tipo, ['Visitante', 'Cliente'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tipo de turno inválido']);
            exit;
        }

        // Si es Cliente, validar afiliado
        if ($tipo === 'Cliente') {
            if (!$afiliado) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Número de afiliado requerido']);
                exit;
            }
            $afiliado = preg_replace('/\D/', '', $afiliado);
            if (strlen($afiliado) !== 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Número de afiliado debe tener 6 dígitos']);
                exit;
            }
        }

        // Obtener próximo número de turno
        $seq_result = obtener_proximo_turno($mysqli, $tipo);
        
        if (!$seq_result['success']) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $seq_result['error']]);
            exit;
        }

        $numero_turno = $seq_result['numero'];

        // Insertar turno en tabla
        $estado = 'Espera';
        $fecha = date('Y-m-d H:i:s');
        
        if ($tipo === 'Visitante') {
            $sql = "INSERT INTO turnos (Numero_Turno, Tipo, Estado, Fecha) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssss', $numero_turno, $tipo, $estado, $fecha);
        } else {
            $sql = "INSERT INTO turnos (Numero_Turno, Tipo, Estado, Fecha, No_Afiliado) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sssss', $numero_turno, $tipo, $estado, $fecha, $afiliado);
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'turno' => $numero_turno,
                'tipo' => $tipo,
                'mensaje' => 'Turno generado exitosamente'
            ]);
            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error insertando turno: ' . $stmt->error]);
            $stmt->close();
        }

        $conexion->cerrar_conexion();
        exit;
    }

    // GET: Obtener turnos en espera y atendiendo
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $hoy = date('Y-m-d');

        $sql_espera = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado 
                       FROM turnos 
                       WHERE Estado = 'Espera' AND DATE(Fecha) = ? 
                       ORDER BY Fecha ASC 
                       LIMIT 100";
        
        $sql_atendiendo = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado 
                          FROM turnos 
                          WHERE Estado = 'Atendiendo' AND DATE(Fecha) = ? 
                          ORDER BY Fecha ASC";

        $sql_stats = "SELECT 
                        COUNT(CASE WHEN Estado = 'Espera' THEN 1 END) as turnos_espera,
                        COUNT(CASE WHEN Estado = 'Atendiendo' THEN 1 END) as turnos_atendiendo,
                        COUNT(CASE WHEN Estado = 'Finalizado' THEN 1 END) as turnos_finalizados,
                        COUNT(CASE WHEN Estado = 'Cancelado' THEN 1 END) as turnos_cancelados
                     FROM turnos 
                     WHERE DATE(Fecha) = ?";

        $turnos_espera = [];
        $stmt = $mysqli->prepare($sql_espera);
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $turnos_espera[] = $row;
        }
        $stmt->close();

        $turnos_atendiendo = [];
        $stmt = $mysqli->prepare($sql_atendiendo);
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $turnos_atendiendo[] = $row;
        }
        $stmt->close();

        $stats = ['turnos_espera' => 0, 'turnos_atendiendo' => 0, 'turnos_finalizados' => 0, 'turnos_cancelados' => 0];
        $stmt = $mysqli->prepare($sql_stats);
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $stats = $row;
        }
        $stmt->close();

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
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
