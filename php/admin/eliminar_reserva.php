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

// Verificar si se ha enviado un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Convertir a entero para mayor seguridad
    echo "<script>console.log('ID recibido: $id');</script>"; // Depuración

    // Verificar si el ID existe en la base de datos
    $checkQuery = "SELECT * FROM reservas WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>console.log('Reserva encontrada. Procediendo a eliminar...');</script>"; // Depuración

        // Si existe, proceder a eliminar
        $deleteQuery = "DELETE FROM reservas WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo "<script>alert('Reserva eliminada correctamente.');</script>";
        } else {
            echo "<script>alert('Error al eliminar la reserva: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('La reserva con el ID especificado no existe.');</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('ID de reserva no válido o no enviado.');</script>";
}

$conn->close();
echo "<script>window.location.href = 'admin_inventario.php';</script>";
?>
