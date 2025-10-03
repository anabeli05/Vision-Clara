// Función para mostrar/ocultar contraseña
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleButton = passwordInput.nextElementSibling;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.textContent = '🔒';
    } else {
        passwordInput.type = 'password';
        toggleButton.textContent = '👁️';
    }
}

// Función para mover automáticamente al siguiente campo de código
function initCodeInputs() {
    const inputs = document.querySelectorAll('.code-inputs input');
    
    if (inputs.length > 0) {
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && index > 0 && e.target.value === '') {
                    inputs[index - 1].focus();
                }
            });
        });
    }
}

// Validación de fortaleza de contraseña
function initPasswordStrength() {
    const newPasswordInput = document.getElementById('new-password');
    const strengthMeter = document.getElementById('password-strength-meter');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const matchMessage = document.getElementById('password-match-message');
    
    if (newPasswordInput && strengthMeter) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Validar longitud
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
                strengthMeter.style.backgroundColor = '#ff4d4d';
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#ffa64d';
            } else {
                strengthMeter.style.backgroundColor = '#2ecc71';
            }
        });
    }
    
    if (newPasswordInput && confirmPasswordInput && matchMessage) {
        confirmPasswordInput.addEventListener('input', function() {
            if (newPasswordInput.value !== this.value) {
                matchMessage.textContent = 'Las contraseñas no coinciden';
                matchMessage.style.color = '#ff4d4d';
            } else {
                matchMessage.textContent = 'Las contraseñas coinciden';
                matchMessage.style.color = '#2ecc71';
            }
        });
    }
}

// Inicializar todas las funcionalidades cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initCodeInputs();
    initPasswordStrength();
});