<<<<<<< Updated upstream
<?php 
=======
<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que NO sea Super Admin (puede ser Admin, Usuario, etc.)
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/inicio/InicioSA.php');
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Inicializar variables
$turnos = [];
$error = '';

try {
    // Obtener todos los turnos pendientes
    $stmt = $conn->prepare("
        SELECT t.ID_Turno, t.Numero_Turno, t.Estado, t.Fecha_Hora,
               c.Nombre as Nombre_Cliente, c.No_Afiliado
        FROM turnos t
        LEFT JOIN clientes c ON t.ID_Cliente = c.No_Afiliado
        WHERE t.Estado IN ('Pendiente', 'En Proceso')
        ORDER BY t.Fecha_Hora ASC
    ");
    $stmt->execute();
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error al cargar los turnos: " . $e->getMessage();
}
>>>>>>> Stashed changes
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href='Gestion-Turnos.css'>
    <link rel="stylesheet" href='../Dashboard/sidebar.css'>
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>
    <div class="container">
        <!-- Header -->
        <div class="header_1">
            <h1><i class="fas fa-ticket-alt" data-no-translate></i> Gestión de Turnos</h1>
        </div>

        <!-- Panel Principal -->
        <div class="main-panel">
            <!-- Turno Actual -->
            <div class="current-turn">
                <div class="turn-info">
                    <div class="turn-label">Turno actual</div>
                    <div class="turn-number">C-789</div>
                    <div class="turn-status">En espera</div>
                </div>
                <div class="attendance-info">
                    <div class="attendance-label">Atendidos</div>
                    <div class="attendance-number">30</div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="content-wrapper">
                <!-- Sección de Cola -->
                <div class="queue-section">
                    <table class="queue-table">
                        <thead class="queue-header">
                            <tr>
                                <th>Número</th>
                                <th>Cliente/Visitante</th>
                                <th>Número de afiliado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="queue-row">
                                <td class="turn-code">C-800</td>
                                <td class="client-type">Nombre</td>
                                <td class="affiliate-number">978546</td>
                            </tr>
                            <tr class="queue-row">
                                <td class="turn-code">N-564</td>
                                <td class="client-type">Visitante</td>
                                <td class="affiliate-number">-</td>
                            </tr>
                            <tr class="queue-row">
                                <td class="turn-code">C-959</td>
                                <td class="client-type">Nombre</td>
                                <td class="affiliate-number">978696</td>
                            </tr>
                            <tr class="queue-row">
                                <td class="turn-code">N-645</td>
                                <td class="client-type">Visitante</td>
                                <td class="affiliate-number">-</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Botones de Acción -->
                    <div class="action-buttons">
                        <button class="btn btn-primary">Llamar</button>
                        <button class="btn btn-secondary">Pasar</button>
                        <button class="btn btn-danger">Reagendar</button>
                    </div>
                </div>

                <!-- Sección de Ventanillas -->
                <div class="windows-section">
                    <div class="windows-header">
                        <div class="windows-title">Ventanillas</div>
                    </div>
                    <div class="divider"></div>
                    <div class="windows-list">
                        <div class="window-item">
                            <span class="window-number">Ventanilla 1</span>
                            <span class="window-status">Libre</span>
                        </div>
                        <div class="window-item">
                            <span class="window-number">Ventanilla 2</span>
                            <span class="window-status occupied">Ocupada</span>
                        </div>
                        <div class="window-item">
                            <span class="window-number">Ventanilla 3</span>
                            <span class="window-status">Libre</span>
                        </div>
                        <div class="window-item">
                            <span class="window-number">Ventanilla 4</span>
                            <span class="window-status occupied">Ocupada</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>