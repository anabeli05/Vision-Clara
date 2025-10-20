<?php
include("conexion.php");

$tipo = $_POST['tipo'] ?? 'Visitante'; // Por defecto Visitante

// Prefijo según tipo
$prefijo = ($tipo === 'Cliente') ? 'C' : 'V';

// Conectar y obtener último turno
$conexion_obj = new Conexion();
$conexion_obj->abrir_conexion();

$stmt = $conexion_obj->conexion->prepare("SELECT numero FROM turnos WHERE tipo=? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $tipo);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $fila = $result->fetch_assoc();
    $ultimo_numero = intval(substr($fila['numero'],1));
    $nuevo_numero = $ultimo_numero +1;
} else {
    $nuevo_numero = 1;
}

$turno = $prefijo . str_pad($nuevo_numero,3,'0',STR_PAD_LEFT);

// Insertar nuevo turno
$stmtInsert = $conexion_obj->conexion->prepare("INSERT INTO turnos (tipo, numero, fecha) VALUES (?,?,NOW())");
$stmtInsert->bind_param("ss", $tipo, $turno);
$insertOk = $stmtInsert->execute();

if($insertOk){
    echo json_encode(['status'=>'ok','turno'=>$turno]);
} else {
    echo json_encode(['status'=>'error','mensaje'=>'No se pudo generar el turno.']);
}

$conexion_obj->cerrar_conexion();
?>
