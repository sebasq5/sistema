<?php
// editar_pez.php

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['message'] = 'Por favor, inicie sesión como administrador.';
    header("Location: admin_login.php");
    exit;
}

// Verificar si se ha enviado el ID del pez
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID de pez no proporcionado.';
    header("Location: admin_inventario.php?section=peces");
    exit;
}

$pez_id = intval($_GET['id']);

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Obtener los datos del pez
$pez_query = "SELECT * FROM peces WHERE id = ?";
$stmt = $conn->prepare($pez_query);

if ($stmt) {
    $stmt->bind_param('i', $pez_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pez = $result->fetch_assoc();

    if (!$pez) {
        $_SESSION['error'] = 'Pez no encontrado.';
        header("Location: admin_inventario.php?section=peces");
        exit;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Error en la preparación de la consulta: ' . htmlspecialchars($conn->error);
    header("Location: admin_inventario.php?section=peces");
    exit;
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_pez'])) {
    $nombre     = trim($_POST['nombre']);
    $pecera     = trim($_POST['pecera']);
    $observable = trim($_POST['observable']);
    $oxigenado  = trim($_POST['oxigenado']);

    // Validar y sanitizar datos
    if (!empty($nombre) && !empty($pecera) && !empty($observable) && !empty($oxigenado)) {
        $update_query = "UPDATE peces SET nombre = ?, pecera = ?, observable = ?, oxigenado = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);

        if ($update_stmt) {
            $update_stmt->bind_param('ssssi', $nombre, $pecera, $observable, $oxigenado, $pez_id);
            if ($update_stmt->execute()) {
                $_SESSION['message'] = 'Pez actualizado correctamente.';
                header("Location: admin_inventario.php?section=peces");
                exit;
            } else {
                $_SESSION['error'] = 'Error al actualizar el pez: ' . htmlspecialchars($update_stmt->error);
            }
            $update_stmt->close();
        } else {
            $_SESSION['error'] = 'Error en la preparación de la consulta: ' . htmlspecialchars($conn->error);
        }
    } else {
        $_SESSION['error'] = 'Por favor, completa todos los campos correctamente.';
    }

    header("Location: admin_inventario.php?section=peces");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Pez</title>
    <link rel="stylesheet" href="../../css/style_inventario.css">
    <style>
        /* Estilos para la notificación */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50; /* Verde para éxito */
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .notification.error {
            background-color: #f44336; /* Rojo para errores */
        }

        .notification.hide {
            opacity: 0;
        }
    </style>
    <script>
        // Manejar la desaparición automática de notificaciones
        document.addEventListener('DOMContentLoaded', () => {
            const notification = document.getElementById('notification');
            if (notification) {
                setTimeout(() => {
                    notification.classList.add('hide');
                }, 3000); // 3 segundos
            }

            const notificationError = document.getElementById('notification-error');
            if (notificationError) {
                setTimeout(() => {
                    notificationError.classList.add('hide');
                }, 5000); // 5 segundos para errores
            }
        });
    </script>
</head>

<body>
    <div class="sidebar">
        <h2>AcuSystem</h2>
        <ul>
            <li><a href="admin_inventario.php?section=facturas">Facturas</a></li>
            <li><a href="admin_inventario.php?section=reservas">Reservas</a></li>
            <li><a href="admin_inventario.php?section=usuarios">Usuarios</a></li>
            <li><a href="admin_inventario.php?section=restaurante">Restaurante</a></li>
            <li><a href="admin_inventario.php?section=peces">Peces</a></li>
            <li>
                <form action="../../includes/logout_admin.php" method="post">
                    <button type="submit">Cerrar Sesión</button>
                </form>
            </li>
        </ul>
    </div>

    <div class="content">
        <?php
        // Mostrar mensajes de sesión como notificaciones
        if (isset($_SESSION['message'])) {
            echo '<div class="notification" id="notification">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="notification error" id="notification-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <h2>Editar Pez</h2>
        <form method="POST" action="">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($pez['nombre']); ?>" required>

            <label for="pecera">Pecera:</label>
            <select id="pecera" name="pecera" required>
                <option value="Limpio" <?php echo ($pez['pecera'] == 'Limpio') ? 'selected' : ''; ?>>Limpio</option>
                <option value="Sucio" <?php echo ($pez['pecera'] == 'Sucio') ? 'selected' : ''; ?>>Sucio</option>
            </select>

            <label for="observable">Observable:</label>
            <select id="observable" name="observable" required>
                <option value="Sí" <?php echo ($pez['observable'] == 'Sí') ? 'selected' : ''; ?>>Sí</option>
                <option value="No" <?php echo ($pez['observable'] == 'No') ? 'selected' : ''; ?>>No</option>
            </select>

            <label for="oxigenado">Oxigenado:</label>
            <select id="oxigenado" name="oxigenado" required>
                <option value="Sí" <?php echo ($pez['oxigenado'] == 'Sí') ? 'selected' : ''; ?>>Sí</option>
                <option value="No" <?php echo ($pez['oxigenado'] == 'No') ? 'selected' : ''; ?>>No</option>
            </select>

            <button type="submit" name="editar_pez">Actualizar Pez</button>
            <button type="button" onclick="window.location.href = 'admin_inventario.php?section=peces';">Cancelar</button>
        </form>
    </div>
</body>

</html>
