<?php
/**
 * conexion.php
 * Clase para manejar la conexión a MySQL usando mysqli.
 * * NOTA: Se eliminó todo el código de conexión global y los 'die()' al final
 * para evitar la salida de texto que corrompería la generación de archivos PDF.
 */

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Conexion
{
    private $host='gondola.proxy.rlwy.net';
    private $port='38159';
    private $usuario='root';
    private $password = 'UhyGRvmIBqWGvhBJZRhwBojCBVxvInyQ';
    private $base='railway';
    public $sentencia;
    private $rows =array();
    public $conexion;     

    public function abrir_conexion(){
        try {
            $this->conexion = new mysqli($this->host, $this->usuario, $this->password, $this->base, $this->port);
            
            if ($this->conexion->connect_error) {
                error_log("Error de conexión: " . $this->conexion->connect_error);
                // Si hay error, lanzamos una excepción, NO imprimimos con die()
                throw new Exception("Error de conexión: " . $this->conexion->connect_error);
            }

            $this->conexion->set_charset("utf8");
        } catch (Exception $e) {/*
            error_log("Excepción en la conexión: " . $e->getMessage()); */
			throw $e;
        }
    }

    public function cerrar_conexion(){
        if ($this->conexion) {
            $this->conexion->close(); 
            $this->conexion = null;
        }
    }

    public function ejecutar_sentencia(){
        try {
            $this->abrir_conexion();
            $bandera = $this->conexion->query($this->sentencia);
            if (!$bandera) {
                throw new Exception("Error en la consulta: " . $this->conexion->error);
            }
            $this->cerrar_conexion(); 
            return $bandera;
        } catch (Exception $e) {
            error_log("Error en ejecutar_sentencia: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtener_sentencia(){
        try {
            $this->abrir_conexion();
            $result = $this->conexion->query($this->sentencia);
            if (!$result) {
                throw new Exception("Error en la consulta: " . $this->conexion->error);
            }
            return $result;
		} catch (Exception $e) {
            error_log("Error en obtener_sentencia: " . $e->getMessage());
            throw $e;
        }finally {
            $this->cerrar_conexion();
        }
    }

    public function obtener_ultimo_id()
    {
		return $this->conexion ? $this->conexion->insert_id : null;
    }
}
	// Crear instancia global de mysqli para compatibilidad con login_var.php
	try {
		$host = 'gondola.proxy.rlwy.net';
		$port = 38159;
		$usuario = 'root';
		$db_password = 'UhyGRvmIBqWGvhBJZRhwBojCBVxvInyQ';
		$base = 'railway';
        
		$conexion = new mysqli($host, $usuario, $db_password, $base, $port);
        
		if ($conexion->connect_error) {
			error_log("Error de conexión mysqli: " . $conexion->connect_error);
			die("Error de conexión: " . $conexion->connect_error);
		}
        
		$conexion->set_charset("utf8");
		error_log("Conexión mysqli global establecida correctamente");
	} catch (Exception $e) {
		error_log("Error al crear conexión mysqli global: " . $e->getMessage());
		die("Error de conexión a la base de datos");
	}

	// Crear instancia PDO para compatibilidad con otros archivos
	try {
		$dsn = "mysql:host=$host;port=$port;dbname=$base;charset=utf8mb4";
		$conn = new PDO($dsn, $usuario, $db_password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		error_log("Conexión PDO global establecida correctamente");
	} catch(PDOException $e) {
		error_log("Error al crear conexión PDO: " . $e->getMessage());
		die("Error de conexión a la base de datos");
	}
?>