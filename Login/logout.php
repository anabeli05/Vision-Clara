<?php
/**
 * Cierra sesión y elimina tokens de "Recuérdame"
 */

session_start();
include_once '../Base de Datos/log_utils.php';

// Registrar logout si hay usuario logueado
if(isset($_SESSION['user_id'])) {
    writeLog("INFO: Logout - Usuario ID: " . $_SESSION['user_id']);
    
    // Si existe token de recuérdame, eliminarlo de BD
    if(isset($_COOKIE['remember_token'])) {
        require_once '../Base de datos/conexion.php';
        
        try {
            $token = $_COOKIE['remember_token'];
            $user_id = $_SESSION['user_id'];
            
            // Eliminar token de la base de datos
            $stmt = $conexion->prepare("
                DELETE FROM remember_tokens 
                WHERE user_id = ? AND token = ?
            ");
            $stmt->bind_param("is", $user_id, $token);
            $stmt->execute();
            
            writeLog("INFO: Token de recuérdame eliminado - Usuario ID: " . $user_id);
            
        } catch(Exception $e) {
            writeLog("ERROR: Error al eliminar token - " . $e->getMessage());
        } finally {
            if(isset($conexion)) {
                $conexion->close();
            }
        }
        
        // Eliminar cookie
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Mensaje de éxito
session_start();
$_SESSION['login_success'] = 'Sesión cerrada correctamente';

// Redirigir al login
header('Location: inicioSecion.php');
exit;
?>