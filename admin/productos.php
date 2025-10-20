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

// Manejar eliminación de producto
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt_delete = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt_delete->bind_param("i", $id_eliminar);
    $stmt_delete->execute();
    header("Location: productos.php");
    exit;
}

// Manejar agregado de producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_producto'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);

    $stmt_insert = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio) VALUES (?,?,?)");
    $stmt_insert->bind_param("ssd", $nombre, $descripcion, $precio);
    $stmt_insert->execute();
    header("Location: productos.php");
    exit;
}

// Obtener todos los productos
$query_productos = "SELECT * FROM productos";
$result_productos = mysqli_query($conexion, $query_productos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Dashboard</title>
    <link rel="stylesheet" href="../css/estilo_admin.css">
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
            <h1>Gestión de Productos</h1>

            <!-- Formulario para agregar producto -->
            <h2>Agregar Producto</h2>
            <form method="POST">
                <input type="text" name="nombre" placeholder="Nombre del producto" required>
                <textarea name="descripcion" placeholder="Descripción del producto" required></textarea>
                <input type="number" step="0.01" name="precio" placeholder="Precio" required>
                <button type="submit" name="agregar_producto">Agregar Producto</button>
            </form>

            <!-- Lista de productos -->
            <h2>Lista de Productos</h2>
            <table border="1" cellpadding="10" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($producto = mysqli_fetch_assoc($result_productos)) { ?>
                    <tr>
                        <td><?php echo $producto['id']; ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                        <td><?php echo number_format($producto['precio'],2); ?></td>
                        <td>
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>">Editar</a> |
                            <a href="productos.php?eliminar=<?php echo $producto['id']; ?>" onclick="return confirm('¿Seguro que quieres eliminar este producto?');">Eliminar</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </main>
    </div>
</body>
</html>
