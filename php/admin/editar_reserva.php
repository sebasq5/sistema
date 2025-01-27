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

// Inicializar la variable $reserva
$reserva = null;

// Verificar si se ha enviado un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Convertir a entero para mayor seguridad

    // Obtener los datos de la reserva
    $query = "SELECT * FROM reservas WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reserva = $result->fetch_assoc(); // Cargar los datos en $reserva
    } else {
        echo "<script>alert('Reserva no encontrada.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php';</script>";
        exit;
    }

    $stmt->close();
} else {
    echo "<script>alert('ID de reserva no válido.');</script>";
    echo "<script>window.location.href = 'admin_inventario.php';</script>";
    exit;
}

// Guardar cambios en la reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actividad = $_POST['actividad'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $personas = intval($_POST['personas']);

    $updateQuery = "UPDATE reservas SET actividad = ?, fecha = ?, hora = ?, personas = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('sssii', $actividad, $fecha, $hora, $personas, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Reserva actualizada correctamente.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar la reserva: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reserva</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('../../assets/fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            width: 400px;
            text-align: center;
            animation: fadeInUp 1s ease-in-out;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-container label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #555;
        }

        .form-container input[type="text"],
        .form-container input[type="date"],
        .form-container input[type="time"],
        .form-container input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-container button {
            background-color: #6b3a1e;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .form-container button:hover {
            background-color: #4e2c15;
            transform: scale(1.05);
        }

        .form-container a {
            text-decoration: none;
            color: #6b3a1e;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
        }

        .form-container a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Editar Reserva</h2>
        <form method="POST">
            <label>Actividad:</label>
            <input type="text" name="actividad" value="<?php echo htmlspecialchars($reserva['actividad'] ?? ''); ?>" required>
            <label>Fecha:</label>
            <input type="date" name="fecha" value="<?php echo htmlspecialchars($reserva['fecha'] ?? ''); ?>" required>
            <label>Hora:</label>
            <input type="time" name="hora" value="<?php echo htmlspecialchars($reserva['hora'] ?? ''); ?>" required>
            <label>Personas:</label>
            <input type="number" name="personas" value="<?php echo htmlspecialchars($reserva['personas'] ?? ''); ?>" required>
            <button type="submit">Guardar Cambios</button>
            <a href="admin_inventario.php">Cancelar</a>
        </form>
    </div>
</body>
</html>
