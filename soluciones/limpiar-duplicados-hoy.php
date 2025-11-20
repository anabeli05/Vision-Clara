<?php
/**
 * SOLUCIÓN INMEDIATA - Limpiar duplicados de HOY
 * 
 * Ejecutar si ves el error "Duplicate entry 'N001'" HOY
 * URL: http://localhost/Vision-Clara/soluciones/limpiar-duplicados-hoy.php
 */

require_once __DIR__ . '/../Base de Datos/conexion.php';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    echo "<h2>🧹 Limpieza de Duplicados de HOY</h2>";
    echo "<hr>";

    $hoy = date('Y-m-d');

    // 1. Encontrar duplicados de hoy
    $sql = "SELECT Numero_Turno, COUNT(*) as cantidad, GROUP_CONCAT(ID_Turno) as ids
            FROM turnos 
            WHERE DATE(Fecha) = ? 
            GROUP BY Numero_Turno 
            HAVING COUNT(*) > 1";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $hoy);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $duplicados = [];
    while ($row = $result->fetch_assoc()) {
        $duplicados[] = $row;
    }
    $stmt->close();

    if (empty($duplicados)) {
        echo "<p style='color: green;'>✅ No hay duplicados hoy (" . $hoy . ")</p>";
        echo "<p>El sistema está funcionando correctamente.</p>";
        $conexion->cerrar_conexion();
        exit;
    }

    echo "<p style='color: orange;'>⚠️ Se encontraron " . count($duplicados) . " turno(s) duplicado(s):</p>";

    foreach ($duplicados as $dup) {
        echo "<p>- <strong>" . $dup['Numero_Turno'] . "</strong>: " . $dup['cantidad'] . " registros (IDs: " . $dup['ids'] . ")</p>";
    }

    echo "<hr>";
    echo "<h3>Limpiando...</h3>";

    // 2. Mantener solo el PRIMER registro de cada duplicado, eliminar los demás
    foreach ($duplicados as $dup) {
        $ids = explode(',', $dup['ids']);
        if (count($ids) > 1) {
            // Mantener el primero, eliminar los demás
            $ids_a_eliminar = array_slice($ids, 1);
            $placeholders = implode(',', array_fill(0, count($ids_a_eliminar), '?'));
            
            $sql_delete = "DELETE FROM turnos WHERE ID_Turno IN ($placeholders)";
            $stmt = $mysqli->prepare($sql_delete);
            
            // Crear array de tipos
            $types = str_repeat('i', count($ids_a_eliminar));
            $stmt->bind_param($types, ...$ids_a_eliminar);
            
            if ($stmt->execute()) {
                $eliminados = $stmt->affected_rows;
                echo "<p style='color: green;'>✓ " . $dup['Numero_Turno'] . ": Se mantuvieron 1, se eliminaron " . $eliminados . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Error al limpiar " . $dup['Numero_Turno'] . "</p>";
            }
            $stmt->close();
        }
    }

    // 3. Verificar resultado
    echo "<hr>";
    echo "<h3>Verificación Final:</h3>";
    
    $sql_check = "SELECT Numero_Turno, COUNT(*) as cantidad
                  FROM turnos 
                  WHERE DATE(Fecha) = ? 
                  GROUP BY Numero_Turno 
                  HAVING COUNT(*) > 1";
    
    $stmt = $mysqli->prepare($sql_check);
    $stmt->bind_param('s', $hoy);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "<p style='color: green;'>✅ Limpieza completada correctamente</p>";
        echo "<p>No hay más duplicados. El sistema está operativo.</p>";
    } else {
        echo "<p style='color: red;'>⚠️ Aún hay duplicados. Contactar soporte.</p>";
    }
    $stmt->close();

    $conexion->cerrar_conexion();

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
