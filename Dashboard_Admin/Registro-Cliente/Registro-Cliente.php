<?php 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Clientes - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Registro-Cliente.css">
    <link rel="stylesheet" href="../Dashboard/sidebar.css">
</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>

    <section>
    <div class="contenedor-principal">
        <div class="header_1">
            <h1><i class="fas fa-user-plus" data-no-translate></i> Registro de Clientes</h1>
        </div>
    

        <!-- mensaje de error de la base de datos -->
        <!-- <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>-->

        <!-- Formulario de registro -->
        <form method="POST" class="formulario-registro">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="registro" value="1">

            <div class="formulario">
                <label for="Nombre">Nombre Completo:</label>
                <input type="text" id="Nombre" name="Nombre" required
                        value="<?php echo htmlspecialchars($_POST['Nombre']?? ''); ?>">
            </div>

            <div class="formulario">
                <label for="Correo">Correo:</label>
                <input type="email" id="Correo" name="Correo" required
                        value="<?php echo htmlspecialchars($_POST['Correo']?? ''); ?>">
            </div>

            <div class="formulario">
                <label for="Numero">Numero de Telefono:</label>
                <input type="number" id="Numero" name="Numero" required
                        minlength="12">
            </div>

            <div class="formulario">
                <label for="Numero_Afiliado">Numero de Afiliado:</label>
                <input type="number_affiliate" id="Numero_Afiliado" name="Confirmar_Contraseña" required
                        minlength="8"> <!-------------->
            </div>

            <!-- Botones para Registro y Cancelacion -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Registrar 
             </button>
                <a href='../Cliente/Cliente.php' class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</section>
</body>
</html>
