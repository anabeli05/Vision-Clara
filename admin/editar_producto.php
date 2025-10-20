<?php
include("../conexion.php");
session_start();

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Crear conexión
$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();
$conexion = $conexion_obj->conexion;

// Obtener el producto por ID
if (!isset($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$id_producto = intval($_GET['id']);
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: productos.php");
    exit;
}

$producto = $result->fetch_assoc();

// Manejar actualización de datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);

    if (empty($nombre) || empty($descripcion) || empty($precio)) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {
        $stmt_update = $conexion->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ? WHERE id = ?");
        $stmt_update->bind_param("ssdi", $nombre, $descripcion, $precio, $id_producto);

        if ($stmt_update->execute()) {
            $actualizado = true;
            $producto['nombre'] = $nombre;
            $producto['descripcion'] = $descripcion;
            $producto['precio'] = $precio;
        } else {
            $error = "❌ Error al actualizar el producto.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="../css/estilo_admin.css">
    <link rel="stylesheet" href="../css/editar_producto.css">
    <link rel="stylesheet" href="../css/productos.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a href="clientes.php">Clientes</a></li>
                <li><a href="turnos.php">Turnos</a></li>
                <li><a href="productos.php" class="active">Productos</a></li>
                <li><a href="estadisticas.php">Estadísticas</a></li>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Editar Producto</h1>

            <form method="POST">
                <input type="text" name="nombre" placeholder="Nombre del producto" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                <textarea name="descripcion" placeholder="Descripción" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                <input type="number" step="0.01" name="precio" placeholder="Precio" value="<?php echo $producto['precio']; ?>" required>
                <button type="submit">Guardar Cambios</button>
            </form>

            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
