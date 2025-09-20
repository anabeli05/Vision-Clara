// visitante.js
document.addEventListener('DOMContentLoaded', () => {
  const modalVisitante = document.getElementById('modalVisitante');
  const turnoAleatorio = document.getElementById('turnoAleatorio');
  const ticketTurno = document.getElementById('ticketTurno');
  let contadorTurnos = 1;

  // Cuando modalVisitante emite 'modalOpen' (lo hace modal.js al abrir) => generar y mostrar turno
  modalVisitante.addEventListener('modalOpen', () => {
    // Mostrar el turno en el modal (igual que antes)
    turnoAleatorio.textContent = "N(" + contadorTurnos + ")";

    // Preparar el texto del ticket (puedes ajustar la numeración si quieres)
    const ticketNumber = "C - " + (100 + contadorTurnos);
    ticketTurno.textContent = ticketNumber;

    contadorTurnos++;

    // Dar tiempo a que el usuario vea el modal antes de capturar
    setTimeout(() => {
      descargarTicket(ticketNumber);
    }, 600); // 600ms, ajustable
  });

  // Función para convertir el ticket oculto a PDF y descargarlo
  async function descargarTicket(ticketNumber) {
    const ticket = document.getElementById('ticket');
    ticket.style.display = 'block'; // mostrar para capturar

    // Captura con html2canvas
    const canvas = await html2canvas(ticket);
    const imgData = canvas.toDataURL('image/png');

    // Crear PDF con jsPDF (ajusta tamaño si quieres)
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
    // Nombre del archivo con el número del ticket
    pdf.save(ticketNumber.replace(/\s/g, '_') + '.pdf');

    ticket.style.display = 'none'; // volver a ocultar
  }
});
