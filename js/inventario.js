// Clase para el Nodo del Árbol Binario
class Nodo {
    constructor(data) {
        this.data = data;
        this.left = null;
        this.right = null;
    }
}

// Clase para el Árbol Binario
class ArbolBinario {
    constructor() {
        this.root = null;
    }

    // Insertar en el árbol
    insertar(data) {
        const nuevoNodo = new Nodo(data);
        if (this.root === null) {
            this.root = nuevoNodo;
        } else {
            this._insertarRecursivo(this.root, nuevoNodo);
        }
    }

    _insertarRecursivo(actual, nuevoNodo) {
        if (nuevoNodo.data.activity.toLowerCase() < actual.data.activity.toLowerCase()) {
            if (actual.left === null) {
                actual.left = nuevoNodo;
            } else {
                this._insertarRecursivo(actual.left, nuevoNodo);
            }
        } else {
            if (actual.right === null) {
                actual.right = nuevoNodo;
            } else {
                this._insertarRecursivo(actual.right, nuevoNodo);
            }
        }
    }

    // Buscar en el árbol
    buscar(activity) {
        return this._buscarRecursivo(this.root, activity.toLowerCase());
    }

    _buscarRecursivo(actual, activity) {
        if (actual === null) return null;
        if (activity === actual.data.activity.toLowerCase()) return actual.data;
        if (activity < actual.data.activity.toLowerCase()) return this._buscarRecursivo(actual.left, activity);
        return this._buscarRecursivo(actual.right, activity);
    }

    // Obtener todos los nodos en orden
    obtenerEnOrden() {
        const resultado = [];
        this._enOrden(this.root, resultado);
        return resultado;
    }

    _enOrden(actual, resultado) {
        if (actual !== null) {
            this._enOrden(actual.left, resultado);
            resultado.push(actual.data);
            this._enOrden(actual.right, resultado);
        }
    }
}

// Instancia del Árbol Binario
const arbolReservas = new ArbolBinario();

// Normalizar texto para evitar duplicados y facilitar búsquedas
function normalizarTexto(texto) {
    return texto
        .toLowerCase()
        .normalize("NFD") // Elimina acentos
        .replace(/[\u0300-\u036f]/g, ""); // Remueve marcas diacríticas
}

// Obtener reservas desde localStorage
function getReservations() {
    const reservas = JSON.parse(localStorage.getItem("reservations")) || [];
    arbolReservas.root = null; // Reiniciar el árbol
    reservas.forEach((reserva) => arbolReservas.insertar(reserva));
    return arbolReservas.obtenerEnOrden();
}

// Guardar reservas en localStorage
function saveReservations(reservations) {
    localStorage.setItem("reservations", JSON.stringify(reservations));
}

// Renderizar la tabla de reservas
function renderizarReservas(filtradas = null) {
    const reservasLista = document.getElementById("reservas-lista");
    reservasLista.innerHTML = ""; // Limpiar la tabla

    const reservas = filtradas || getReservations();

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

// Función para agregar una nueva reserva
function agregarReserva(nuevaReserva) {
    const reservas = getReservations();

    // Validar duplicados
    if (reservas.some((reserva) =>
        normalizarTexto(reserva.username) === normalizarTexto(nuevaReserva.username) &&
        normalizarTexto(reserva.activity) === normalizarTexto(nuevaReserva.activity) &&
        reserva.date === nuevaReserva.date &&
        reserva.time === nuevaReserva.time
    )) {
        alert("La reserva ya existe.");
        return;
    }

    reservas.push(nuevaReserva); // Agregar la nueva reserva
    saveReservations(reservas); // Guardar en localStorage
    arbolReservas.insertar(nuevaReserva); // Insertar en el árbol
    renderizarReservas(); // Actualizar la tabla
}

// Función para eliminar una reserva
function eliminarReserva(index) {
    const reservas = getReservations();
    reservas.splice(index, 1); // Eliminar la reserva seleccionada
    saveReservations(reservas); // Guardar los cambios en localStorage
    renderizarReservas(); // Actualizar la tabla
}

// Función para editar una reserva
function editarReserva(index) {
    const reservas = getReservations();
    const reserva = reservas[index];

    // Mostrar los datos actuales en un formulario emergente o modal
    const username = prompt("Editar Usuario:", reserva.username);
    const activity = prompt("Editar Actividad:", reserva.activity);
    const date = prompt("Editar Fecha (YYYY-MM-DD):", reserva.date);
    const time = prompt("Editar Hora (HH:MM):", reserva.time);
    const people = prompt("Editar Personas:", reserva.people);

    // Validar campos
    if (!username || !activity || !date || !time || isNaN(people)) {
        alert("Todos los campos deben ser válidos.");
        return;
    }

    // Actualizar la reserva
    reservas[index] = {
        username,
        activity,
        date,
        time,
        people: parseInt(people, 10),
    };

    saveReservations(reservas); // Guardar los cambios
    renderizarReservas(); // Actualizar la tabla
}

// Función para buscar reservas por actividad
function buscarReservas() {
    const termino = normalizarTexto(document.getElementById("busqueda-input").value.trim());

    // Validar si el término de búsqueda está vacío
    if (!termino) {
        alert("Por favor, ingresa una actividad para buscar.");
        return;
    }

    // Buscar en el árbol binario
    const resultado = arbolReservas.buscar(termino);

    const reservasLista = document.getElementById("reservas-lista");
    reservasLista.innerHTML = ""; // Limpiar la tabla

    if (resultado) {
        // Si se encuentra el resultado, mostrarlo
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>1</td>
            <td>${resultado.username}</td>
            <td>${resultado.activity}</td>
            <td>${resultado.date}</td>
            <td>${resultado.time}</td>
            <td>${resultado.people}</td>
            <td>
                <button class="edit" onclick="editarReserva(0)">Editar</button>
                <button class="delete" onclick="eliminarReserva(0)">Eliminar</button>
            </td>
        `;
        reservasLista.appendChild(fila);
    } else {
        // Si no hay resultados, mostrar un mensaje
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td colspan="7" style="text-align: center;">No se encontraron resultados para "${termino}".</td>
        `;
        reservasLista.appendChild(fila);
    }
}

// Inicializar la tabla al cargar la página
document.addEventListener("DOMContentLoaded", () => {
    renderizarReservas();
});
