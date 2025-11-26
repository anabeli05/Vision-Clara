<?php
/**
 * api-turnos-admin-clean.php
 * API limpia para gestionar turnos desde el panel de administración
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir conexión a base de datos
require_once __DIR__ . '/../Base de Datos/conexion.php';

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

/**
 * Función para obtener todos los datos de turnos
 */
function obtenerDatosTurnos($mysqli) {
    $hoy = date('Y-m-d');
    $datos = [
        'turnos_espera' => [],
        'turnos_atendiendo' => [],
        'estadisticas' => []
    ];
    
    try {
        // Obtener turnos en espera
        $sql_espera = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado 
                       FROM turnos 
                       WHERE Estado = 'Espera' AND DATE(Fecha) = ? 
                       ORDER BY Fecha ASC 
                       LIMIT 100";
        $stmt = $mysqli->prepare($sql_espera);
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $datos['turnos_espera'][] = $row;
        }
        $stmt->close();
        
        // Obtener turnos atendiendo
        $sql_atendiendo = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado 
                           FROM turnos 
                           WHERE Estado = 'Atendiendo' AND DATE(Fecha) = ? 
                           ORDER BY Fecha ASC";
        $stmt = $mysqli->prepare($sql_atendiendo);
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $datos['turnos_atendiendo'][] = $row;
        }
        $stmt->close();
        
        // Obtener estadísticas del día
        $sql_stats = "SELECT 
                        COUNT(CASE WHEN Estado = 'Espera' THEN 1 END) as turnos_espera,
                        COUNT(CASE WHEN Estado = 'Atendiendo' THEN 1 END) as turnos_atendiendo,
                        COUNT(CASE WHEN Estado = 'Finalizado' THEN 1 END) as turnos_finalizados,
                        COUNT(CASE WHEN Estado = 'Cancelado' THEN 1 END) as turnos_cancelados
                      FROM turnos 
                      WHERE DATE(Fecha) = ?";
        $stmt = $mysqli->prepare($sql_stats);
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos['estadisticas'] = $result->fetch_assoc();
        $stmt->close();
        
        return ['success' => true, 'data' => $datos];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Función para cambiar el estado de un turno
 */
function cambiarEstadoTurno($mysqli, $numero_turno, $nuevo_estado) {
    try {
        // Validar estado
        $estados_validos = ['Espera', 'Atendiendo', 'Finalizado', 'Cancelado'];
        if (!in_array($nuevo_estado, $estados_validos)) {
            return ['success' => false, 'error' => 'Estado no válido'];
        }
        
        // Actualizar estado
        $stmt = $mysqli->prepare("UPDATE turnos SET Estado = ? WHERE Numero_Turno = ?");
        $stmt->bind_param('ss', $nuevo_estado, $numero_turno);
        
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'error' => 'Error al actualizar el turno'];
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected === 0) {
            return ['success' => false, 'error' => 'Turno no encontrado'];
        }
        
        return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Función para llamar al siguiente turno en espera
 */
function llamarSiguienteTurno($mysqli) {
    try {
        $hoy = date('Y-m-d');
        
        // Buscar el primer turno en espera del día
        $stmt = $mysqli->prepare("SELECT Numero_Turno FROM turnos 
                                  WHERE Estado = 'Espera' AND DATE(Fecha) = ? 
                                  ORDER BY Fecha ASC 
                                  LIMIT 1");
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'error' => 'No hay turnos en espera'];
        }
        
        $row = $result->fetch_assoc();
        $numero_turno = $row['Numero_Turno'];
        $stmt->close();
        
        // Cambiar estado a Atendiendo
        $resultado = cambiarEstadoTurno($mysqli, $numero_turno, 'Atendiendo');
        
        if ($resultado['success']) {
            return [
                'success' => true, 
                'turno_llamado' => $numero_turno,
                'message' => 'Turno llamado correctamente'
            ];
        } else {
            return $resultado;
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// --- LÓGICA PRINCIPAL ---

try {
    // Crear conexión
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;
    
    // --- MANEJO DE POST: ACCIONES ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? trim($_POST['action']) : '';
        
        switch ($action) {
            case 'cambiar_estado':
                // Cambiar estado de un turno específico
                $numero_turno = isset($_POST['numero_turno']) ? trim($_POST['numero_turno']) : '';
                $nuevo_estado = isset($_POST['nuevo_estado']) ? trim($_POST['nuevo_estado']) : '';
                
                if (empty($numero_turno) || empty($nuevo_estado)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Faltan parámetros: numero_turno y nuevo_estado son requeridos'
                    ]);
                    break;
                }
                
                $resultado = cambiarEstadoTurno($mysqli, $numero_turno, $nuevo_estado);
                echo json_encode($resultado);
                break;
                
            case 'llamar':
                // Llamar al siguiente turno en espera
                $resultado = llamarSiguienteTurno($mysqli);
                echo json_encode($resultado);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'error' => 'Acción no válida. Acciones disponibles: cambiar_estado, llamar'
                ]);
                break;
        }
    }
    
    // --- MANEJO DE GET: CONSULTAR DATOS ---
    else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $resultado = obtenerDatosTurnos($mysqli);
        echo json_encode($resultado);
    }
    
    // Método no permitido
    else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido. Use GET o POST'
        ]);
    }
    
    $conexion->cerrar_conexion();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>