<?php
session_start();
require_once '../Base de Datos/log_utils.php';
require_once '../Base de Datos/email_utils.php';

// Verificar que venimos del proceso de recuperación
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code'])) {
    writeLog("WARN: Acceso directo a codigoRecuperacion.php sin sesión válida");
    header('Location: recuperarContrasena.php');
    exit;
}

// Verificar expiración del código
if (time() > $_SESSION['reset_expires']) {
    $error = "El código ha expirado. Por favor, solicita uno nuevo.";
    writeLog("WARN: Código expirado para: " . $_SESSION['reset_email']);
    unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_expires']);
}

// Procesar verificación del código
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoIngresado = trim($_POST['codigo'] ?? '');
    
    if (empty($codigoIngresado)) {
        $error = "Por favor, ingrese el código de verificación";
        writeLog("WARN: Intento de verificación sin código");
    } else {
        // Verificar el código usando la función de email_utils.php
        $resultado = verificarCodigoRecuperacion(
            $codigoIngresado,
            $_SESSION['reset_code'],
            $_SESSION['reset_expires']
        );
        
        if ($resultado['valido']) {
            writeLog("SUCCESS: Código verificado correctamente para: " . $_SESSION['reset_email']);
            
            // Marcar como código verificado
            $_SESSION['codigo_verificado'] = true;
            
            // Redireccionar a la página de nueva contraseña
            header('Location: nuevaContrasena.php');
            exit;
        } else {
            $error = $resultado['mensaje'];
            writeLog("WARN: Código incorrecto para: " . $_SESSION['reset_email']);
        }
    }
}

// Obtener el email para mostrarlo (parcialmente oculto)
$emailMostrar = $_SESSION['reset_email'] ?? '';
if (strlen($emailMostrar) > 3) {
    $partes = explode('@', $emailMostrar);
    $emailMostrar = substr($partes[0], 0, 3) . '***@' . $partes[1];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .code-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
            padding: 15px;
            border: 2px solid #667eea;
            border-radius: 8px;
            width: 100%;
            max-width: 250px;
            margin: 20px auto;
            display: block;
        }
        
        .code-input:focus {
            outline: none;
            border-color: #764ba2;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .resend-code {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .resend-code a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .resend-code a:hover {
            text-decoration: underline;
        }
        
        .timer {
            text-align: center;
            color: #f44336;
            font-weight: bold;
            margin: 15px 0;
        }
    </style>
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
                    <h1>VERIFICAR CÓDIGO</h1>
                </div>
                
                <div class="info-box">
                    <p style="margin: 0;">📧 Hemos enviado un código de 6 dígitos a:</p>
                    <p style="margin: 10px 0 0 0; font-weight: bold;"><?php echo htmlspecialchars($emailMostrar); ?></p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <?php 
                        echo htmlspecialchars($_SESSION['success']); 
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="verificar_codigo.php">
                    <div class="form-group">
                        <label for="code">Ingrese el Código</label>
                        <div class="code-inputs" style="display: flex; gap: 10px; justify-content: center; margin: 20px 0;">
                            <input type="text" name="code1" maxlength="1" pattern="[0-9]" required style="width: 50px; height: 50px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 8px;">
                            <input type="text" name="code2" maxlength="1" pattern="[0-9]" required style="width: 50px; height: 50px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 8px;">
                            <input type="text" name="code3" maxlength="1" pattern="[0-9]" required style="width: 50px; height: 50px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 8px;">
                            <input type="text" name="code4" maxlength="1" pattern="[0-9]" required style="width: 50px; height: 50px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 8px;">
                            <input type="text" name="code5" maxlength="1" pattern="[0-9]" required style="width: 50px; height: 50px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 8px;">
                            <input type="text" name="code6" maxlength="1" pattern="[0-9]" required style="width: 50px; height: 50px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 8px;">
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['reset_expires'])): 
                        $minutosRestantes = ceil(($_SESSION['reset_expires'] - time()) / 60);
                        if ($minutosRestantes > 0):
                    ?>
                        <div class="timer">
                            ⏱️ Tiempo restante: <?php echo $minutosRestantes; ?> minuto(s)
                        </div>
                    <?php endif; endif; ?>
                    
                    <button type="submit" class="btn send-button">Verificar Código</button>
                </form>
                
                <div class="resend-code">
                    ¿No recibiste el código? <a href="reenviar_codigo.php">Reenviar código</a>
                </div>
                
                <div class="remember-login">
                    ¿Recordaste tu contraseña? <a href="inicioSecion.php">Inicia sesión aquí</a>
                </div>
                
                <div class="divider"></div>
                
                <div class="vision-section">
                    <h3>Seguridad</h3>
                    <p>Tu código es válido por 30 minutos y solo puede usarse una vez.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-avance entre inputs
        const inputs = document.querySelectorAll('.code-inputs input');
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Solo permitir números
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                if (e.target.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Prevenir entrada no numérica
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        // Temporizador de cuenta regresiva
        <?php if (isset($_SESSION['reset_expires'])): 
            $tiempoRestante = $_SESSION['reset_expires'] - time();
            if ($tiempoRestante > 0):
        ?>
        let tiempoRestante = <?php echo $tiempoRestante; ?>;
        const timerElement = document.querySelector('.timer');
        
        if (timerElement) {
            const countdown = setInterval(() => {
                tiempoRestante--;
                const minutos = Math.floor(tiempoRestante / 60);
                const segundos = tiempoRestante % 60;
                
                timerElement.textContent = `⏱️ Tiempo restante: ${minutos} minuto(s) ${segundos} segundo(s)`;
                
                if (tiempoRestante <= 0) {
                    clearInterval(countdown);
                    alert('El código ha expirado. Serás redirigido para solicitar uno nuevo.');
                    window.location.href = 'recuperarContrasena.php';
                }
            }, 1000);
        }
        <?php endif; endif; ?>
    </script>
</body>
</html>