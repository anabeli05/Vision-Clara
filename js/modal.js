// Obtener elementos
const modal = document.getElementById("modalCliente");
const btnCliente = document.getElementById("btnCliente");
const spanClose = document.querySelector(".close");

// Abrir modal
btnCliente.addEventListener("click", () => {
  modal.style.display = "flex";
});

// Cerrar modal
spanClose.addEventListener("click", () => {
  modal.style.display = "none";
});

// Cerrar si clickea fuera
window.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
});
