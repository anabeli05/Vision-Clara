<?php
require_once '../Base de Datos/conexion.php';

// La variable $conexion (mysqli) ya está disponible globalmente desde conexion.php

// Función para obtener turnos en espera desde la tabla `turnos`
function obtenerTurnosEnEspera($conexion) {
    $turnos = [];
    if (!$conexion) return $turnos;

    // La tabla `turnos` tiene columnas: Numero_Turno, Tipo, Estado, Fecha
    $sql = "SELECT Numero_Turno as numero, Tipo as tipo, Estado as estado, Fecha as fecha FROM turnos WHERE Estado = 'Espera' ORDER BY Fecha ASC LIMIT 100";
    $res = @$conexion->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $turnos[] = [
                'numero' => $row['numero'],
                'tipo' => $row['tipo'] ?? 'Cliente',
                'estado' => $row['estado'] ?? 'Espera',
                'fecha' => $row['fecha'] ?? null
            ];
        }
        return $turnos;
    }

    // Fallback estático
    return [
        ['numero' => 'C-800', 'tipo' => 'Cliente'],
        ['numero' => 'N-564', 'tipo' => 'Visitante'],
        ['numero' => 'C-959', 'tipo' => 'Cliente'],
        ['numero' => 'N-645', 'tipo' => 'Visitante']
    ];
}

// Obtener ventanillas derivadas de los turnos que están en 'Atendiendo'
function obtenerVentanillas($conexion) {
    $ventanillas = [];
    if (!$conexion) return $ventanillas;

    $sql = "SELECT Numero_Turno as numero, Tipo as tipo, Fecha as fecha FROM turnos WHERE Estado = 'Atendiendo' ORDER BY Fecha ASC LIMIT 50";
    $res = @$conexion->query($sql);
    if ($res && $res->num_rows > 0) {
        $index = 1;
        while ($row = $res->fetch_assoc()) {
            $ventanillas[] = [
                'numero' => $index,
                'estado' => 'ocupada',
                'turno_actual' => $row['numero'] ?? '-',
                'tipo' => $row['tipo'] ?? 'Cliente'
            ];
            $index++;
        }
        return $ventanillas;
    }

    // Fallback si no hay turnos atendiendo
    return [
        ['numero' => 1, 'estado' => 'libre', 'turno_actual' => 'C-897'],
        ['numero' => 2, 'estado' => 'ocupada', 'turno_actual' => 'N-789'],
        ['numero' => 3, 'estado' => 'libre', 'turno_actual' => 'C-777']
    ];
}

// Obtener datos
try {
    $turnosEnEspera = obtenerTurnosEnEspera($conexion);
    $ventanillas = obtenerVentanillas($conexion);
} catch (Exception $e) {
    // En caso de error, usar datos de ejemplo
    $turnosEnEspera = [
        ['numero' => 'C-800', 'tipo' => 'Cliente'],
        ['numero' => 'N-564', 'tipo' => 'Visitante'],
        ['numero' => 'C-959', 'tipo' => 'Cliente'],
        ['numero' => 'N-645', 'tipo' => 'Visitante']
    ];
    $ventanillas = [
        ['numero' => 1, 'estado' => 'libre', 'turno_actual' => 'C-897'],
        ['numero' => 2, 'estado' => 'ocupada', 'turno_actual' => 'N-789'],
        ['numero' => 3, 'estado' => 'libre', 'turno_actual' => 'C-777']
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Turnos - Visión Clara</title>
    <link rel="stylesheet" href="Vista-Turno.css">
    <link rel="stylesheet" href="../Dashboard/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    
    <div class="main-container">
        <!-- Header con patrón de puntos -->
        <div class="header-section">
            <div class="dots-pattern"></div>
            <div class="header-content">
                <div class="logo-section">
                    <img src='../Imagenes/logo_black.png' alt="Visión Clara" class="logo-image">
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content-container">
            <!-- Sección Atendiendo -->
            <div class="attending-section">
                <h2 class="section-title">Atendiendo</h2>
                <div class="windows-table">
                    <div class="table-header">
                        <span>Numero</span>
                        <span>Estado</span>
                        <span>Turno</span>
                    </div>
                    <div class="table-body">
                        <?php foreach ($ventanillas as $ventanilla): ?>
                        <div class="table-row">
                            <span class="window-number">Ventanilla <?php echo $ventanilla['numero']; ?></span>
                            <span class="window-status <?php echo $ventanilla['estado'] === 'ocupada' ? 'occupied' : 'free'; ?>">
                                <?php echo ucfirst($ventanilla['estado']); ?>
                            </span>
                            <span class="current-turn"><?php echo isset($ventanilla['turno_actual']) ? $ventanilla['turno_actual'] : '-'; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sección En espera -->
            <div class="waiting-section">
                <h2 class="section-title">En espera</h2>
                <div class="waiting-list">
                    <div class="list-header">
                        <span>Turno</span>
                    </div>
                    <div class="list-body">
                        <?php foreach ($turnosEnEspera as $turno): ?>
                        <div class="waiting-item">
                            <span class="turn-number"><?php echo isset($turno['numero']) ? $turno['numero'] : $turno['numero']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="Vista-Turno.js"></script>
</body>
</html>