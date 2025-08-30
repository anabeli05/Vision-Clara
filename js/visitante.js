// Modal Visitante
const modalVisitante = document.getElementById("modalVisitante");
const btnVisitante = document.getElementById("btnVisitante");
const turnoAleatorio = document.getElementById("turnoAleatorio");

// Contador de turnos en secuencia
let contadorTurnos = 1;

// Abrir modal visitante
btnVisitante.onclick = () => {
  modalVisitante.style.display = "flex";
  // Asignar turno en secuencia
  turnoAleatorio.textContent = "N(" + contadorTurnos + ")";
  contadorTurnos++;
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
