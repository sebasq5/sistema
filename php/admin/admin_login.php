<?php
// Iniciar sesión
session_start();

// Verificar si el administrador ya ha iniciado sesión
if (isset($_SESSION['admin_id'])) {
    // Redirigir al panel del administrador si ya está autenticado
    header("Location: admin_inventario.php");
    exit;
}

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Manejar inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Verificar si el correo existe en la tabla de administradores
    $query = "SELECT * FROM administradores WHERE correo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];

            // Redirigir al panel del administrador
            header("Location: admin_inventario.php");
            exit;
        } else {
            echo "<script>alert('Contraseña incorrecta.');</script>";
        }
    } else {
        echo "<script>alert('El correo no está registrado como administrador.');</script>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Administrador</title>
    <link rel="stylesheet" href="../../css/style_log.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Iniciar Sesión</h1>
            <p>Panel de Administración</p>
            <form method="POST" action="admin_login.php">
                <div class="input-group">
                    <input type="email" name="correo" placeholder="Correo" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn">Iniciar sesión</button>
            </form>
        </div>
        <div class="image-box">
            <img src="../../assets/logo.ico" alt="Logo del Sistema">
        </div>
    </div>
</body>
</html>
