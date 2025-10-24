<?php

    include 'conexion.php';

    class Contacto extends Conexion{
        public function login($correo, $password)
        {
            echo "<br>DEBUG: Entrando a login()<br>";
            echo "<br>DEBUG: Correo recibido: " . htmlspecialchars($correo) . "<br>";
            echo "<br>DEBUG: Contraseña recibida: " . htmlspecialchars($password) . "<br>";
            try {
                // Abrir conexión
                $this->abrir_conexion();
                echo "<br>DEBUG: Conexión abierta<br>";
                
                // Usar prepared statement para prevenir SQL injection
                $stmt = $this->conexion->prepare("SELECT * FROM usuarios WHERE Correo = ?");
                if (!$stmt) {
                    echo "<br>DEBUG: Error en la preparación de la consulta<br>";
                    throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
                }
                
                $stmt->bind_param("s", $correo);
                $stmt->execute();
                $resultado = $stmt->get_result();
                echo "<br>DEBUG: Consulta ejecutada<br>";

                if ($row = $resultado->fetch_assoc()) {
                    echo "<br>DEBUG: Usuario encontrado<br>";
                    echo "<br>DEBUG: Contraseña en BD: " . htmlspecialchars($row['Contraseña']) . "<br>";
                    // Verificar contraseña
                    $password_in_db = $row['Contraseña'];
                    $password_match = false;

                    // Verificar si la contraseña en la base de datos parece ser un hash (ej: bcrypt)
                    if (str_starts_with($password_in_db, '$2y$')) {
                        // Es un hash, usar password_verify
                        echo "<br>DEBUG: Verificando contraseña hasheada<br>";
                        $password_match = password_verify($password, $password_in_db);
                    } else {
                        // No es un hash conocido, usar comparación directa (texto plano)
                        echo "<br>DEBUG: Verificando contraseña en texto plano<br>";
                        $password_match = ($password_in_db == $password);
                    }

                    if ($password_match) {
                        echo "<br>DEBUG: Contraseña correcta<br>";
                        // Si el usuario está inactivo, reactivarlo
                        if (isset($row['activo']) && $row['activo'] == 0) {
                            $id = $row['Usuario_ID'];
                            $updateStmt = $this->conexion->prepare("UPDATE usuarios SET activo = 1 WHERE Usuario_ID = ?");
                            $updateStmt->bind_param("i", $id);
                            $updateStmt->execute();
                            $row['activo'] = 1;
                        }
                        
                        // Iniciar sesión y guardar variables de sesión
                        session_start();
                        $_SESSION['Usuario_ID'] = $row['Usuario_ID'];
                        $_SESSION['Nombre'] = $row['Nombre'];
                        $_SESSION['Correo'] = $row['Correo'];
                        
                        // Normalizar el rol
                        $rol = $row['Rol'];
                        if ($rol === 'SuperAdmin') {
                            $rol = 'Super Admin';
                        } elseif ($rol === 'Usuario') {
                            $rol = 'Usuario';
                        }
                        $_SESSION['Rol'] = $rol;
                        
                        $_SESSION['Fecha'] = $row['Fecha'];
                        $_SESSION['Codigo de Recuperacion'] = $row['Codigo de Recuperacion'];
                        echo "<br>DEBUG: Redirigiendo según el rol: $rol<br>";
                        // Redirigir al dashboard de acuerdo al tipo_usuario
                        switch ($_SESSION['Rol']) {
                            case 'Super Admin':
                                echo "<br>DEBUG: Redirigiendo a Super Admin<br>";
                                header("location: ../Dashboard_SuperAdmin/inicio/SuperInicio.php");
                                break;
                            case 'Usuario':
                                echo "<br>DEBUG: Redirigiendo a Usuario>";
                                header("location: ../Dashboard_Admin/inicio/inicioAdmin.php");
                                break;
                            default:
                                echo "<br>DEBUG: Rol desconocido<br>";
                                header("location: ../Login/inicioSecion.php?error=1");
                                break;
                        }
                        exit();
                    } else {
                        echo "<br>DEBUG: Contraseña incorrecta<br>";
                        // Agregar log aquí si la contraseña es incorrecta
                        writeLog("DEBUG: Contraseña incorrecta para usuario: " . $correo);
                    }
                } else {
                    echo "<br>DEBUG: Usuario no encontrado<br>";
                    // Agregar log aquí si el usuario no es encontrado
                    writeLog("DEBUG: Usuario no encontrado con correo: " . $correo);
                }
                
                // Si llegamos aquí, las credenciales son incorrectas
                // Este header se ejecutará si el usuario no es encontrado o la contraseña es incorrecta
                writeLog("DEBUG: Redirigiendo a login con error=1");
                header("location: ../Login/inicioSecion.php?error=1");
                exit();
                
            } catch (Exception $e) {
                error_log("Error en login: " . $e->getMessage());
                echo "<br>DEBUG: Excepción capturada: " . $e->getMessage() . "<br>";
                writeLog("DEBUG: Excepción en login: " . $e->getMessage());
                header("location: ../Login/inicioSecion.php?error=1");
                exit();
            } finally {
                if (isset($stmt)) {
                    $stmt->close();
                }
                $this->cerrar_conexion();
            }
        }
    }
?>
