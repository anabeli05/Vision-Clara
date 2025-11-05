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
$cliente = [];
$error = '';
$exito = '';
$id = '';

// Obtener el ID del cliente desde la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    header('Location: index.php');
    exit;
}

try {
    // Obtener datos del cliente
    $stmt = $conn->prepare("SELECT No_Afiliado, Nombre, Correo, Telefono FROM clientes WHERE No_Afiliado = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        $error = "Cliente no encontrado";
    }
} catch(PDOException $e) {
    $error = "Error al cargar el cliente: " . $e->getMessage();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        
        // Validaciones básicas
        if (empty($nombre)) {
            $error = "El nombre es requerido";
        } elseif (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = "Correo electrónico inválido";
        } elseif (empty($telefono)) {
            $error = "El teléfono es requerido";
        } else {
            // Actualizar cliente
            $stmt = $conn->prepare("UPDATE clientes SET Nombre = ?, Correo = ?, Telefono = ? WHERE No_Afiliado = ?");
            $stmt->execute([$nombre, $correo, $telefono, $id]);
            
            $exito = "Cliente actualizado correctamente";
            
            // Recargar datos del cliente
            $stmt = $conn->prepare("SELECT No_Afiliado, Nombre, Correo, Telefono FROM clientes WHERE No_Afiliado = ?");
            $stmt->execute([$id]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        $error = "Error al actualizar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href='editar-cliente.css'>
    <link rel="stylesheet" href='../Dashboard/sidebar.css'>
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-edit"></i> Editar Cliente</h1>
            <a href='SuperGestion.php' class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($exito); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($cliente): ?>
        <div class="formulario-container">
            <form method="POST" class="form-editar">
                <div class="form-group">
                    <label for="no_afiliado">No. Afiliado (No editable)</label>
                    <input type="text" id="no_afiliado" value="<?php echo htmlspecialchars($cliente['No_Afiliado']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo htmlspecialchars($cliente['Nombre']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" required 
                           value="<?php echo htmlspecialchars($cliente['Correo']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" required 
                           value="<?php echo htmlspecialchars($cliente['Telefono']); ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href='SuperGestion.php' class="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> No se pudo cargar el cliente
            </div>
        <?php endif; ?>
    </div>
</body>
</html>