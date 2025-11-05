<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que NO sea Super Admin (puede ser Admin, Usuario, etc.)
if ($user_rol === 'Super Admin') {
    header('Location: ../../Dashboard_SuperAdmin/inicio/InicioSA.php');
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
                    
                    $success = "Cliente registrado exitosamente";
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
    <link rel="stylesheet" href="Registro-Cliente.css">
    <link rel="stylesheet" href="../Dashboard/sidebar.css">
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>

    <section>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-user-plus" data-no-translate></i> Registro de Clientes</h1>
        </div>
    

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
        <form method="POST" class="formulario-registro">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="registro" value="1">

            <div class="formulario">
                <label for="Nombre">Nombre Completo:</label>
                <input type="text" id="Nombre" name="Nombre" required
                        value="<?php echo htmlspecialchars($_POST['Nombre']?? ''); ?>">
            </div>

            <div class="formulario">
                <label for="Correo">Correo:</label>
                <input type="email" id="Correo" name="Correo" required
                        value="<?php echo htmlspecialchars($_POST['Correo']?? ''); ?>">
            </div>

            <div class="formulario">
                <label for="Numero">Numero de Telefono:</label>
                <input type="number" id="Numero" name="Numero" required
                        minlength="12">
            </div>

            <p class="info-message">
                <i class="fas fa-info-circle"></i> El número de afiliado se generará automáticamente
            </p>

            <!-- Botones para Registro y Cancelacion -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Registrar 
             </button>
                <a href='../Cliente/Gestion-Cliente.php' class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</section>
</body>
</html>
