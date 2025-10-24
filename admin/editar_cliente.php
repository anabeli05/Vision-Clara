<?php
include("../conexion.php");
session_start();

// 🔐 Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit;
}

$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();
$conexion = $conexion_obj->conexion;

// 🚫 Verificar que se reciba el número de afiliado
if (!isset($_GET['numero_afiliado'])) {
    header("Location: clientes.php");
    exit;
}

$numero_afiliado = intval($_GET['numero_afiliado']);
$stmt = $conexion->prepare("SELECT * FROM clientes WHERE numero_afiliado=?");
$stmt->bind_param("i", $numero_afiliado);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows != 1) {
    header("Location: clientes.php");
    exit;
}
$cliente = $result->fetch_assoc();

$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    // ✅ Validaciones
    if(empty($nombre)) $errores['nombre'] = "El nombre es obligatorio.";
    if(empty($apellido)) $errores['apellido'] = "El apellido es obligatorio.";
    if(empty($telefono)) $errores['telefono'] = "El teléfono es obligatorio.";
    elseif(!preg_match("/^[0-9]{7,15}$/",$telefono)) $errores['telefono'] = "Teléfono inválido (7-15 dígitos).";
    if(empty($email)) $errores['email'] = "El email es obligatorio.";
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errores['email'] = "Email inválido.";

    // 🔍 Verificar duplicado de email en otro cliente
    if(empty($errores['email'])){
        $stmt_check = $conexion->prepare("SELECT numero_afiliado FROM clientes WHERE email=? AND numero_afiliado!=?");
        $stmt_check->bind_param("si", $email, $numero_afiliado);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if($res_check->num_rows>0) $errores['email'] = "El email ya está registrado en otro cliente.";
    }

    // ✅ Actualizar si no hay errores
    if(empty($errores)){
        $stmt_update = $conexion->prepare("UPDATE clientes SET nombre=?, apellido=?, telefono=?, email=? WHERE numero_afiliado=?");
        $stmt_update->bind_param("ssssi",$nombre,$apellido,$telefono,$email,$numero_afiliado);
        $stmt_update->execute();
        $actualizado = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="../css/estilo_admin.css">
    <link rel="stylesheet" href="../css/editar_cliente.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="clientes.php" class="active">Clientes</a></li>
            <li><a href="turnos.php">Turnos</a></li>
            <li><a href="productos.php">Productos</a></li>
            <li><a href="estadisticas.php">Estadísticas</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Editar Cliente</h1>
        <form method="POST">
            <div class="form-group">
                <label>Número de Afiliado</label>
                <input type="text" value="<?php echo $cliente['numero_afiliado']; ?>" disabled>
            </div>

            <div class="form-group">
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? $cliente['nombre']); ?>">
                <?php if(isset($errores['nombre'])) echo "<p class='error'>{$errores['nombre']}</p>"; ?>
            </div>

            <div class="form-group">
                <input type="text" name="apellido" value="<?php echo htmlspecialchars($_POST['apellido'] ?? $cliente['apellido']); ?>">
                <?php if(isset($errores['apellido'])) echo "<p class='error'>{$errores['apellido']}</p>"; ?>
            </div>

            <div class="form-group">
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? $cliente['telefono']); ?>">
                <?php if(isset($errores['telefono'])) echo "<p class='error'>{$errores['telefono']}</p>"; ?>
            </div>

            <div class="form-group">
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $cliente['email']); ?>">
                <?php if(isset($errores['email'])) echo "<p class='error'>{$errores['email']}</p>"; ?>
            </div>

            <button type="submit">Guardar Cambios</button>
        </form>

        <?php if(isset($actualizado) && $actualizado): ?>
            <div id="modalExito" class="modal">
                <div class="modal-content">
                    <h2>✅ Cliente actualizado</h2>
                    <p>Los datos se guardaron correctamente.</p>
                    <button onclick="cerrarModal()">Aceptar</button>
                </div>
            </div>
            <script src="../js/editar_cliente.js"></script>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
