<?php
class Conexion {
    private $host = "shinkansen.proxy.rlwy.net"; // Host de Railway
    private $usuario = "root";                   // Usuario que te dio Railway
    private $password = "DgGAmKnhcbHKtEbgGIOyKZzusJQjOvur"; // Contraseña de Railway
    private $base_datos = "railway";             // Nombre de la base (Railway usa "railway" por defecto)
    private $puerto = 46493;                     // Puerto que muestra Railway
    public $conexion;

    public function abrir_conexion() {
        $this->conexion = new mysqli(
            $this->host,
            $this->usuario,
            $this->password,
            $this->base_datos,
            $this->puerto
        );

        if ($this->conexion->connect_error) {
            die("❌ Error de conexión: " . $this->conexion->connect_error);
        }
    }

    public function cerrar_conexion() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
?>
