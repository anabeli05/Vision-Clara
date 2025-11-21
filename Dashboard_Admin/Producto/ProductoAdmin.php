<?php 
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que NO sea Super Admin (puede ser Admin, Usuario, etc.)
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/inicio/InicioAdmin.php');
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Inicializar variables
$productos = [];
$error = '';
$error_message = '';

// Debug: mostrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Función auxiliar para truncar texto
function truncarDescripcion($texto, $limite = 100) {
    if (strlen($texto) <= $limite) return $texto;
    return substr($texto, 0, $limite) . '...';
}

function formatearPrecio($precio) {
    return '$' . number_format($precio, 2);
}

try {
    // Obtener todos los productos
    $stmt = $conn->prepare("SELECT ID_Producto, Nombre, Descripcion, Precio, Stock, Imagen_URL FROM productos WHERE Activo = 1 ORDER BY Nombre ASC");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error al cargar productos";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="ProductoAdmin.css">
    <link rel="stylesheet" href="../Dashboard/SidebarAdmin.css">
</head>
<body>

    <?php include '../Dashboard/SidebarAdmin.php'; ?>
    
    <div class="contenedor-principal">
        <!-- Header -->
        <div class="header_1">
            <h1><i class="fas fa-glasses" data-no-translate></i> Productos</h1>
        </div>

        <!-- Mensaje de error si existe -->
        <?php if(isset($error) && $error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Contador de productos -->
        <?php if (!empty($productos)): ?>
            <div class="products-count">
                <?= count($productos) ?> productos disponibles
            </div>
        <?php endif; ?>

        <!-- Mensaje de error adicional si existe -->
        <?php if (isset($error_message) && $error_message): ?>
            <div class="error-message">
                ⚠️ Error al cargar productos: <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Grid de productos -->
        <div class="products-grid">
            <?php if (!empty($productos)): ?>
                <?php foreach ($productos as $producto): ?>
                    <div class="product-card <?= $producto['Stock'] <= 0 ? 'out-of-stock' : '' ?>" 
                         data-product-id="<?= htmlspecialchars($producto['ID_Producto']) ?>">
                        
                        <div class="product-image-container">
                            <?php 
                                // Debug temporal - quita esto después de probar
                                $rutaImagen = !empty($producto['Imagen_URL']) ? '../../' . $producto['Imagen_URL'] : '../../uploads/productos/default-glasses.jpg';
                            ?>
    
                            <img src="<?= htmlspecialchars($rutaImagen) ?>" 
                                alt="<?= htmlspecialchars($producto['Nombre']) ?>" 
                                class="product-image"
                                onerror="this.src='../../uploads/productos/default-glasses.jpg'; console.error('Error cargando imagen');">
                        </div>
                        
                        <div class="product-content">
                            <h3 class="product-title"><?= htmlspecialchars($producto['Nombre']) ?></h3>
                            <p class="product-description">
                                <?= htmlspecialchars(truncarDescripcion($producto['Descripcion'] ?? '')) ?>
                            </p>
                            
                            <div class="product-footer">
                                <div class="product-price">
                                    <?= formatearPrecio($producto['Precio']) ?>
                                </div>
                                <div class="product-stock">
                                    Stock: <?= intval($producto['Stock']) ?>
                                </div>
                            </div>
                            
                            <?php if ($producto['Stock'] > 0): ?>
                                <button class="add-to-cart-btn" onclick="addToCart(<?= $producto['ID_Producto'] ?>)">
                                    Vender
                                </button>
                            <?php else: ?>
                                <button class="add-to-cart-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
                                    Sin Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <h3>No hay productos disponibles</h3>
                    <p>En este momento no tenemos gafas en stock. ¡Vuelve pronto!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

     <!-- Imagen decorativa inferior -->
    <div class="illustration">
        <div class="character"></div>
    </div>

    <script>
        function addToCart(productId) {
            fetch('vender.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({product_id: productId, quantity: 1})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Venta realizada', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage('Sin stock', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error de conexión', 'error');
            });
        }

        function showMessage(text, type = 'success') {
            const message = document.createElement('div');
            message.textContent = text;
            message.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: ${type === 'success' ? 
                    'linear-gradient(135deg, #4FC3F7 0%, #0277BD 100%)' : 
                    'linear-gradient(135deg, #ef5350 0%, #d32f2f 100%)'};
                color: white;
                padding: 15px 30px;
                border-radius: 50px;
                font-weight: 600;
                z-index: 1000;
                animation: showMessage 3s ease-in-out;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            `;
    
            document.body.appendChild(message);
    
            setTimeout(() => {
                if (document.body.contains(message)) {
                    document.body.removeChild(message);
                }
            }, 3000);
        }

        const style = document.createElement('style');
        style.textContent = `
            @keyframes showMessage {
                0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
                15% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
                85% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                100% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
            }
        `;
        document.head.appendChild(style);
    </script>

</body>
</html>