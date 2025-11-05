<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que NO sea Super Admin (puede ser Admin, Usuario, etc.)
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/inicio/SuperInicio.php');
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Inicializar variables
$clientes = [];
$error = '';

try {
    // Obtener todos los clientes
    $stmt = $conn->prepare("SELECT No_Afiliado, Nombre, Correo, Telefono FROM clientes ORDER BY Nombre ASC");
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error al cargar los clientes: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="GestionAdmin.css">
    <link rel="stylesheet" href="../Dashboard/SidebarAdmin.css">
</head>
<body>

    <?php include '../Dashboard/SidebarAdmin.php'; ?>
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
            <a href="../Registro-Cliente/RegistroAdmin.php" class="btn-add">
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