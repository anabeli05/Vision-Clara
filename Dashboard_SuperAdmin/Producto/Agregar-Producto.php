<?php 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Clientes - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Agregar-Producto.css">
    <link rel="stylesheet" href="../Dashboard/sidebar.css">
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1>
            <i class="fas fa-glasses" data-no-translate></i> 
            <i class="fas fa-plus" data-no-translate></i> 
                Agregar Producto
            </h1>
        </div>

        
        <?php if(isset($error) && $error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

           <!-- Formulario -->
        <div class="product-form">
            <!-- Sección de imagen -->
            <div class="image-upload-section">
                <label class="form-label">Imagen del producto</label>
                <div class="image-buttons">
                    <button class="btn btn-upload">
                        <i class="fas fa-upload"></i> Cargar Imagen
                    </button>
                    <button class="btn btn-remove">
                        <i class="fas fa-trash"></i> Eliminar Imagen
                    </button>
                </div>
            </div>

            <!-- Descripción del producto -->
            <div class="form-section">
                <label class="form-label">Descripción del Producto</label>
                <input type="text" class="form-input" placeholder="Ingrese la descripción del producto...">
            </div>

            <!-- Precio del producto -->
            <div class="form-section">
                <div class="price-section">
                    <label class="form-label">Precio del Producto</label>
                    <input type="number" class="price-input" placeholder="$0.00" step="0.01">
                </div>
            </div>

            <!-- Botón agregar -->
            <button class="btn-add-product">
                <i class="fas fa-plus"></i> Añadir Producto
            </button>
        </div>
    </div>

    <script>
        // Funcionalidad básica para los botones
        document.querySelector('.btn-upload').addEventListener('click', function() {
            // Crear input file oculto
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    alert('Imagen seleccionada: ' + file.name);
                }
            };
            input.click();
        });

        document.querySelector('.btn-remove').addEventListener('click', function() {
            alert('Imagen eliminada');
        });

        document.querySelector('.btn-add-product').addEventListener('click', function() {
            const descripcion = document.querySelector('.form-input').value;
            const precio = document.querySelector('.price-input').value;
            
            if (descripcion.trim() === '' || precio.trim() === '') {
                alert('Por favor, complete todos los campos');
                return;
            }
            
            alert('Producto agregado:\nDescripción: ' + descripcion + '\nPrecio: $' + precio);
            
            // Limpiar formulario
            document.querySelector('.form-input').value = '';
            document.querySelector('.price-input').value = '';
        });
    </script>
</body> 
</html>