<?php
// Protección de sesión - Solo usuarios autenticados pueden acceder
require_once '../../Login/check_session.php';

// Verificar que sea Super Admin
if ($user_rol !== 'Super Admin') {
    header('Location: ../../Login/inicioSecion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta  name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vision-Clara superAdmin </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href='SuperInicio.css'>
    <link rel="stylesheet" href='../Dashboard/SuperSidebar.css'> 
    <link rel="stylesheet" href='../../estilos/translator.css'>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript" src="../../js/translator.js"></script>
</head>
</head>
<body>

<!--Header importado-->
<?php include('../Dashboard/SuperSidebar.php'); ?> 

<section class="contenedor-principal">
    <div class="recuadro-sa">
        <div class="formato-txt">
        <h2>Vision Clara le da la bienvenida <?php echo htmlspecialchars($user_nombre); ?></h2>    
        <p>Nuestro deber es apoyar al cliente a ver el mundo de una forma asombrosa!!<p>
        </div>
        <div class="sadmin-img">    
            <img src="../../Imagenes/imagen_inicio.png" alt="Admin Ilustracion">
        </div> 
    </div>

    <div class="contenedor-turno">
        <div class="columna">
            <h3>Turno</h3>
            <p>
        </div>
        <div class="separador"></div>
        <div class="columna">
            <h3>Siguiente</h3>
            <p>
        </div>
    </div>
     <!-- Imagen decorativa inferior -->
    <div class="illustration">
        <div class="character"></div>
    </div>
</body>