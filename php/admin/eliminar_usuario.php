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

// Verificar si se ha proporcionado un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Convertir a entero para mayor seguridad

    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario eliminado correctamente.');</script>";
    } else {
        echo "<script>alert('Error al eliminar el usuario: " . $stmt->error . "');</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('ID de usuario no válido.');</script>";
}

$conn->close();
echo "<script>window.location.href = 'admin_inventario.php';</script>";
