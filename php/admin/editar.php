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

// Lista de tablas permitidas
$tablas_permitidas = ['usuarios', 'reservas', 'comidas', 'bebidas', 'postres', 'pedidos', 'clientes'];

if (isset($_GET['tabla']) && isset($_GET['id'])) {
    $tabla = $_GET['tabla'];
    $id = intval($_GET['id']); // Convertir a número entero para mayor seguridad

    // Validar que la tabla sea permitida
    if (!in_array($tabla, $tablas_permitidas)) {
        echo "<script>alert('Tabla no permitida.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php';</script>";
        exit;
    }

    // Preparar la consulta de eliminación
    $deleteQuery = "DELETE FROM $tabla WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $id);

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        echo "<script>alert('Registro eliminado correctamente.');</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro: " . $stmt->error . "');</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Parámetros no válidos.');</script>";
}

$conn->close();
echo "<script>window.location.href = 'admin_inventario.php';</script>";
?>
