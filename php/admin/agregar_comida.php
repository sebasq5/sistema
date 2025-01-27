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

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $cantidad_disponible = intval($_POST['cantidad_disponible']);
    $imagen_url = trim($_POST['imagen_url']);
    $descripcion = trim($_POST['descripcion']);

    // Validar los datos
    if (empty($nombre) || empty($precio) || empty($cantidad_disponible) || empty($imagen_url) || empty($descripcion)) {
        echo "<script>alert('Por favor, complete todos los campos.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    // Normalizar el nombre para la validación
    $nombre_normalizado = strtolower(preg_replace('/\s+/', '', $nombre)); // Quitar espacios y convertir a minúsculas

    // Verificar si el nombre ya existe en la base de datos
    $check_query = "SELECT * FROM comidas WHERE LOWER(REPLACE(nombre, ' ', '')) = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("s", $nombre_normalizado);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Si ya existe una comida con el mismo nombre
        echo "<script>alert('Ya existe una comida con este nombre o uno similar. Por favor, elija otro nombre.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    $stmt_check->close();

    // Insertar la comida en la base de datos con el formato original
    $query = "INSERT INTO comidas (nombre, precio, cantidad_disponible, imagen_url, descripcion, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdiss", $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion);

    if ($stmt->execute()) {
        echo "<script>alert('Comida agregada con éxito.');</script>";
    } else {
        echo "<script>alert('Error al agregar la comida.');</script>";
    }

    $stmt->close();

    // Redirigir de vuelta al dashboard
    echo "<script>window.location.href = 'admin_inventario.php#restaurante';</script>";
    exit;
}
?>
