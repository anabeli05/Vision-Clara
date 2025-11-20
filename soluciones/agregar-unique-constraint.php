<?php
/**
 * SOLUCIÓN: Recrear UNIQUE constraint si está dañado
 * 
 * Ejecutar si el UNIQUE constraint no existe o está dañado
 * URL: http://localhost/Vision-Clara/soluciones/agregar-unique-constraint.php
 */

require_once __DIR__ . '/../Base de Datos/conexion.php';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    echo "<h2>🔧 Recrear UNIQUE Constraint</h2>";
    echo "<hr>";

    // 1. Verificar si existe el constraint
    echo "<h3>Paso 1: Verificar estado actual</h3>";
    
    $sql = "SHOW INDEXES FROM turnos WHERE Key_name = 'unique_numero_fecha'";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ UNIQUE constraint ya existe</p>";
        echo "<p>No es necesario hacer nada.</p>";
        $conexion->cerrar_conexion();
        exit;
    }

    // 2. Eliminar constraints antiguos si existen
    echo "<h3>Paso 2: Limpiar constraints antiguos</h3>";
    
    $sql_drop = "ALTER TABLE turnos DROP KEY Numero_Turno";
    if ($mysqli->query($sql_drop)) {
        echo "<p style='color: green;'>✓ Eliminado constraint 'Numero_Turno'</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Constraint 'Numero_Turno' no existía</p>";
    }

    $sql_drop = "ALTER TABLE turnos DROP KEY unique_numero_turno";
    if ($mysqli->query($sql_drop)) {
        echo "<p style='color: green;'>✓ Eliminado constraint 'unique_numero_turno'</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Constraint 'unique_numero_turno' no existía</p>";
    }

    // 3. Verificar columna Fecha_Solo
    echo "<h3>Paso 3: Verificar columna Fecha_Solo</h3>";
    
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'turnos' AND COLUMN_NAME = 'Fecha_Solo'";
    $result = $mysqli->query($sql);
    
    if (!($result && $result->num_rows > 0)) {
        echo "<p>Agregar columna Fecha_Solo...</p>";
        $sql_add = "ALTER TABLE turnos ADD COLUMN Fecha_Solo DATE GENERATED ALWAYS AS (DATE(Fecha)) STORED";
        if ($mysqli->query($sql_add)) {
            echo "<p style='color: green;'>✓ Columna Fecha_Solo agregada</p>";
        } else {
            echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
            exit;
        }
    } else {
        echo "<p style='color: green;'>✅ Columna Fecha_Solo ya existe</p>";
    }

    // 4. Crear nuevo UNIQUE constraint
    echo "<h3>Paso 4: Crear nuevo UNIQUE constraint</h3>";
    
    $sql_unique = "ALTER TABLE turnos ADD UNIQUE KEY unique_numero_fecha (Numero_Turno, Fecha_Solo)";
    
    if ($mysqli->query($sql_unique)) {
        echo "<p style='color: green;'>✓ UNIQUE constraint creado correctamente</p>";
    } else {
        if (str_contains($mysqli->error, "Duplicate")) {
            echo "<p style='color: orange;'>⚠️ Hay duplicados en la BD. Ejecutar limpiar-duplicados-hoy.php primero.</p>";
            echo "<p>Error: " . $mysqli->error . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
        }
        exit;
    }

    echo "<hr>";
    echo "<p style='color: green;'><strong>✅ UNIQUE Constraint restaurado</strong></p>";
    echo "<p>El sistema está protegido nuevamente contra duplicados.</p>";

    $conexion->cerrar_conexion();

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
