(function () {
    // Simulación de base de datos con JSON
    let users = JSON.parse(localStorage.getItem("users")) || [];

    // Registro de usuario
    document.getElementById("register-form")?.addEventListener("submit", function (e) {
        e.preventDefault();
        const username = document.getElementById("register-username").value;
        const email = document.getElementById("register-email").value;
        const password = document.getElementById("register-password").value;
        const passwordConfirm = document.getElementById("register-password-confirm").value;

        if (password !== passwordConfirm) {
            alert("Las contraseñas no coinciden.");
            return;
        }

        if (users.some(user => user.username === username)) {
            alert("El usuario ya existe. Elige otro nombre.");
            return;
        }

        users.push({ username, email, password });
        localStorage.setItem("users", JSON.stringify(users));
        alert("Usuario registrado correctamente.");
        window.location.href = "../usuarios/cliente.html";
    });

    // Inicio de sesión
    document.getElementById("login-form")?.addEventListener("submit", function (e) {
        e.preventDefault();
        const username = document.getElementById("login-username").value;
        const password = document.getElementById("login-password").value;

        const user = users.find(user => user.username === username && user.password === password);
        if (!user) {
            alert("Usuario o contraseña incorrectos.");
            return;
        }

        localStorage.setItem("activeUser", JSON.stringify(user));
        alert("Inicio de sesión exitoso.");
        window.location.href = "usuario_reservar.html";
    });

    // Validar sesión
    window.checkSession = function () {
        const activeUser = JSON.parse(localStorage.getItem("activeUser"));
        if (!activeUser) {
            alert("Por favor, inicia sesión para continuar.");
            window.location.href = "../usuarios/cliente.html";
        }
    };

    // Cerrar sesión
    window.logout = function () {
        localStorage.removeItem("activeUser");
        alert("Sesión cerrada exitosamente.");
        window.location.href = "../usuarios/cliente.html";
    };
})();

document.querySelector('form').addEventListener('submit', function(event) {
    const cedula = document.getElementById('cedula').value;

    // Validación de cédula
    if (!validarCedula(cedula)) {
        event.preventDefault();
        alert('La cédula ingresada no es válida.');
    }
});

function validarCedula(cedula) {
    if (cedula.length !== 10) return false;

    const digito_region = parseInt(cedula.substring(0, 2));
    if (digito_region < 1 || digito_region > 24) return false;

    const ultimo_digito = parseInt(cedula.substring(9, 10));
    let suma_pares = 0, suma_impares = 0;

    for (let i = 0; i < 9; i++) {
        let digito = parseInt(cedula.charAt(i));
        if (i % 2 === 0) {
            digito *= 2;
            if (digito > 9) digito -= 9;
            suma_impares += digito;
        } else {
            suma_pares += digito;
        }
    }

    const suma_total = suma_pares + suma_impares;
    const digito_validador = (Math.ceil(suma_total / 10) * 10) - suma_total;

    return digito_validador === ultimo_digito;
}

