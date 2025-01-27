<?php
// eliminar_pez.php

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['message'] = 'Por favor, inicie sesión como administrador.';
    header("Location: admin_login.php");
    exit;
}

// Verificar si se ha enviado el ID del pez
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $pez_id = intval($_POST['id']);

    // Conectar con la base de datos
    require '../../includes/db_connect.php';

    // Preparar la consulta para eliminar el pez
    $delete_query = "DELETE FROM peces WHERE id = ?";
    $stmt = $conn->prepare($delete_query);

    if ($stmt) {
        $stmt->bind_param('i', $pez_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Pez eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al eliminar el pez: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Error en la preparación de la consulta: ' . htmlspecialchars($conn->error);
    }

    header("Location: admin_inventario.php?section=peces");
    exit;
} else {
    // Si no se ha enviado el ID, redirigir al inventario
    header("Location: admin_inventario.php?section=peces");
    exit;
}
?>
