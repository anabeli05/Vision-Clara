<?php
//se incluye la utilidad del log
session_start();
include_once '../Base de Datos/log_utils.php';

    //procesar si es POST(seguridad)
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        writeLog('WARN!!: Acceso directo sin POST');
        header('Location: inicioSecion.php');
        exit;
    }

    writeLog('INFO: iniciando validacion del login');

    //validacion de campos existentes
    if(!isset($_POST['email'], $_POST['password'])){
        writeLog('WARN: Campos faltantes');
        $_SESSION['login_error'] = 'Error en el formulario';
        header('Location: inicioSecion.php');
        exit;
    }

    //Limpiar espacios
    writeLog('DEBUG: Password RAW recibido del POST: [' . $_POST['password'] . '] - Longitud: ' . strlen($_POST['password']));
    
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    writeLog('INFO: intento de login - Email: ' .$email);
    writeLog('DEBUG: Password después de trim: [' . $password . '] - Longitud: ' . strlen($password));

    // Validar que los campos no estén vacíos o con espacios
    if(empty($email) || empty($password)) {
        writeLog("WARN: Campos vacios");
        $_SESSION['login_error'] = 'Complete todos los campos de favor';
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_remember'] = $remember;
        header("location: inicioSecion.php");
        exit;
    }

    //validar formato del email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        writeLog('WARN: Email invalido: ' . $email);
        $_SESSION['login_error'] = 'Correo invalido';
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_remember'] = $remember;
        header('Location: inicioSecion.php');
        exit;
    }

    //validar longitud de contraseña (puedes quitar esto también si quieres)
    if(strlen($password) < 3 || strlen($password) > 255){
        writeLog('WARN: Longitud de contraseña invalida');
        $_SESSION['login_error'] = 'Contraseña muy corta o larga';
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_remember'] = $remember;
        header('Location: inicioSecion.php');
        exit;
    }

    //conectamos la base de datos
    require_once '../Base de Datos/conexion.php';

    //Proteccion de datos
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as intentos 
        FROM login_attempts 
        WHERE ip_address = ? 
        AND success = 0 
        AND attempted_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();   
    $resultado = $stmt->get_result();
    $intentos = $resultado->fetch_assoc()['intentos'];

    if($intentos >= 9) {
        writeLog("WARN: Demasiados intentos fallidos desde IP: " . $ip);
        $_SESSION['login_error'] = "Demasiados intentos fallidos. Espere 2 minutos.";
        $_SESSION['temp_email'] = $email; 
        $_SESSION['temp_remember'] = $remember;
        header('Location: inicioSecion.php');
        exit;
    }

    //consulta a la base de datos el usuario
    try {
        $stmt = $conexion->prepare("
            SELECT Usuario_ID, Nombre, Correo, Contraseña, Rol, activo 
            FROM usuarios 
            WHERE Correo = ? 
            LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            // Verificar si está activo
            if($usuario['activo'] != 1) {
                writeLog("WARN: Usuario inactivo - Email: " . $email);
                $_SESSION['login_error'] = "Cuenta desactivada. Contacte al administrador.";
                $_SESSION['temp_email'] = $email; 
                $_SESSION['temp_remember'] = $remember;
                header('Location: inicioSecion.php');
                exit;
            }
            
            // ⚠️ VERIFICACIÓN SIN HASH - COMPARACIÓN DIRECTA ⚠️
            writeLog("DEBUG: Password ingresado: [" . $password . "] - Longitud: " . strlen($password) . " - Hex: " . bin2hex($password));
            writeLog("DEBUG: Password en BD: [" . $usuario['Contraseña'] . "] - Longitud: " . strlen($usuario['Contraseña']) . " - Hex: " . bin2hex($usuario['Contraseña']));
            writeLog("DEBUG: Comparación (===): " . ($password === $usuario['Contraseña'] ? 'COINCIDE' : 'NO COINCIDE'));
            
            if($password === $usuario['Contraseña']) {
                writeLog("SUCCESS: Login exitoso - Usuario ID: " . $usuario['Usuario_ID']);
                
                // Regenerar ID de sesión (seguridad)
                session_regenerate_id(true);
                
                // Guardar datos en sesión
                $_SESSION['user_id'] = $usuario['Usuario_ID'];
                $_SESSION['user_email'] = $usuario['Correo'];
                $_SESSION['user_nombre'] = $usuario['Nombre'];
                $_SESSION['user_rol'] = $usuario['Rol'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                //Implementar "Recuérdame" 
                if($remember) {
                    writeLog("INFO: Creando token de recuérdame - Usuario ID: " . $usuario['Usuario_ID']);
                    
                    // Generar token aleatorio seguro (64 caracteres)
                    $token = bin2hex(random_bytes(32));
                    $user_id = $usuario['Usuario_ID'];
                    
                    // Guardar token en la base de datos (válido por 30 días)
                    $stmt_token = $conexion->prepare("
                        INSERT INTO remember_tokens (user_id, token, expires_at) 
                        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
                        ON DUPLICATE KEY UPDATE 
                        token = VALUES(token), 
                        expires_at = VALUES(expires_at)");
                    $stmt_token->bind_param("is", $user_id, $token);
                    $stmt_token->execute();
                    
                    // Crear cookie segura en el navegador
                    setcookie('remember_token', $token, [
                        'expires' => time() + (30 * 24 * 60 * 60),
                        'path' => '/',
                        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    
                    writeLog("SUCCESS: Token de recuérdame creado exitosamente");
                }

                // Registrar intento exitoso
                $stmt_success = $conexion->prepare("
                    INSERT INTO login_attempts (email, ip_address, success) 
                    VALUES (?, ?, 1)");
                $stmt_success->bind_param("ss", $email, $ip);
                $stmt_success->execute();
                
                // Actualizar último login
                $stmt_update = $conexion->prepare("
                    UPDATE usuarios 
                    SET ultimo_login = NOW() 
                    WHERE Usuario_ID = ?");
                $stmt_update->bind_param("i", $usuario['Usuario_ID']);
                $stmt_update->execute();

                //redireccion segun el rol
                if($usuario['Rol'] === 'Super Admin') {
                    header("location: ../Dashboard_SuperAdmin/inicio/SuperInicio.php");
                } else {
                    header("location: ../Dashboard_Admin/inicio/inicioAdmin.php");
                }
                exit;
            } else {
                //contraseña incorrecta
                writeLog("WARN: Contraseña incorrecta - Email: " . $email);
                
                // Registrar intento fallido
                $stmt_fail = $conexion->prepare("
                    INSERT INTO login_attempts (email, ip_address, success) 
                    VALUES (?, ?, 0)");
                $stmt_fail->bind_param("ss", $email, $ip);
                $stmt_fail->execute();
                
                $_SESSION['login_error'] = 'Credenciales incorrectas';
                $_SESSION['temp_email'] = $email;
                $_SESSION['temp_remember'] = $remember;
                header('Location: inicioSecion.php');
                exit;
            }
                
        } else {
            //cuando el usuario no existe
            writeLog('WARN: usuario no encontrado - Email: ' . $email);
                
            $stmt_fail = $conexion->prepare("
                INSERT INTO login_attempts(email, ip_address, success)
                VALUES(?, ?, 0)");
            $stmt_fail->bind_param("ss", $email, $ip);
            $stmt_fail->execute();

            $_SESSION['login_error'] = 'Credenciales incorrectas';
            $_SESSION['temp_email'] = $email; 
            $_SESSION['temp_remember'] = $remember;
            header('Location: inicioSecion.php');
            exit;
        }
    }catch(Exception $e) {
        writeLog('ERROR: excepcion de login - ' . $e->getMessage());
        $_SESSION['login_error'] = 'Error del sistema. Intente mas tarde.';
        header('Location: inicioSecion.php');
        exit;
    } finally {
        if(isset($conexion)) {
            $conexion->close();
        }
    }
?>