<?php
session_start();

// Procesar el formulario de recuperación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Validar email
    if (empty($email)) {
        $error = "Por favor, ingrese su correo electrónico";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, ingrese un correo electrónico válido";
    } else {
        // Aquí iría la lógica para:
        // 1. Verificar si el email existe en la base de datos
        // 2. Generar un código de verificación
        // 3. Enviar el código por email
        
        // Simulamos el envío exitoso
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_code'] = rand(100000, 999999); // Código de 6 dígitos
        $_SESSION['reset_expires'] = time() + 1800; // Expira en 30 minutos
        
        // Redireccionar a la página de verificación
        header('Location: codigoRecuperacion.php');
        exit;
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
                    ¿Recordaste tu contraseña? <a href="inicioSesion.php">Inicia sesión aquí</a>
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