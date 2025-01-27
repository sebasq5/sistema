// Validar credenciales del administrador
function validarAdmin() {
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    if (username === "admin" && password === "admin") {
        // Redirigir al panel administrativo
        window.location.href = "../admin/index_admin.html";
    } else {
        // Mostrar mensaje de error
        alert("Usuario o contraseña incorrectos");
    }
}

