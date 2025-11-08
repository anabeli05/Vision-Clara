<?php
session_start();
require_once '../Base de Datos/email_utils.php';
require_once '../Base de Datos/log_utils.php';

// Verificar que hay una sesión activa de recuperación
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_nombre'])) {
    writeLog("WARN: Intento de reenvío sin sesión válida");
    header('Location: recuperarContra.php');
    exit;
}

// Generar un nuevo código
$nuevoCodigo = generarCodigoVerificacion();

// Actualizar la sesión con el nuevo código
$_SESSION['reset_code'] = $nuevoCodigo;
$_SESSION['reset_expires'] = time() + 1800; // Nuevo tiempo de expiración: 30 minutos

// Enviar el nuevo código por email
$emailEnviado = enviarCodigoRecuperacion(
    $_SESSION['reset_email'], 
    $_SESSION['reset_nombre'], 
    $nuevoCodigo
);

if ($emailEnviado) {
    writeLog("SUCCESS: Código reenviado a: " . $_SESSION['reset_email']);
    $_SESSION['success'] = "Se ha enviado un nuevo código a tu correo electrónico.";
} else {
    writeLog("ERROR: No se pudo reenviar el código a: " . $_SESSION['reset_email']);
    $_SESSION['error'] = "Error al enviar el correo. Intente nuevamente más tarde.";
}

// Redireccionar de vuelta a la página de código
header('Location: codigoRecuperacion.php');
exit;
?>