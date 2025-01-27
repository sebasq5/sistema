// Validar la reserva según la actividad, cantidad de personas, fecha y hora
function validateReservation(activity, people, date, time) {
    const minPeople = {
        Fútbol: 12,
        Vóley: 10,
        Piscina: 10,
        Acuario: 1
    };

    const maxPeople = {
        Acuario: 10
    };

    const currentDate = new Date();
    const reservationDate = new Date(date);
    const maxDate = new Date();
    maxDate.setMonth(maxDate.getMonth() + 3);

    // Validar cantidad mínima de personas
    if (people < minPeople[activity]) {
        alert(`La actividad ${activity} requiere al menos ${minPeople[activity]} personas.`);
        return false;
    }

    // Validar cantidad máxima de personas
    if (maxPeople[activity] && people > maxPeople[activity]) {
        alert(`La actividad ${activity} no puede tener más de ${maxPeople[activity]} personas.`);
        return false;
    }

    // Validar fecha dentro del rango permitido (3 meses)
    if (reservationDate < currentDate) {
        alert("La fecha de la reserva no puede ser en el pasado.");
        return false;
    }
    if (reservationDate > maxDate) {
        alert("Solo puedes reservar con hasta 3 meses de anticipación.");
        return false;
    }

    // Validar hora dentro del rango permitido (8:00 - 18:00)
    const [hours, minutes] = time.split(":").map(Number);
    if (hours < 8 || hours > 18 || (hours === 18 && minutes > 0)) {
        alert("El horario de reserva debe ser entre las 8:00 y las 18:00.");
        return false;
    }

    return true;
}

// Guardar reservas en localStorage
function saveReservation(reservation) {
    let reservations = JSON.parse(localStorage.getItem("reservations")) || [];
    reservations.push(reservation);
    localStorage.setItem("reservations", JSON.stringify(reservations));
}

// Cargar reservas desde localStorage
function getReservations() {
    return JSON.parse(localStorage.getItem("reservations")) || [];
}

// Manejar el envío del formulario de reserva
document.getElementById("reservation-form").addEventListener("submit", function (e) {
    e.preventDefault();

    const activity = document.getElementById("activity").value;
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;
    const people = parseInt(document.getElementById("people").value, 10);

    // Validar la reserva
    if (!validateReservation(activity, people, date, time)) {
        return;
    }

    // Crear la reserva
    const activeUser = JSON.parse(localStorage.getItem("activeUser"));
    const reservation = {
        username: activeUser.username,
        activity,
        date,
        time,
        people
    };

    // Guardar la reserva en localStorage
    saveReservation(reservation);
    alert("Reserva guardada correctamente.");
    document.getElementById("reservation-form").reset();
});

// Mostrar reservas en la página de resumen
function loadReservations() {
    const activeUser = JSON.parse(localStorage.getItem("activeUser"));
    if (!activeUser) {
        alert("Por favor, inicia sesión para continuar.");
        window.location.href = "login.html";
        return;
    }

    const reservations = getReservations();
    const userReservations = reservations.filter(res => res.username === activeUser.username);

    const reservationsContainer = document.getElementById("reservations-container");
    reservationsContainer.innerHTML = ""; // Limpiar contenedor

    if (userReservations.length === 0) {
        reservationsContainer.innerHTML = "<p>No tienes reservas realizadas.</p>";
        return;
    }

    userReservations.forEach(res => {
        const div = document.createElement("div");
        div.classList.add("reservation-item");
        div.innerHTML = `
            <h2>${res.activity}</h2>
            <p><strong>Fecha:</strong> ${res.date}</p>
            <p><strong>Hora:</strong> ${res.time}</p>
            <p><strong>Personas:</strong> ${res.people}</p>
        `;
        reservationsContainer.appendChild(div);
    });
}

// Ejecutar cuando se cargue la página de resumen
document.addEventListener("DOMContentLoaded", () => {
    const isResumenPage = document.getElementById("reservations-container");
    if (isResumenPage) {
        loadReservations();
    }
});
