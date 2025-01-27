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

// Verificar si se envió el ID del pedido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Eliminar el pedido de la base de datos
    $query = "DELETE FROM pedidos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Pedido eliminado con éxito.');</script>";
    } else {
        echo "<script>alert('Error al eliminar el pedido.');</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('ID de pedido no válido.');</script>";
}

// Redirigir al panel administrativo
echo "<script>window.location.href = 'inventario.php#restaurante';</script>";
exit;
?>
