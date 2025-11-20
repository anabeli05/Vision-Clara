<?php
/**
 * SOLUCIÓN NUCLEAR - Resetear TODO el sistema de turnos
 * 
 * ⚠️ CUIDADO: Ejecutar solo si hay problemas graves
 * Limpia TODOS los turnos de hoy y resetea secuencias
 * 
 * URL: http://localhost/Vision-Clara/soluciones/resetear-sistema-completo.php
 */

require_once __DIR__ . '/../Base de Datos/conexion.php';

// Verificar token de confirmación
$confirmado = isset($_GET['confirmar']) && $_GET['confirmar'] === 'SI';

try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    echo "<h2>⚠️ RESETEAR SISTEMA DE TURNOS COMPLETO</h2>";
    echo "<hr>";

    if (!$confirmado) {
        echo "<div style='background: #fff3cd; border: 2px solid #ff9800; padding: 20px; border-radius: 5px;'>";
        echo "<h3 style='color: #ff6600;'>⚠️ ADVERTENCIA</h3>";
        echo "<p>Esta operación <strong>ELIMINARÁ TODOS LOS TURNOS DE HOY</strong> y reseteará las secuencias.</p>";
        echo "<p>Se perderán:</p>";
        echo "<ul>";
        echo "<li>✗ Todos los turnos de hoy en estado Espera</li>";
        echo "<li>✗ Todos los turnos en estado Atendiendo</li>";
        echo "<li>✗ Historial de secuencias de hoy</li>";
        echo "</ul>";
        echo "<p><strong>Los turnos Finalizado/Cancelado se conservarán</strong> (datos históricos).</p>";
        echo "<hr>";
        echo "<p><strong>¿Continuar?</strong></p>";
        echo "<a href='?confirmar=SI' style='background: #f44336; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;'>";
        echo "SÍ, RESETEAR TODO";
        echo "</a>";
        echo "&nbsp;&nbsp;";
        echo "<a href='javascript:history.back()' style='background: #4CAF50; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>";
        echo "NO, CANCELAR";
        echo "</a>";
        echo "</div>";
        $conexion->cerrar_conexion();
        exit;
    }

    // CONFIRMADO - Proceder con reseteo
    echo "<p style='background: #fff3cd; padding: 10px; border-left: 4px solid #ff9800;'>";
    echo "<strong>⏳ Reseteando...</strong>";
    echo "</p>";

    $hoy = date('Y-m-d');

    // 1. Eliminar turnos de hoy (excepto Finalizado y Cancelado)
    echo "<h3>Paso 1: Limpiar turnos de hoy</h3>";
    
    $sql = "DELETE FROM turnos 
            WHERE DATE(Fecha) = ? 
            AND Estado NOT IN ('Finalizado', 'Cancelado')";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $hoy);
    
    if ($stmt->execute()) {
        $eliminados = $stmt->affected_rows;
        echo "<p style='color: green;'>✓ Eliminados " . $eliminados . " turnos de hoy</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $stmt->error . "</p>";
        exit;
    }
    $stmt->close();

    // 2. Resetear secuencias de hoy
    echo "<h3>Paso 2: Resetear secuencias</h3>";
    
    $sql = "DELETE FROM turno_sequences WHERE fecha_secuencia = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $hoy);
    
    if ($stmt->execute()) {
        $resets = $stmt->affected_rows;
        echo "<p style='color: green;'>✓ Secuencias reseteadas: " . $resets . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $stmt->error . "</p>";
        exit;
    }
    $stmt->close();

    // 3. Reinicializar secuencias (empezar de 1)
    echo "<h3>Paso 3: Reinicializar secuencias</h3>";
    
    $tipos = ['Visitante', 'Cliente'];
    
    foreach ($tipos as $tipo) {
        $cero = 0;
        
        $sql_insert = "INSERT INTO turno_sequences (tipo, fecha_secuencia, ultimo_numero) 
                       VALUES (?, ?, ?)";
        
        $stmt = $mysqli->prepare($sql_insert);
        $stmt->bind_param('ssi', $tipo, $hoy, $cero);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ " . $tipo . " reseteado (próximo = 1)</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ " . $tipo . ": " . $stmt->error . "</p>";
        }
        $stmt->close();
    }

    // 4. Verificación final
    echo "<h3>Paso 4: Verificación</h3>";
    
    $sql = "SELECT COUNT(*) as total FROM turnos WHERE DATE(Fecha) = ? AND Estado NOT IN ('Finalizado', 'Cancelado')";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $hoy);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['total'] == 0) {
        echo "<p style='color: green;'>✓ No hay turnos activos de hoy</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Aún hay " . $row['total'] . " turnos activos</p>";
    }

    echo "<hr>";
    echo "<div style='background: #c8e6c9; border: 2px solid #4CAF50; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #2e7d32;'>✅ SISTEMA RESETEADO</h3>";
    echo "<p>Todos los turnos de hoy han sido limpiados.</p>";
    echo "<p>Los próximos turnos comenzarán desde <strong>N001</strong> y <strong>C001</strong>.</p>";
    echo "<p><a href='../../index.php' style='background: #2e7d32; color: white; padding: 10px 15px; border-radius: 3px; text-decoration: none;'>Volver a inicio</a></p>";
    echo "</div>";

    $conexion->cerrar_conexion();

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
