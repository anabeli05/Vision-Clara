<?php
require_once '../../Base de Datos/conexion.php';

// Crear instancia de conexión
$conexion = new Conexion();

// Función para obtener turnos en espera
function obtenerTurnosEnEspera($conexion) {
    // Usar datos estáticos por ahora
    return [
        ['numero' => 'C-800', 'tipo' => 'Cliente'],
        ['numero' => 'N-564', 'tipo' => 'Visitante'],
        ['numero' => 'C-959', 'tipo' => 'Cliente'],
        ['numero' => 'N-645', 'tipo' => 'Visitante']
    ];
}

// Función para obtener ventanillas
function obtenerVentanillas($conexion) {
    // Usar datos estáticos por ahora
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
    <?php include '../Dashboard/sidebar.php'; ?>
    
    <div class="main-container">
        <!-- Header con patrón de puntos -->
        <div class="header-section">
            <div class="dots-pattern"></div>
            <div class="header-content">
                <div class="logo-section">
                    <img src="../../Imagenes/logo_white.png" alt="Visión Clara" class="logo-image">
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
