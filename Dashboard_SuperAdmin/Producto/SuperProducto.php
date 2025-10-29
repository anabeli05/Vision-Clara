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
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="SuperProducto.css">
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'> 
</head>
<body>

    <?php include '../Dashboard/SuperSidebar.php'; ?>
    
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

        <!-- Botón para añadir producto -->
        <div class="btn-container">
            <a href="SuperAgregarP.php" class="btn-add">
                <i class="fas fa-plus"></i> Añadir Producto
            </a>
        </div>

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
    
                                <!-- Muestra la ruta para debug -->
                            <small style="display:block; font-size:10px; color:#666;">
                                Ruta: <?= htmlspecialchars($rutaImagen) ?>
                            </small>
    
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
        // Función para añadir al carrito
        function addToCart(productId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('¡Producto añadido al carrito!', 'success');
                    updateCartCount(data.cart_count);
                } else {
                    showMessage('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Error de conexión', 'error');
                console.error('Error:', error);
            });
        }

        // Función para mostrar mensajes
        function showMessage(text, type = 'success') {
            const message = document.createElement('div');
            message.textContent = text;
            message.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: ${type === 'success' ? 
                    'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)' : 
                    'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)'};
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

        // Función para actualizar contador del carrito
        function updateCartCount(count) {
            const cartCounter = document.querySelector('.cart-counter');
            if (cartCounter) {
                cartCounter.textContent = count;
                cartCounter.style.animation = 'bounce 0.5s ease';
            }
        }

        // Event listeners para las tarjetas
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Solo si no se clickeó el botón
                if (!e.target.classList.contains('add-to-cart-btn')) {
                    const productId = this.dataset.productId;
                    // Redirigir a página de detalles del producto
                    window.location.href = `product_details.php?id=${productId}`;
                }
            });
        });

        // Lazy loading para imágenes
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.classList.remove('skeleton');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('.product-image').forEach(img => {
            img.classList.add('skeleton');
            imageObserver.observe(img);
        });

        // CSS para animaciones
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