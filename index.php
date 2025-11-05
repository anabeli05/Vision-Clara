<?php
include('./Base de Datos/conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Turno - Óptica Vision Clara</title>
    <link rel="stylesheet" href='./estilos/estilos.css'>
</head>
<body>
<header>
    <div class="logo"></div>
    <a href="./index.php"><img src='Imagenes/logo/logo-white.png' alt="Logo"></a>
</header>

<!-- SECCIÓN PRINCIPAL DE TURNO -->
<div class="seccion-turno">
    <h1 class="titulo-principal">Obten tu turno para tu cita</h1>
    <div class="botones-tipo">
        <button id="abrir-cliente">Cliente</button>
        <button id="abrir-visitante">Visitante</button>
    </div>
</div>

<!-- SECCIÓN INFORMACIÓN -->
<div class="seccion-informacion">
    <div class="inicio">
        <img src="Imagenes/lentes_intro.png" alt="Lentes de calidad">
        <p>En Visión clara nos especializamos en el cuidado integral de tus ojos, combinando 
        la más alta tecnología con un trato humano y cercano. 
        Nuestro compromiso es ofrecerte una experiencia única en salud visual, garantizando 
        calidad, confianza y comodidad.</p>
    </div>

    <div class="apartado2">
        <h1>Cuidamos tu visión y la de tu familia, para que disfruten cada momento con claridad.</h1>
        <img src="Imagenes/mujer_rizada.png" alt="Mujer con lentes">
        <img src="Imagenes/mujer_blanca.png" alt="Mujer sonriendo">
        <img src="Imagenes/familia_lentes.png" alt="Familia con lentes">
    </div> 
    
    <div class="apartado2">
    <h1>También puedes adquirir parte de nuestros productos (solamente en tienda física)</h1>

    <div class="carousel">
        <div class="carousel-container">
            <div class="carousel-track">
                <img src="Imagenes/1_lente.png" alt="Lente individual">
                <img src="Imagenes/lentes_contacto.png" alt="Lentes de contacto">
                <img src="Imagenes/2_lentes.png" alt="Par de lentes">
                <img src="Imagenes/renu.jpg" alt="Limpiador">
                <img src="Imagenes/estuches.png" alt="Estuche">
                <!-- Duplicamos las imágenes para el efecto infinito -->
                <img src="Imagenes/1_lente.png" alt="Lente individual">
                <img src="Imagenes/lentes_contacto.png" alt="Lentes de contacto">
                <img src="Imagenes/2_lentes.png" alt="Par de lentes">
                <img src="Imagenes/renu.jpg" alt="Limpiador">
                <img src="Imagenes/estuches.png" alt="Estuche">
            </div>
        </div>
        <!-- Flechas de navegación -->
        <button class="carousel-btn prev">‹</button>
        <button class="carousel-btn next">›</button>
    </div>
</div>            
</div>

<!-- Modal Cliente -->
<div id="modal-cliente" class="modal">
    <div class="modal-content">
        <span class="cerrar" id="cerrar-cliente">&times;</span>
        <h2>Turno Cliente</h2>
        <form id="form-cliente">
            <label>Número de Afiliado (6 dígitos):</label>
            <div class="afiliado-inputs">
                <input type="text" maxlength="1" class="afiliado-digit" required>
                <input type="text" maxlength="1" class="afiliado-digit" required>
                <input type="text" maxlength="1" class="afiliado-digit" required>
                <input type="text" maxlength="1" class="afiliado-digit" required>
                <input type="text" maxlength="1" class="afiliado-digit" required>
                <input type="text" maxlength="1" class="afiliado-digit" required>
            </div>
            <button type="submit">Obtener Turno</button>
        </form>
        <div id="resultado-cliente"></div>
    </div>
</div>

<!-- Modal Visitante -->
<div id="modal-visitante" class="modal">
    <div class="modal-content">
        <span class="cerrar" id="cerrar-visitante">&times;</span>
        <h2>Turno Visitante</h2>
        <form id="form-visitante">
            <button type="submit">Obtener Turno</button>
        </form>
        <div id="resultado-visitante"></div>
    </div>
</div>

<div class="container-footer">
    <footer>
        <!-- Logo -->
        <div class="logo-footer"> 
            <img src='Imagenes/logo/logo-white.png' alt="Logo Vision Clara">
        </div>

        <!-- Enlaces legales -->
        <div class="footer-info">
            <p>
                Avisos de privacidad <br>
                Política de sostenibilidad <br>
                Términos y condiciones
            </p>
        </div>

        <!-- Contacto -->
        <div class="footer-contact">
            <h4>Contáctanos:</h4>
            <p>Tel. 314 139 7633</p>
            <h4>Correo:</h4>
            <p>Vision.Clara@gmail.com</p>
        </div>
    </footer>
    
    <!-- Línea de derechos reservados -->
    <div class="footer-bottom">
        <p>&copy; 2025 Óptica Visión Clara. Todos los derechos reservados.</p>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>