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

// Debug: mostrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inicializar variables
$producto = null;
$error = '';
$success = '';

// Obtener ID del producto
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: SuperProducto.php');
    exit;
}

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        $stock = intval($_POST['stock']);
        
        // Validaciones
        if (empty($nombre)) {
            throw new Exception("El nombre del producto es obligatorio");
        }
        if ($precio <= 0) {
            throw new Exception("El precio debe ser mayor a 0");
        }
        if ($stock < 0) {
            throw new Exception("El stock no puede ser negativo");
        }

        // Manejar la imagen si se sube una nueva
        $imagen_url = $_POST['imagen_actual']; // Mantener la imagen actual por defecto
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $directorio_destino = '../../uploads/productos/';
            
            // Crear directorio si no existe
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            // Validar tipo de archivo
            $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $tipo_archivo = $_FILES['imagen']['type'];
            
            if (!in_array($tipo_archivo, $tipos_permitidos)) {
                throw new Exception("Solo se permiten imágenes (JPG, PNG, GIF, WEBP)");
            }

            // Validar tamaño (máximo 5MB)
            if ($_FILES['imagen']['size'] > 5242880) {
                throw new Exception("La imagen no debe superar los 5MB");
            }

            // Generar nombre único
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = 'producto_' . $product_id . '_' . time() . '.' . $extension;
            $ruta_completa = $directorio_destino . $nombre_archivo;

            // Mover archivo
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
                // Eliminar imagen anterior si existe y no es la default
                if (!empty($_POST['imagen_actual']) && 
                    $_POST['imagen_actual'] !== 'uploads/productos/default-glasses.jpg' &&
                    file_exists('../../' . $_POST['imagen_actual'])) {
                    unlink('../../' . $_POST['imagen_actual']);
                }
                $imagen_url = 'uploads/productos/' . $nombre_archivo;
            } else {
                throw new Exception("Error al subir la imagen");
            }
        }

        // Actualizar producto en la base de datos
        $stmt = $conn->prepare("UPDATE productos SET 
            Nombre = :nombre,
            Descripcion = :descripcion,
            Precio = :precio,
            Stock = :stock,
            Imagen_URL = :imagen_url,
            Fecha_Actualizacion = NOW()
            WHERE ID_Producto = :id");
        
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':imagen_url', $imagen_url);
        $stmt->bindParam(':id', $product_id);
        
        if ($stmt->execute()) {
            $success = "Producto actualizado correctamente";
            // Recargar datos del producto
            $stmt = $conn->prepare("SELECT * FROM productos WHERE ID_Producto = :id");
            $stmt->bindParam(':id', $product_id);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            throw new Exception("Error al actualizar el producto");
        }
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener datos del producto
try {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE ID_Producto = :id");
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        header('Location: SuperProducto.php');
        exit;
    }
} catch(PDOException $e) {
    $error = "Error al cargar el producto: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Dashboard/SuperSidebar.css">
    <link rel="stylesheet" href="editar-producto.css">
</head>
<body>
    <?php include '../Dashboard/SuperSidebar.php'; ?>
    
    <div class="contenedor-principal">
        <!-- Header -->
        <div class="header">
            <h1>
                <i class="fas fa-edit"></i>
                Editar Producto
            </h1>
        </div>

        <!-- Mensajes -->
        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <?php if($producto): ?>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($producto['Imagen_URL'] ?? ''); ?>">
                
                <div class="form-grid">
                    <!-- Nombre -->
                    <div class="form-group">
                        <label for="nombre">
                            <i class="fas fa-tag"></i>
                            Nombre del Producto
                        </label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            value="<?php echo htmlspecialchars($producto['Nombre']); ?>"
                            required 
                            maxlength="200"
                            placeholder="Ej: Gafas de Sol Ray-Ban Aviator">
                    </div>

                    <!-- Descripción -->
                    <div class="form-group">
                        <label for="descripcion">
                            <i class="fas fa-align-left"></i>
                            Descripción
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            placeholder="Describe las características del producto..."><?php echo htmlspecialchars($producto['Descripcion'] ?? ''); ?></textarea>
                    </div>

                    <!-- Precio -->
                    <div class="form-group">
                        <label for="precio">
                            <i class="fas fa-dollar-sign"></i>
                            Precio
                        </label>
                        <input 
                            type="number" 
                            id="precio" 
                            name="precio" 
                            value="<?php echo htmlspecialchars($producto['Precio']); ?>"
                            step="0.01" 
                            min="0.01" 
                            required
                            placeholder="0.00">
                    </div>

                    <!-- Stock -->
                    <div class="form-group">
                        <label for="stock">
                            <i class="fas fa-boxes"></i>
                            Stock
                        </label>
                        <input 
                            type="number" 
                            id="stock" 
                            name="stock" 
                            value="<?php echo htmlspecialchars($producto['Stock']); ?>"
                            min="0" 
                            required
                            placeholder="0">
                    </div>

                    <!-- Imagen Actual -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-image"></i>
                            Imagen Actual
                        </label>
                        <div class="image-preview-container">
                            <?php 
                                $rutaImagen = !empty($producto['Imagen_URL']) ? '../../' . $producto['Imagen_URL'] : '../../uploads/productos/default-glasses.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($rutaImagen); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['Nombre']); ?>" 
                                 class="image-preview"
                                 id="currentImage"
                                 onerror="this.src='../../uploads/productos/default-glasses.jpg'">
                            <div class="current-image-info">
                                <i class="fas fa-info-circle"></i>
                                Imagen actual del producto
                            </div>
                        </div>
                    </div>

                    <!-- Nueva Imagen -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-upload"></i>
                            Cambiar Imagen (Opcional)
                        </label>
                        <div class="file-input-wrapper">
                            <input 
                                type="file" 
                                id="imagen" 
                                name="imagen" 
                                accept="image/*"
                                onchange="previewNewImage(event)">
                            <label for="imagen" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Seleccionar nueva imagen
                            </label>
                        </div>
                        <div class="image-preview-container" id="newImagePreview" style="display: none; margin-top: 15px;">
                            <img src="" alt="Vista previa" class="image-preview" id="newImage">
                            <div class="current-image-info">
                                <i class="fas fa-eye"></i>
                                Vista previa de la nueva imagen
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                    <a href="SuperProducto.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Vista previa de nueva imagen
        function previewNewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('newImage').src = e.target.result;
                    document.getElementById('newImagePreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        // Validación del formulario
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const precio = parseFloat(document.getElementById('precio').value);
            const stock = parseInt(document.getElementById('stock').value);

            if (!nombre) {
                e.preventDefault();
                alert('Por favor ingresa el nombre del producto');
                return;
            }

            if (precio <= 0) {
                e.preventDefault();
                alert('El precio debe ser mayor a 0');
                return;
            }

            if (stock < 0) {
                e.preventDefault();
                alert('El stock no puede ser negativo');
                return;
            }
        });

        // Mensaje de confirmación si hay cambios sin guardar
        let formModified = false;
        const formInputs = document.querySelectorAll('#editForm input, #editForm textarea');
        
        formInputs.forEach(input => {
            input.addEventListener('change', () => {
                formModified = true;
            });
        });

        window.addEventListener('beforeunload', (e) => {
            if (formModified) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.getElementById('editForm').addEventListener('submit', () => {
            formModified = false;
        });
    </script>
</body>
</html>