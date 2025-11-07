// ---------- Modales ----------
const abrirCliente = document.getElementById('abrir-cliente');
const abrirVisitante = document.getElementById('abrir-visitante');
const modalCliente = document.getElementById('modal-cliente');
const modalVisitante = document.getElementById('modal-visitante');

const cerrarCliente = document.getElementById('cerrar-cliente');
const cerrarVisitante = document.getElementById('cerrar-visitante');

abrirCliente.addEventListener('click', () => { modalCliente.style.display = 'block'; });
abrirVisitante.addEventListener('click', () => { modalVisitante.style.display = 'block'; });

cerrarCliente.addEventListener('click', () => { modalCliente.style.display = 'none'; });
cerrarVisitante.addEventListener('click', () => { modalVisitante.style.display = 'none'; });

window.addEventListener('click', (e) => {
    if(e.target == modalCliente) modalCliente.style.display = 'none';
    if(e.target == modalVisitante) modalVisitante.style.display = 'none';
});

// ---------- Afiliado Inputs ----------
const digits = document.querySelectorAll('.afiliado-digit');

digits.forEach((input, index) => {
    input.addEventListener('input', () => {
        if(input.value.length === 1 && index < digits.length - 1){
            digits[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        if(e.key === "Backspace" && input.value === "" && index > 0){
            digits[index - 1].focus();
        }
    });
});

// ---------- Función AJAX ----------
function enviarTurno(form, tipo, resultadoDivId){
    form.addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData();

        if(tipo === 'Cliente'){
            const numeroAfiliado = Array.from(digits).map(d => d.value).join('');
            if(numeroAfiliado.length !== 6 || !/^\d{6}$/.test(numeroAfiliado)){
                alert("Por favor ingresa los 6 dígitos del número de afiliado.");
                return;
            }
            formData.append('afiliado', numeroAfiliado);
        }

        formData.append('tipo', tipo);

        fetch('generar_turno.php',{
            method:'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const resultadoDiv = document.getElementById(resultadoDivId);
            if(data.status === 'ok'){
                resultadoDiv.innerHTML = `<p>Tu turno es: <strong>${data.turno}</strong></p>`;
            } else {
                resultadoDiv.innerHTML = `<p style="color:red;">${data.mensaje}</p>`;
            }
        })
        .catch(err => console.error(err));
    });
}

// ---------- Activar envíos ----------
enviarTurno(document.getElementById('form-cliente'),'Cliente','resultado-cliente');
enviarTurno(document.getElementById('form-visitante'),'Visitante','resultado-visitante');


// ------- Carrusel infinito con flechas -------
document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector(".carousel-track");
    const nextButton = document.querySelector(".carousel-btn.next");
    const prevButton = document.querySelector(".carousel-btn.prev");
    const slides = document.querySelectorAll('.carousel-track img');
    
    const slideWidth = 300 + 10; // ancho + margin
    let currentPosition = 0;
    let autoScrollInterval;
    let isPaused = false;

    // Función para mover el carrusel
    function moveCarousel(direction) {
        if (direction === 'next') {
            currentPosition -= slideWidth;
        } else {
            currentPosition += slideWidth;
        }
        
        // Aplicar la transformación
        track.style.transition = 'transform 0.5s ease-in-out';
        track.style.transform = `translateX(${currentPosition}px)`;
        
        // Reiniciar automáticamente después de la transición
        setTimeout(() => {
            checkInfiniteLoop();
        }, 500);
    }

    // Verificar y corregir el loop infinito
    function checkInfiniteLoop() {
        const totalWidth = slideWidth * (slides.length / 2); // Mitad de las slides (las duplicadas)
        
        // Si llegamos al final (conjunto duplicado), resetear sin animación
        if (currentPosition <= -totalWidth) {
            track.style.transition = 'none';
            currentPosition = 0;
            track.style.transform = `translateX(${currentPosition}px)`;
        }
        // Si retrocedimos al inicio (conjunto duplicado), resetear sin animación
        else if (currentPosition >= slideWidth) {
            track.style.transition = 'none';
            currentPosition = -totalWidth + slideWidth;
            track.style.transform = `translateX(${currentPosition}px)`;
        }
    }

    // Auto-scroll automático
    function startAutoScroll() {
        autoScrollInterval = setInterval(() => {
            if (!isPaused) {
                moveCarousel('next');
            }
        }, 3000);
    }

    // Event Listeners para las flechas
    nextButton.addEventListener("click", () => {
        moveCarousel('next');
    });

    prevButton.addEventListener("click", () => {
        moveCarousel('prev');
    });

    // Pausar al hacer hover
    const carousel = document.querySelector(".carousel");
    carousel.addEventListener("mouseenter", () => {
        isPaused = true;
    });
    
    carousel.addEventListener("mouseleave", () => {
        isPaused = false;
    });

    // Iniciar auto-scroll
    startAutoScroll();
});