<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Gestion-Cliente.css">
    <link rel="stylesheet" href="../Dashboard/sidebar.css">
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-users"></i> Gestión de Clientes</h1>
        </div>
        
        <?php if(isset($error) && $error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="btn-container">
            <a href="../Registro-Cliente/Registro-Cliente.php" class="btn-add">
                <i class="fas fa-plus"></i> Añadir Cliente
            </a>
        </div>
       
        <div class="cliente-tabla">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>No. Afiliado</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="5" class="no-data">No hay clientes registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>  
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['No_Afiliado']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['Correo']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['Telefono']); ?></td>
                                <td class="actions">
                                    <a href="editar-cliente.php?id=<?php echo urlencode($cliente['No_Afiliado']); ?>"
                                       class="btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmarEliminacion('<?php echo htmlspecialchars($cliente['No_Afiliado'], ENT_QUOTES); ?>')"
                                            class="btn-delete" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>                
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function confirmarEliminacion(id) {
            if(confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
                <?php if(isset($_SESSION['csrf_token'])): ?>
                    window.location.href = `eliminar-cliente.php?id=${encodeURIComponent(id)}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`;
                <?php else: ?>
                    window.location.href = `eliminar-cliente.php?id=${encodeURIComponent(id)}`;
                <?php endif; ?>
            }
        }
    </script>
</body>
</html>