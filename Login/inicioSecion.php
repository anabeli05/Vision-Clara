<?php
session_start();
include_once '../Base de Datos/log_utils.php';

//verificar si recordo el usuario
include_once 'check_remember.php';

//revisar si ya está logueado, redirigir según su rol
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Redirigir a dashboard específico según rol
    if(isset($_SESSION['user_rol'])) {
        if($_SESSION['user_rol'] === 'Super Admin') {
            header('Location: ../Dashboard_SuperAdmin/inicio/InicioSA.php');
        } else {
            header('Location: ../Dashboard_Admin/inicio/InicioSA.php');
        }
    }
    exit;
}

// Obtener mensajes de error/éxito desde sesión
// Estos se guardan en login_var.php cuando hay errores
$error = $_SESSION['login_error'] ?? null;
$success = $_SESSION['login_success'] ?? null;
unset($_SESSION['login_error'], $_SESSION['login_success']);

// Recuperar valores del formulario si hubo error
// Esto permite que el usuario no tenga que volver a escribir
$email_value = $_SESSION['temp_email'] ?? '';
$remember_checked = $_SESSION['temp_remember'] ?? false;
unset($_SESSION['temp_email'], $_SESSION['temp_remember']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Usuario</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="../estilos/translator.css">
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript" src="../js/translator.js"></script>
</head>
<body>
    <header>
        <div class="logo"></div>
        <div id="google_translate_element"></div>
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
                
                <?php if ($error): ?>
                    <div class="error-message" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
            
                <form method="POST" action="login_var.php" autocomplete="on">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required placeholder="Correo electrónico" value="<?php echo htmlspecialchars($email_value); ?>" autocomplete="email">
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
                            <input type="checkbox" id="remember" name="remember"  <?php echo $remember_checked ? 'checked' : ''; ?>>
                            <label for="remember">RECUERDAME</label>
                        </div>
                        <a href="recuperarContra.php" class="forgot-password">¿OLVIDASTE TU CONTRASEÑA?</a>
                    </div>
                    
                    <button type="submit" name="btn_login" class="btn login-button">Ingresar</button>
                </form>
                
                <div class="divider"></div>
                
                <div class="vision-section">
                    <h3>Visión Clara</h3>
                    <p>Nuestra visión es proporcionar soluciones innovadoras que mejoren la experiencia de nuestros usuarios.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="inicioSecion.js"></script>
</body>
</html>


