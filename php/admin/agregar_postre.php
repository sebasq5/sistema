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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $cantidad_disponible = isset($_POST['cantidad_disponible']) ? intval($_POST['cantidad_disponible']) : 0;
    $imagen_url = isset($_POST['imagen_url']) ? trim($_POST['imagen_url']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

    // Validar que todos los campos estén completos
    if (empty($nombre) || $precio <= 0 || $cantidad_disponible <= 0 || empty($imagen_url) || empty($descripcion)) {
        echo "<script>alert('Por favor, complete todos los campos correctamente.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    // Insertar datos en la base de datos
    $stmt = $conn->prepare("INSERT INTO postres (nombre, precio, cantidad_disponible, imagen_url, descripcion, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sdiss", $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion);


    if ($stmt->execute()) {
        echo "<script>alert('Postre agregado con éxito.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php#restaurante';</script>";
    } else {
        echo "<script>alert('Error al agregar el postre. Por favor, intente nuevamente.');</script>";
        echo "<script>window.history.back();</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Acceso no autorizado.');</script>";
    echo "<script>window.location.href = 'admin_inventario.php#restaurante';</script>";
    exit;
}

// Cerrar conexión
$conn->close();
?>
