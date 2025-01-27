<?php
session_start();
session_destroy(); // Elimina todas las variables de sesión
echo "<script>alert('Sesión cerrada exitosamente.');</script>";
echo "<script>window.location.href = '../php/usuarios/cliente.php';</script>";
?>
