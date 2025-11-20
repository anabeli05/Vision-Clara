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

        // reemplaza donde haces fetch(...) con este bloque
        const relativeUrl = 'Pantalla_Turnos/api-turnos-sequences.php';
        const absoluteFallback = `${window.location.origin}/Vision-Clara/Pantalla_Turnos/api-turnos-sequences.php`;

        function doFetch(url) {
          return fetch(url, { method: 'POST', body: formData });
        }

        doFetch(relativeUrl)
        .then(res => {
          if (res.status === 404) {
            // intentar ruta absoluta si la relativa no existe
            return doFetch(absoluteFallback);
          }
          return res;
        })
        .then(res => {
          if (!res) throw new Error('No response received');
          if (!res.ok) {
            const resultadoDiv = document.getElementById(resultadoDivId);
            resultadoDiv.innerHTML = `<p style="color:red;">Error HTTP: ${res.status} ${res.statusText}</p>`;
            throw new Error('HTTP error ' + res.status);
          }
          return res.json();
        })
        .then(data => {
          const resultadoDiv = document.getElementById(resultadoDivId);
          if (data && data.success) {
            resultadoDiv.innerHTML = `<p>Tu turno es: <strong>${data.turno}</strong></p>`;
          } else {
            const msg = (data && (data.error || data.message)) || 'Ocurrió un error al solicitar el turno';
            resultadoDiv.innerHTML = `<p style="color:red;">${msg}</p>`;
          }
        })
        .catch(err => {
          console.error('Fetch error:', err);
          const resultadoDiv = document.getElementById(resultadoDivId);
          resultadoDiv.innerHTML = `<p style="color:red;">Error de red o interno. Revisa la consola y la ruta del endpoint.</p>`;
        });
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