<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <div class="logo">MiSistema</div>
    </header>
    
    <div class="main-container">
        <div class="login-container">
            <div class="image-section vision-bg">
                <!-- Imagen en el lado izquierdo -->
            </div>
            
            <div class="login-form">
                <div class="login-header">
                    <h1>CÓDIGO DE VERIFICACIÓN</h1>
                </div>
                
                <p class="verification-text">Ingresa tu código que fue enviado a tu correo electrónico para restablecer tu contraseña.</p>
                
                <form method="POST" action="verificar_codigo.php">
                    <div class="form-group">
                        <label for="code">Ingrese el Código</label>
                        <div class="code-inputs">
                            <input type="text" name="code1" maxlength="1" pattern="[0-9]" required>
                            <input type="text" name="code2" maxlength="1" pattern="[0-9]" required>
                            <input type="text" name="code3" maxlength="1" pattern="[0-9]" required>
                            <input type="text" name="code4" maxlength="1" pattern="[0-9]" required>
                            <input type="text" name="code5" maxlength="1" pattern="[0-9]" required>
                            <input type="text" name="code6" maxlength="1" pattern="[0-9]" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn submit-button">Ingresar</button>
                </form>
                
                <div class="resend-code">
                    ¿No recibiste el código? <a href="reenviar_codigo.php">Reenviar código</a>
                </div>
                
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