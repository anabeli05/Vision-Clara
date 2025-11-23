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

// Inicializar variables
$usuario = [];
$error = '';
$exito = '';
$id = '';

// Obtener el ID del usuario desde la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    header('Location: GestionUsuarios.php');
    exit;
}

try {
    // Obtener datos del usuario
    $stmt = $conn->prepare("SELECT Usuario_ID, Nombre, Correo, Rol FROM usuarios WHERE Usuario_ID = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $error = "Usuario no encontrado";
    }
} catch(PDOException $e) {
    $error = "Error al cargar el usuario: " . $e->getMessage();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        
        // Validaciones básicas
        if (empty($nombre)) {
            $error = "El nombre es requerido";
        } elseif (preg_match('/[0-9]/', $nombre)) {
            $error = "El nombre no puede contener números";
        } elseif (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = "Correo electrónico inválido";
        } else {
            // Verificar si el correo ya existe en otro usuario
            $stmt = $conn->prepare("SELECT Usuario_ID FROM usuarios WHERE Correo = ? AND Usuario_ID != ?");
            $stmt->execute([$correo, $id]);
            $correo_existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($correo_existente) {
                $error = "El correo electrónico ya está registrado por otro usuario";
            } else {
                // Actualizar usuario (sin modificar el rol)
                $stmt = $conn->prepare("UPDATE usuarios SET Nombre = ?, Correo = ? WHERE Usuario_ID = ?");
                $stmt->execute([$nombre, $correo, $id]);
                
                $exito = "Usuario actualizado correctamente";
                
                // Recargar datos del usuario
                $stmt = $conn->prepare("SELECT Usuario_ID, Nombre, Correo, Rol FROM usuarios WHERE Usuario_ID = ?");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    } catch(PDOException $e) {
        $error = "Error al actualizar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="editar-usuario.css">
    <link rel="stylesheet" href="../Dashboard/SuperSidebar.css">
</head>
<body>

    <?php include '../Dashboard/SuperSidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
            <a href='GestionUsuarios.php' class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($exito); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($usuario): ?>
        <div class="formulario-container">
            <form method="POST" class="form-editar" id="formEditarUsuario" novalidate>
                <div class="form-group">
                    <label for="usuario_id">ID Usuario (No editable)</label>
                    <input type="text" id="usuario_id" value="<?php echo htmlspecialchars($usuario['Usuario_ID']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo htmlspecialchars($usuario['Nombre']); ?>"
                           pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                           title="El nombre solo puede contener letras y espacios"
                           maxlength="100">
                    <small class="form-hint">Solo letras y espacios, sin números</small>
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" required 
                           value="<?php echo htmlspecialchars($usuario['Correo']); ?>"
                           pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                           title="Ingrese un correo electrónico válido"
                           maxlength="100">
                    <small class="form-hint">Formato: usuario@dominio.com</small>
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol (No editable)</label>
                    <input type="text" id="rol" value="<?php echo htmlspecialchars($usuario['Rol']); ?>" disabled>
                    <small class="form-hint">El rol del usuario no puede ser modificado</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="GestionUsuarios.php" class="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> No se pudo cargar el usuario
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Validación en tiempo real del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formEditarUsuario');
            const nombreInput = document.getElementById('nombre');
            const correoInput = document.getElementById('correo');

            // Validar nombre (solo letras y espacios)
            nombreInput.addEventListener('input', function() {
                const nombreValue = this.value;
                const letrasRegex = /^[A-Za-záéíóúÁÉÍÓÚñÑ\s]*$/;
                
                if (!letrasRegex.test(nombreValue)) {
                    this.setCustomValidity('El nombre solo puede contener letras y espacios');
                    this.style.borderColor = '#ef5350';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '';
                }
            });

            // Validar formato de correo
            correoInput.addEventListener('input', function() {
                const correoValue = this.value;
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                
                if (correoValue && !emailRegex.test(correoValue)) {
                    this.setCustomValidity('Ingrese un correo electrónico válido');
                    this.style.borderColor = '#ef5350';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '';
                }
            });

            // Validación antes de enviar el formulario
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validar nombre
                if (!/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/.test(nombreInput.value.trim())) {
                    showError('El nombre no puede contener números');
                    isValid = false;
                }
                
                // Validar correo
                if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(correoInput.value)) {
                    showError('Ingrese un correo electrónico válido');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });

            function showError(message) {
                // Crear o actualizar mensaje de error
                let errorDiv = document.querySelector('.alert-danger');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger';
                    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                    form.parentNode.insertBefore(errorDiv, form);
                } else {
                    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                }
                
                // Scroll al mensaje de error
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
</body>
</html>