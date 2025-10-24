<?php
include("conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Turno - Óptica Vision Clara</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<header>
    <div class="logo"></div>
    <a href="./index.php"><img src='Imagenes/logo/logo-white.png' alt="Logo"></a>
</header>

<<<<<<< HEAD
<div class="All_contenido">
    <div class="interfaz1">
        <h1>Obtén tu turno para tu cita</h1>

        <div class="botones_interfaces">
            <div class="botones">
                <!-- Botón que abre el modal Cliente -->
                <button id="btnCliente">Clientes</button>
            </div>

            <div class="botones">
                <!-- Botón que abre el modal Visitante -->
                <button id="btnVisitante">Visitante</button>
            </div>
        </div>

        <div class="inicio">
            <img src="Imagenes/lentes_intro.png" alt="">
            <p>En Visión clara nos especializamos en el cuidado integral de tus ojos, combinando 
            la más alta tecnología con un trato humano y cercano. 
            Nuestro compromiso es ofrecerte una experiencia única en salud visual, garantizando 
            calidad, confianza y comodidad.</p>
        </div>

        <div class="apartado2">
            <h1>Cuidamos tu visión y la de tu familia, para que disfruten cada momento con claridad.</h1>
            <img src="Imagenes/mujer_rizada.png" alt="">
            <img src="Imagenes/mujer_blanca.png" alt="">
            <img src="Imagenes/familia_lentes.png" alt="">
        </div> 
        <div class="apartado2">
            <h1>Tambien puedes adquirir parte de nuetros productos (solamente en tienda fisica)</h1>
            <img src="Imagenes/1_lente.png" alt="">
            <img src="Imagenes/lentes_contacto.png" alt="">
            <img src="Imagenes/2_lentes.png" alt="">
        </div>                   
    </div>

    <!-- Modal Clientes -->
    <div id="modalCliente" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Obten tu turno</h2>
            <div class="tabs">
                <button class="tab active">Cliente</button>
                <button class="tab">Visitante</button>
            </div>
            <h3>Ingresa tu número de afiliado</h3>
            <form id="formTurnoCliente" autocomplete="off">
                <div class="input-boxes">
                    <input type="text" maxlength="1" class="afiliado-digit" required>
                    <input type="text" maxlength="1" class="afiliado-digit" required>
                    <input type="text" maxlength="1" class="afiliado-digit" required>
                    <input type="text" maxlength="1" class="afiliado-digit" required>
                    <input type="text" maxlength="1" class="afiliado-digit" required>
                    <input type="text" maxlength="1" class="afiliado-digit" required>
                    <input type="hidden" name="afiliado" id="afiliadoHidden">
                </div>
                <button type="submit" class="btn-obtener">Obtener</button>
            </form>
            <div id="turnoClienteMsg" style="margin-top:10px;color:green;display:none;"></div>
        </div>
    </div>

    <!-- Modal Visitante -->
    <div id="modalVisitante" class="modal">
        <div class="modal-content">
            <span class="close" data-close="modalVisitante">&times;</span>
            <h2>Obten tu turno</h2>
            <div class="turno">
                <span class="turno-label">Turno</span>
                <span class="turno-num" id="turnoAleatorio">C - 000</span>
            </div>
        </div>
=======
<!-- SECCIÓN PRINCIPAL DE TURNO -->
<div class="seccion-turno">
    <h1 class="titulo-principal">Obten tu turno para tu cita</h1>
    <div class="botones-tipo">
        <button id="abrir-cliente">Cliente</button>
        <button id="abrir-visitante">Visitante</button>
>>>>>>> f9f521d105b24a79aa40b2a098a06a746dcb4010
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
        <img src="Imagenes/1_lente.png" alt="Lente individual">
        <img src="Imagenes/lentes_contacto.png" alt="Lentes de contacto">
        <img src="Imagenes/2_lentes.png" alt="Par de lentes">
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
        <p>&copy; 2024 Óptica Visión Clara. Todos los derechos reservados.</p>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>