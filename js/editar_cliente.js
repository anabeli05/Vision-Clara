function cerrarModal() {
    const modal = document.getElementById('modalExito');
    if(modal) {
        // Agregar animación de salida antes de cerrar
        modal.style.animation = 'fadeOut 0.4s ease forwards';
        
        setTimeout(() => {
            modal.style.display = 'none';
            window.location.href = 'clientes.php';
        }, 400);
    }
}

// Mostrar modal automáticamente si existe
window.onload = function() {
    const modal = document.getElementById('modalExito');
    if(modal) {
        modal.style.display = 'flex';
        
        // Agregar animación de entrada
        setTimeout(() => {
            modal.style.animation = 'fadeIn 0.4s ease forwards';
        }, 10);
        
        // Cerrar automáticamente después de 2 segundos
        setTimeout(cerrarModal, 2000);
        
        // Cerrar al hacer clic fuera del modal
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                cerrarModal();
            }
        });
        
        // Cerrar con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });
    }
    
    // Mejoras para el formulario
    const form = document.querySelector('form');
    const inputs = document.querySelectorAll('input');
    
    // Efectos visuales para inputs con datos
    inputs.forEach(input => {
        // Resaltar inputs que ya tienen datos
        if (input.value.trim() !== '') {
            input.style.borderColor = '#0073e6';
            input.style.backgroundColor = '#f8fbff';
        }
        
        // Efecto al interactuar con el input
        input.addEventListener('focus', function() {
            this.style.borderColor = '#0073e6';
            this.style.boxShadow = '0 0 0 3px rgba(0, 115, 230, 0.1)';
            this.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.style.boxShadow = 'none';
            this.style.transform = 'translateY(0)';
            
            if (this.value.trim() === '') {
                this.style.borderColor = '#e0e0e0';
                this.style.backgroundColor = '#fff';
            }
        });
        
        // Efecto al escribir
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.style.borderColor = '#0073e6';
                this.style.backgroundColor = '#f8fbff';
            } else {
                this.style.borderColor = '#e0e0e0';
                this.style.backgroundColor = '#fff';
            }
        });
    });
    
    // Prevenir envío múltiple del formulario
    if (form) {
        let formSubmitted = false;
        
        form.addEventListener('submit', function(e) {
            if (formSubmitted) {
                e.preventDefault();
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                formSubmitted = true;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '🔄 Guardando...';
                submitBtn.style.opacity = '0.8';
                submitBtn.style.cursor = 'not-allowed';
                
                // Restaurar después de 3 segundos por si hay error
                setTimeout(() => {
                    if (formSubmitted) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Guardar Cambios';
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                        formSubmitted = false;
                    }
                }, 3000);
            }
        });
    }
};

// Agregar estilos CSS para las animaciones
document.head.insertAdjacentHTML('beforeend', `
<style>
    @keyframes fadeIn {
        from { 
            opacity: 0;
            transform: scale(0.9);
        }
        to { 
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes fadeOut {
        from { 
            opacity: 1;
            transform: scale(1);
        }
        to { 
            opacity: 0;
            transform: scale(0.9);
        }
    }
    
    /* Efecto de pulso para el modal */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    .modal-content {
        animation: pulse 2s infinite;
    }
</style>
`);