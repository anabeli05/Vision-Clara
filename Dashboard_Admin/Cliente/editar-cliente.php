<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que NO sea Super Admin (puede ser Admin, Usuario, etc.)
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/inicio/InicioAdmin.php');
    exit;
}

// Conexión a la base de datos
require_once '../../Base de Datos/conexion.php';

// Inicializar variables
$cliente = [];
$error = '';
$exito = '';
$id = '';

// Obtener el ID del cliente desde la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    header('Location: GestionAdmin.php');
    exit;
}

try {
    // Obtener datos del cliente
    $stmt = $conn->prepare("SELECT No_Afiliado, Nombre, Correo, Telefono FROM clientes WHERE No_Afiliado = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        $error = "Cliente no encontrado";
    }
} catch(PDOException $e) {
    $error = "Error al cargar el cliente: " . $e->getMessage();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        
        // Validaciones básicas
        if (empty($nombre)) {
            $error = "El nombre es requerido";
        } elseif (preg_match('/[0-9]/', $nombre)) {
            $error = "El nombre no puede contener números";
        } elseif (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = "Correo electrónico inválido";
        } elseif (empty($telefono)) {
            $error = "El teléfono es requerido";
        } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
            $error = "El teléfono debe contener exactamente 10 dígitos";
        } else {
            // Verificar si el correo ya existe en otro cliente
            $stmt = $conn->prepare("SELECT No_Afiliado FROM clientes WHERE Correo = ? AND No_Afiliado != ?");
            $stmt->execute([$correo, $id]);
            $correo_existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($correo_existente) {
                $error = "El correo electrónico ya está registrado por otro cliente";
            } else {
                // Actualizar cliente
                $stmt = $conn->prepare("UPDATE clientes SET Nombre = ?, Correo = ?, Telefono = ? WHERE No_Afiliado = ?");
                $stmt->execute([$nombre, $correo, $telefono, $id]);
                
                $exito = "Cliente actualizado correctamente";
                
                // Recargar datos del cliente
                $stmt = $conn->prepare("SELECT No_Afiliado, Nombre, Correo, Telefono FROM clientes WHERE No_Afiliado = ?");
                $stmt->execute([$id]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Editar Cliente - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="editar-cliente.css">
    <link rel="stylesheet" href="../Dashboard/SidebarAdmin.css">
</head>
<body>

    <?php include '../Dashboard/SidebarAdmin.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-edit"></i> Editar Cliente</h1>
            <a href='GestionAdmin.php' class="btn-volver">
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
        
        <?php if ($cliente): ?>
        <div class="formulario-container">
            <form method="POST" class="form-editar" id="formEditarCliente" novalidate>
                <div class="form-group">
                    <label for="no_afiliado">No. Afiliado (No editable)</label>
                    <input type="text" id="no_afiliado" value="<?php echo htmlspecialchars($cliente['No_Afiliado']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo htmlspecialchars($cliente['Nombre']); ?>"
                           pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+"
                           title="El nombre solo puede contener letras y espacios"
                           maxlength="100">
                    <small class="form-hint">Solo letras y espacios, sin números</small>
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" required 
                           value="<?php echo htmlspecialchars($cliente['Correo']); ?>"
                           pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                           title="Ingrese un correo electrónico válido"
                           maxlength="100">
                    <small class="form-hint">Formato: usuario@dominio.com</small>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" required 
                           value="<?php echo htmlspecialchars($cliente['Telefono']); ?>"
                           pattern="[0-9]{10}"
                           title="El teléfono debe contener exactamente 10 dígitos"
                           maxlength="10"
                           minlength="10">
                    <small class="form-hint">10 dígitos sin espacios ni guiones</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="GestionAdmin.php" class="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> No se pudo cargar el cliente
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Validación en tiempo real del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formEditarCliente');
            const nombreInput = document.getElementById('nombre');
            const correoInput = document.getElementById('correo');
            const telefonoInput = document.getElementById('telefono');

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

            // Validar teléfono (solo números, exactamente 10 dígitos)
            telefonoInput.addEventListener('input', function() {
                const telefonoValue = this.value.replace(/\D/g, ''); // Remover caracteres no numéricos
                this.value = telefonoValue; // Actualizar el valor sin caracteres no numéricos
                
                if (telefonoValue.length !== 10 && telefonoValue.length > 0) {
                    this.setCustomValidity('El teléfono debe tener exactamente 10 dígitos');
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
                
                // Validar teléfono
                if (!/^[0-9]{10}$/.test(telefonoInput.value)) {
                    showError('El teléfono debe contener exactamente 10 dígitos');
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

            // Limitar entrada de teléfono a solo números
            telefonoInput.addEventListener('keypress', function(e) {
                const charCode = e.which ? e.which : e.keyCode;
                if (charCode < 48 || charCode > 57) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });

            // Prevenir espacios en el teléfono
            telefonoInput.addEventListener('keydown', function(e) {
                if (e.key === ' ') {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>