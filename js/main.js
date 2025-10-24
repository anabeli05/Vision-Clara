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
