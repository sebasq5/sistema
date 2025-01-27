<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ya ha iniciado sesión
if (isset($_SESSION['user_id'])) {
    // Redirigir al usuario a usuario_reservar.php si la sesión está activa
    header("Location: usuario_reservar.php");
    exit; // Detener la ejecución para evitar que se cargue el resto del archivo
}

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Manejar inicio de sesión
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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];

            // Redirigir a usuario_reservar.php
            echo "<script>alert('Inicio de sesión exitoso. Bienvenido, " . htmlspecialchars($user['nombre']) . "!');</script>";
            echo "<script>window.location.href = 'usuario_reservar.php';</script>";
            exit; // Detener la ejecución aquí
        } else {
            echo "<script>alert('Contraseña incorrecta.');</script>";
        }
    } else {
        echo "<script>alert('El correo no está registrado.');</script>";
    }

    $stmt->close();
}

// Si no hay sesión activa, mostrar el formulario de inicio de sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../../css/style_log.css">
    <link rel="icon" type="image/x-icon" href="../../assets/ico.jpg">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Vamos a iniciar sesión</h1>
            <p>Bienvenido a nuestra página <a href="register.php">Regístrate</a></p>
            <form method="POST" action="cliente.php">
                <div class="input-group">
                    <input type="email" name="correo" placeholder="Correo" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="keep-logged-in">
                    <label for="keep-logged-in">Mantenerme conectado</label>
                </div>
                <button type="submit" class="btn">Iniciar sesión</button>
            </form>
        </div>
        <div class="image-box">
            <img src="../../assets/logo.ico" alt="Snake illustration">
        </div>
    </div>
</body>
</html>
