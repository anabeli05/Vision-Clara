<?php


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta  name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vision-Clara superAdmin </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="inicioSA.css">
    <link rel="stylesheet" href="..Dashboard/sidebar.css">
    <script src='../Dasboard/barra-nav.j' defer></script>
</head>
<body>

<!--Header importado-->
<?php //include('../Dashboard/sidebar.php'); ?> 

<section class="contenedor-principal">
    <div class="recuadro-sa">
        <div class="formato-txt">
        <h2>Vision Clara le da la bienvenida <?php //echo htmlspecialchars(admin_nombre); ?></h2>    
        <p>Nuestro deber es apoyar al cliente a ver el mundo de una forma asombrosa!!<p>
        </div>
        <div class="admin-img">    
            <img rc="../../Imagenes/imagen_inicio.png" alt="Admin Ilustracion">
        </div> 
    </div>

    <div class="contenedor-turno">
        <div class="columna">
            <h3>Turno</h3>
            <p><?php// echo $turno; ?></p>
        </div>
        <div class="separador"></div>
        <div class="columna">
            <h3>Siguiente</h3>
            <p><?php// echo $siguiente; ?></p>
        </div>
            <!-- Imagen decorativa inferior -->
        <div class="columna">
           <img src="../../Imagenes/doctora_inicio.png" alt="Doctora" style="max-height:120px;">
        </div>
    </div>
</body>    
