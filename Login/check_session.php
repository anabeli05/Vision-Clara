<?php
/**
 * Verificación de sesión para proteger páginas del dashboard
 * Incluir este archivo al inicio de cada página protegida
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // No está logueado, redirigir al login
    header('Location: ../../Login/inicioSecion.php');
    exit;
}

// Verificar si la sesión ha expirado (opcional: 2 horas de inactividad)
$timeout_duration = 7200; // 2 horas en segundos
if (isset($_SESSION['login_time'])) {
    $elapsed_time = time() - $_SESSION['login_time'];
    if ($elapsed_time > $timeout_duration) {
        // Sesión expirada
        session_unset();
        session_destroy();
        header('Location: ../../Login/inicioSecion.php?timeout=1');
        exit;
    }
}

// Actualizar el tiempo de última actividad
$_SESSION['last_activity'] = time();

// Verificar que las variables de sesión necesarias existan
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol'])) {
    // Sesión corrupta, cerrar y redirigir
    session_unset();
    session_destroy();
    header('Location: ../../Login/inicioSecion.php');
    exit;
}

// Variables disponibles para usar en las páginas
$user_id = $_SESSION['user_id'];
$user_nombre = $_SESSION['user_nombre'] ?? 'Usuario';
$user_email = $_SESSION['user_email'] ?? '';
$user_rol = $_SESSION['user_rol'];
?>
