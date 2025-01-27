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

// Verificar si se ha proporcionado un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Obtener los datos actuales del postre
    $query = "SELECT * FROM postres WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $postre = $result->fetch_assoc();
    } else {
        echo "<script>alert('Postre no encontrado.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('ID de postre no válido.');</script>";
    echo "<script>window.location.href = 'admin_inventario.php';</script>";
    exit;
}

// Procesar la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $cantidad_disponible = $_POST['cantidad_disponible'];
    $imagen_url = $_POST['imagen_url'];
    $descripcion = $_POST['descripcion'];

    $updateQuery = "UPDATE postres SET nombre = ?, precio = ?, cantidad_disponible = ?, imagen_url = ?, descripcion = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('sdissi', $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Postre actualizado correctamente.');</script>";
        echo "<script>window.location.href = 'admin_inventario.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el postre: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Postre</title>
    <style>
        body {
            background: url('../../assets/fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        .container h2 {
            text-align: center;
            color: #6b3a1e;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: none;
        }

        button {
            background-color: #6b3a1e;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: #4e2c15;
        }

        .cancelar {
            display: block;
            text-align: center;
            margin-top: 10px;
            text-decoration: none;
            color: #6b3a1e;
            font-weight: bold;
        }

        .cancelar:hover {
            color: #4e2c15;
        }

        /* Animaciones */
        .container {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Postre</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($postre['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label for="precio">Precio:</label>
                <input type="number" step="0.01" id="precio" name="precio" value="<?php echo htmlspecialchars($postre['precio']); ?>" required>
            </div>
            <div class="form-group">
                <label for="cantidad_disponible">Cantidad Disponible:</label>
                <input type="number" id="cantidad_disponible" name="cantidad_disponible" value="<?php echo htmlspecialchars($postre['cantidad_disponible']); ?>" required>
            </div>
            <div class="form-group">
                <label for="imagen_url">Imagen URL:</label>
                <input type="text" id="imagen_url" name="imagen_url" value="<?php echo htmlspecialchars($postre['imagen_url']); ?>" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($postre['descripcion']); ?></textarea>
            </div>
            <button type="submit">Guardar Cambios</button>
            <a href="admin_inventario.php" class="cancelar">Cancelar</a>
        </form>
    </div>
</body>
</html>
