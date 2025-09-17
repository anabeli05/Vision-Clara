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
            <i class="fas fa-users" data-no-translate></i> 
            <h1>Gestión de Clientes </h1>
        </div>

        <!--<?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?> --> 
        
        <div class="cliente-tabla">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>No.Afilido</th>
                        <th>Correo</th>
                        <th>Telefono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="6" class="no-data">No hay clientes registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>  
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['No.Afiliado']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['Correo']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['Telefono']); ?></td>
                                <td class="acciones">
                        <!-- Boton para editar -->
                                    <a href="editar-cliente.php?=<?php echo $cliente[No.Afiliado]; ?>"
                                        class="btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmarEliminacion(<?php echo $cliente[No.Afiliado];?>)"
                                            class="btn-eliminar" tittle="Eliminar">
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
        function confirmarEliminacion(id){
            if(confirm('¿Estás seguro de que deseas eliminar este cliente?')){
                window.location.href = `eliminar-cliente.php?id=${id}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`;
            }
        }
    </script>
</body>
</html>

    