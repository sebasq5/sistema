<?php
// editar_factura.php

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Por favor, inicie sesión como administrador.');</script>";
    echo "<script>window.location.href = 'admin_login.php';</script>";
    exit;
}

// Verificar si se ha enviado el ID de la factura
if (!isset($_GET['id'])) {
    echo "<script>alert('ID de factura no proporcionado.'); window.location.href = 'inventario.php#facturas';</script>";
    exit;
}

$factura_id = intval($_GET['id']);

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Obtener los datos de la factura
$factura_query = "SELECT * FROM facturas WHERE id = ?";
$stmt = $conn->prepare($factura_query);

if ($stmt) {
    $stmt->bind_param('i', $factura_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $factura = $result->fetch_assoc();

    if (!$factura) {
        echo "<script>alert('Factura no encontrada.'); window.location.href = 'inventario.php#facturas';</script>";
        exit;
    }

    $stmt->close();
} else {
    echo "<script>alert('Error en la preparación de la consulta: " . htmlspecialchars($conn->error) . "'); window.location.href = 'inventario.php#facturas';</script>";
    exit;
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_factura'])) {
    $fecha = $_POST['fecha'];
    $total = floatval($_POST['total']);
    $id_cliente = intval($_POST['id_cliente']);
    $drive_file_id = trim($_POST['drive_file_id']);

    // Validar los datos
    if (!empty($fecha) && $total >= 0 && $id_cliente > 0 && !empty($drive_file_id)) {
        $update_query = "UPDATE facturas SET fecha = ?, total = ?, id_cliente = ?, drive_file_id = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);

        if ($update_stmt) {
            $update_stmt->bind_param('sdisi', $fecha, $total, $id_cliente, $drive_file_id, $factura_id);
            if ($update_stmt->execute()) {
                echo "<script>alert('Factura actualizada correctamente.'); window.location.href = 'inventario.php#facturas';</script>";
            } else {
                echo "<script>alert('Error al actualizar la factura: " . htmlspecialchars($update_stmt->error) . "');</script>";
            }
            $update_stmt->close();
        } else {
            echo "<script>alert('Error en la preparación de la consulta: " . htmlspecialchars($conn->error) . "');</script>";
        }
    } else {
        echo "<script>alert('Por favor, completa todos los campos correctamente.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Factura</title>
    <link rel="stylesheet" href="../../css/style_inventario.css">
</head>

<body>
    <div class="content">
        <h2>Editar Factura</h2>
        <form method="POST" action="">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($factura['fecha']); ?>" required>

            <label for="total">Total:</label>
            <input type="number" step="0.01" id="total" name="total" value="<?php echo htmlspecialchars($factura['total']); ?>" required>

            <label for="id_cliente">ID Cliente:</label>
            <input type="number" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($factura['id_cliente']); ?>" required>

            <label for="drive_file_id">Drive File ID:</label>
            <input type="text" id="drive_file_id" name="drive_file_id" value="<?php echo htmlspecialchars($factura['drive_file_id']); ?>" required>

            <button type="submit" name="editar_factura">Actualizar Factura</button>
        </form>
        <button onclick="window.location.href = 'inventario.php#facturas';">Cancelar</button>
    </div>
</body>

</html>
