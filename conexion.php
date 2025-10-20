<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Conexion {
    private $host='localhost';
    private $usuario='root';
    private $password = '';
    private $base='vision_clara';
    public $conexion;    

    public function abrir_conexion(){
        $this->conexion = new mysqli($this->host, $this->usuario, $this->password, $this->base);
        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
        $this->conexion->set_charset("utf8");
    }

    public function cerrar_conexion(){
        if ($this->conexion) {
            $this->conexion->close(); 
            $this->conexion = null;
        }
    }
}
?>
