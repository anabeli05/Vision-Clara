<?php

/**
 * Escribe un mensaje de log en el archivo de logs
 * @param string $message Mensaje a escribir
 */
function writeLog($message) {
    // Usar ruta absoluta desde la raíz del proyecto
    $logFile = dirname(__DIR__) . '/Login/debug.log';
    
    // Asegurarse de que el directorio existe
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "--- Nuevo Log de Sesión: $timestamp ---\n" .
                "DEBUG: $message\n";
    
    // Intentar escribir el log usando fopen para mejor manejo de permisos
    $fp = fopen($logFile, 'a');
    if ($fp !== false) {
        fwrite($fp, $logEntry);
        fclose($fp);
    } else {
        // Si no se puede escribir, usar error_log como fallback
        error_log("ERROR: No se pudo escribir en el archivo de logs: " . $logFile);
        error_log($logEntry);
    }
}
?>
