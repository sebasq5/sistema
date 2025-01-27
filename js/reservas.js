// Obtener reservas desde localStorage
function getReservations() {
    return JSON.parse(localStorage.getItem("reservations")) || [];
}

// Renderizar la tabla de reservas
function renderizarReservas() {
    const reservasLista = document.getElementById("reservas-lista");
    reservasLista.innerHTML = ""; // Limpiar la tabla

    const reservas = getReservations();

    reservas.forEach((reserva, index) => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${index + 1}</td>
            <td>${reserva.username}</td>
            <td>${reserva.activity}</td>
            <td>${reserva.date}</td>
            <td>${reserva.time}</td>
            <td>${reserva.people}</td>
            <td>
                <button class="edit" onclick="editarReserva(${index})">Editar</button>
                <button class="delete" onclick="eliminarReserva(${index})">Eliminar</button>
            </td>
        `;
        reservasLista.appendChild(fila);
    });
}

// Función para eliminar una reserva
function eliminarReserva(index) {
    const reservas = getReservations();
    reservas.splice(index, 1); // Eliminar la reserva seleccionada
    localStorage.setItem("reservations", JSON.stringify(reservas));
    renderizarReservas(); // Actualizar la tabla
}

// Función para editar una reserva
function editarReserva(index) {
    const reservas = getReservations();
    const reserva = reservas[index];

    // Mostrar los datos actuales en el formulario de edición (si existe un formulario adicional)
    alert(
        `Editar Reserva:\nUsuario: ${reserva.username}\nActividad: ${reserva.activity}\nFecha: ${reserva.date}\nHora: ${reserva.time}\nPersonas: ${reserva.people}`
    );

    // Implementar lógica para guardar los cambios
    // Por ejemplo, abrir un modal o formulario emergente
}

// Inicializar la tabla al cargar la página
document.addEventListener("DOMContentLoaded", () => {
    renderizarReservas();
});
