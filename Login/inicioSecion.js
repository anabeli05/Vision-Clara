// ===== FUNCIONES PARA CONTRASEÑA =====

// Función para mostrar/ocultar contraseña
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleButton = passwordInput.nextElementSibling;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.setAttribute('aria-label', 'Ocultar contraseña');
    } else {
        passwordInput.type = 'password';
        toggleButton.setAttribute('aria-label', 'Mostrar contraseña');
    }
}

// Inicializar el ícono del ojito en los campos de contraseña
function initPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        // Establecer el ícono inicial (ojo abierto = contraseña oculta)
        button.textContent = '👁️';
        button.setAttribute('aria-label', 'Mostrar contraseña');
        button.style.cursor = 'pointer';
    });
}

// ===== FUNCIONES PARA CÓDIGOS DE VERIFICACIÓN =====

// Función para mover automáticamente al siguiente campo de código
function initCodeInputs() {
    const inputs = document.querySelectorAll('.code-inputs input');
    
    if (inputs.length > 0) {
        inputs.forEach((input, index) => {
            // Mover al siguiente campo cuando se ingresa un carácter
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            
            // Retroceder al campo anterior cuando se presiona Backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && index > 0 && e.target.value === '') {
                    inputs[index - 1].focus();
                }
            });
            
            // Permitir solo números
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });
        
        // Enfocar el primer campo automáticamente
        if (inputs[0]) {
            inputs[0].focus();
        }
    }
}

// ===== FUNCIONES PARA VALIDACIÓN DE CONTRASEÑA =====

// Validación de fortaleza de contraseña
function initPasswordStrength() {
    const newPasswordInput = document.getElementById('new-password');
    const strengthMeter = document.getElementById('password-strength-meter');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const matchMessage = document.getElementById('password-match-message');
    
    // Validar fortaleza de la contraseña
    if (newPasswordInput && strengthMeter) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Validar longitud (mínimo 8 caracteres)
            if (password.length >= 8) strength += 25;
            
            // Validar mayúsculas
            if (/[A-Z]/.test(password)) strength += 25;
            
            // Validar números
            if (/[0-9]/.test(password)) strength += 25;
            
            // Validar caracteres especiales
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;
            
            // Actualizar medidor visual
            strengthMeter.style.width = strength + '%';
            
            // Cambiar color según fortaleza
            if (strength < 50) {
                strengthMeter.style.backgroundColor = '#ff4d4d'; // Rojo - Débil
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#ffa64d'; // Naranja - Media
            } else {
                strengthMeter.style.backgroundColor = '#2ecc71'; // Verde - Fuerte
            }
        });
    }
    
    // Validar que las contraseñas coincidan
    if (newPasswordInput && confirmPasswordInput && matchMessage) {
        confirmPasswordInput.addEventListener('input', function() {
            if (newPasswordInput.value !== this.value) {
                matchMessage.textContent = '❌ Las contraseñas no coinciden';
                matchMessage.style.color = '#ff4d4d';
            } else if (this.value !== '') {
                matchMessage.textContent = '✓ Las contraseñas coinciden';
                matchMessage.style.color = '#2ecc71';
            } else {
                matchMessage.textContent = '';
            }
        });
    }
}

// ===== FUNCIONES DE ANIMACIÓN Y UI =====

// Eliminar mensajes de error/éxito después de 5 segundos
function initMessageTimeout() {
    const errorMessage = document.querySelector('.error-message');
    const successMessage = document.querySelector('.success-message');
    
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.style.opacity = '0';
            errorMessage.style.transition = 'opacity 0.5s ease';
            setTimeout(() => errorMessage.remove(), 500);
        }, 5000);
    }
    
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.opacity = '0';
            successMessage.style.transition = 'opacity 0.5s ease';
            setTimeout(() => successMessage.remove(), 500);
        }, 5000);
    }
}

// ===== INICIALIZACIÓN PRINCIPAL =====

// Inicializar todas las funcionalidades cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar funcionalidades de contraseña
    initPasswordToggles();
    initPasswordStrength();
    
    // Inicializar campos de código de verificación
    initCodeInputs();
    
    // Inicializar mensajes con temporizador
    initMessageTimeout();
    
    console.log('✓ Todas las funcionalidades inicializadas correctamente');
});