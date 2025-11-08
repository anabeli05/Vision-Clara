<?php
// Utilidades para envío de correos electrónicos
 

// Incluir PHPMailer
// Resolver rutas de PHPMailer considerando diferentes ubicaciones del vendor
$__vendorRoot = __DIR__ . '/../vendor/phpmailer/PHPMailer-6.8.0/src';
$__vendorLogin = __DIR__ . '/../Login/vendor/phpmailer/PHPMailer-6.8.0/src';
$__phpmailerPath = is_dir($__vendorRoot) ? $__vendorRoot : $__vendorLogin;
require_once $__phpmailerPath . '/PHPMailer.php';
require_once $__phpmailerPath . '/SMTP.php';
require_once $__phpmailerPath . '/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Configuración SMTP centralizada
 */
function obtenerConfiguracionSMTP() {
    return [
        'host' => 'smtp.gmail.com',
        'username' => 'vision.clara.optica.mzo@gmail.com',
        'password' => 'crlv dehs etaw xvty',
        'port' => 587,
        'encryption' => 'tls',
        'from_email' => 'vision.clara.optica.mzo@gmail.com',
        'from_name' => 'Sistema Vision Clara'
    ];
}

/**
 * Genera un código de verificación de 6 dígitos
 * @return string Código de 6 dígitos
 */
function generarCodigoVerificacion() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Configura una instancia de PHPMailer con la configuración estándar
 * @return PHPMailer Instancia configurada
 */
function configurarPHPMailer() {
    $config = obtenerConfiguracionSMTP();
    $mail = new PHPMailer(true);
    
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'];
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';
    // Habilitar depuración SMTP y enviar salida al log
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        if (function_exists('writeLog')) {
            writeLog("SMTP[$level]: " . $str);
        }
    };
    
    // Opciones adicionales de seguridad
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    
    // Remitente predeterminado
    $mail->setFrom($config['from_email'], $config['from_name']);
    
    return $mail;
}

/**
 * Envía el código de recuperación de contraseña por email
 * @param string $destinatario Email del destinatario
 * @param string $nombreUsuario Nombre del usuario
 * @param string $codigo Código de verificación de 6 dígitos
 * @return bool True si se envió correctamente, False en caso contrario
 */
function enviarCodigoRecuperacion($destinatario, $nombreUsuario, $codigo) {
    try {
        $mail = configurarPHPMailer();
        
        // Destinatario
        $mail->addAddress($destinatario);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Código de recuperación de contraseña - Vision Clara';
        $mail->Body = construirHTMLCodigoRecuperacion($nombreUsuario, $codigo);
        $mail->AltBody = construirTextoPlanoCodigoRecuperacion($nombreUsuario, $codigo);
        
        // Enviar
        $ok = $mail->send();
        if (!$ok && function_exists('writeLog')) {
            writeLog('ERROR PHPMailer (send=false): ' . $mail->ErrorInfo . ' - Destinatario: ' . $destinatario);
        }
        return $ok;
        
    } catch (Exception $e) {
        // Registrar error en log
        if (function_exists('writeLog')) {
            writeLog("ERROR al enviar email: " . $e->getMessage() . " - Destinatario: " . $destinatario);
        }
        return false;
    }
}

/**
 * Construye el HTML del email de recuperación
 * @param string $nombreUsuario Nombre del usuario
 * @param string $codigo Código de verificación
 * @return string HTML del email
 */
function construirHTMLCodigoRecuperacion($nombreUsuario, $codigo) {
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code-box { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
            .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 8px; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>🔐 Recuperación de Contraseña</h1>
            </div>
            <div class='content'>
                <p>Hola <strong>" . htmlspecialchars($nombreUsuario) . "</strong>,</p>
                
                <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>Vision Clara</strong>.</p>
                
                <div class='code-box'>
                    <p style='margin: 0 0 10px 0; color: #666;'>Tu código de verificación es:</p>
                    <div class='code'>" . htmlspecialchars($codigo) . "</div>
                </div>
                
                <div class='warning'>
                    <strong>⚠️ Importante:</strong>
                    <ul style='margin: 10px 0; padding-left: 20px;'>
                        <li>Este código es válido por <strong>30 minutos</strong></li>
                        <li>No compartas este código con nadie</li>
                        <li>Si no solicitaste este cambio, ignora este mensaje</li>
                    </ul>
                </div>
                
                <p>Para completar el proceso, ingresa este código en la página de recuperación de contraseña.</p>
                
                <p style='margin-top: 30px;'>Saludos,<br><strong>Equipo Vision Clara</strong></p>
            </div>
            <div class='footer'>
                <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                <p>&copy; " . date('Y') . " Vision Clara. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Construye la versión de texto plano del email
 * @param string $nombreUsuario Nombre del usuario
 * @param string $codigo Código de verificación
 * @return string Texto plano del email
 */
function construirTextoPlanoCodigoRecuperacion($nombreUsuario, $codigo) {
    return "
    RECUPERACIÓN DE CONTRASEÑA - VISION CLARA
    
    Hola " . $nombreUsuario . ",
    
    Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.
    
    Tu código de verificación es: " . $codigo . "
    
    IMPORTANTE:
    - Este código es válido por 30 minutos
    - No compartas este código con nadie
    - Si no solicitaste este cambio, ignora este mensaje
    
    Para completar el proceso, ingresa este código en la página de recuperación de contraseña.
    
    Saludos,
    Equipo Vision Clara
    
    ---
    Este es un correo automático, por favor no respondas a este mensaje.
    © " . date('Y') . " Vision Clara. Todos los derechos reservados.
    ";
}

/**
 * Verifica si un código de recuperación es válido
 * @param string $codigoIngresado Código ingresado por el usuario
 * @param string $codigoAlmacenado Código almacenado en sesión
 * @param int $expiracion Timestamp de expiración
 * @return array Array con 'valido' (bool) y 'mensaje' (string)
 */
function verificarCodigoRecuperacion($codigoIngresado, $codigoAlmacenado, $expiracion) {
    // Verificar expiración
    if (time() > $expiracion) {
        return [
            'valido' => false,
            'mensaje' => 'El código ha expirado. Solicita uno nuevo.'
        ];
    }
    
    // Verificar código
    if ($codigoIngresado !== $codigoAlmacenado) {
        return [
            'valido' => false,
            'mensaje' => 'El código ingresado es incorrecto.'
        ];
    }
    
    return [
        'valido' => true,
        'mensaje' => 'Código verificado correctamente.'
    ];
}