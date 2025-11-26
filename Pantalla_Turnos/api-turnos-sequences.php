<?php
/**
 * api-turnos-sequences.php
 * El cerebro de la API: genera turnos, los guarda y crea el ticket PDF.
 */

// Habilitar todos los errores para depuración (pero no mostrarlos)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/api_errors.log');

// Permitimos la comunicación entre tu web y este servidor.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar si el autoloader existe
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false, 
        'error' => 'No se encontró el autoloader de Composer en: ' . $autoloadPath
    ]));
}

// Incluimos las librerías necesarias
require $autoloadPath;
use Dompdf\Dompdf;
use Dompdf\Options;
require_once '../Base de Datos/conexion.php';

// --- Función para crear el ticket en PDF (DISEÑO MEJORADO) ---
function generarPDF($numero_turno, $tipo, $afiliado = null) {
    // Limpiar CUALQUIER salida previa
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        // Configuración de DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Helvetica'); 
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', false);
        
        $dompdf = new Dompdf($options);

        // Configuración de fecha y hora
        date_default_timezone_set('America/Mexico_City'); 
        $fecha = new DateTime();
        $fecha_formateada = $fecha->format('d/m/Y h:i A');
        
        // Configuración del tipo de turno
        $tipo_display = ($tipo === 'Cliente') ? 'Cliente Afiliado' : 'Visitante';
        $afiliado_info = $afiliado ? '<p class="ticket-afiliado">No. Afiliado: <strong>' . htmlspecialchars($afiliado) . '</strong></p>' : '';
        
        // --- MANEJO DEL LOGO CON REDIMENSIONAMIENTO AUTOMÁTICO ---
        $relative_logo_path = '../Imagenes/logo_black.png';
        $logo_path = realpath(__DIR__ . '/' . $relative_logo_path);

        error_log("=== DIAGNÓSTICO DE LOGO ===");
        error_log("__DIR__: " . __DIR__);
        error_log("Ruta relativa: " . $relative_logo_path);
        error_log("Ruta absoluta (realpath): " . ($logo_path ? $logo_path : 'NULL/FALSE'));

        if ($logo_path && file_exists($logo_path)) {
            try {
                // Verificar si GD está disponible
                if (!extension_loaded('gd')) {
                    throw new Exception("Extensión GD no disponible");
                }

                // Leer información de la imagen
                $image_info = getimagesize($logo_path);
                if ($image_info === false) {
                    throw new Exception("No se pudo leer la información de la imagen");
                }
                
                $image_type = $image_info[2];
                
                // Crear imagen desde el archivo según su tipo
                if ($image_type == IMAGETYPE_PNG) {
                    $source_image = imagecreatefrompng($logo_path);
                } elseif ($image_type == IMAGETYPE_JPEG) {
                    $source_image = imagecreatefromjpeg($logo_path);
                } else {
                    throw new Exception("Tipo de imagen no soportado (solo PNG y JPG)");
                }
                
                if ($source_image === false) {
                    throw new Exception("No se pudo crear la imagen desde el archivo");
                }
                
                // Obtener dimensiones originales
                $original_width = imagesx($source_image);
                $original_height = imagesy($source_image);
                
                // Calcular nuevas dimensiones (máximo 200px de ancho)
                $max_width = 200;
                if ($original_width > $max_width) {
                    $ratio = $max_width / $original_width;
                    $new_width = $max_width;
                    $new_height = (int)($original_height * $ratio);
                } else {
                    $new_width = $original_width;
                    $new_height = $original_height;
                }
                
                // Crear imagen redimensionada
                $resized_image = imagecreatetruecolor($new_width, $new_height);
                
                if ($resized_image === false) {
                    imagedestroy($source_image);
                    throw new Exception("No se pudo crear la imagen redimensionada");
                }
                
                // Preservar transparencia para PNG
                if ($image_type == IMAGETYPE_PNG) {
                    imagealphablending($resized_image, false);
                    imagesavealpha($resized_image, true);
                    $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
                    imagefilledrectangle($resized_image, 0, 0, $new_width, $new_height, $transparent);
                }
                
                // Redimensionar
                imagecopyresampled(
                    $resized_image, 
                    $source_image, 
                    0, 0, 0, 0, 
                    $new_width, $new_height, 
                    $original_width, $original_height
                );
                
                // Convertir a base64
                ob_start();
                if ($image_type == IMAGETYPE_PNG) {
                    imagepng($resized_image, null, 9); // Máxima compresión
                    $mime_type = 'png';
                } else {
                    imagejpeg($resized_image, null, 85); // 85% calidad
                    $mime_type = 'jpeg';
                }
                $image_content = ob_get_clean();
                
                // Liberar memoria
                imagedestroy($source_image);
                imagedestroy($resized_image);
                
                if ($image_content === false || empty($image_content)) {
                    throw new Exception("No se pudo generar el contenido de la imagen");
                }
                
                $image_data = base64_encode($image_content);
                $logo_url = 'data:image/' . $mime_type . ';base64,' . $image_data;
                $logo_html = '<img src="' . $logo_url . '" alt="Logo Visión Clara" class="logo-image">';
                
                error_log("✓ Logo redimensionado exitosamente: {$original_width}x{$original_height} → {$new_width}x{$new_height}");
                error_log("✓ Tamaño optimizado: " . strlen($image_content) . " bytes (era " . filesize($logo_path) . " bytes)");
                error_log("✓ Tipo MIME: image/" . $mime_type);
                
            } catch (Exception $e) {
                error_log("✗ Error al procesar el logo: " . $e->getMessage());
                $logo_html = '<div class="logo-text">Visión clara<span style="color: #d9534f; margin-left: 5px; font-size: 28px;">👁</span></div>';
            }
        } else {
            error_log("✗ Logo NO encontrado en: " . ($logo_path ? $logo_path : 'ruta no construida'));
            $logo_html = '<div class="logo-text">Visión clara<span style="color: #d9534f; margin-left: 5px; font-size: 28px;">👁</span></div>';
        }
        error_log("=========================");
        
        // Definición del HTML con el nuevo CSS
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Turno $numero_turno</title>
            <style>
                @page {
                    margin: 0;
                    size: letter portrait;
                }
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body { 
                    font-family: Helvetica, Arial, sans-serif; 
                    margin: 0;
                    padding: 0;
                    width: 100%;
                    background: #f0f0f0;
                }
                .ticket-wrapper {
                    width: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 10mm;
                    text-align: center;
                }
                
                .ticket-container {
                    width: 300px;
                    padding: 20px;
                    background: white;
                    border: 1px solid #ccc;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    border-style: solid;
                    border-width: 2px;
                    border-color: #333;
                    border-radius: 5px; 
                    box-sizing: content-box;
                    margin: auto;
                }
                
                .ticket-content {
                    padding: 0;
                    overflow: hidden;
                }
                
                .ticket-header {
                    margin-bottom: 15px;
                    border-bottom: 2px dashed #ccc;
                    padding-bottom: 10px;
                }
                
                .logo-image {
                    max-width: 150px;
                    height: auto;
                    display: block;
                    margin: 0 auto 5px auto;
                }
                
                .logo-text {
                    font-family: 'Times New Roman', serif;
                    font-size: 24px;
                    color: #4a9fd8;
                    font-weight: bold;
                    margin-bottom: 0;
                }
                
                .ticket-label {
                    font-size: 18px;
                    font-weight: normal;
                    color: #666;
                    margin: 10px 0 5px 0;
                }
                
                .ticket-turno {
                    font-size: 60px;
                    font-weight: bold;
                    color: #d9534f;
                    letter-spacing: 2px;
                    margin: 10px 0;
                    line-height: 1;
                    padding: 5px 0;
                    border-top: 1px solid #eee;
                    border-bottom: 1px solid #eee;
                }
                
                .ticket-tipo {
                    font-size: 14px;
                    color: #333;
                    margin: 8px 0;
                    font-weight: bold;
                }
                
                .ticket-afiliado {
                    font-size: 12px;
                    margin: 5px 0;
                    color: #555;
                }
                
                .ticket-fecha {
                    font-size: 11px;
                    color: #888;
                    margin: 15px 0 20px 0;
                }
                
                .ticket-footer {
                    font-size: 12px;
                    color: #333;
                    margin-top: 10px;
                    padding-top: 10px;
                    border-top: 2px dashed #ccc;
                }
            </style>
        </head>
        <body>
            <div class="ticket-wrapper">
                <div class="ticket-container">
                    <div class="ticket-content">
                        <div class="ticket-header">
                            {$logo_html}
                        </div>
                        
                        <div class="ticket-label">SU TURNO ES</div>
                        
                        <div class="ticket-turno">$numero_turno</div>
                        
                        <p class="ticket-tipo">**$tipo_display**</p>
                        $afiliado_info

                        <p class="ticket-fecha">Fecha y Hora: $fecha_formateada</p>
                        
                        <div class="ticket-footer">
                            Agradecemos su Preferencia<br>
                            *Por favor, espere su llamado*
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;

        // Cargar el HTML en DomPDF
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Obtener el PDF
        $output = $dompdf->output();
        
        // Enviar encabezados limpios
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Turno-' . $numero_turno . '.pdf"');
        header('Content-Length: ' . strlen($output));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Enviar el PDF
        echo $output;
        exit;
        
    } catch (Exception $e) {
        // Limpiar buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Error al generar el PDF: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// --- Función para obtener el siguiente número de turno ---
function obtener_proximo_turno($mysqli, $tipo) {
    $tipo_letra = ($tipo === 'Visitante') ? 'N' : 'C'; 
    date_default_timezone_set('America/Mexico_City');
    $hoy = date('Y-m-d');
    
    $mysqli->begin_transaction();
    
    try {
        $stmt = $mysqli->prepare("SELECT ultimo_numero FROM turno_sequences WHERE tipo = ? AND fecha_secuencia = ? FOR UPDATE");
        $stmt->bind_param('ss', $tipo_letra, $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nuevo_numero = $row['ultimo_numero'] + 1;
            
            $stmt = $mysqli->prepare("UPDATE turno_sequences SET ultimo_numero = ? WHERE tipo = ? AND fecha_secuencia = ?");
            $stmt->bind_param('iss', $nuevo_numero, $tipo_letra, $hoy);
            $stmt->execute();
            $stmt->close();
        } else {
            $nuevo_numero = 1;
            
            $stmt = $mysqli->prepare("INSERT INTO turno_sequences (tipo, fecha_secuencia, ultimo_numero) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $tipo_letra, $hoy, $nuevo_numero);
            $stmt->execute();
            $stmt->close();
        }
        
        $mysqli->commit();
        
        $numero_turno = $tipo_letra . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);
        return ['success' => true, 'numero' => $numero_turno];
        
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// --- Lógica Principal ---
try {
    $conexion = new Conexion();
    $conexion->abrir_conexion();
    $mysqli = $conexion->conexion;

    // --- POST: CREAR TURNO ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
        $tipo = ucfirst(strtolower($tipo));
        
        error_log("POST recibido - Tipo: " . $tipo . " | Action: " . (isset($_POST['action']) ? $_POST['action'] : 'no definida'));

        if (!in_array($tipo, ['Visitante', 'Cliente'], true)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'error' => 'Tipo de turno no válido.',
                'tipo_recibido' => $tipo
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $afiliado = null;
        if ($tipo === 'Cliente') {
            $afiliado = isset($_POST['afiliado']) ? trim($_POST['afiliado']) : '';
            
            error_log("Validando afiliado: '$afiliado' (longitud: " . strlen($afiliado) . ")");
            
            if (empty($afiliado) || !preg_match('/^\d{6}$/', $afiliado)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'error' => 'Número de afiliado no válido. Debe tener exactamente 6 dígitos numéricos.',
                    'afiliado_recibido' => $afiliado,
                    'longitud' => strlen($afiliado)
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmt = $mysqli->prepare("SELECT No_Afiliado, Nombre FROM clientes WHERE No_Afiliado = ?");
            if ($stmt === false) {
                error_log("Error en la preparación de la consulta: " . $mysqli->error);
                throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
            }
            
            $stmt->bind_param('s', $afiliado);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar la consulta: " . $stmt->error);
                throw new Exception("Error al verificar el afiliado: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $cliente_data = $result->fetch_assoc();
            $stmt->close();

            if (!$cliente_data) {
                $debug_stmt = $mysqli->prepare("SELECT No_Afiliado FROM clientes LIMIT 5");
                $debug_stmt->execute();
                $debug_result = $debug_stmt->get_result();
                $afiliados_existentes = [];
                while ($row = $debug_result->fetch_assoc()) {
                    $afiliados_existentes[] = $row['No_Afiliado'];
                }
                $debug_stmt->close();
                
                error_log("Afiliado no encontrado. Ejemplos existentes: " . implode(', ', $afiliados_existentes));
                
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'error' => 'El número de afiliado no existe en el sistema.',
                    'afiliado_buscado' => $afiliado,
                    'ejemplos_existentes' => $afiliados_existentes,
                    'sugerencia' => 'Verifica que el número esté correcto'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            error_log("Afiliado encontrado: " . $cliente_data['Nombre']);
        }

        $seq_result = obtener_proximo_turno($mysqli, $tipo);
        
        if (!$seq_result['success']) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'error' => 'Fallo al generar número de secuencia: ' . $seq_result['error']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $numero_turno = $seq_result['numero'];

        if ($afiliado) {
            $stmt = $mysqli->prepare("INSERT INTO turnos (Numero_Turno, Tipo, No_Afiliado) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $numero_turno, $tipo, $afiliado);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO turnos (Numero_Turno, Tipo) VALUES (?, ?)");
            $stmt->bind_param('ss', $numero_turno, $tipo);
        }
        
        if (!$stmt->execute()) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'error' => 'Error al guardar el turno: ' . $stmt->error
            ], JSON_UNESCAPED_UNICODE);
            $stmt->close();
            $conexion->cerrar_conexion();
            exit;
        }
        $stmt->close();
        
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        if ($action === 'generate_pdf') {
            $conexion->cerrar_conexion();
            generarPDF($numero_turno, $tipo, $afiliado);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'turno' => $numero_turno
            ], JSON_UNESCAPED_UNICODE);
            $conexion->cerrar_conexion();
            exit;
        }
    }

    // --- GET: CONSULTAR ESTADOS ---
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        date_default_timezone_set('America/Mexico_City');
        $hoy = date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(CASE WHEN Estado = 'Espera' THEN 1 END) as en_espera,
                    COUNT(CASE WHEN Estado = 'Atendiendo' THEN 1 END) as atendiendo,
                    COUNT(CASE WHEN Estado = 'Finalizado' THEN 1 END) as finalizados,
                    COUNT(CASE WHEN Estado = 'Cancelado' THEN 1 END) as cancelados
                  FROM turnos 
                  WHERE Fecha_Solo = ?";
        
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'fecha' => $hoy,
            'estadisticas' => $stats
        ], JSON_UNESCAPED_UNICODE);

        $conexion->cerrar_conexion();
        exit;
    }

    $conexion->cerrar_conexion();

} catch (Exception $e) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>