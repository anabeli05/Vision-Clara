// modal.js
document.addEventListener('DOMContentLoaded', () => {
  const modalCliente = document.getElementById('modalCliente');
  const modalVisitante = document.getElementById('modalVisitante');
  const btnCliente = document.getElementById('btnCliente');
  const btnVisitante = document.getElementById('btnVisitante');

  // Abrir modal Cliente
  btnCliente.addEventListener('click', () => {
    modalCliente.style.display = 'flex';
  });

  // Abrir modal Visitante y notificar a visitante.js que se abrió
  btnVisitante.addEventListener('click', () => {
    modalVisitante.style.display = 'flex';
    // evento personalizado que escucha visitante.js
    modalVisitante.dispatchEvent(new Event('modalOpen'));
  });

  // Cerrar al hacer click en cualquier botón .close dentro de un modal
  document.querySelectorAll('.modal .close').forEach(closeBtn => {
    closeBtn.addEventListener('click', (e) => {
      const modal = e.target.closest('.modal');
      if (modal) modal.style.display = 'none';
    });
  });

  // Cerrar si hace click fuera del contenido (en el fondo del modal)
  window.addEventListener('click', (e) => {
    if (e.target.classList && e.target.classList.contains('modal')) {
      e.target.style.display = 'none';
    }
  });
});
