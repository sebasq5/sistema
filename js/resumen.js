document.addEventListener("DOMContentLoaded", () => {
    // Validar sesión del usuario
    const activeUser = JSON.parse(localStorage.getItem("activeUser"));
    if (!activeUser) {
        alert("Por favor, inicia sesión para continuar.");
        window.location.href = "login.html";
        return;
    }

    // Mostrar mensaje de bienvenida
    const welcomeMessage = document.getElementById("welcome-message");
    welcomeMessage.innerHTML = `
        <h1>Saludos, <span style="color: #6b3a1e; font-weight: bold;">${activeUser.username}</span></h1>
        <p>Este es tu resumen de reservas:</p>
    `;

    // Leer reservas desde localStorage
    const reservations = JSON.parse(localStorage.getItem("reservations")) || [];
    console.log("Reservas cargadas:", reservations); // Depuración

    // Filtrar reservas del usuario actual
    const userReservations = reservations.filter(
        (reservation) => reservation.username === activeUser.username
    );

    // Mostrar reservas en la página
    const reservationsContainer = document.getElementById("reservations-container");
    reservationsContainer.innerHTML = ""; // Limpiar contenedor

    if (userReservations.length === 0) {
        reservationsContainer.innerHTML = "<p>No tienes reservas realizadas.</p>";
        return;
    }

    userReservations.forEach((reservation, index) => {
        const reservationDiv = document.createElement("div");
        reservationDiv.classList.add("reservation-item");
        reservationDiv.innerHTML = `
            <h2>${reservation.activity}</h2>
            <p><strong>Fecha:</strong> ${reservation.date}</p>
            <p><strong>Hora:</strong> ${reservation.time}</p>
            <p><strong>Personas:</strong> ${reservation.people}</p>
            <button class="delete-btn" onclick="deleteReservation(${index})">Eliminar</button>
        `;
        reservationsContainer.appendChild(reservationDiv);
    });
});

// Función para eliminar una reserva
function deleteReservation(index) {
    const activeUser = JSON.parse(localStorage.getItem("activeUser"));
    const reservations = JSON.parse(localStorage.getItem("reservations")) || [];
    const userReservations = reservations.filter(
        (reservation) => reservation.username === activeUser.username
    );

    // Remover la reserva seleccionada
    const globalIndex = reservations.indexOf(userReservations[index]);
    if (globalIndex !== -1) {
        reservations.splice(globalIndex, 1);
    }

    // Guardar cambios en localStorage
    localStorage.setItem("reservations", JSON.stringify(reservations));

    // Recargar las reservas en el DOM
    alert("Reserva eliminada exitosamente.");
    location.reload();
}

// Función para cerrar sesión
function logout() {
    localStorage.removeItem("activeUser"); // Elimina la sesión activa
    alert("Sesión cerrada exitosamente.");
    window.location.href = "login.html"; // Redirige a la página de inicio de sesión
}
