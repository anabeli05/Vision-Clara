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

                            <div class="product-actions">
                            <button class="action-btn-new edit-btn-new" 
                                    onclick="event.stopPropagation(); editProduct(<?= $producto['ID_Producto'] ?>)" 
                                    title="Editar producto">
                                <i class="fas fa-edit"></i>
                                <span>Editar</span>
                            </button>
                            <button class="action-btn-new stock-btn-new" 
                                    onclick="event.stopPropagation(); showStockModal(<?= $producto['ID_Producto'] ?>, '<?= htmlspecialchars($producto['Nombre']) ?>', <?= $producto['Stock'] ?>)" 
                                    title="Ajustar stock">
                                <i class="fas fa-boxes"></i>
                                <span>Stock</span>
                            </button>
                            <button class="action-btn-new delete-btn-new" 
                                    onclick="event.stopPropagation(); deleteProduct(<?= $producto['ID_Producto'] ?>, '<?= htmlspecialchars($producto['Nombre']) ?>')" 
                                    title="Eliminar producto">
                                <i class="fas fa-trash"></i>
                                <span>Eliminar</span>
                            </button>
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
                    <!-- Modal para ajustar stock -->
                    <div id="stockModal" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeStockModal()">&times;</span>
                            <h2><i class="fas fa-boxes"></i> Ajustar Stock</h2>
                            <p id="productNameStock"></p>
                            <div class="stock-current">
                                Stock actual: <strong id="currentStock">0</strong>
                            </div>
                            <form id="stockForm" onsubmit="updateStock(event)">
                                <input type="hidden" id="productIdStock" name="product_id">
                                <div class="form-group">
                                    <label for="stockAction">Acción:</label>
                                    <select id="stockAction" name="action" required>
                                        <option value="add">Aumentar</option>
                                        <option value="subtract">Disminuir</option>
                                        <option value="set">Establecer cantidad exacta</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stockAmount">Cantidad:</label>
                                    <input type="number" id="stockAmount" name="amount" min="1" required>
                                </div>
                                <div class="modal-buttons">
                                    <button type="submit" class="btn-confirm">Actualizar Stock</button>
                                    <button type="button" class="btn-cancel" onclick="closeStockModal()">Cancelar</button>
                                </div>
                            </form>
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
    // Función para mostrar mensajes centrados
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
            z-index: 10000;
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

    // Función para editar producto
    function editProduct(productId) {
        console.log('Editando producto:', productId);
        window.location.href = `editar-producto.php?id=${productId}`;
    }

    // Función para eliminar producto
    function deleteProduct(productId, productName) {
        if (confirm(`¿Estás seguro de que deseas eliminar "${productName}"?\n\nEsta acción no se puede deshacer.`)) {
            showMessage('Eliminando producto...', 'info');
            
            fetch('eliminar-producto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showMessage('Producto eliminado correctamente', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Error de conexión: ' + error.message, 'error');
                console.error('Error completo:', error);
            });
        }
    }

    // Función para mostrar modal de stock
    function showStockModal(productId, productName, currentStock) {
        document.getElementById('stockModal').style.display = 'block';
        document.getElementById('productIdStock').value = productId;
        document.getElementById('productNameStock').textContent = productName;
        document.getElementById('currentStock').textContent = currentStock;
        document.getElementById('stockAmount').value = '';
        document.getElementById('stockAction').value = 'add';
    }

    // Función para cerrar modal
    function closeStockModal() {
        document.getElementById('stockModal').style.display = 'none';
    }

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('stockModal');
        if (event.target == modal) {
            closeStockModal();
        }
    }

    // Función para actualizar stock
    function updateStock(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const data = {
            product_id: formData.get('product_id'),
            action: formData.get('action'),
            amount: parseInt(formData.get('amount'))
        };

        if (!data.amount || data.amount <= 0) {
            showMessage('Por favor ingresa una cantidad válida', 'error');
            return;
        }

        showMessage('Actualizando stock...', 'info');

        fetch('actualizar-stock.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage('Stock actualizado correctamente', 'success');
                closeStockModal();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showMessage('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Error de conexión: ' + error.message, 'error');
            console.error('Error completo:', error);
        });
    }
    
    // Función para vender
    function addToCart(productId) {
        fetch('../../Dashboard_Admin/Producto/vender.php', {
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

    // Estilos para animaciones
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        @keyframes showMessage {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
            15% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
            85% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
        }
    `;
    document.head.appendChild(styleElement);
</script>
</body>
</html>