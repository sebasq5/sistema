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

// Verificar si se recibió un ID válido
if ((isset($_GET['id']) && is_numeric($_GET['id'])) || (isset($_POST['id']) && is_numeric($_POST['id']))) {
    $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];

    // Preparar la consulta para eliminar la bebida
    $stmt = $conn->prepare("DELETE FROM bebidas WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Bebida eliminada con éxito.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php#restaurante';</script>";
    } else {
        echo "<script>alert('Error al eliminar la bebida.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php#restaurante';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('ID de bebida no válido.');</script>";
    echo "<script>window.location.href = 'admin_inventario.php#restaurante';</script>";
}

// Cerrar conexión
$conn->close();
?>
