<?php
// editar-usuario.php
// Simulación de datos recibidos desde la BD
$usuario = [
    'Nombre' => 'Fernando Benitez Astudillo',
    'Correo' => 'fbenitez2@ucol.mx',
    'Rol' => 'Usuario'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="editar-usuario.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="titulo">Editar Usuario</h2>

            <form action="actualizar_usuario.php" method="POST" class="formulario">

                <div class="campo">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" value="<?php echo $usuario['Nombre']; ?>" required>
                </div>

                <div class="campo">
                    <label>Correo Electrónico *</label>
                    <input type="email" name="correo" value="<?php echo $usuario['Correo']; ?>" required>
                </div>

                <div class="campo">
                    <label>Rol *</label>
                    <select name="rol" required>
                        <option value="Usuario" <?php echo ($usuario['Rol']==='Usuario') ? 'selected' : '' ?>>Usuario</option>
                        <option value="Super Admin" <?php echo ($usuario['Rol']==='Super Admin') ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                </div>

                <div class="acciones">
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                    <a href="gestion-usuarios.php" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>