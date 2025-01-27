<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Por favor, inicie sesión como administrador.');</script>";
    echo "<script>window.location.href = 'admin_login.php';</script>";
    exit;
}

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Inicializar la variable $usuario
$usuario = null;

// Verificar si se ha enviado un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Convertir a entero para mayor seguridad

    // Obtener los datos del usuario
    $query = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc(); // Cargar los datos en $usuario
    } else {
        echo "<script>alert('Usuario no encontrado.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php';</script>";
        exit;
    }

    $stmt->close();
} else {
    echo "<script>alert('ID de usuario no válido.');</script>";
    echo "<script>window.location.href = 'admin_inventario.php';</script>";
    exit;
}

// Guardar cambios en el usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $telefono = $_POST['telefono'];

    $updateQuery = "UPDATE usuarios SET nombre = ?, correo = ?, cedula = ?, telefono = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ssssi', $nombre, $correo, $cedula, $telefono, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario actualizado correctamente.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el usuario: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
        /* Similar al diseño de editar reserva */
        body {
            font-family: Arial, sans-serif;
            background: url('../../assets/fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 20px;
            width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container button {
            background-color: #6b3a1e;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-container button:hover {
            background-color: #4e2c15;
        }

        .form-container a {
            text-decoration: none;
            color: #6b3a1e;
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Editar Usuario</h2>
        <form method="POST">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
            <label>Correo:</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo'] ?? ''); ?>" required>
            <label>Cédula:</label>
            <input type="text" name="cedula" value="<?php echo htmlspecialchars($usuario['cedula'] ?? ''); ?>" required>
            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" required>
            <button type="submit">Guardar Cambios</button>
            <a href="admin_inventario.php">Cancelar</a>
        </form>
    </div>
</body>
</html>
