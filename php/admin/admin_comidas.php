<?php
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Por favor, inicie sesión como administrador.');</script>";
    echo "<script>window.location.href = 'admin_login.php';</script>";
    exit;
}

require '../../includes/db_connect.php';

// Manejar el formulario de añadir comida
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_food'])) {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $cantidad_disponible = $_POST['cantidad_disponible'];
    $imagen_url = $_POST['imagen_url'];
    $descripcion = $_POST['descripcion'];

    if (empty($nombre) || empty($precio) || empty($cantidad_disponible) || empty($imagen_url) || empty($descripcion)) {
        echo "<script>alert('Todos los campos son obligatorios.');</script>";
    } else {
        $query = "INSERT INTO comidas (nombre, precio, cantidad_disponible, imagen_url, descripcion, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdiss", $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion);

        if ($stmt->execute()) {
            echo "<script>alert('Comida añadida con éxito.');</script>";
        } else {
            echo "<script>alert('Error al añadir la comida.');</script>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Comidas</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>
<body>
    <div class="content">
        <h1>Añadir Nueva Comida</h1>
        <form method="POST" action="admin_comidas.php">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="precio">Precio:</label>
            <input type="number" step="0.01" id="precio" name="precio" required>

            <label for="cantidad_disponible">Cantidad Disponible:</label>
            <input type="number" id="cantidad_disponible" name="cantidad_disponible" required>

            <label for="imagen_url">URL de la Imagen:</label>
            <input type="url" id="imagen_url" name="imagen_url" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required></textarea>

            <button type="submit" name="add_food">Añadir Comida</button>
        </form>
    </div>
</body>
</html>
