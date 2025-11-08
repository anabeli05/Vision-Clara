<?php
session_start();
require_once '../Base de Datos/log_utils.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: recuperarContra.php');
    exit;
}

// Verificar que hay una sesión de recuperación activa
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code']) || !isset($_SESSION['reset_expires'])) {
    $_SESSION['codigo_error'] = "Sesión expirada. Solicite un nuevo código.";
    header('Location: recuperarContra.php');
    exit;
}

// Verificar si el código ha expirado
if (time() > $_SESSION['reset_expires']) {
    $_SESSION['codigo_error'] = "El código ha expirado. Solicite uno nuevo.";
    unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_expires'], $_SESSION['reset_user_id']);
    header('Location: recuperarContra.php');
    exit;
}

// Obtener el código ingresado por el usuario
$code1 = $_POST['code1'] ?? '';
$code2 = $_POST['code2'] ?? '';
$code3 = $_POST['code3'] ?? '';
$code4 = $_POST['code4'] ?? '';
$code5 = $_POST['code5'] ?? '';
$code6 = $_POST['code6'] ?? '';

$codigoIngresado = $code1 . $code2 . $code3 . $code4 . $code5 . $code6;

// Validar que el código tenga 6 dígitos
if (strlen($codigoIngresado) !== 6 || !ctype_digit($codigoIngresado)) {
    $_SESSION['codigo_error'] = "Por favor, ingrese un código válido de 6 dígitos.";
    writeLog("WARN: Código inválido ingresado para: " . $_SESSION['reset_email']);
    header('Location: codigoRecuperacion.php');
    exit;
}

// Verificar que el código coincida
if ($codigoIngresado === $_SESSION['reset_code']) {
    // Código correcto - permitir cambio de contraseña
    writeLog("SUCCESS: Código verificado correctamente para: " . $_SESSION['reset_email']);
    
    // Marcar que el código fue verificado
    $_SESSION['codigo_verificado'] = true;
    
    // Redireccionar a la página de nueva contraseña
    header('Location: nuevaContra.php');
    exit;
} else {
    // Código incorrecto
    writeLog("WARN: Código incorrecto ingresado para: " . $_SESSION['reset_email'] . " - Esperado: " . $_SESSION['reset_code'] . " - Recibido: " . $codigoIngresado);
    $_SESSION['codigo_error'] = "Código incorrecto. Verifique e intente nuevamente.";
    header('Location: codigoRecuperacion.php');
    exit;
}
?>
