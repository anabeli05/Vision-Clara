<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
//require_once '../../Login/check_session.php';

// Verificar que sea Super Admin
//if ($user_rol !== 'Super Admin') {
//    header('Location: ../../Login/inicioSecion.php');
//    exit;
//}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inicializar variables
$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token de seguridad inválido";
    } else {
        $nombre = trim($_POST['Nombre'] ?? '');
        $descripcion = trim($_POST['Descripcion'] ?? '');
        $precio = trim($_POST['Precio'] ?? '');
        $stock = intval($_POST['Stock'] ?? 0);
        $imagen_url = '';
        
        // Procesar imagen subida
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/productos/';
            
            // Crear directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid('producto_') . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_path)) {
                    $imagen_url = 'uploads/productos/' . $new_filename;
                }
            }
        }
        
        // Validaciones básicas
        if (empty($nombre) || empty($precio)) {
            $error = "Nombre y precio son obligatorios";
        } elseif (!is_numeric($precio) || $precio <= 0) {
            $error = "El precio debe ser un número mayor a 0";
        } else {
            try {
                // Insertar nuevo producto
                $stmt = $conn->prepare("INSERT INTO productos (Nombre, Descripcion, Precio, Stock, Imagen_URL, Activo) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen_url]);
                
                $success = "Producto agregado exitosamente";
                // Limpiar el formulario
                $_POST = [];
            } catch(PDOException $e) {
                $error = "Error al agregar el producto: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="SuperAgregarP.css">
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'> 

</head>
<body>

    <?php include '../Dashboard/SuperSidebar.php'; ?>
    <div class="contenedor-principal">
        
        <?php if(isset($error) && $error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($success) && $success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" enctype="multipart/form-data" class="product-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="agregar" value="1">

            <div class="form-section">
                <label class="form-label">Nombre del Producto *</label>
                <input type="text" name="Nombre" class="form-input" placeholder="Ej: Gafas Ray-Ban Aviator" required
                       value="<?php echo htmlspecialchars($_POST['Nombre'] ?? ''); ?>">
            </div>

            <div class="form-section">
                <label class="form-label">Imagen del producto</label>
                <input type="file" name="imagen" id="imagen" accept="image/*" class="form-input" style="padding: 8px;">
                <small style="color: #0277BD;">Formatos permitidos: JPG, PNG, GIF, WEBP</small>
            </div>

            <div class="form-section">
                <label class="form-label">Descripción del Producto</label>
                <textarea name="Descripcion" class="form-input" placeholder="Ingrese la descripción del producto..." rows="3"><?php echo htmlspecialchars($_POST['Descripcion'] ?? ''); ?></textarea>
            </div>

            <div class="form-section">
                <label class="form-label">Precio del Producto *</label>
                <input type="number" name="Precio" class="form-input" placeholder="0.00" step="0.01" min="0" required
                       value="<?php echo htmlspecialchars($_POST['Precio'] ?? ''); ?>">
            </div>

            <div class="form-section">
                <label class="form-label">Stock</label>
                <input type="number" name="Stock" class="form-input" placeholder="0" min="0"
                       value="<?php echo htmlspecialchars($_POST['Stock'] ?? '0'); ?>">
            </div>

            <!-- Botón agregar -->
            <button type="submit" class="btn-add-product">
                <i class="fas fa-plus"></i> Añadir Producto
            </button>
        </form>
    </div>

</body> 
</html>