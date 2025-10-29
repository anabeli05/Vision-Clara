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

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inicializar variables
$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token de seguridad inválido";
    } else {
        $nombre = trim($_POST['Nombre'] ?? '');
        $correo = trim($_POST['Correo'] ?? '');
        $contrasena = $_POST['Contraseña'] ?? '';
        $rol = 'Usuario'; // Siempre Usuario, no Super Admin
        
        // Validaciones básicas
        if (empty($nombre) || empty($correo) || empty($contrasena)) {
            $error = "Todos los campos son obligatorios";
        } elseif (strlen($contrasena) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres";
        } else {
            try {
                // Verificar si el correo ya existe
                $stmt = $conn->prepare("SELECT Correo FROM usuarios WHERE Correo = ?");
                $stmt->execute([$correo]);
                
                if ($stmt->fetch()) {
                    $error = "El correo ya está registrado";
                } else {
                    // Hash de la contraseña
                    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    
                    // Insertar nuevo usuario
                    $stmt = $conn->prepare("INSERT INTO usuarios (Nombre, Correo, Contraseña, Rol) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nombre, $correo, $contrasena_hash, $rol]);
                    
                    $success = "Usuario registrado exitosamente";
                    // Limpiar el formulario
                    $_POST = [];
                }
            } catch(PDOException $e) {
                $error = "Error al registrar el usuario: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="SuperRegistroA.css">
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'> 

</head>
<body>

    <?php include '../Dashboard/SuperSidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-user-edit" data-no-translate></i> Registro de Usuarios</h1>
        </div>

        <!-- mensaje de error de la base de datos -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="POST" class="formulario-registro">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="registro" value="1">

            <div class="formulario">
                <label for="Nombre">Nombre Completo:</label>
                <input type="text" id="Nombre" name="Nombre" required
                        value="<?php echo htmlspecialchars($_POST['Nombre']?? ''); ?>">
            </div>

            <div class="formulario">
                <label for="Correo">Correo:</label>
                <input type="email" id="Correo" name="Correo" required
                        value="<?php echo htmlspecialchars($_POST['Correo']?? ''); ?>">
            </div>

            <div class="formulario">
                <label for="Contraseña">Contraseña:</label>
                <input type="password" id="Contraseña" name="Contraseña" required
                        minlength="8">
                <small>Mínimo 8 caracteres</small>
            </div>

            <input type="hidden" name="Rol" value="Usuario">

            <!-- Botones para Registro y Cancelacion -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Registrar 
             </button>
                <a href='../Usuario/SuperGestionU.php' class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</body>
</html>