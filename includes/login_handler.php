<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Verificar si el correo existe en la base de datos
    $query = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['id_usuario'] = $user['id']; // Guarda el ID del usuario en la sesión
            $_SESSION['nombre'] = $user['nombre']; // Guarda el nombre del usuario en la sesión

            echo "<script>alert('Inicio de sesión exitoso.');</script>";
            echo "<script>window.location.href = '../usuarios/usuario_resumen.php';</script>"; // Redirige al resumen
        } else {
            echo "<script>alert('Contraseña incorrecta.');</script>";
            echo "<script>window.location.href = '../cliente.php';</script>";
        }
    } else {
        echo "<script>alert('El correo no está registrado.');</script>";
        echo "<script>window.location.href = '../cliente.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
