<?php
session_start();
include("../conexion.php");

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Conexión
$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();
$conexion = $conexion_obj->conexion;

// Contar clientes
$result_clientes = $conexion->query("SELECT COUNT(*) as total FROM clientes");
$total_clientes = $result_clientes->fetch_assoc()['total'];

// Contar turnos de hoy
$result_turnos = $conexion->query("SELECT COUNT(*) as total FROM turnos WHERE DATE(fecha) = CURDATE()");
$total_turnos_hoy = $result_turnos->fetch_assoc()['total'];

// Contar productos
$result_productos = $conexion->query("SELECT COUNT(*) as total FROM productos");
$total_productos = $result_productos->fetch_assoc()['total'];

// Obtener nombre de usuario de forma segura
$nombre_usuario = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../css/estilo_admin.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php" class="active">🏠 Inicio</a></li>
            <li><a href="clientes.php">👥 Clientes</a></li>
            <li><a href="turnos.php">📅 Turnos</a></li>
            <li><a href="productos.php">🛍️ Productos</a></li>
            <li><a href="estadisticas.php">📊 Estadísticas</a></li>
            <li><a href="logout.php">🚪 Cerrar sesión</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Bienvenido, <?php echo $nombre_usuario; ?> 👋</h1>
        <p>Resumen general del sistema de la óptica</p>

        <div class="dashboard-cards">
            <div class="card card-clientes">
                <p>Clientes Registrados</p>
                <h2><?php echo $total_clientes; ?></h2>
            </div>
            <div class="card card-turnos">
                <p>Turnos de Hoy</p>
                <h2><?php echo $total_turnos_hoy; ?></h2>
            </div>
            <div class="card card-productos">
                <p>Productos en Inventario</p>
                <h2><?php echo $total_productos; ?></h2>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-item">
                <div class="stat-value">100%</div>
                <div class="stat-label">Sistema Activo</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Disponibilidad</div>
            </div>
        </div>
    </main>
</div>
</body>
</html>