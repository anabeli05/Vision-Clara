<?php
include("../conexion.php");
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['usuario']) && $_SESSION['rol'] == 'admin') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $conexion_obj = new Conexion();
    $conexion_obj->abrir_conexion();
    $conexion = $conexion_obj->conexion;

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario=? AND password=MD5(?)");
    $stmt->bind_param("ss", $usuario, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }

    $conexion_obj->cerrar_conexion();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - Óptica Visión Clara</title>
    <link rel="stylesheet" href="../css/estilo_admin.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="login-container">
    <h2>Acceso Administrativo</h2>
    <form method="POST">
        <div class="input-group">
            <input type="text" name="usuario" placeholder="Usuario" required>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Contraseña" required>
        </div>
        <button type="submit">🔐 Iniciar Sesión</button>
    </form>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
</div>
</body>
</html>
