document.addEventListener('DOMContentLoaded', function() {
    
    // Aquí inicializamos los elementos. ¡Si un elemento falta en el HTML, 
    // esta variable será null, lo que causa el error "modalBackground is null"!
    const modalCliente = document.getElementById('modal-cliente');
    const modalVisitante = document.getElementById('modal-visitante');
    const modalBackground = document.getElementById('modal-bg'); 
    const digitInputs = document.querySelectorAll('.afiliado-digit');

    // --- Lógica de usabilidad: Inputs de 6 dígitos ---
    
    // Esto hace que la experiencia de teclear el afiliado sea fluida.
    digitInputs.forEach((input, index) => {
        // Mover el foco al siguiente input al escribir...
        input.addEventListener('input', function() {
            if (this.value.length === 1 && index < digitInputs.length - 1) {
                digitInputs[index + 1].focus();
            }
            if (this.value.length > 1) {
                this.value = this.value.slice(0, 1); 
            }
        });

        // Volver al input anterior al borrar (con la tecla Backspace)...
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                e.preventDefault();
                digitInputs[index - 1].focus();
            }
        });
    });

    // --- Manejo de la Interfaz: Abrir y Cerrar Modales ---
    
    // Abrir Modal Cliente
    const btnCliente = document.getElementById('btn-cliente');
    if (btnCliente) {
        btnCliente.addEventListener('click', function() {
            // Solo intentamos mostrar el elemento si existe (modalCliente != null)
            if (modalCliente) modalCliente.style.display = 'block';
            if (modalBackground) modalBackground.style.display = 'block'; 
            document.querySelector('.afiliado-digit:first-child')?.focus(); 
        });
    }

    // Abrir Modal Visitante
    const btnVisitante = document.getElementById('btn-visitante');
    if (btnVisitante) {
        btnVisitante.addEventListener('click', function() {
            if (modalVisitante) modalVisitante.style.display = 'block';
            if (modalBackground) modalBackground.style.display = 'block'; 
        });
    }

    // Cerrar Modales 
    document.querySelectorAll('.cerrar, #modal-bg').forEach(element => {
        element.addEventListener('click', function() {
            // Cerramos todas las modales si existen
            if (modalCliente) modalCliente.style.display = 'none';
            if (modalVisitante) modalVisitante.style.display = 'none';
            if (modalBackground) modalBackground.style.display = 'none'; 
            document.getElementById('resultado-cliente').innerHTML = ''; 
            document.getElementById('resultado-visitante').innerHTML = ''; 
        });
    });

    // --- Función auxiliar para mostrar mensaje de turno ---
    function mostrarMensajeTurno(resultadoDiv, numeroTurno, esPDF) {
        if (esPDF) {
            resultadoDiv.innerHTML = `
                <div style="color: green; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; background-color: #d4edda; margin-top: 10px;">
                    <p style="margin: 0; font-weight: bold;">✅ ¡Turno generado con éxito!</p>
                    <p style="margin: 5px 0 0 0;">Número de turno: <strong>${numeroTurno}</strong></p>
                    <p style="margin: 5px 0 0 0;">El PDF se abrió en una nueva pestaña.</p>
                </div>
            `;
        } else {
            resultadoDiv.innerHTML = `
                <div style="color: green; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; background-color: #d4edda; margin-top: 10px;">
                    <p style="margin: 0; font-weight: bold;">✅ ¡Turno generado con éxito!</p>
                    <p style="margin: 5px 0 0 0;">Número de turno: <strong>${numeroTurno}</strong></p>
                </div>
            `;
        }
    }

    // --- Función de Envío: Generar Turno y Recibir PDF ---

    function enviarTurno(form, tipo, resultadoDivId){
        form.addEventListener('submit', function(e){
            e.preventDefault(); 
            const resultadoDiv = document.getElementById(resultadoDivId);
            resultadoDiv.innerHTML = ''; 
            
            let formData = new FormData();
            
            // 1. Validación de Cliente (sólo si es tipo 'Cliente')
            if(tipo === 'Cliente'){
                let numeroAfiliado = Array.from(digitInputs).map(d => d.value).join('');
                
                if(numeroAfiliado.length !== 6 || !/^\d{6}$/.test(numeroAfiliado)){
                    resultadoDiv.innerHTML = '<p style="color:red;">❌ ¡Error! Debes ingresar los 6 dígitos del afiliado.</p>';
                    return; 
                }
                formData.append('afiliado', numeroAfiliado);
            }

            // 2. Preparar datos para la API
            // Asegurar que el tipo tenga la primera letra en mayúscula
            const tipoFormateado = tipo.charAt(0).toUpperCase() + tipo.slice(1).toLowerCase();
            console.log('Enviando tipo de turno:', tipoFormateado);
            formData.append('tipo', tipoFormateado);
            formData.append('action', 'generate_pdf');

            // Rutas de la API (ajustadas a tu entorno local)
            const relativeUrl = 'Pantalla_Turnos/api-turnos-sequences.php';
            const absoluteFallback = `${window.location.origin}/VISION-CLARA-ana/Pantalla_Turnos/api-turnos-sequences.php`;

            function doFetch(url) {
                return fetch(url, { 
                    method: 'POST', 
                    body: formData,
                    credentials: 'same-origin' // Importante para mantener las sesiones/cookies
                });
            }
            
            resultadoDiv.innerHTML = '<p>⏳ Generando turno... ¡Un momento!</p>';

            // 3. Ejecutar la solicitud
            // Primero intentamos con la URL relativa
            doFetch(relativeUrl)
            .catch(error => {
                console.error('Error con URL relativa, intentando con URL absoluta:', error);
                return doFetch(absoluteFallback);
            })
            .then(async res => {
                console.log('Respuesta del servidor:', res.status, res.statusText);
                
                if (!res.ok) {
                    // Intentamos obtener más información del error
                    let errorData;
                    try {
                        // Intentamos leer la respuesta como JSON
                        errorData = await res.clone().json();
                        console.error('Error del servidor (JSON):', errorData);
                        throw new Error(errorData.error || `Error del servidor (Código ${res.status}).`);
                    } catch (jsonError) {
                        // Si no es JSON, intentamos leer como texto
                        try {
                            const errorText = await res.clone().text();
                            console.error('Error del servidor (texto):', errorText);
                            throw new Error(`Error del servidor (${res.status}): ${errorText.substring(0, 200)}`);
                        } catch (textError) {
                            console.error('No se pudo leer la respuesta del servidor:', textError);
                            throw new Error(`Error de red o interno. Código HTTP: ${res.status}.`);
                        }
                    }
                }
                
                // Si la respuesta es OK, procesamos el blob
                const blob = await res.blob();
                
                // Verificar si es un PDF
                if (blob.type === 'application/pdf') {
                    const url = window.URL.createObjectURL(blob);
                    window.open(url, '_blank');
                    
                    // Extraer el número de turno del nombre del archivo si está disponible
                    const contentDisposition = res.headers.get('Content-Disposition');
                    let numeroTurno = '';
                    if (contentDisposition) {
                        const turnoMatch = contentDisposition.match(/Turno-([A-Z]\d+)\.pdf/);
                        numeroTurno = turnoMatch ? turnoMatch[1] : '';
                    }
                    
                    mostrarMensajeTurno(resultadoDiv, numeroTurno, true);
                    
                    // Cerrar el modal después de 3 segundos
                    setTimeout(() => {
                        if (modalCliente) modalCliente.style.display = 'none';
                        if (modalVisitante) modalVisitante.style.display = 'none';
                        if (modalBackground) modalBackground.style.display = 'none';
                    }, 3000);
                    
                    return;
                }
                
                // Si no es PDF, intentamos leer como JSON
                try {
                    const text = await blob.text();
                    const data = JSON.parse(text);
                    
                    // Si es un JSON con un turno, lo mostramos
                    if (data.success && data.turno) {
                        mostrarMensajeTurno(resultadoDiv, data.turno, false);
                        
                        // Cerrar el modal después de 5 segundos
                        setTimeout(() => {
                            if (modalCliente) modalCliente.style.display = 'none';
                            if (modalVisitante) modalVisitante.style.display = 'none';
                            if (modalBackground) modalBackground.style.display = 'none';
                        }, 5000);
                        
                        return;
                    }
                    
                    // Si hay un mensaje de error en el JSON, lo mostramos
                    throw new Error(data.error || 'Error desconocido del servidor');
                } catch (e) {
                    console.error('Error al procesar la respuesta:', e);
                    throw new Error('El servidor respondió con un formato inesperado. Por favor, inténtalo de nuevo.');
                }
            })
            .catch(err => {
                // Mostrar el error al usuario
                console.error('Fallo en el proceso de turno:', err);
                resultadoDiv.innerHTML = `
                    <div style="color: red; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; background-color: #f8d7da; margin-top: 10px;">
                        <p style="margin: 0; font-weight: bold;">❌ ¡Error al generar el turno!</p>
                        <p style="margin: 5px 0 0 0;">${err.message}</p>
                    </div>
                `;
            });
        });
    }

    
    // --- Arranque: Enganchar la lógica de envío a los formularios ---
    
    const formCliente = document.getElementById('form-cliente');
    if (formCliente) {
        enviarTurno(formCliente, 'Cliente', 'resultado-cliente');
    }

    const formVisitante = document.getElementById('form-visitante');
    if (formVisitante) {
        enviarTurno(formVisitante, 'Visitante', 'resultado-visitante');
    }
});