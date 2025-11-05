<?php
// Script CLI para comprobar la conexión a la base de datos.
// Incluye la configuración/instancias definidas en "Base de Datos/conexion.php"

require_once __DIR__ . '/Base de Datos/conexion.php';

$ok = false;

echo "---- Comprobación de conexión a la base de datos ----\n";

// Probar la conexión mysqli global (variable $conexion creada por conexion.php)
echo "Probando mysqli...\n";
if (isset($conexion) && $conexion instanceof mysqli) {
    if ($conexion->connect_error) {
        fwrite(STDERR, "mysqli: error de conexión: " . $conexion->connect_error . "\n");
    } else {
        $r = $conexion->query('SELECT 1');
        if ($r) {
            echo "mysqli: OK\n";
            $ok = true;
        } else {
            fwrite(STDERR, "mysqli: error en consulta: " . $conexion->error . "\n");
        }
    }
} else {
    echo "La variable \$conexion (mysqli) no está definida. Intentando crear una conexión temporal...\n";
    // Intentar crear una conexión usando variables definidas en conexion.php si existen
    if (isset($host) && isset($usuario) && isset($db_password) && isset($base) && isset($port)) {
        try {
            $tmp = new mysqli($host, $usuario, $db_password, $base, $port);
            if ($tmp->connect_error) {
                fwrite(STDERR, "mysqli temporal: error de conexión: " . $tmp->connect_error . "\n");
            } else {
                $r = $tmp->query('SELECT 1');
                if ($r) {
                    echo "mysqli temporal: OK\n";
                    $ok = true;
                } else {
                    fwrite(STDERR, "mysqli temporal: error en consulta: " . $tmp->error . "\n");
                }
            }
            $tmp->close();
        } catch (Exception $e) {
            fwrite(STDERR, "mysqli temporal: excepción: " . $e->getMessage() . "\n");
        }
    } else {
        fwrite(STDERR, "No hay suficiente información para crear una conexión temporal mysqli.\n");
    }
}

// Probar la conexión PDO global (variable $conn creada por conexion.php)
echo "Probando PDO...\n";
if (isset($conn) && $conn instanceof PDO) {
    try {
        $stmt = $conn->query('SELECT 1');
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            echo "PDO: OK\n";
            $ok = true;
        } else {
            fwrite(STDERR, "PDO: la consulta no devolvió resultados.\n");
        }
    } catch (Exception $e) {
        fwrite(STDERR, "PDO: excepción: " . $e->getMessage() . "\n");
    }
} else {
    echo "La variable \$conn (PDO) no está definida. Intentando crear una conexión PDO temporal...\n";
    if (isset($host) && isset($usuario) && isset($db_password) && isset($base) && isset($port)) {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$base};charset=utf8mb4";
            $tmpd = new PDO($dsn, $usuario, $db_password);
            $tmpd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $tmpd->query('SELECT 1');
            $val = $stmt->fetchColumn();
            if ($val !== false) {
                echo "PDO temporal: OK\n";
                $ok = true;
            } else {
                fwrite(STDERR, "PDO temporal: la consulta no devolvió resultados.\n");
            }
        } catch (Exception $e) {
            fwrite(STDERR, "PDO temporal: excepción: " . $e->getMessage() . "\n");
        }
    } else {
        fwrite(STDERR, "No hay suficiente información para crear una conexión PDO temporal.\n");
    }
}

echo "---- Resultado final: ";
if ($ok) {
    echo "ALGUNA conexión exitosa detectada.\n";
    exit(0);
} else {
    echo "NINGUNA conexión pudo establecerse.\n";
    exit(1);
}

?>
