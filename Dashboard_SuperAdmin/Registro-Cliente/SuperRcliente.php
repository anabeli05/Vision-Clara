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

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inicializar variables
$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token de seguridad inválido";
    } else {
        $nombre = trim($_POST['Nombre'] ?? '');
        $correo = trim($_POST['Correo'] ?? '');
        $telefono = trim($_POST['Numero'] ?? '');
        
        // Validaciones básicas
        if (empty($nombre) || empty($correo) || empty($telefono)) {
            $error = "Todos los campos son obligatorios";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
            $error = "El nombre solo puede contener letras y espacios";
        } elseif (!preg_match('/^\d{10}$/', $telefono)) {
            $error = "El número de teléfono debe tener exactamente 10 dígitos";
        } else {
            try {
                // Generar número de afiliado único de 6 caracteres
                $no_afiliado = null;
                $intentos = 0;
                $max_intentos = 10;
                
                while ($no_afiliado === null && $intentos < $max_intentos) {
                    // Generar número aleatorio de 6 dígitos
                    $temp_afiliado = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                    
                    // Verificar si ya existe
                    $stmt = $conn->prepare("SELECT No_Afiliado FROM clientes WHERE No_Afiliado = ?");
                    $stmt->execute([$temp_afiliado]);
                    
                    if (!$stmt->fetch()) {
                        $no_afiliado = $temp_afiliado;
                    }
                    $intentos++;
                }
                
                if ($no_afiliado === null) {
                    $error = "No se pudo generar un número de afiliado único. Intente nuevamente.";
                } else {
                    // Insertar nuevo cliente
                    $stmt = $conn->prepare("INSERT INTO clientes (No_Afiliado, Nombre, Correo, Telefono) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$no_afiliado, $nombre, $correo, $telefono]);
                    
                    $success = "Cliente registrado exitosamente. Número de afiliado: " . $no_afiliado;
                    // Limpiar el formulario
                    $_POST = [];
                }
            } catch(PDOException $e) {
                $error = "Error al registrar el cliente: " . $e->getMessage();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Clientes - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="SuperRcliente.css">
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'> 

</head>
<body>

    <?php include '../Dashboard/SuperSidebar.php'; ?>

    <section>
    <div class="contenedor-principal">
    
        <!-- mensaje de error de la base de datos -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="POST" class="formulario-registro" id="registroForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="registro" value="1">

            <div class="formulario">
                <label for="Nombre">Nombre Completo:</label>
                <input type="text" id="Nombre" name="Nombre" required 
                       pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" 
                       title="Solo se permiten letras y espacios"
                       value="<?php echo htmlspecialchars($_POST['Nombre']?? ''); ?>">
                <small>Solo letras y espacios</small>
            </div>

            <div class="formulario">
                <label for="Correo">Correo:</label>
                <input type="email" id="Correo" name="Correo" required
                       value="<?php echo htmlspecialchars($_POST['Correo']?? ''); ?>">
            </div>

            <div class="formulario">
                <label for="Numero">Número de Teléfono:</label>
                <input type="tel" id="Numero" name="Numero" required
                       pattern="\d{10}"
                       title="Debe tener exactamente 10 dígitos"
                       maxlength="10"
                       placeholder="10 dígitos"
                       value="<?php echo htmlspecialchars($_POST['Numero']?? ''); ?>">
                <small>Exactamente 10 dígitos</small>
            </div>

            <p class="info-message">
                <i class="fas fa-info-circle"></i> El número de afiliado se generará automáticamente
            </p>

            <!-- Botones para Registro y Cancelacion -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Registrar 
                </button>
                <a href='../Cliente/SuperGestion.php' class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</section>

<script>
// Validación del teléfono en tiempo real
document.getElementById('Numero').addEventListener('input', function(e) {
    // Remover cualquier caracter que no sea número
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Limitar a 10 dígitos
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});

// Validación del nombre en tiempo real
document.getElementById('Nombre').addEventListener('input', function(e) {
    // Solo permitir letras, espacios y caracteres especiales en español
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
});

// Validación antes de enviar el formulario
document.getElementById('registroForm').addEventListener('submit', function(e) {
    const nombre = document.getElementById('Nombre').value;
    const telefono = document.getElementById('Numero').value;
    
    // Validar que el nombre solo contenga letras y espacios
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
        e.preventDefault();
        alert('El nombre solo puede contener letras y espacios');
        return false;
    }
    
    // Validar que el teléfono tenga exactamente 10 dígitos
    if (!/^\d{10}$/.test(telefono)) {
        e.preventDefault();
        alert('El número de teléfono debe tener exactamente 10 dígitos');
        return false;
    }
    
    return true;
});
</script>

</body>
</html>
