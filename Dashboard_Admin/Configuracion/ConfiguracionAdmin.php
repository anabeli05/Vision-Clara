<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Redirección si es Super Admin
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/configuracion/ConfigSuperAdmin.php');
    exit;
}

// Conexión BD
require_once '../../Base de Datos/conexion.php';
$conexion = new Conexion();
$conexion->abrir_conexion();
$mysqli = $conexion->conexion;

// Mensajes de feedback
$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nuevo_nombre = trim($_POST['nombre']);
    $nuevo_correo = trim($_POST['correo']);
    
    if (!empty($nuevo_nombre) && !empty($nuevo_correo)) {
        $query = $mysqli->prepare("UPDATE usuarios SET Nombre = ?, Correo = ? WHERE Usuario_ID = ?");
        $query->bind_param("ssi", $nuevo_nombre, $nuevo_correo, $user_id);
        
        if ($query->execute()) {
            $mensaje = "Perfil actualizado correctamente";
            $tipo_mensaje = "success";
            $_SESSION['user_nombre'] = $nuevo_nombre;
            $user_nombre = $nuevo_nombre;
        } else {
            $mensaje = "Error al actualizar el perfil";
            $tipo_mensaje = "error";
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = trim($_POST['password_actual'] ?? '');
    $password_nueva = trim($_POST['password_nueva'] ?? '');
    $password_confirmar = trim($_POST['password_confirmar'] ?? '');
    
    // Validaciones
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        $mensaje = "Por favor, complete todos los campos";
        $tipo_mensaje = "error";
    } elseif ($password_nueva !== $password_confirmar) {
        $mensaje = "Las contraseñas nuevas no coinciden";
        $tipo_mensaje = "error";
    } elseif (strlen($password_nueva) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres";
        $tipo_mensaje = "error";
    } elseif (!preg_match('/[A-Z]/', $password_nueva)) {
        $mensaje = "La contraseña debe incluir al menos una letra mayúscula";
        $tipo_mensaje = "error";
    } elseif (!preg_match('/[0-9]/', $password_nueva)) {
        $mensaje = "La contraseña debe incluir al menos un número";
        $tipo_mensaje = "error";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password_nueva)) {
        $mensaje = "La contraseña debe incluir al menos un carácter especial";
        $tipo_mensaje = "error";
    } else {
        // Obtener contraseña actual de la BD
        $query = $mysqli->prepare("SELECT Contraseña FROM usuarios WHERE Usuario_ID = ?");
        $query->bind_param("i", $user_id);
        $query->execute();
        $resultado = $query->get_result()->fetch_assoc();
        
        // Verificar contraseña actual (sin hash, texto plano según tu sistema)
        if ($password_actual === $resultado['Contraseña']) {
            // Actualizar contraseña (guardada en texto plano según tu sistema)
            $query_update = $mysqli->prepare("UPDATE usuarios SET Contraseña = ? WHERE Usuario_ID = ?");
            $query_update->bind_param("si", $password_nueva, $user_id);
            
            if ($query_update->execute()) {
                $mensaje = "Contraseña actualizada correctamente";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar la contraseña";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "La contraseña actual es incorrecta";
            $tipo_mensaje = "error";
        }
    }
}

// Obtener datos del usuario
$query_user = $mysqli->prepare("SELECT Nombre, Correo, Rol FROM usuarios WHERE Usuario_ID = ?");
$query_user->bind_param("i", $user_id);
$query_user->execute();
$datos_usuario = $query_user->get_result()->fetch_assoc();

$conexion->cerrar_conexion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Vision Clara Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href='ConfiguracionAdmin.css'>
    <link rel="stylesheet" href="../Dashboard/SidebarAdmin.css">
</head>
<body>
<!--Header importado-->
<?php include('../Dashboard/SidebarAdmin.php'); ?> 

<section class="contenedor-principal">
    <!-- Encabezado -->
    <div class="recuadro-header">
        <div class="formato-txt">
            <h2><i class="fas fa-cog"></i> Configuración de Cuenta</h2>
            <p>Administra tu perfil y preferencias de seguridad</p>
        </div>
        <div class="header-img">    
            <img src="../../Imagenes/config_icon.png" alt="Configuración" onerror="this.style.display='none'">
        </div> 
    </div>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipo_mensaje ?>">
            <i class="fas <?= $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($mensaje) ?>
            <button class="cerrar-mensaje" onclick="this.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Información del Perfil -->
    <div class="tarjeta-config">
        <div class="tarjeta-header">
            <h3><i class="fas fa-user-circle"></i> Información Personal</h3>
        </div>
        <form method="POST" class="formulario-config">
            <div class="grupo-campos">
                <div class="campo">
                    <label for="nombre">
                        <i class="fas fa-user"></i> Nombre Completo
                    </label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($datos_usuario['Nombre']) ?>" required>
                </div>

                <div class="campo">
                    <label for="correo">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" id="correo" name="correo" 
                           value="<?= htmlspecialchars($datos_usuario['Correo']) ?>" required>
                </div>
            </div>

            <div class="grupo-campos">
                <div class="campo">
                    <label>
                        <i class="fas fa-user-tag"></i> Rol
                    </label>
                    <input type="text" value="<?= htmlspecialchars($datos_usuario['Rol']) ?>" disabled>
                </div>
            </div>

            <div class="campo-botones">
                <button type="submit" name="actualizar_perfil" class="btn-guardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <!-- Cambio de Contraseña -->
    <div class="tarjeta-config">
        <div class="tarjeta-header">
            <h3><i class="fas fa-lock"></i> Seguridad - Cambiar Contraseña</h3>
        </div>
        <form method="POST" class="formulario-config">
            <div class="campo">
                <label for="password_actual">
                    <i class="fas fa-key"></i> Contraseña Actual
                </label>
                <div class="password-input-wrapper">
                    <input type="password" id="password_actual" name="password_actual" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password_actual')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="campo">
                <label for="password_nueva">
                    <i class="fas fa-lock"></i> Nueva Contraseña
                </label>
                <div class="password-input-wrapper">
                    <input type="password" id="password_nueva" name="password_nueva" 
                           minlength="8" required onkeyup="checkPasswordStrength()">
                    <button type="button" class="toggle-password" onclick="togglePassword('password_nueva')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-meter" id="password-strength-meter"></div>
                </div>
                <div class="password-requirements">
                    La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, un número y un carácter especial.
                </div>
            </div>

            <div class="campo">
                <label for="password_confirmar">
                    <i class="fas fa-lock"></i> Confirmar Nueva Contraseña
                </label>
                <div class="password-input-wrapper">
                    <input type="password" id="password_confirmar" name="password_confirmar" 
                           minlength="8" required onkeyup="checkPasswordMatch()">
                    <button type="button" class="toggle-password" onclick="togglePassword('password_confirmar')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="password-match-message" class="password-match-message"></div>
            </div>

            <div class="campo-botones">
                <button type="submit" name="cambiar_password" class="btn-guardar">
                    <i class="fas fa-shield-alt"></i> Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>

    <!-- Preferencias -->
    <div class="tarjeta-config">
        <div class="tarjeta-header">
            <h3><i class="fas fa-palette"></i> Preferencias de Visualización</h3>
        </div>
        <div class="contenido-preferencias">
            <div class="preferencia-item">
                <div class="preferencia-info">
                    <i class="fas fa-moon"></i>
                    <div>
                        <h4>Modo Oscuro</h4>
                        <p>Activa el tema oscuro para reducir la fatiga visual</p>
                    </div>
                </div>
                <label class="switch">
                    <input type="checkbox" id="toggle-theme">
                    <span class="slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Imagen decorativa inferior -->
    <div class="illustration">
        <div class="character"></div>
    </div>
</section>

<script>
// Toggle tema oscuro
const toggleTheme = document.getElementById('toggle-theme');
const currentTheme = localStorage.getItem('theme');

if (currentTheme === 'dark') {
    document.body.classList.add('dark-theme');
    toggleTheme.checked = true;
}

toggleTheme.addEventListener('change', function() {
    if (this.checked) {
        document.body.classList.add('dark-theme');
        localStorage.setItem('theme', 'dark');
    } else {
        document.body.classList.remove('dark-theme');
        localStorage.setItem('theme', 'light');
    }
});

// Auto-ocultar mensajes después de 5 segundos
setTimeout(() => {
    const mensajes = document.querySelectorAll('.mensaje');
    mensajes.forEach(msg => {
        msg.style.opacity = '0';
        setTimeout(() => msg.style.display = 'none', 300);
    });
}, 5000);

// Toggle visibilidad de contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.toggle-password');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Verificar fortaleza de contraseña
function checkPasswordStrength() {
    const password = document.getElementById('password_nueva').value;
    const meter = document.getElementById('password-strength-meter');
    
    let strength = 0;
    
    // Verificar longitud
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Verificar mayúsculas
    if (/[A-Z]/.test(password)) strength++;
    
    // Verificar números
    if (/[0-9]/.test(password)) strength++;
    
    // Verificar caracteres especiales
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Actualizar medidor
    meter.className = 'strength-meter';
    if (strength === 0) {
        meter.style.width = '0%';
    } else if (strength <= 2) {
        meter.classList.add('weak');
        meter.style.width = '33%';
    } else if (strength <= 4) {
        meter.classList.add('medium');
        meter.style.width = '66%';
    } else {
        meter.classList.add('strong');
        meter.style.width = '100%';
    }
}

// Verificar coincidencia de contraseñas
function checkPasswordMatch() {
    const password = document.getElementById('password_nueva').value;
    const confirm = document.getElementById('password_confirmar').value;
    const message = document.getElementById('password-match-message');
    
    if (confirm === '') {
        message.textContent = '';
        message.className = 'password-match-message';
        return;
    }
    
    if (password === confirm) {
        message.textContent = '✓ Las contraseñas coinciden';
        message.className = 'password-match-message match';
    } else {
        message.textContent = '✗ Las contraseñas no coinciden';
        message.className = 'password-match-message no-match';
    }
}
</script>
</body>
</html>