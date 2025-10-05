<?php
include_once 'log_utils.php';

writeLog("Iniciando proceso de login");

session_start();
include '../Base de datos/conexion.php';


// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Aquí iría la validación con la base de datos
    // Por ahora solo simulamos un login exitoso
    if (!empty($email) && !empty($password)) {
        $_SESSION['user_email'] = $email;
        $_SESSION['logged_in'] = true;
        
        // Redireccionar al dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Por favor, complete todos los campos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Usuario</title>
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
                    <h1>INGRESAR USUARIO</h1>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required placeholder="Correo electrónico" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required placeholder="Contraseña">
                            <button type="button" class="toggle-password" onclick="togglePassword('password')"></button>
                        </div>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="remember">
                            <input type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                            <label for="remember">RECUERDAME</label>
                        </div>
                        <a href="recuperarContra.php" class="forgot-password">¿OLVIDASTE TU CONTRASEÑA?</a>
                    </div>
                    
                    <button type="submit" class="btn login-button">Ingresar</button>
                </form>
                
                <div class="divider"></div>
                
                <div class="vision-section">
                    <h3>Visión clara</h3>
                    <p>Nuestra visión es proporcionar soluciones innovadoras que mejoren la experiencia de nuestros usuarios.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="inicioSecion.js"></script>
</body>
</html>