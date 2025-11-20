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
$turnos_espera = [];
$turnos_atendiendo = [];
$error = '';
$stats = ['turnos_espera' => 0, 'turnos_atendiendo' => 0];

try {
    $mysqli = new Conexion();
    $mysqli->abrir_conexion();
    $conn = $mysqli->conexion;

    // Obtener turnos en espera
    $sql_espera = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado 
                   FROM turnos 
                   WHERE Estado = 'Espera' 
                   ORDER BY Fecha ASC 
                   LIMIT 100";
    $res = @$conn->query($sql_espera);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $turnos_espera[] = $row;
        }
    }

    // Obtener turnos en atención
    $sql_atendiendo = "SELECT ID_Turno, Numero_Turno, Tipo, Estado, Fecha, No_Afiliado 
                       FROM turnos 
                       WHERE Estado = 'Atendiendo' 
                       ORDER BY Fecha ASC";
    $res = @$conn->query($sql_atendiendo);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $turnos_atendiendo[] = $row;
        }
    }

    // Obtener estadísticas del día
    $sql_stats = "SELECT 
                    COUNT(CASE WHEN Estado = 'Espera' THEN 1 END) as turnos_espera,
                    COUNT(CASE WHEN Estado = 'Atendiendo' THEN 1 END) as turnos_atendiendo,
                    COUNT(CASE WHEN Estado = 'Finalizado' THEN 1 END) as turnos_finalizados,
                    COUNT(CASE WHEN Estado = 'Cancelado' THEN 1 END) as turnos_cancelados
                  FROM turnos 
                  WHERE DATE(Fecha) = CURDATE()";
    $res_stats = @$conn->query($sql_stats);
    if ($res_stats && $row = $res_stats->fetch_assoc()) {
        $stats = $row;
    }

    $mysqli->cerrar_conexion();
} catch (Exception $e) {
    $error = "Error al cargar los turnos: " . $e->getMessage();
}

// Obtener primer turno en espera (próximo a llamar)
$proximo_turno = !empty($turnos_espera) ? $turnos_espera[0] : null;
$turno_atendiendo = !empty($turnos_atendiendo) ? $turnos_atendiendo[0] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href='TurnosAdmin.css'>
    <link rel="stylesheet" href='../Dashboard/SidebarAdmin.css'>
</head>
<body>

    <?php include '../Dashboard/SidebarAdmin.php'; ?>
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
                    <div class="turn-label">Turno Actual</div>
                    <div class="turn-number" id="turno-actual">
                        <?php echo $turno_atendiendo ? htmlspecialchars($turno_atendiendo['Numero_Turno']) : '-'; ?>
                    </div>
                    <div class="turn-status" id="turn-status">
                        <?php echo $turno_atendiendo ? 'Atendiendo' : 'Disponible'; ?>
                    </div>
                </div>
                <div class="attendance-info">
                    <div class="attendance-label">Atendidos Hoy</div>
                    <div class="attendance-number" id="turnos-finalizados">
                        <?php echo isset($stats['turnos_finalizados']) ? $stats['turnos_finalizados'] : 0; ?>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="content-wrapper">
                <!-- Sección de Cola -->
                <div class="queue-section">
                    <h3 style="margin-bottom: 15px; color: #0066cc;">Cola de Espera (<?php echo count($turnos_espera); ?>)</h3>
                    <table class="queue-table">
                        <thead class="queue-header">
                            <tr>
                                <th>Número</th>
                                <th>Tipo</th>
                                <th>No. Afiliado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="queue-tbody">
                            <?php foreach ($turnos_espera as $turno): ?>
                            <tr class="queue-row" data-numero="<?php echo htmlspecialchars($turno['Numero_Turno']); ?>">
                                <td class="turn-code"><?php echo htmlspecialchars($turno['Numero_Turno']); ?></td>
                                <td class="client-type">
                                    <?php echo $turno['Tipo'] === 'Cliente' ? 'Cliente' : 'Visitante'; ?>
                                </td>
                                <td class="affiliate-number">
                                    <?php echo $turno['No_Afiliado'] ? htmlspecialchars($turno['No_Afiliado']) : '-'; ?>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn-small btn-info" onclick="cambiarEstado('<?php echo htmlspecialchars($turno['Numero_Turno']); ?>', 'Atendiendo')" title="Llamar">
                                        <i class="fas fa-phone"></i> Llamar
                                    </button>
                                    <button class="btn-small btn-danger" onclick="cambiarEstado('<?php echo htmlspecialchars($turno['Numero_Turno']); ?>', 'Cancelado')" title="Cancelar">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($turnos_espera)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px; color: #999;">
                                    No hay turnos en espera
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Botones de Acción Rápida -->
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="llamarProximo()" id="btn-llamar">
                            <i class="fas fa-bell"></i> Llamar Siguiente
                        </button>
                        <button class="btn btn-secondary" onclick="finalizarActual()" id="btn-finalizar">
                            <i class="fas fa-check"></i> Finalizar Actual
                        </button>
                        <button class="btn btn-info" onclick="recargarDatos()">
                            <i class="fas fa-sync-alt"></i> Recargar
                        </button>
                    </div>
                </div>

                <!-- Sección de Ventanillas -->
                <div class="windows-section">
                    <div class="windows-header">
                        <div class="windows-title">Estadísticas del Día</div>
                    </div>
                    <div class="divider"></div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">En Espera</span>
                            <span class="stat-value" id="stat-espera">
                                <?php echo isset($stats['turnos_espera']) ? $stats['turnos_espera'] : 0; ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Atendiendo</span>
                            <span class="stat-value" id="stat-atendiendo">
                                <?php echo isset($stats['turnos_atendiendo']) ? $stats['turnos_atendiendo'] : 0; ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Finalizados</span>
                            <span class="stat-value" id="stat-finalizados">
                                <?php echo isset($stats['turnos_finalizados']) ? $stats['turnos_finalizados'] : 0; ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Cancelados</span>
                            <span class="stat-value" id="stat-cancelados">
                                <?php echo isset($stats['turnos_cancelados']) ? $stats['turnos_cancelados'] : 0; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notificación flotante -->
    <div id="notification" class="notification"></div>

    <script>
        // Actualizar datos cada 5 segundos
        const updateInterval = setInterval(recargarDatos, 5000);

        async function cambiarEstado(numeroTurno, nuevoEstado) {
            try {
                const formData = new FormData();
                formData.append('action', 'cambiar_estado');
                formData.append('numero_turno', numeroTurno);
                formData.append('nuevo_estado', nuevoEstado);

                const response = await fetch('../../Pantalla_Turnos/api-turnos-admin.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    mostrarNotificacion('Turno ' + numeroTurno + ' ' + nuevoEstado, 'success');
                    recargarDatos();
                } else {
                    mostrarNotificacion('Error: ' + (data.error || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión', 'error');
            }
        }

        async function llamarProximo() {
            try {
                const formData = new FormData();
                formData.append('action', 'llamar');

                const response = await fetch('../../Pantalla_Turnos/api-turnos-admin.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    mostrarNotificacion('¡Turno ' + data.turno_llamado + ' por favor!', 'success');
                    reproducirSonido();
                    recargarDatos();
                } else {
                    mostrarNotificacion('Error: ' + (data.error || 'No hay turnos'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión', 'error');
            }
        }

        async function finalizarActual() {
            const turnoActual = document.getElementById('turno-actual').innerText;
            if (turnoActual === '-') {
                mostrarNotificacion('No hay turno actual atendiendo', 'warning');
                return;
            }

            if (confirm('¿Finalizar turno ' + turnoActual + '?')) {
                await cambiarEstado(turnoActual, 'Finalizado');
            }
        }

        async function recargarDatos() {
            try {
                const response = await fetch('../../Pantalla_Turnos/api-turnos-admin.php');
                const data = await response.json();

                if (data.success && data.data) {
                    // Actualizar cola
                    const queueBody = document.getElementById('queue-tbody');
                    queueBody.innerHTML = '';

                    if (data.data.turnos_espera && data.data.turnos_espera.length > 0) {
                        data.data.turnos_espera.forEach(turno => {
                            const row = document.createElement('tr');
                            row.className = 'queue-row';
                            row.setAttribute('data-numero', turno.Numero_Turno);
                            row.innerHTML = `
                                <td class="turn-code">${turno.Numero_Turno}</td>
                                <td class="client-type">${turno.Tipo === 'Cliente' ? 'Cliente' : 'Visitante'}</td>
                                <td class="affiliate-number">${turno.No_Afiliado || '-'}</td>
                                <td class="actions-cell">
                                    <button class="btn-small btn-info" onclick="cambiarEstado('${turno.Numero_Turno}', 'Atendiendo')" title="Llamar">
                                        <i class="fas fa-phone"></i> Llamar
                                    </button>
                                    <button class="btn-small btn-danger" onclick="cambiarEstado('${turno.Numero_Turno}', 'Cancelado')" title="Cancelar">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </td>
                            `;
                            queueBody.appendChild(row);
                        });
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = '<td colspan="4" style="text-align: center; padding: 20px; color: #999;">No hay turnos en espera</td>';
                        queueBody.appendChild(row);
                    }

                    // Actualizar turno actual
                    if (data.data.turnos_atendiendo && data.data.turnos_atendiendo.length > 0) {
                        const turno = data.data.turnos_atendiendo[0];
                        document.getElementById('turno-actual').innerText = turno.Numero_Turno;
                        document.getElementById('turn-status').innerText = 'Atendiendo';
                    } else {
                        document.getElementById('turno-actual').innerText = '-';
                        document.getElementById('turn-status').innerText = 'Disponible';
                    }

                    // Actualizar estadísticas
                    if (data.data.estadisticas) {
                        document.getElementById('stat-espera').innerText = data.data.estadisticas.turnos_espera || 0;
                        document.getElementById('stat-atendiendo').innerText = data.data.estadisticas.turnos_atendiendo || 0;
                        document.getElementById('stat-finalizados').innerText = data.data.estadisticas.turnos_finalizados || 0;
                        document.getElementById('stat-cancelados').innerText = data.data.estadisticas.turnos_cancelados || 0;
                    }
                }
            } catch (error) {
                console.error('Error al recargar datos:', error);
            }
        }

        function mostrarNotificacion(mensaje, tipo = 'info') {
            const notif = document.getElementById('notification');
            notif.innerText = mensaje;
            notif.className = 'notification show ' + tipo;
            setTimeout(() => {
                notif.classList.remove('show');
            }, 3000);
        }

        function reproducirSonido() {
            // Crear un sonido simple (beep)
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscilador = audioContext.createOscillator();
            const volumen = audioContext.createGain();

            oscilador.connect(volumen);
            volumen.connect(audioContext.destination);

            oscilador.frequency.value = 800;
            oscilador.type = 'sine';

            volumen.gain.setValueAtTime(0.3, audioContext.currentTime);
            volumen.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscilador.start(audioContext.currentTime);
            oscilador.stop(audioContext.currentTime + 0.5);
        }
    </script>
</body>
</html>
