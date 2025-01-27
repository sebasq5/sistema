<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado"]);
    exit;
}

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Verificar si se recibió la solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $cantidad_disponible = $_POST['cantidad_disponible'];
    $imagen_url = $_POST['imagen_url'];
    $descripcion = $_POST['descripcion'];

    // Validar datos
    if (empty($nombre) || empty($precio) || empty($cantidad_disponible) || empty($imagen_url) || empty($descripcion)) {
        http_response_code(400);
        echo json_encode(["error" => "Todos los campos son obligatorios."]);
        exit;
    }

    // Insertar datos en la tabla 'comidas'
    $query = "INSERT INTO comidas (nombre, precio, cantidad_disponible, imagen_url, descripcion, fecha_actualizacion) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdiss", $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Comida agregada exitosamente."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al agregar la comida."]);
    }

    $stmt->close();
}

$conn->close();
?>
