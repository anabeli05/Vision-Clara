<?php
//Verifica y procesa tokens de "Recuérdame"


// Solo ejecutar si existe cookie y no hay sesión activa
if(!isset($_SESSION['logged_in']) && isset($_COOKIE['remember_token'])) {
    
    require_once '../Base de datos/conexion.php';
    include_once '../Base de Datos/log_utils.php';
    
    $token = $_COOKIE['remember_token'];
    
    try {
        // Buscar token válido y no expirado
        $stmt = $conexion->prepare("
            SELECT rt.user_id, u.Usuario_ID, u.Nombre, u.Correo, u.Rol, u.activo
            FROM remember_tokens rt
            INNER JOIN usuarios u ON rt.user_id = u.Usuario_ID
            WHERE rt.token = ? 
            AND rt.expires_at > NOW()
            LIMIT 1
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            // Verificar que el usuario esté activo
            if($usuario['activo'] == 1) {
                writeLog("INFO: Auto-login exitoso vía Remember Token - Usuario ID: " . $usuario['Usuario_ID']);
                
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);
                
                // Establecer sesión
                $_SESSION['user_id'] = $usuario['Usuario_ID'];
                $_SESSION['user_email'] = $usuario['Correo'];
                $_SESSION['user_nombre'] = $usuario['Nombre'];
                $_SESSION['user_rol'] = $usuario['Rol'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['auto_login'] = true; // Marcar como auto-login
                
                // Renovar token (extender 30 días más)
                $nuevo_token = bin2hex(random_bytes(32));
                $stmt_renovar = $conexion->prepare("
                    UPDATE remember_tokens 
                    SET token = ?, 
                        expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY),
                        created_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt_renovar->bind_param("si", $nuevo_token, $usuario['Usuario_ID']);
                $stmt_renovar->execute();
                
                // Actualizar cookie
                setcookie('remember_token', $nuevo_token, [
                    'expires' => time() + (30 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
                
                // Registrar login automático
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt_log = $conexion->prepare("
                    INSERT INTO login_attempts (email, ip_address, success) 
                    VALUES (?, ?, 1)
                ");
                $stmt_log->bind_param("ss", $usuario['Correo'], $ip);
                $stmt_log->execute();
                
                // Actualizar último login
                $stmt_update = $conexion->prepare("
                    UPDATE usuarios 
                    SET ultimo_login = NOW() 
                    WHERE Usuario_ID = ?
                ");
                $stmt_update->bind_param("i", $usuario['Usuario_ID']);
                $stmt_update->execute();
                
                // Redirigir según rol
                if($usuario['Rol'] === 'Super Admin') {
                    header('Location: ../Dashboard_SuperAdmin/inicio/SuperInicio.php');
                } else {
                    header('Location: ../Dashboard_Admin/inicio/InicioAdmin.php');
                }
                exit;
                
            } else {
                // Usuario inactivo - eliminar token
                writeLog("WARN: Auto-login fallido - Usuario inactivo: " . $usuario['Usuario_ID']);
                eliminarToken($conexion, $token);
            }
            
        } else {
            // Token inválido o expirado
            writeLog("WARN: Token de remember inválido o expirado");
            eliminarToken($conexion, $token);
        }
        
    } catch(Exception $e) {
        writeLog("ERROR: Error en check_remember - " . $e->getMessage());
        eliminarToken($conexion, $token);
    } finally {
        if(isset($conexion)) {
            $conexion->close();
        }
    }
}

/**
 * Función auxiliar para eliminar token inválido
 */
function eliminarToken($conexion, $token) {
    try {
        $stmt = $conexion->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
    } catch(Exception $e) {
        writeLog("ERROR: No se pudo eliminar token - " . $e->getMessage());
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
?>