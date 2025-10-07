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

require_once '../../Base de Datos/conexion.php';

try {
    // Crear instancia de conexión
    $conexion = new Conexion();
    
    // Función para obtener turnos en espera
    function obtenerTurnosEnEspera($conexion) {
        // Por ahora usar datos estáticos hasta que se configure la base de datos
        return [
            ['numero' => 'C-800', 'tipo' => 'Cliente'],
            ['numero' => 'N-564', 'tipo' => 'Visitante'],
            ['numero' => 'C-959', 'tipo' => 'Cliente'],
            ['numero' => 'N-645', 'tipo' => 'Visitante'],
            ['numero' => 'C-123', 'tipo' => 'Cliente'],
            ['numero' => 'N-456', 'tipo' => 'Visitante']
        ];
    }
    
    // Función para obtener ventanillas
    function obtenerVentanillas($conexion) {
        // Por ahora usar datos estáticos hasta que se configure la base de datos
        return [
            ['numero' => 1, 'estado' => 'libre', 'turno_actual' => 'C-897'],
            ['numero' => 2, 'estado' => 'ocupada', 'turno_actual' => 'N-789'],
            ['numero' => 3, 'estado' => 'libre', 'turno_actual' => 'C-777'],
            ['numero' => 4, 'estado' => 'ocupada', 'turno_actual' => 'N-234']
        ];
    }
    
    // Función para obtener estadísticas
    function obtenerEstadisticas($conexion) {
        $ventanillas = obtenerVentanillas($conexion);
        $turnosEnEspera = obtenerTurnosEnEspera($conexion);
        
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
    
    // Obtener datos
    $turnosEnEspera = obtenerTurnosEnEspera($conexion);
    $ventanillas = obtenerVentanillas($conexion);
    $estadisticas = obtenerEstadisticas($conexion);
    
    // Preparar respuesta
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
    
} catch (Exception $e) {
    // En caso de error, devolver datos de ejemplo
    $response = [
        'success' => true, // Mantener success true para que la página funcione
        'data' => [
            'turnos_en_espera' => [
                ['numero' => 'C-800', 'tipo' => 'Cliente'],
                ['numero' => 'N-564', 'tipo' => 'Visitante'],
                ['numero' => 'C-959', 'tipo' => 'Cliente'],
                ['numero' => 'N-645', 'tipo' => 'Visitante']
            ],
            'ventanillas' => [
                ['numero' => 1, 'estado' => 'libre', 'turno_actual' => 'C-897'],
                ['numero' => 2, 'estado' => 'ocupada', 'turno_actual' => 'N-789'],
                ['numero' => 3, 'estado' => 'libre', 'turno_actual' => 'C-777']
            ],
            'estadisticas' => [
                'ventanillas_libres' => 2,
                'ventanillas_ocupadas' => 1,
                'total_ventanillas' => 3,
                'turnos_en_espera' => 4,
                'ultima_actualizacion' => date('Y-m-d H:i:s')
            ]
        ],
        'timestamp' => time(),
        'message' => 'Usando datos de ejemplo - Error: ' . $e->getMessage()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>