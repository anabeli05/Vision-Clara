<<<<<<< Updated upstream
<?php 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadisticas - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Estadisticas.css">
    <link rel="stylesheet" href="../Dashboard/sidebar.css">
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <i class="fas fa-chart-bar" data-no-translate></i> 
            <h1>Estadisticas</h1>
        </div>

        <!-- Gráfico de Barras Horizontales -->
        <div class="contenedor-grafico">
            <div class="grafico-barras">
                <!-- Barra 1 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 95%"></div>
                    </div>
                    <span class="porcentaje">95%</span>
                </div>
                
                <!-- Barra 2 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 80%"></div>
                    </div>
                    <span class="porcentaje">80%</span>
                </div>
                
                <!-- Barra 3 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 70%"></div>
                    </div>
                    <span class="porcentaje">70%</span>
                </div>
                
                <!-- Barra 4 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 50%"></div>
                    </div>
                    <span class="porcentaje">50%</span>
                </div>
                
                <!-- Barra 5 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 30%"></div>
                    </div>
                    <span class="porcentaje">30%</span>
                </div>
                
                <!-- Barra 6 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 15%"></div>
                    </div>
                    <span class="porcentaje">15%</span>
                </div>
                
                <!-- Barra 7 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 35%"></div>
                    </div>
                    <span class="porcentaje">35%</span>
                </div>
                
                <!-- Barra 8 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 20%"></div>
                    </div>
                    <span class="porcentaje">20%</span>
                </div>
            </div>

            <!-- Escala de porcentajes -->
            <div class="escala-porcentajes">
                <span>0%</span>
                <span>10%</span>
                <span>20%</span>
                <span>30%</span>
                <span>40%</span>
                <span>50%</span>
                <span>60%</span>
                <span>70%</span>
                <span>80%</span>
                <span>90%</span>
                <span>100%</span>
            </div>
        </div>
    </div>
</body>
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

// Inicializar variables para estadísticas
$total_clientes = 0;
$total_turnos = 0;
$turnos_hoy = 0;
$error = '';

try {
    // Obtener total de clientes
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM clientes");
    $stmt->execute();
    $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener total de turnos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM turnos");
    $stmt->execute();
    $total_turnos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener turnos de hoy
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM turnos WHERE DATE(Fecha_Hora) = CURDATE()");
    $stmt->execute();
    $turnos_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch(PDOException $e) {
    $error = "Error al cargar estadísticas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadisticas - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Estadisticas.css">
    <link rel="stylesheet" href="../Dashboard/sidebar.css">
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-chart-bar" data-no-translate></i> Estadísticas</h1>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Tarjetas de estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Clientes</h3>
                    <p class="stat-number"><?php echo number_format($total_clientes); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Turnos</h3>
                    <p class="stat-number"><?php echo number_format($total_turnos); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <h3>Turnos Hoy</h3>
                    <p class="stat-number"><?php echo number_format($turnos_hoy); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
>>>>>>> Stashed changes
</html>