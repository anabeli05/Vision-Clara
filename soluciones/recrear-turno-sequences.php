<?php
/**
 * SOLUCIÓN: Recrear tabla turno_sequences si está dañada
 * 
 * Ejecutar si la tabla turno_sequences no existe o está corrupta
 * URL: http://localhost/Vision-Clara/soluciones/recrear-turno-sequences.php
 */

require_once __DIR__ . '/../Base de Datos/conexion.php';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    echo "<h2>🔧 Recrear tabla turno_sequences</h2>";
    echo "<hr>";

    // 1. Verificar si existe
    echo "<h3>Paso 1: Verificar estado</h3>";
    
    $sql = "SELECT COUNT(*) as total FROM turno_sequences";
    $result = @$mysqli->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Tabla turno_sequences EXISTE (registros: " . $row['total'] . ")</p>";
        echo "<p>No es necesario recrearla.</p>";
        $conexion->cerrar_conexion();
        exit;
    }

    // 2. Eliminar si existe corrupta
    echo "<h3>Paso 2: Eliminar tabla corrupta</h3>";
    
    $sql_drop = "DROP TABLE IF EXISTS turno_sequences";
    if ($mysqli->query($sql_drop)) {
        echo "<p style='color: green;'>✓ Tabla eliminada</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Tabla no existía</p>";
    }

    // 3. Recrear tabla
    echo "<h3>Paso 3: Crear tabla nueva</h3>";
    
    $sql_create = "
    CREATE TABLE turno_sequences (
        id INT PRIMARY KEY AUTO_INCREMENT,
        tipo VARCHAR(10) NOT NULL,
        fecha_secuencia DATE NOT NULL,
        ultimo_numero INT DEFAULT 0,
        UNIQUE KEY unique_tipo_fecha (tipo, fecha_secuencia)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($mysqli->query($sql_create)) {
        echo "<p style='color: green;'>✓ Tabla turno_sequences recreada</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
        exit;
    }

    // 4. Inicializar con datos de hoy
    echo "<h3>Paso 4: Inicializar secuencias</h3>";
    
    $hoy = date('Y-m-d');
    
    // Obtener máximo número de cada tipo hoy
    $tipos = ['Visitante', 'Cliente'];
    $tipo_letra = ['Visitante' => 'N', 'Cliente' => 'C'];
    
    foreach ($tipos as $tipo) {
        $sql_max = "SELECT MAX(CAST(SUBSTRING(Numero_Turno, 2) AS UNSIGNED)) as max_num
                    FROM turnos 
                    WHERE Tipo = ? AND DATE(Fecha) = ?";
        
        $stmt = $mysqli->prepare($sql_max);
        $stmt->bind_param('ss', $tipo, $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $ultimo = $row['max_num'] ?? 0;
        
        // Insertar en secuencias
        $sql_insert = "INSERT INTO turno_sequences (tipo, fecha_secuencia, ultimo_numero) 
                       VALUES (?, ?, ?)";
        
        $stmt = $mysqli->prepare($sql_insert);
        $stmt->bind_param('ssi', $tipo, $hoy, $ultimo);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ " . $tipo . ": último número = " . $ultimo . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ " . $tipo . ": " . $stmt->error . "</p>";
        }
        $stmt->close();
    }

    echo "<hr>";
    echo "<p style='color: green;'><strong>✅ Tabla turno_sequences restaurada</strong></p>";
    echo "<p>La generación de turnos funcionará correctamente nuevamente.</p>";

    $conexion->cerrar_conexion();

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
