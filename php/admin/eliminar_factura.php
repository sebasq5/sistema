<?php
// eliminar_factura.php

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Por favor, inicie sesión como administrador.');</script>";
    echo "<script>window.location.href = 'admin_login.php';</script>";
    exit;
}

// Verificar si se ha enviado el ID de la factura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $factura_id = intval($_POST['id']);

    // Conectar con la base de datos
    require '../../includes/db_connect.php';

    // Preparar la consulta para eliminar la factura
    $delete_query = "DELETE FROM facturas WHERE id = ?";
    $stmt = $conn->prepare($delete_query);

    if ($stmt) {
        $stmt->bind_param('i', $factura_id);
        if ($stmt->execute()) {
            echo "<script>alert('Factura eliminada correctamente.'); window.location.href = 'inventario.php#facturas';</script>";
        } else {
            echo "<script>alert('Error al eliminar la factura: " . htmlspecialchars($stmt->error) . "'); window.location.href = 'inventario.php#facturas';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error en la preparación de la consulta: " . htmlspecialchars($conn->error) . "'); window.location.href = 'inventario.php#facturas';</script>";
    }

    // Cerrar conexión
    $conn->close();
} else {
    // Si no se ha enviado el ID, redirigir al inventario
    echo "<script>window.location.href = 'inventario.php#facturas';</script>";
}
?>
