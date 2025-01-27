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

// Verificar si se ha enviado un ID y una tabla
if (isset($_GET['tabla']) && isset($_GET['id'])) {
    $tabla = $_GET['tabla'];
    $id = intval($_GET['id']);
    $deleteQuery = "DELETE FROM $tabla WHERE id = $id";

    if ($conn->query($deleteQuery)) {
        echo "<script>alert('Registro eliminado correctamente.');</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro.');</script>";
    }
}

echo "<script>window.location.href = 'admin_inventario.php';</script>";
?>
