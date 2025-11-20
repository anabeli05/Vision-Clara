<?php
/**
 * limpiar-turnos-diarios-mejorado.php
 * Limpia turnos finalizados o cancelados más antiguos de X días
 * Ejecutar diariamente con CRON: 0 2 * * * /usr/bin/php /ruta/a/limpiar-turnos-diarios-mejorado.php
 */

require_once __DIR__ . '/../Base de Datos/conexion.php';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    // Eliminar turnos Finalizados o Cancelados más antiguos de 7 días
    $dias_retencion = 7;
    $fecha_limite = date('Y-m-d H:i:s', strtotime("-{$dias_retencion} days"));

    $sql = "DELETE FROM turnos WHERE (Estado = 'Finalizado' OR Estado = 'Cancelado') AND Fecha < ?";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
        exit;
    }

    $stmt->bind_param('s', $fecha_limite);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
        $stmt->close();
        exit;
    }

    $registros_eliminados = $stmt->affected_rows;
    $stmt->close();

    // Log
    $log_file = __DIR__ . '/cleanup.log';
    $mensaje = "[" . date('Y-m-d H:i:s') . "] Registros eliminados: {$registros_eliminados} (antes de {$fecha_limite})\n";
    file_put_contents($log_file, $mensaje, FILE_APPEND);

    echo json_encode([
        'success' => true, 
        'mensaje' => "Limpieza completada",
        'registros_eliminados' => $registros_eliminados,
        'fecha_limite' => $fecha_limite
    ]);

    $conexion->cerrar_conexion();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
