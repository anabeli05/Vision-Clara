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
require_once '../../Base de Datos/email_utils.php';

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
        $contrasena = 'Clara789.'; // Contraseña por defecto
        $rol = 'Usuario';
        
        // Validaciones básicas
        if (empty($nombre) || empty($correo)) {
            $error = "Todos los campos son obligatorios";
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
                    
                    // Enviar correo de bienvenida
                    try {
                        $mail = configurarPHPMailer();
                        $mail->addAddress($correo);
                        $mail->Subject = 'Bienvenido a Vision Clara';
                        $mail->isHTML(true);
                        $mail->Body = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                                    <h1>¡Bienvenido a Vision Clara!</h1>
                                </div>
                                <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
                                    <p>Hola <strong>$nombre</strong>,</p>
                                    <p>Tu cuenta ha sido creada exitosamente.</p>
                                    <p><strong>Correo:</strong> $correo</p>
                                    <p><strong>Contraseña temporal:</strong> Clara789.</p>
                                    <p style='color: #f57c00; font-weight: bold;'>⚠️ Por seguridad, te recomendamos cambiar tu contraseña al iniciar sesión.</p>
                                    <p>Ya puedes iniciar sesión en nuestra plataforma.</p>
                                    <p style='margin-top: 30px;'>Saludos,<br><strong>Equipo Vision Clara</strong></p>
                                </div>
                            </div>
                        ";
                        $mail->send();
                        $success = "Usuario registrado y correo enviado";
                    } catch (Exception $e) {
                        $success = "Usuario registrado pero no se pudo enviar el correo";
                    }
                    
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
                <label>Contraseña:</label>
                <input type="text" value="Clara789." disabled style="background: #f0f0f0;">
                <small>La contraseña por defecto es <strong>Clara789.</strong> (el usuario puede cambiarla después)</small>
            </div>

            <input type="hidden" name="Rol" value="Usuario">

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