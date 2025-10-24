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

$errores = [];

// Agregar cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_cliente'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    // Validaciones
    if (empty($nombre)) $errores['nombre'] = "El nombre es obligatorio.";
    if (empty($apellido)) $errores['apellido'] = "El apellido es obligatorio.";
    if (empty($telefono)) $errores['telefono'] = "El teléfono es obligatorio.";
    elseif (!preg_match("/^[0-9]{7,15}$/", $telefono)) $errores['telefono'] = "Teléfono inválido (7-15 dígitos).";
    if (empty($email)) $errores['email'] = "El email es obligatorio.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores['email'] = "Email inválido.";

    // Verificar duplicado de email
    if (empty($errores['email'])) {
        $stmt_check = $conexion->prepare("SELECT numero_afiliado FROM clientes WHERE email=?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if ($res_check->num_rows > 0) $errores['email'] = "El email ya está registrado.";
    }

    // Insertar cliente si no hay errores
    if (empty($errores)) {
        // Generar número de afiliado automático (6 dígitos)
        $result = $conexion->query("SELECT MAX(numero_afiliado) AS maximo FROM clientes");
        $fila = $result->fetch_assoc();
        $ultimo = intval($fila['maximo'] ?? 0);
        $nuevo_numero = str_pad($ultimo + 1, 6, "0", STR_PAD_LEFT);

        // Insertar cliente
        $stmt_insert = $conexion->prepare("INSERT INTO clientes (numero_afiliado, nombre, apellido, telefono, email) VALUES (?,?,?,?,?)");
        $stmt_insert->bind_param("sssss", $nuevo_numero, $nombre, $apellido, $telefono, $email);
        $stmt_insert->execute();

        header("Location: clientes.php");
        exit;
    }
}

// Eliminar cliente
if (isset($_GET['eliminar'])) {
    $numero_afiliado = $_GET['eliminar'];
    $stmt_delete = $conexion->prepare("DELETE FROM clientes WHERE numero_afiliado=?");
    $stmt_delete->bind_param("s", $numero_afiliado);
    $stmt_delete->execute();
    header("Location: clientes.php");
    exit;
}

// Obtener clientes
$result_clientes = $conexion->query("SELECT * FROM clientes ORDER BY numero_afiliado ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes - Admin</title>
    <link rel="stylesheet" href="../css/estilo_admin.css">
    <link rel="stylesheet" href="../css/clientes.css">
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
        <h1>Gestión de Clientes</h1>

        <form method="POST" class="form-agregar">
            <div class="form-group">
                <input type="text" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                <?php if (isset($errores['nombre'])) echo "<p class='error'>{$errores['nombre']}</p>"; ?>
            </div>
            <div class="form-group">
                <input type="text" name="apellido" placeholder="Apellido" value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>">
                <?php if (isset($errores['apellido'])) echo "<p class='error'>{$errores['apellido']}</p>"; ?>
            </div>
            <div class="form-group">
                <input type="text" name="telefono" placeholder="Teléfono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                <?php if (isset($errores['telefono'])) echo "<p class='error'>{$errores['telefono']}</p>"; ?>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <?php if (isset($errores['email'])) echo "<p class='error'>{$errores['email']}</p>"; ?>
            </div>
            <button type="submit" name="agregar_cliente">Agregar Cliente</button>
        </form>

        <h2>Lista de Clientes</h2>
        <table>
            <thead>
                <tr>
                    <th>Número de Afiliado</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cliente = $result_clientes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $cliente['numero_afiliado']; ?></td>
                    <td><?php echo $cliente['nombre']; ?></td>
                    <td><?php echo $cliente['apellido']; ?></td>
                    <td><?php echo $cliente['telefono']; ?></td>
                    <td><?php echo $cliente['email']; ?></td>
                    <td>
                        <a href="editar_cliente.php?numero_afiliado=<?php echo $cliente['numero_afiliado']; ?>">Editar</a> |
                        <a href="clientes.php?eliminar=<?php echo $cliente['numero_afiliado']; ?>" onclick="return confirm('¿Seguro eliminar?')">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
