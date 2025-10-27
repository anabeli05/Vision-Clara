<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que NO sea Super Admin (puede ser Admin, Usuario, etc.)
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/inicio/SuperInicio.php');
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Crear conexión
$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();
$conexion = $conexion_obj->conexion;

// Inicializar variables con valores por defecto
$total_clientes = 0;
$total_turnos = 0;
$total_productos = 0;
$hoy = date('Y-m-d');

// Total de clientes
try {
    $query_clientes = "SELECT COUNT(*) AS total_clientes FROM clientes";
    $result_clientes = mysqli_query($conexion, $query_clientes);
    if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
        $row_clientes = mysqli_fetch_assoc($result_clientes);
        $total_clientes = $row_clientes['total_clientes'] ?? 0;
    }
} catch (Exception $e) {
    $total_clientes = 0;
}

// Total de turnos hoy

try {
    $query_turnos = "SELECT COUNT(*) AS total_turnos FROM turnos WHERE DATE(fecha) = ?";
    $stmt_turnos = $conexion->prepare($query_turnos);
    $stmt_turnos->bind_param("s", $hoy);
    $stmt_turnos->execute();
    $result_turnos = $stmt_turnos->get_result();
    if ($result_turnos && $result_turnos->num_rows > 0) {
        $row_turnos = $result_turnos->fetch_assoc();
        $total_turnos = $row_turnos['total_turnos'] ?? 0;
    }
} catch (Exception $e) {
    $total_turnos = 0;
}

// Total de productos
try {
    $query_productos = "SELECT COUNT(*) AS total_productos FROM productos";
    $result_productos = mysqli_query($conexion, $query_productos);
    if ($result_productos && mysqli_num_rows($result_productos) > 0) {
        $row_productos = mysqli_fetch_assoc($result_productos);
        $total_productos = $row_productos['total_productos'] ?? 0;
    }
} catch (Exception $e) {
    $total_productos = 0;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href='../Estadisticas/EstadisticasAdmin.css'>
    <link rel="stylesheet" href='../Dashboard/SidebarAdmin.css'>
</head>
<body>
    <?php include '../Dashboard/SidebarAdmin.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1> Estadísticas Generales</h1>
        </div>

        <div class="estadisticas-grid">
            <div class="estadistica-card">
                <h2>Total de Clientes Registrados</h2>
                <p><?php echo $total_clientes; ?></p>
            </div>
            <div class="estadistica-card">
                <h2>Turnos del Día<br><small><?php echo $hoy; ?></small></h2>
                <p><?php echo $total_turnos; ?></p>
            </div>
            <div class="estadistica-card">
                <h2>Productos en Inventario</h2>
                <p><?php echo $total_productos; ?></p>
            </div>
        </div>
    </div>
</body>
</html>