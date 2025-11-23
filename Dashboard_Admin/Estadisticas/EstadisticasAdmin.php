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

// Obtener semana seleccionada (por defecto: semana actual)
$semana_offset = isset($_GET['semana']) ? (int)$_GET['semana'] : 0;
$fecha_base = new DateTime();
$fecha_base->modify("$semana_offset week");

// Calcular inicio y fin de la semana (Lunes a Domingo)
$inicio_semana = clone $fecha_base;
$inicio_semana->modify('monday this week');
$fin_semana = clone $inicio_semana;
$fin_semana->modify('+6 days');

// Verificar que la conexión esté activa
if (!$conexion) {
    die("Error de conexión a la base de datos");
}

// Total de clientes
try {
    $query_clientes = "SELECT COUNT(*) AS total_clientes FROM clientes";
    $result_clientes = mysqli_query($conexion, $query_clientes);
    if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
        $row_clientes = mysqli_fetch_assoc($result_clientes);
        $total_clientes = (int)($row_clientes['total_clientes'] ?? 0);
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
        if ($result_turnos && $result_turnos->num_rows > 0) {
            $row_turnos = $result_turnos->fetch_assoc();
            $total_turnos = (int)($row_turnos['total_turnos'] ?? 0);
        }
        $stmt_turnos->close();
    }
} catch (Exception $e) {
    error_log("Excepción en turnos: " . $e->getMessage());
    $total_turnos = 0;
}

// Total de productos
try {
    $query_productos = "SELECT COUNT(*) AS total_productos FROM productos";
    $result_productos = mysqli_query($conexion, $query_productos);
    if ($result_productos && mysqli_num_rows($result_productos) > 0) {
        $row_productos = mysqli_fetch_assoc($result_productos);
        $total_productos = (int)($row_productos['total_productos'] ?? 0);
    }
} catch (Exception $e) {
    error_log("Excepción en productos: " . $e->getMessage());
    $total_productos = 0;
}

// Obtener turnos por día de la semana
$turnos_semana = [];
$dias_nombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$max_turnos = 0;

try {
    $fecha_actual = clone $inicio_semana;
    for ($i = 0; $i < 7; $i++) {
        $fecha_str = $fecha_actual->format('Y-m-d');
        
        $query_dia = "SELECT COUNT(*) AS total FROM turnos WHERE DATE(fecha) = ?";
        $stmt_dia = $conexion->prepare($query_dia);
        if ($stmt_dia) {
            $stmt_dia->bind_param("s", $fecha_str);
            $stmt_dia->execute();
            $result_dia = $stmt_dia->get_result();
            $row_dia = $result_dia->fetch_assoc();
            $total_dia = (int)($row_dia['total'] ?? 0);
            
            $turnos_semana[] = [
                'dia' => $dias_nombres[$i],
                'fecha' => $fecha_actual->format('d/m'),
                'total' => $total_dia
            ];
            
            if ($total_dia > $max_turnos) {
                $max_turnos = $total_dia;
            }
            
            $stmt_dia->close();
        }
        
        $fecha_actual->modify('+1 day');
    }
} catch (Exception $e) {
    error_log("Error obteniendo turnos de la semana: " . $e->getMessage());
}

// Ajustar max_turnos para mejor visualización
$max_turnos = max($max_turnos, 10); // Mínimo 10 para mejor escala

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
            <h1><i class="fas fa-chart-bar"></i> Estadísticas Generales</h1>
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
        </div>

        <!-- Gráfica de Turnos por Semana -->
        <div class="grafica-container">
            <div class="grafica-header">
                <h2><i class="fas fa-calendar-week"></i> Turnos de la Semana</h2>
                <div class="semana-selector">
                    <button onclick="cambiarSemana(-1)" class="btn-semana">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                    <span class="semana-actual">
                        <?php echo $inicio_semana->format('d/m/Y') . ' - ' . $fin_semana->format('d/m/Y'); ?>
                    </span>
                    <button onclick="cambiarSemana(1)" class="btn-semana">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="grafica-barras">
                <?php foreach ($turnos_semana as $index => $dia): ?>
                    <?php 
                        $porcentaje = $max_turnos > 0 ? ($dia['total'] / $max_turnos) * 100 : 0;
                        $color_class = 'barra-color-' . ($index + 1);
                    ?>
                    <div class="barra-row">
                        <div class="barra-label">
                            <span class="dia-nombre"><?php echo $dia['dia']; ?></span>
                            <span class="dia-fecha"><?php echo $dia['fecha']; ?></span>
                        </div>
                        <div class="barra-container">
                            <div class="barra-fill <?php echo $color_class; ?>" 
                                 style="width: <?php echo $porcentaje; ?>%"
                                 data-turnos="<?php echo $dia['total']; ?>">
                                <span class="barra-valor"><?php echo $dia['total']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        let semanaActual = <?php echo $semana_offset; ?>;
        
        function cambiarSemana(offset) {
            semanaActual += offset;
            window.location.href = `?semana=${semanaActual}`;
        }

        // Animar las barras al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const barras = document.querySelectorAll('.barra-fill');
            barras.forEach((barra, index) => {
                setTimeout(() => {
                    barra.style.opacity = '1';
                    barra.style.transform = 'scaleX(1)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>