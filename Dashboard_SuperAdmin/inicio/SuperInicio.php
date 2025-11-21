<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que sea Super Admin
if ($user_rol !== 'Super Admin') {
    header('Location: ../../Login/inicioSecion.php');
    exit;
}

// Obtener fecha actual
$hoy = date('Y-m-d');

// Conexión BD
require_once '../../Base de Datos/conexion.php';
$conexion = new Conexion();
$conexion->abrir_conexion();
$mysqli = $conexion->conexion;

/*obtener turno actual */
$query_actual = $mysqli->prepare("
    SELECT Numero_Turno, Tipo, Estado, Fecha
    FROM turnos 
    WHERE Estado = 'Atendiendo'
    AND Usuario_ID = ?
    ORDER BY Fecha DESC 
    LIMIT 1
");
$query_actual->bind_param("i", $user_id);
$query_actual->execute();
$turno_actual = $query_actual->get_result()->fetch_assoc();


/*obtener turno siguiente*/
$query_siguiente = $mysqli->prepare("
    SELECT Numero_Turno, Tipo, Estado, Fecha
    FROM turnos 
    WHERE Estado = 'Espera'
    ORDER BY Fecha ASC 
    LIMIT 1
");
$query_siguiente->execute();
$siguiente_turno = $query_siguiente->get_result()->fetch_assoc();



// Cerrar conexión
$conexion->cerrar_conexion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta  name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vision-Clara superAdmin </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href='SuperInicio.css'>
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'> 
    <link rel="stylesheet" href='../../estilos/translator.css'>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript" src="../../js/translator.js"></script>
</head>
</head>
<body>

<!--Header importado-->
<?php include('../Dashboard/SuperSidebar.php'); ?> 

<section class="contenedor-principal">
    <div class="recuadro-sa">
        <div class="formato-txt">
        <h2>Vision Clara le da la bienvenida <?php echo htmlspecialchars($user_nombre); ?></h2>    
        <p>Nuestro deber es apoyar al cliente a ver el mundo de una forma asombrosa!!<p>
        </div>
        <div class="sadmin-img">    
            <img src="../../Imagenes/imagen_inicio.png" alt="Admin Ilustracion">
        </div> 
    </div>

    <div class="contenedor-turno">
        <div class="columna">
            <h3>Turno Actual</h3>

        <?php if ($turno_actual): ?>
            <div class="numero-turno">
                <?= htmlspecialchars($turno_actual['Numero_Turno']); ?>
            </div>
            <div class="tipo-turno">
                <?= htmlspecialchars($turno_actual['Tipo']); ?>
            </div>
        <?php else: ?>
            <div class="sin-turno">No hay turno en atención</div>
        <?php endif; ?>
    </div>
        <div class="separador"></div>

    <!-- SIGUIENTE TURNO -->
    <div class="columna">
        <h3>Siguiente</h3>

        <?php if ($siguiente_turno): ?>
            <div class="numero-turno">
                <?= htmlspecialchars($siguiente_turno['Numero_Turno']); ?>
            </div>
            <div class="tipo-turno">
                <?= htmlspecialchars($siguiente_turno['Tipo']); ?>
            </div>
        <?php else: ?>
            <div class="sin-turno">No hay turnos en espera</div>
        <?php endif; ?>
        </div>
    </div>
    
     <!-- Imagen decorativa inferior -->
    <div class="illustration">
        <div class="character"></div>
    </div>
</body>