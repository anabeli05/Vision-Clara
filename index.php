<?php
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visión Clara</title>

    <!-- Estilos externos -->
    <link rel="stylesheet" href="estilos/estilos_cliente.css">
    <link rel="stylesheet" href="estilos/modal.css">
    <link rel="stylesheet" href="estilos/modal_visitante.css">
</head>
<body>
<header>
    <div class="logo"></div>
    <a href="./index.php"><img src='Imagenes/logo/logo-white.png' alt="Logo"></a>
</header>

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
    </div>
</div>

<!-- Ticket oculto -->
<div id="ticket">
    <div class="ticket-content">
        <h2>Visión clara</h2>
        <h3>Turno</h3>
        <h1 id="ticketTurno">C - 000</h1>
        <p>Agradecemos su Preferencia</p>
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
            <p> Vision.Clara@gmail.com</p>
        </div>
    </footer>
</div>

<!-- Librerías para exportar -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Tus scripts -->
<script src="js/modal.js"></script>
<script src="js/visitante.js"></script>
<script>
// Recolectar número de afiliado y enviar por AJAX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTurnoCliente');
    if (form) {
        const digits = form.querySelectorAll('.afiliado-digit');
        const hidden = document.getElementById('afiliadoHidden');
        const msg = document.getElementById('turnoClienteMsg');
        const ticket = document.getElementById('ticket');
        const ticketTurno = document.getElementById('ticketTurno');

        // Mover foco automáticamente
        digits.forEach((input, idx) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && idx < digits.length - 1) {
                    digits[idx + 1].focus();
                }
            });
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Concatenar los dígitos
            let afiliado = '';
            digits.forEach(input => afiliado += input.value);
            if (afiliado.length !== 6 || !/^\d{6}$/.test(afiliado)) {
                msg.style.display = 'block';
                msg.style.color = 'red';
                msg.textContent = 'Número de afiliado inválido';
                return;
            }
            hidden.value = afiliado;
            msg.style.display = 'none';

            // Enviar por AJAX
            fetch('Pantalla_Turnos/api-turnos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'afiliado=' + encodeURIComponent(afiliado) + '&tipo=Cliente'
            })
            .then(r => r.json())
            .then(data => {
                console.log('API response (cliente):', data);
                if (data.success) {
                    msg.style.display = 'block';
                    msg.style.color = 'green';
                    msg.textContent = '¡Turno asignado: ' + data.turno + '!';
                    if (ticket && ticketTurno) {
                        // Formatear para mostrar con guion si viene en 4 caracteres (C001 -> C - 001)
                        let display = data.turno;
                        if (typeof display === 'string' && display.length === 4) {
                            display = display.charAt(0) + ' - ' + display.slice(1);
                        }
                        ticketTurno.textContent = display;
                        ticket.style.display = 'block';
                    }
                } else {
                    msg.style.display = 'block';
                    msg.style.color = 'red';
                    msg.textContent = data.error || 'Error al asignar turno';
                }
            })
            .catch(err => {
                console.error('Fetch error (cliente):', err);
                msg.style.display = 'block';
                msg.style.color = 'red';
                msg.textContent = 'Error de conexión con el servidor';
            });
        });
    }
});
</script>
</body>
</html>