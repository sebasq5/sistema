<?php
$servername = "localhost";  // Dirección del servidor
$username = "root";         // Usuario de MySQL
$password = "";             // Contraseña de MySQL (vacía en XAMPP)
$dbname = "acusystem";      // Nombre de la base de datos (cambiado a acusystem)

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
?>
