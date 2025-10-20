<?php
include("../conexion.php");
session_start();

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit;
}

$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();
$conexion = $conexion_obj->conexion;

// Inicializar variables
$result_turnos = null;
$total_turnos = 0;
$total_paginas = 0;
$por_pagina = 10;

// Marcar un turno como atendido
if (isset($_GET['atendido'])) {
    $id_turno = intval($_GET['atendido']);
    try {
        $stmt = $conexion->prepare("UPDATE turnos SET atendido = 1 WHERE id = ?");
        $stmt->bind_param("i", $id_turno);
        $stmt->execute();
        header("Location: turnos.php");
        exit;
    } catch (Exception $e) {
        $error = "Error al marcar el turno como atendido: " . $e->getMessage();
    }
}

// Filtros
$tipo_filtro = $_GET['tipo'] ?? 'Todos';
$fecha_filtro = $_GET['fecha'] ?? '';

// Construir WHERE de forma segura
$where = [];
$params = [];
$types = '';

if ($tipo_filtro !== 'Todos') {
    $where[] = "tipo = ?";
    $params[] = $tipo_filtro;
    $types .= 's';
}

if (!empty($fecha_filtro)) {
    $where[] = "DATE(fecha) = ?";
    $params[] = $fecha_filtro;
    $types .= 's';
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Paginación
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina - 1) * $por_pagina;

try {
    // Contar total de turnos
    $count_query = "SELECT COUNT(*) AS total FROM turnos $where_sql";
    if ($where_sql) {
        $stmt_count = $conexion->prepare($count_query);
        if ($types) $stmt_count->bind_param($types, ...$params);
        $stmt_count->execute();
        $result_total = $stmt_count->get_result();
    } else {
        $result_total = mysqli_query($conexion, $count_query);
    }
    
    if ($result_total) {
        $total_data = mysqli_fetch_assoc($result_total);
        $total_turnos = $total_data['total'] ?? 0;
        $total_paginas = ceil($total_turnos / $por_pagina);
    }

    // Obtener turnos paginados
    $query_turnos = "SELECT * FROM turnos $where_sql ORDER BY fecha DESC LIMIT ?, ?";
    $params[] = $inicio;
    $params[] = $por_pagina;
    $types .= 'ii';
    
    $stmt_turnos = $conexion->prepare($query_turnos);
    if ($types) $stmt_turnos->bind_param($types, ...$params);
    $stmt_turnos->execute();
    $result_turnos = $stmt_turnos->get_result();
    
} catch (Exception $e) {
    $error = "Error en la consulta: " . $e->getMessage();
    $result_turnos = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Turnos - Dashboard</title>
    <link rel="stylesheet" href="../css/estilo_admin.css">
    <link rel="stylesheet" href="../css/turnos.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">🏠 Inicio</a></li>
                <li><a href="clientes.php">👥 Clientes</a></li>
                <li><a href="turnos.php" class="active">📅 Turnos</a></li>
                <li><a href="productos.php">🛍️ Productos</a></li>
                <li><a href="estadisticas.php">📊 Estadísticas</a></li>
                <li><a href="logout.php">🚪 Cerrar sesión</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>📅 Gestión de Turnos</h1>

            <!-- Mostrar errores -->
            <?php if(isset($error)): ?>
                <div style="background: #fef2f2; color: #dc2626; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fecaca;">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de filtros -->
            <form class="filtros" method="GET">
                <label>
                    Tipo de Turno:
                    <select name="tipo">
                        <option value="Todos" <?php if($tipo_filtro=='Todos') echo 'selected'; ?>>Todos los turnos</option>
                        <option value="Cliente" <?php if($tipo_filtro=='Cliente') echo 'selected'; ?>>Clientes</option>
                        <option value="Visitante" <?php if($tipo_filtro=='Visitante') echo 'selected'; ?>>Visitantes</option>
                    </select>
                </label>

                <label>
                    Fecha específica:
                    <input type="date" name="fecha" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
                </label>

                <button type="submit">🔍 Filtrar</button>
                <a href="turnos.php">🔄 Restablecer</a>
            </form>

            <!-- Tabla de turnos -->
            <?php if(!$result_turnos): ?>
                <p>❌ Error al cargar los turnos. Por favor, intente nuevamente.</p>
            <?php elseif($result_turnos->num_rows == 0): ?>
                <p>📭 No hay turnos que coincidan con los filtros seleccionados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Número</th>
                            <th>Fecha y Hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($turno = $result_turnos->fetch_assoc()): ?>
                        <tr class="<?php echo $turno['atendido'] ? 'atendido' : ''; ?>">
                            <td><?php echo $turno['id']; ?></td>
                            <td>
                                <span style="font-weight: 600; color: <?php echo $turno['tipo'] == 'Cliente' ? '#059669' : '#d97706'; ?>">
                                    <?php echo $turno['tipo']; ?>
                                </span>
                            </td>
                            <td style="font-weight: 700; font-size: 1.1em;"><?php echo $turno['numero']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($turno['fecha'])); ?></td>
                            <td>
                                <?php if($turno['atendido']): ?>
                                    <span style="color: #059669;">✅ Atendido</span>
                                <?php else: ?>
                                    <span style="color: #dc2626;">⏳ Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!$turno['atendido']): ?>
                                    <a href="turnos.php?atendido=<?php echo $turno['id']; ?>" onclick="return confirm('¿Marcar este turno como atendido?');">
                                        ✅ Marcar atendido
                                    </a>
                                <?php else: ?>
                                    <span style="color: #666; font-style: italic;">Completado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <?php if($total_paginas > 1): ?>
                <div class="paginacion">
                    <?php for($i=1;$i<=$total_paginas;$i++): ?>
                        <a class="<?php echo $i==$pagina?'active':''; ?>" href="turnos.php?pagina=<?php echo $i; ?>&tipo=<?php echo urlencode($tipo_filtro); ?>&fecha=<?php echo urlencode($fecha_filtro); ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>