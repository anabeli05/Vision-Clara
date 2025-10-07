// Vista de Turno - JavaScript
class VistaTurno {
    constructor() {
        this.updateInterval = 5000; // Actualizar cada 5 segundos
        this.isUpdating = false;
        this.init();
    }

    init() {
        console.log('Inicializando Vista de Turno...');
        this.setupEventListeners();
        this.startAutoUpdate();
        this.addVisualEffects();
    }

    setupEventListeners() {
        // Agregar efectos de hover a las filas
        const tableRows = document.querySelectorAll('.table-row, .waiting-item');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', this.handleRowHover.bind(this));
            row.addEventListener('mouseleave', this.handleRowLeave.bind(this));
        });

        // Efecto de click en los elementos
        const clickableElements = document.querySelectorAll('.table-row, .waiting-item');
        clickableElements.forEach(element => {
            element.addEventListener('click', this.handleElementClick.bind(this));
        });

        // Detener actualización automática cuando la página no está visible
        document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
    }

    handleRowHover(event) {
        const row = event.currentTarget;
        row.style.transform = 'translateX(5px)';
        row.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    }

    handleRowLeave(event) {
        const row = event.currentTarget;
        row.style.transform = 'translateX(0)';
        row.style.boxShadow = 'none';
    }

    handleElementClick(event) {
        const element = event.currentTarget;
        
        // Efecto de ripple
        this.createRippleEffect(element, event);
        
        // Destacar temporalmente el elemento
        element.style.backgroundColor = '#dbeafe';
        setTimeout(() => {
            element.style.backgroundColor = '';
        }, 300);
    }

    createRippleEffect(element, event) {
        const ripple = document.createElement('div');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.stopAutoUpdate();
        } else {
            this.startAutoUpdate();
        }
    }

    startAutoUpdate() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.updateTimer = setInterval(() => {
            this.updateData();
        }, this.updateInterval);
        
        console.log('Actualización automática iniciada');
    }

    stopAutoUpdate() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
            this.updateTimer = null;
        }
        console.log('Actualización automática detenida');
    }

    async updateData() {
        if (this.isUpdating) return;
        
        this.isUpdating = true;
        
        try {
            // Simular actualización de datos
            await this.fetchUpdatedData();
            this.showUpdateNotification();
        } catch (error) {
            console.error('Error al actualizar datos:', error);
        } finally {
            this.isUpdating = false;
        }
    }

    async fetchUpdatedData() {
        try {
            const response = await fetch('api-turnos.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateUI(data.data);
                return data;
            } else {
                throw new Error(data.error || 'Error al obtener datos');
            }
        } catch (error) {
            console.error('Error en fetchUpdatedData:', error);
            throw error;
        }
    }

    updateUI(data) {
        // Actualizar ventanillas
        this.updateVentanillas(data.ventanillas);
        
        // Actualizar turnos en espera
        this.updateTurnosEnEspera(data.turnos_en_espera);
        
        // Actualizar estadísticas
        this.updateEstadisticas(data.estadisticas);
    }

    updateVentanillas(ventanillas) {
        const tableBody = document.querySelector('.table-body');
        if (!tableBody) return;

        tableBody.innerHTML = '';
        
        ventanillas.forEach(ventanilla => {
            const row = document.createElement('div');
            row.className = 'table-row';
            row.innerHTML = `
                <span class="window-number">Ventanilla ${ventanilla.numero}</span>
                <span class="window-status ${ventanilla.estado === 'ocupada' ? 'occupied' : 'free'}">
                    ${ventanilla.estado.charAt(0).toUpperCase() + ventanilla.estado.slice(1)}
                </span>
                <span class="current-turn">${ventanilla.turno_actual || '-'}</span>
            `;
            tableBody.appendChild(row);
        });
    }

    updateTurnosEnEspera(turnos) {
        const listBody = document.querySelector('.list-body');
        if (!listBody) return;

        listBody.innerHTML = '';
        
        turnos.forEach(turno => {
            const item = document.createElement('div');
            item.className = 'waiting-item';
            item.innerHTML = `
                <span class="turn-number">${turno.numero}</span>
            `;
            listBody.appendChild(item);
        });
    }

    updateEstadisticas(estadisticas) {
        // Aquí se pueden actualizar elementos adicionales como contadores
        console.log('Estadísticas actualizadas:', estadisticas);
    }

    showUpdateNotification() {
        // Crear notificación de actualización
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
        `;
        notification.textContent = 'Datos actualizados';
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    }

    addVisualEffects() {
        // Agregar animaciones CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            .pulse-animation {
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
                100% {
                    transform: scale(1);
                }
            }
        `;
        document.head.appendChild(style);

        // Agregar efecto de pulso a elementos importantes
        const importantElements = document.querySelectorAll('.turn-number, .current-turn');
        importantElements.forEach(element => {
            element.classList.add('pulse-animation');
        });
    }

    // Método para actualizar manualmente
    forceUpdate() {
        this.updateData();
    }

    // Método para cambiar intervalo de actualización
    setUpdateInterval(interval) {
        this.updateInterval = interval;
        this.stopAutoUpdate();
        this.startAutoUpdate();
    }

    // Método para obtener estadísticas
    getStats() {
        const ventanillasLibres = document.querySelectorAll('.window-status.free').length;
        const ventanillasOcupadas = document.querySelectorAll('.window-status.occupied').length;
        const turnosEnEspera = document.querySelectorAll('.waiting-item').length;
        
        return {
            ventanillasLibres,
            ventanillasOcupadas,
            turnosEnEspera,
            totalVentanillas: ventanillasLibres + ventanillasOcupadas
        };
    }

    // Método para mostrar estadísticas en consola
    logStats() {
        const stats = this.getStats();
        console.log('Estadísticas actuales:', stats);
        return stats;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.vistaTurno = new VistaTurno();
    
    // Agregar métodos globales para debugging
    window.updateTurnos = () => window.vistaTurno.forceUpdate();
    window.getStats = () => window.vistaTurno.logStats();
    window.setUpdateInterval = (interval) => window.vistaTurno.setUpdateInterval(interval);
    
    console.log('Vista de Turno inicializada correctamente');
    console.log('Comandos disponibles:');
    console.log('- updateTurnos(): Actualizar datos manualmente');
    console.log('- getStats(): Ver estadísticas actuales');
    console.log('- setUpdateInterval(ms): Cambiar intervalo de actualización');
});

// Manejar errores globales
window.addEventListener('error', (event) => {
    console.error('Error en Vista de Turno:', event.error);
});

// Limpiar al cerrar la página
window.addEventListener('beforeunload', () => {
    if (window.vistaTurno) {
        window.vistaTurno.stopAutoUpdate();
    }
});
