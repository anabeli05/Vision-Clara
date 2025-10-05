<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

	class Conexion
	{
		private $host='gondola.proxy.rlwy.net';// MYSQL_PUBLIC_URL host
        private $port='38159';
		private $usuario='root';
		private $password = 'UhyGRvmIBqWGvhBJZRhwBojCBVxvInyQ';
		private $base='railway';
		public $sentencia;
		private $rows =array();
		public $conexion;	

		public function abrir_conexion(){
			try {
                error_log("Intentando conectar a la base de datos...");
                error_log("Host: " . $this->host);
                error_log("Puerto: " . $this->port);
                error_log("Usuario: " . $this->usuario);
                error_log("Base de datos: " . $this->base);

                
				$this->conexion = new mysqli($this->host, $this->usuario, $this->password, $this->base, $this->port);
				
                if ($this->conexion->connect_error) {
                    error_log("Error de conexión: " . $this->conexion->connect_error);
                    throw new Exception("Error de conexión: " . $this->conexion->connect_error);
				}

                error_log("Conexión exitosa a la base de datos");
                $this->conexion->set_charset("utf8");
            } catch (Exception $e) {
                error_log("Excepción en la conexión: " . $e->getMessage());	
                error_log("Error de conexión: " . $e->getMessage());
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
?>
 