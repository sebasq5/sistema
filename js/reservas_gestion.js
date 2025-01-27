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

    insertar(data) {
        const nuevoNodo = new Nodo(data);
        if (this.root === null) {
            this.root = nuevoNodo;
        } else {
            this._insertarRecursivo(this.root, nuevoNodo);
        }
    }

    _insertarRecursivo(actual, nuevoNodo) {
        if (nuevoNodo.data.username?.toLowerCase() < actual.data.username?.toLowerCase()) {
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

    buscar(criterio) {
        return this._buscarRecursivo(this.root, criterio.toLowerCase());
    }

    _buscarRecursivo(actual, criterio) {
        if (actual === null) return null;

        if (
            actual.data.username?.toLowerCase() === criterio ||
            actual.data.email?.toLowerCase() === criterio ||
            actual.data.activity?.toLowerCase() === criterio
        ) {
            return actual.data;
        }

        if (criterio < actual.data.username?.toLowerCase()) {
            return this._buscarRecursivo(actual.left, criterio);
        } else {
            return this._buscarRecursivo(actual.right, criterio);
        }
    }

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

// Instancias de Árboles Binarios
const arbolReservas = new ArbolBinario();
const arbolUsuarios = new ArbolBinario();
const arbolPeces = new ArbolBinario();

// ** Normalización de Texto **
function normalizarTexto(texto) {
    return texto
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
}

// ** Gestión de Reservas **
function getReservations() {
    const reservas = JSON.parse(localStorage.getItem("reservations")) || [];
    arbolReservas.root = null;
    reservas.forEach((reserva) => arbolReservas.insertar(reserva));
    return arbolReservas.obtenerEnOrden();
}

function renderizarReservas() {
    const reservasLista = document.getElementById("reservas-lista");
    const reservas = getReservations();
    reservasLista.innerHTML = reservas.map((reserva, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${reserva.username}</td>
            <td>${reserva.activity}</td>
            <td>${reserva.date}</td>
            <td>${reserva.time}</td>
            <td>${reserva.people}</td>
            <td>
                <button onclick="editarReserva(${index})">Editar</button>
                <button onclick="eliminarReserva(${index})">Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function eliminarReserva(index) {
    const reservas = getReservations();
    reservas.splice(index, 1);
    localStorage.setItem("reservations", JSON.stringify(reservas));
    renderizarReservas();
}

// ** Gestión de Usuarios **
function getUsers() {
    const usuarios = JSON.parse(localStorage.getItem("users")) || [];
    arbolUsuarios.root = null;
    usuarios.forEach((user) => arbolUsuarios.insertar(user));
    return arbolUsuarios.obtenerEnOrden();
}

function renderizarUsuarios() {
    const usuariosLista = document.getElementById("usuarios-lista");
    const usuarios = getUsers();
    usuariosLista.innerHTML = usuarios.map((usuario, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${usuario.username}</td>
            <td>${usuario.email}</td>
            <td>${usuario.rol || "Cliente"}</td>
            <td>${usuario.estado || "Activo"}</td>
            <td>
                <button onclick="editarUsuario(${index})">Editar</button>
                <button onclick="eliminarUsuario(${index})">Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function eliminarUsuario(index) {
    const usuarios = getUsers();
    usuarios.splice(index, 1);
    localStorage.setItem("users", JSON.stringify(usuarios));
    renderizarUsuarios();
}

// ** Gestión de Peces **
function getPeces() {
    const peces = JSON.parse(localStorage.getItem("peces")) || [];
    arbolPeces.root = null;
    peces.forEach((pez) => arbolPeces.insertar(pez));
    return arbolPeces.obtenerEnOrden();
}

function renderizarPeces() {
    const pecesLista = document.getElementById("peces-lista");
    const peces = getPeces();
    pecesLista.innerHTML = peces.map((pez, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${pez.nombre}</td>
            <td>${pez.disponibilidad}</td>
            <td>${pez.descripcion}</td>
            <td>
                <button onclick="editarPez(${index})">Editar</button>
                <button onclick="eliminarPez(${index})">Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function eliminarPez(index) {
    const peces = getPeces();
    peces.splice(index, 1);
    localStorage.setItem("peces", JSON.stringify(peces));
    renderizarPeces();
}

// Inicializar la Página
document.addEventListener("DOMContentLoaded", () => {
    renderizarReservas();
    renderizarUsuarios();
    renderizarPeces();
});
