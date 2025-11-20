<?php
/**
 * SOLUCIONES RÁPIDAS - Si vuelve a ocurrir "Duplicate entry N001"
 * 
 * Ejecuta el script correspondiente según el problema
 */

// ============================================================================
// SOLUCIÓN 1: Verificar el estado actual (siempre ejecutar primero)
// ============================================================================

require_once __DIR__ . '/Base de Datos/conexion.php';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    echo "<h2>🔍 DIAGNÓSTICO: Estado del Sistema de Turnos</h2>";
    echo "<hr>";

    // 1. Verificar UNIQUE constraint
    echo "<h3>1. Verificar UNIQUE constraint</h3>";
    $sql = "SHOW INDEXES FROM turnos WHERE Key_name = 'unique_numero_fecha'";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ UNIQUE constraint EXISTE</p>";
        echo "<p>Columnas:";
        while ($row = $result->fetch_assoc()) {
            echo " " . $row['Column_name'];
        }
        echo "</p>";
    } else {
        echo "<p style='color: red;'>❌ UNIQUE constraint NO EXISTE</p>";
        echo "<p><strong>SOLUCIÓN:</strong> Ejecutar agregar-unique-constraint.php</p>";
    }

    // 2. Verificar columna Fecha_Solo
    echo "<h3>2. Verificar columna Fecha_Solo</h3>";
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'turnos' AND COLUMN_NAME = 'Fecha_Solo'";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Columna Fecha_Solo EXISTE</p>";
    } else {
        echo "<p style='color: red;'>❌ Columna Fecha_Solo NO EXISTE</p>";
        echo "<p><strong>SOLUCIÓN:</strong> Ejecutar agregar-fecha-solo.php</p>";
    }

    // 3. Verificar tabla turno_sequences
    echo "<h3>3. Verificar tabla turno_sequences</h3>";
    $sql = "SELECT COUNT(*) as total FROM turno_sequences";
    $result = $mysqli->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Tabla turno_sequences EXISTE (registros: " . $row['total'] . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla turno_sequences NO EXISTE</p>";
        echo "<p><strong>SOLUCIÓN:</strong> Ejecutar crear-turno-sequences.php</p>";
    }

    // 4. Detectar duplicados reales
    echo "<h3>4. Detectar duplicados en BD</h3>";
    $sql = "SELECT Numero_Turno, Fecha_Solo, COUNT(*) as cantidad 
            FROM turnos 
            WHERE Fecha_Solo = CURDATE() 
            GROUP BY Numero_Turno, Fecha_Solo 
            HAVING cantidad > 1";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: red;'>❌ DUPLICADOS ENCONTRADOS HOY:</p>";
        while ($row = $result->fetch_assoc()) {
            echo "<p>- " . $row['Numero_Turno'] . ": " . $row['cantidad'] . " registros</p>";
        }
        echo "<p><strong>SOLUCIÓN:</strong> Ejecutar limpiar-duplicados-hoy.php</p>";
    } else {
        echo "<p style='color: green;'>✅ No hay duplicados hoy</p>";
    }

    // 5. Verificar API
    echo "<h3>5. Verificar API</h3>";
    if (file_exists(__DIR__ . '/Pantalla_Turnos/api-turnos-sequences.php')) {
        echo "<p style='color: green;'>✅ api-turnos-sequences.php EXISTE</p>";
    } else {
        echo "<p style='color: red;'>❌ api-turnos-sequences.php NO EXISTE</p>";
    }

    echo "<hr>";
    echo "<p><strong>⚠️ Nota:</strong> Si todo está en verde ✅, el sistema está OK. Si hay ❌, ejecuta los scripts sugeridos.</p>";

    $conexion->cerrar_conexion();

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
