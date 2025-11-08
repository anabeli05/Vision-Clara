<?php
session_start();
require_once '../Base de Datos/conexion.php';
require_once '../Base de Datos/log_utils.php';

// Verificar si el usuario tiene permiso para cambiar la contraseña
if (!isset($_SESSION['codigo_verificado']) || !isset($_SESSION['reset_user_id'])) {
    header('Location: recuperarContra.php');
    exit;
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validaciones
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Por favor, complete todos los campos";
    } elseif ($new_password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($new_password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } else {
        try {
            // Actualizar la contraseña en la base de datos
            // NOTA: Actualmente las contraseñas se guardan en texto plano (sin hash)
            // según la lógica en login_var.php
            $stmt = $conexion->prepare("
                UPDATE usuarios 
                SET Contraseña = ? 
                WHERE Usuario_ID = ?
            ");
            $stmt->bind_param("si", $new_password, $_SESSION['reset_user_id']);
            
            if ($stmt->execute()) {
                writeLog("SUCCESS: Contraseña actualizada para usuario ID: " . $_SESSION['reset_user_id'] . " - Email: " . $_SESSION['reset_email']);
                
                // Limpiar todas las variables de sesión de recuperación
                unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_expires'], $_SESSION['reset_user_id'], $_SESSION['codigo_verificado']);
                
                // Redireccionar al login con mensaje de éxito
                $_SESSION['login_success'] = "Contraseña cambiada exitosamente. Inicie sesión con su nueva contraseña.";
                header('Location: inicioSecion.php');
                exit;
            } else {
                $error = "Error al actualizar la contraseña. Intente nuevamente.";
                writeLog("ERROR: No se pudo actualizar la contraseña para usuario ID: " . $_SESSION['reset_user_id']);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error = "Error del sistema. Intente más tarde.";
            writeLog("ERROR: Excepción al actualizar contraseña: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Contraseña</title>
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
                    <h1>CAMBIO DE CONTRASEÑA</h1>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new-password">Contraseña Nueva</label>
                        <div class="password-input">
                            <input type="password" id="new-password" name="new_password" required placeholder="Contraseña Nueva">
                            <button type="button" class="toggle-password" onclick="togglePassword('new-password')">👁️</button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-meter" id="password-strength-meter"></div>
                        </div>
                        <div class="password-requirements">
                            La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, un número y un carácter especial.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password">Confirmar Contraseña</label>
                        <div class="password-input">
                            <input type="password" id="confirm-password" name="confirm_password" required placeholder="Confirmar Contraseña">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm-password')">👁️</button>
                        </div>
                        <div id="password-match-message" class="password-match-message"></div>
                    </div>
                    
                    <button type="submit" class="btn confirm-button">Confirmar</button>
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