<?php

// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que sea Super Admin
if ($user_rol !== 'Super Admin') {
    header('Location: ../../Login/inicioSecion.php');
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
$total_usuarios = 0;
$hoy = date('Y-m-d');

// Verificar que la conexión esté activa
if (!$conexion) {
    die("Error de conexión a la base de datos");
}


// Total de clientes
try {
    $query_clientes = "SELECT COUNT(*) AS total_clientes FROM clientes";
    $result_clientes = mysqli_query($conexion, $query_clientes);
    if ($result_clientes) {
        $row_clientes = mysqli_fetch_assoc($result_clientes);
        $total_clientes = (int)$row_clientes['total_clientes'];
    } else {
        error_log("Error en consulta clientes: " . mysqli_error($conexion));
    }
} catch (Exception $e) {
    error_log("Excepción en clientes: " . $e->getMessage());
    $total_clientes = 0;
}

// Total de turnos hoy
try {
    $query_turnos = "SELECT COUNT(*) AS total_turnos FROM turnos WHERE DATE(fecha) = ?";
    $stmt_turnos = $conexion->prepare($query_turnos);
    if ($stmt_turnos) {
        $stmt_turnos->bind_param("s", $hoy);
        $stmt_turnos->execute();
        $result_turnos = $stmt_turnos->get_result();
        if ($result_turnos) {
            $row_turnos = $result_turnos->fetch_assoc();
            $total_turnos = (int)$row_turnos['total_turnos'];
        }
        $stmt_turnos->close();
    } else {
        error_log("Error preparando consulta turnos: " . mysqli_error($conexion));
    }
} catch (Exception $e) {
    error_log("Excepción en turnos: " . $e->getMessage());
    $total_turnos = 0;
}

// Total de productos
try {
    $query_productos = "SELECT COUNT(*) AS total_productos FROM productos";
    $result_productos = mysqli_query($conexion, $query_productos);
    if ($result_productos) {
        $row_productos = mysqli_fetch_assoc($result_productos);
        $total_productos = (int)$row_productos['total_productos'];
    } else {
        error_log("Error en consulta productos: " . mysqli_error($conexion));
    }
} catch (Exception $e) {
    error_log("Excepción en productos: " . $e->getMessage());
    $total_productos = 0;
}

// Total de usuarios/trabajadores
try {
    $query_usuarios = "SELECT COUNT(*) AS total_usuarios FROM usuarios";
    $result_usuarios = mysqli_query($conexion, $query_usuarios);
    if ($result_usuarios) {
        $row_usuarios = mysqli_fetch_assoc($result_usuarios);
        $total_usuarios = (int)$row_usuarios['total_usuarios'];
    } else {
        error_log("Error en consulta usuarios: " . mysqli_error($conexion));
    }
} catch (Exception $e) {
    error_log("Excepción en usuarios: " . $e->getMessage());
    $total_usuarios = 0;
}

// Debug temporal (eliminar después de verificar)
//echo "Clientes: $total_clientes, Turnos: $total_turnos, Productos: $total_productos, Usuarios: $total_usuarios";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href='../Estadisticas/SuperEstadisticas.css'>
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'>
</head>
<body>
    <?php include '../Dashboard/SuperSidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1>📊 Estadísticas Generales</h1>
        </div>

        <div class="estadisticas-grid">
            <div class="estadistica-card">
                <h2>Total de Clientes Registrados</h2>
                <p><?php echo number_format($total_clientes); ?></p>
            </div>
            <div class="estadistica-card">
                <h2>Turnos del Día<br><small><?php echo date('d/m/Y', strtotime($hoy)); ?></small></h2>
                <p><?php echo number_format($total_turnos); ?></p>
            </div>            
            <div class="estadistica-card">
                <h2>Productos en Inventario</h2>
                <p><?php echo number_format($total_productos); ?></p>            
            </div>
            <div class="estadistica-card">
                <h2>Total de Trabajadores</h2>
                <p style="font-size: 48px; color: #333; font-weight: bold;">
                    <?php echo $total_usuarios; 
                        // Debug
                    if ($total_usuarios === 0 || empty($total_usuarios)) {
                        echo " (debug: valor='$total_usuarios')";
                    }?>
                </p>            
            </div>
        </div>
    </div>
</body>
</html>