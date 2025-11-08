<?php
session_start();
require_once '../Base de Datos/conexion.php';
require_once '../Base de Datos/email_utils.php';
require_once '../Base de Datos/log_utils.php';

// Procesar el formulario de recuperación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Validar email
    if (empty($email)) {
        $error = "Por favor, ingrese su correo electrónico";
        writeLog("WARN: Intento de recuperación sin email");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, ingrese un correo electrónico válido";
        writeLog("WARN: Email inválido en recuperación: " . $email);
    } else {
        try {
            // 1. Verificar si el email existe en la base de datos
            $stmt = $conexion->prepare("
                SELECT Usuario_ID, Nombre, Correo, Rol, activo 
                FROM usuarios 
                WHERE Correo = ? 
                LIMIT 1
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows === 1) {
                $usuario = $resultado->fetch_assoc();
                
                // Verificar que el usuario esté activo
                if ($usuario['activo'] != 1) {
                    $error = "Esta cuenta está desactivada. Contacte al administrador.";
                    writeLog("WARN: Intento de recuperación para cuenta inactiva: " . $email);
                } else {
                    // 2. Generar un código de verificación de 6 dígitos
                    $codigo = generarCodigoVerificacion();
                    
                    // 3. Guardar el código en la sesión
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_code'] = $codigo;
                    $_SESSION['reset_expires'] = time() + 1800; // Expira en 30 minutos
                    $_SESSION['reset_user_id'] = $usuario['Usuario_ID'];
                    $_SESSION['reset_nombre'] = $usuario['Nombre'];
                    
                    // 4. Enviar el código por email usando la función de email_utils.php
                    $emailEnviado = enviarCodigoRecuperacion($email, $usuario['Nombre'], $codigo);
                    
                    if ($emailEnviado) {
                        writeLog("SUCCESS: Código de recuperación enviado a: " . $email . " (Rol: " . $usuario['Rol'] . ")");
                        
                        // Redireccionar a la página de verificación
                        header('Location: codigoRecuperacion.php');
                        exit;
                    } else {
                        $error = "Error al enviar el correo. Intente nuevamente más tarde.";
                        writeLog("ERROR: No se pudo enviar el código a: " . $email);
                    }
                }
            } else {
                // El email no existe en la base de datos
                // Por seguridad, no revelamos si el email existe o no
                $error = "Si el correo está registrado, recibirás un código de verificación.";
                writeLog("WARN: Intento de recuperación para email no registrado: " . $email);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error = "Error del sistema. Intente más tarde.";
            writeLog("ERROR: Excepción en recuperación de contraseña: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <div class="logo"></div>
    </header>
    
    <div class="main-container">
        <div class="login-container">
            <div class="image-section vision-bg">
                <!-- Imagen en el lado izquierdo -->
            </div>
            
            <div class="login-form">
                <div class="login-header">
                    <h1>RECUPERAR CONTRASEÑA</h1>
                </div>
                
                <p class="recovery-text">Ingresa tu correo electrónico gmail y te enviaremos un código para restablecer tu contraseña.</p>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required placeholder="Correo electrónico" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn send-button">Enviar Código</button>
                </form>
                
                <div class="remember-login">
                    ¿Recordaste tu contraseña? <a href="inicioSecion.php">Inicia sesión aquí</a>
                </div>
                
                <div class="divider"></div>
                
                <div class="vision-section">
                    <h3>Visión clara</h3>
                    <p>Nuestra visión es proporcionar soluciones innovadoras que mejoren la experiencia de nuestros usuarios.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>