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

// Verificar si se envió el formulario y evitar reenvíos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_bebida'])) {
    // Obtener datos del formulario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $cantidad_disponible = isset($_POST['cantidad_disponible']) ? intval($_POST['cantidad_disponible']) : 0;
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
    $imagen_url = isset($_POST['imagen_url']) ? trim($_POST['imagen_url']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

    // Validar que todos los campos estén completos
    if (empty($nombre) || $precio <= 0 || $cantidad_disponible <= 0 || empty($tipo) || empty($imagen_url) || empty($descripcion)) {
        echo "<script>alert('Por favor, complete todos los campos correctamente.');</script>";
        exit;
    }

    // Normalizar el nombre para la validación (minúsculas y quitar espacios extras)
    $nombre_normalizado = strtolower(preg_replace('/\s+/', '', $nombre));

    // Verificar si ya existe una bebida con el mismo nombre normalizado
    $check_query = "SELECT COUNT(*) as total FROM bebidas WHERE LOWER(REPLACE(nombre, ' ', '')) = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("s", $nombre_normalizado);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    if ($row['total'] > 0) {
        echo "<script>alert('Ya existe una bebida con este nombre o uno similar. Por favor, elija otro nombre.');</script>";
        exit;
    }

    $stmt_check->close();

    // Insertar datos en la base de datos
    $stmt = $conn->prepare("INSERT INTO bebidas (nombre, precio, cantidad_disponible, tipo_presentacion, imagen_url, descripcion, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sdisss", $nombre, $precio, $cantidad_disponible, $tipo, $imagen_url, $descripcion);

    if ($stmt->execute()) {
        echo "<script>alert('Bebida agregada correctamente.');</script>";
        // Redirigir para evitar el reenvío del formulario
        echo "<script>window.location.href = 'admin_inventario.php#restaurante';</script>";
        exit;
    } else {
        echo "<script>alert('Error al agregar la bebida. Por favor, intente nuevamente.');</script>";
    }

    $stmt->close();
}

// Cerrar conexión
$conn->close();
?>
