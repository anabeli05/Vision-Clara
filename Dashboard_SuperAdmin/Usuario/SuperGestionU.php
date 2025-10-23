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

// Inicializar variables
$usuarios = [];
$error = '';

//try {
    // Obtener solo usuarios con rol 'Usuario' (no Super Admin)
//    $stmt = $conn->prepare("SELECT Usuario_ID as ID_Empleado, Nombre, Correo, Rol FROM usuarios WHERE Rol = 'Usuario' ORDER BY Nombre ASC");
//    $stmt->execute();
//    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
//} catch(PDOException $e) {
//    $error = "Error al cargar los usuarios: " . $e->getMessage();
//}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Clientes - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="SuperGestionU.css">
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'> 

</head>
<body>

    <?php include '../Dashboard/SuperSidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-user-cog" data-no-translate></i> Gestion de Usuarios</h1>
        </div>

        
        <?php if(isset($error) && $error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="btn-container">
            <a href="../Registro-Usuario/Registro-Usuario.php" class="btn-add">
                <i class="fas fa-plus"></i> Añadir Usuario
            </a>
        </div>
       
        <div class="usuario-tabla">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="4" class="no-data">No hay usuarios registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>  
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Correo']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Rol']); ?></td>
                                <td class="actions">
                                    <a href="editar-usuario.php?id=<?php echo urlencode($usuario['ID_Empleado']); ?>"
                                       class="btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmarEliminacion('<?php echo htmlspecialchars($usuario['ID_Empleado'], ENT_QUOTES); ?>')"
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
            if(confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                <?php if(isset($_SESSION['csrf_token'])): ?>
                    window.location.href = `eliminar-usuario.php?id=${encodeURIComponent(id)}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`;
                <?php else: ?>
                    window.location.href = `eliminar-usuario.php?id=${encodeURIComponent(id)}`;
                <?php endif; ?>
            }
        }
    </script>
</body>
</html>