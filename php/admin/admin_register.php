<?php
// Conectar con la base de datos
require '../../includes/db_connect.php';

// Manejar registro de nuevo administrador
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $query = "INSERT INTO administradores (nombre, correo, direccion, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $nombre, $correo, $direccion, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Administrador registrado exitosamente.');</script>";
        header("Location: admin_login.php");
    } else {
        echo "<script>alert('Error al registrar el administrador.');</script>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Administrador</title>
    <link rel="stylesheet" href="../../css/style_log.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Registrar Administrador</h1>
            <form method="POST" action="admin_register.php">
                <div class="input-group">
                    <input type="text" name="nombre" placeholder="Nombre" required>
                </div>
                <div class="input-group">
                    <input type="email" name="correo" placeholder="Correo" required>
                </div>
                <div class="input-group">
                    <input type="text" name="direccion" placeholder="Dirección" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn">Registrar</button>
            </form>
        </div>
        <div class="image-box">
            <img src="../../assets/logo.ico" alt="Logo del Sistema">
        </div>
    </div>
</body>
</html>
