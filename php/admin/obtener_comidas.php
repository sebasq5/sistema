<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403); // Código de error de acceso denegado
    echo json_encode(["error" => "Acceso denegado"]);
    exit;
}

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Obtener datos de la tabla 'comidas'
$query = "SELECT * FROM comidas";
$result = $conn->query($query);

$comidas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $comidas[] = $row;
    }
}

// Devolver datos en formato JSON
header('Content-Type: application/json');
echo json_encode($comidas);

$conn->close();
?>
