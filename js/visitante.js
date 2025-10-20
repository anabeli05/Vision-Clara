// Modal Visitante
const modalVisitante = document.getElementById("modalVisitante");
const btnVisitante = document.getElementById("btnVisitante");
const turnoAleatorio = document.getElementById("turnoAleatorio");
const ticket = document.getElementById('ticket');
const ticketTurno = document.getElementById('ticketTurno');

// Abrir modal visitante y solicitar turno al servidor
btnVisitante.onclick = () => {
  modalVisitante.style.display = "flex";
  // Solicitar turno visitante al backend
  fetch('Pantalla_Turnos/api-turnos.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'tipo=Visitante'
  })
  .then(r => r.json())
  .then(data => {
    if (data.success && data.turno) {
      turnoAleatorio.textContent = data.turno;
      if (ticket && ticketTurno) {
        ticketTurno.textContent = data.turno;
        ticket.style.display = 'block';
      }
    } else {
      turnoAleatorio.textContent = 'Error';
    }
  })
  .catch(() => {
    turnoAleatorio.textContent = 'Error de conexión';
  });
};

// Cerrar modal visitante con la X
document.querySelector('#modalVisitante .close').onclick = () => {
  modalVisitante.style.display = "none";
};

// Cerrar modal visitante al hacer clic afuera
window.addEventListener("click", e => {
  if (e.target.id === "modalVisitante") {
    modalVisitante.style.display = "none";
  }
});
