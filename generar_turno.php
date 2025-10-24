<?php
include("conexion.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'] ?? '';
    $ip_usuario = $_SERVER['REMOTE_ADDR']; // Obtener IP del cliente
    
    $conexion_obj = new Conexion();
    $conexion_obj->abrir_conexion();
    $conexion = $conexion_obj->conexion;
    
    if ($tipo === 'Cliente') {
        $numero_afiliado = $_POST['no_afiliado'] ?? '';
        
        // Verificar que el número de afiliado tenga 6 dígitos
        if (strlen($numero_afiliado) !== 6 || !ctype_digit($numero_afiliado)) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'Número de afiliado inválido. Debe tener 6 dígitos.'
            ]);
            exit;
        }
        
        // Verificar si el cliente existe
        $stmt = $conexion->prepare("SELECT numero_afiliado FROM clientes WHERE numero_afiliado = ?");
        $stmt->bind_param("s", $numero_afiliado);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'Cliente no encontrado. Verifica tu número de afiliado.'
            ]);
            exit;
        }
        
        // Validar si el cliente ya tiene turno hoy
        $stmt_turno_existente = $conexion->prepare("SELECT id FROM turnos WHERE tipo = 'Cliente' AND numero_afiliado = ? AND DATE(fecha) = CURDATE()");
        $stmt_turno_existente->bind_param("s", $numero_afiliado);
        $stmt_turno_existente->execute();
        $result_turno_existente = $stmt_turno_existente->get_result();
        
        if ($result_turno_existente->num_rows > 0) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'Ya tienes un turno asignado para hoy. Solo se permite un turno por cliente por día.'
            ]);
            exit;
        }
        
        // Generar número de turno para cliente
        $result_turno = $conexion->query("SELECT COUNT(*) as total FROM turnos WHERE DATE(fecha) = CURDATE() AND tipo = 'Cliente'");
        $fila = $result_turno->fetch_assoc();
        $consecutivo = intval($fila['total']) + 1;
        $numero_turno = 'C' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
        
        // Insertar turno con número de afiliado
        $stmt_insert = $conexion->prepare("INSERT INTO turnos (tipo, numero, fecha, atendido, numero_afiliado, ip_cliente) VALUES (?, ?, NOW(), 0, ?, ?)");
        $stmt_insert->bind_param("ssss", $tipo, $numero_turno, $numero_afiliado, $ip_usuario);
        
    } elseif ($tipo === 'Visitante') {
        
        // 🔥 NUEVA VALIDACIÓN: Verificar si esta IP ya generó un turno recientemente (10 minutos)
        $stmt_ip = $conexion->prepare("SELECT id FROM turnos WHERE tipo = 'Visitante' AND ip_cliente = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
        $stmt_ip->bind_param("s", $ip_usuario);
        $stmt_ip->execute();
        
        if ($stmt_ip->get_result()->num_rows > 0) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'Ya generaste un turno recientemente. Espera 10 minutos antes de generar otro.'
            ]);
            exit;
        }
        
        // Generar número de turno para visitante
        $result_turno = $conexion->query("SELECT COUNT(*) as total FROM turnos WHERE DATE(fecha) = CURDATE() AND tipo = 'Visitante'");
        $fila = $result_turno->fetch_assoc();
        $consecutivo = intval($fila['total']) + 1;
        $numero_turno = 'V' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
        
        // Insertar turno de visitante con IP
        $stmt_insert = $conexion->prepare("INSERT INTO turnos (tipo, numero, fecha, atendido, ip_cliente) VALUES (?, ?, NOW(), 0, ?)");
        $stmt_insert->bind_param("sss", $tipo, $numero_turno, $ip_usuario);
        
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Tipo de turno inválido.']);
        exit;
    }
    
    if ($stmt_insert->execute()) {
        echo json_encode([
            'status' => 'ok', 
            'turno' => $numero_turno,
            'mensaje' => 'Turno generado correctamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'mensaje' => 'Error al guardar el turno en la base de datos'
        ]);
    }
    
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
}
?>