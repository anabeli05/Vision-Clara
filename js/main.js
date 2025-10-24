// ---------- Modales ----------
const abrirCliente = document.getElementById('abrir-cliente');
const abrirVisitante = document.getElementById('abrir-visitante');
const modalCliente = document.getElementById('modal-cliente');
const modalVisitante = document.getElementById('modal-visitante');

const cerrarCliente = document.getElementById('cerrar-cliente');
const cerrarVisitante = document.getElementById('cerrar-visitante');

abrirCliente.addEventListener('click', () => { 
    modalCliente.style.display = 'block'; 
    // Enfocar el primer input cuando se abre el modal
    setTimeout(() => document.querySelector('.afiliado-digit').focus(), 100);
});
abrirVisitante.addEventListener('click', () => { modalVisitante.style.display = 'block'; });

cerrarCliente.addEventListener('click', () => { modalCliente.style.display = 'none'; });
cerrarVisitante.addEventListener('click', () => { modalVisitante.style.display = 'none'; });

window.addEventListener('click', (e) => {
    if (e.target == modalCliente) modalCliente.style.display = 'none';
    if (e.target == modalVisitante) modalVisitante.style.display = 'none';
});

// ---------- Afiliado Inputs ----------
const digits = document.querySelectorAll('.afiliado-digit');

digits.forEach((input, index) => {
    // Solo permitir números
    input.addEventListener('input', (e) => {
        // Remover cualquier caracter que no sea número
        input.value = input.value.replace(/\D/g, '');
        
        if (input.value.length === 1 && index < digits.length - 1) {
            digits[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        // Permitir solo teclas numéricas, backspace, tab, flechas
        if (!/[\d]|Backspace|Tab|ArrowLeft|ArrowRight/.test(e.key)) {
            e.preventDefault();
        }
        
        if (e.key === "Backspace" && input.value === "" && index > 0) {
            digits[index - 1].focus();
        }
        
        // Navegación con flechas
        if (e.key === "ArrowLeft" && index > 0) {
            digits[index - 1].focus();
        }
        if (e.key === "ArrowRight" && index < digits.length - 1) {
            digits[index + 1].focus();
        }
    });
});

// ---------- Función AJAX ----------
function enviarTurno(form, tipo, resultadoDivId) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        
        // 🔥 NUEVA VALIDACIÓN CLIENTE: Para visitantes verificar tiempo de espera
        if (tipo === 'Visitante') {
            const ultimoTurnoVisitante = localStorage.getItem('ultimo_turno_visitante');
            if (ultimoTurnoVisitante) {
                const tiempoTranscurrido = Date.now() - parseInt(ultimoTurnoVisitante);
                const minutosTranscurridos = Math.floor(tiempoTranscurrido / (1000 * 60));
                
                if (minutosTranscurridos < 10) {
                    const minutosRestantes = 10 - minutosTranscurridos;
                    alert(`Debes esperar ${minutosRestantes} minutos antes de generar otro turno de visitante.`);
                    return;
                }
            }
        }
        
        let formData = new FormData();

        if (tipo === 'Cliente') {
            let numeroAfiliado = Array.from(digits).map(d => d.value).join('');

            // Validación más estricta
            if (numeroAfiliado.length !== 6) {
                alert("Por favor ingresa los 6 dígitos completos del número de afiliado.");
                digits[0].focus();
                return;
            }
            
            if (!/^\d{6}$/.test(numeroAfiliado)) {
                alert("El número de afiliado debe contener solo dígitos.");
                digits[0].focus();
                return;
            }

            formData.append('no_afiliado', numeroAfiliado);
        }

        formData.append('tipo', tipo);

        // Mostrar loading
        const resultadoDiv = document.getElementById(resultadoDivId);
        resultadoDiv.innerHTML = '<p>⏳ Generando turno...</p>';

        fetch('generar_turno.php', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'ok') {
                resultadoDiv.innerHTML = `
                    <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; text-align: center;">
                        <p style="margin: 0; font-size: 1.2em;">✅ <strong>Turno generado:</strong></p>
                        <p style="margin: 10px 0; font-size: 2em; font-weight: bold;">${data.turno}</p>
                        <p style="margin: 0; font-size: 0.9em;">Guarda este número para tu atención</p>
                    </div>
                `;
                form.reset();
                
                // Limpiar inputs de afiliado
                digits.forEach(d => d.value = '');
                
                // 🔥 GUARDAR EN LOCALSTORAGE PARA VISITANTES
                if (tipo === 'Visitante') {
                    localStorage.setItem('ultimo_turno_visitante', Date.now().toString());
                }
                
                // Cerrar modal después de 5 segundos
                setTimeout(() => {
                    if (tipo === 'Cliente') modalCliente.style.display = 'none';
                    if (tipo === 'Visitante') modalVisitante.style.display = 'none';
                }, 5000);
                
            } else {
                resultadoDiv.innerHTML = `
                    <div style="background: #fef2f2; color: #dc2626; padding: 15px; border-radius: 10px; text-align: center;">
                        <p style="margin: 0;">⚠️ <strong>Error:</strong> ${data.mensaje}</p>
                    </div>
                `;
                
                // Enfocar el primer input si hay error en cliente
                if (tipo === 'Cliente') {
                    digits[0].focus();
                }
            }
        })
        .catch(err => {
            console.error('Error:', err);
            resultadoDiv.innerHTML = `
                <div style="background: #fef2f2; color: #dc2626; padding: 15px; border-radius: 10px; text-align: center;">
                    <p style="margin: 0;">❌ Error de conexión. Intenta nuevamente.</p>
                </div>
            `;
        });
    });
}

// ---------- Activar envíos ----------
enviarTurno(document.getElementById('form-cliente'), 'Cliente', 'resultado-cliente');
enviarTurno(document.getElementById('form-visitante'), 'Visitante', 'resultado-visitante');