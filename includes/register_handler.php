<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y limpiar los datos del formulario
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Encripta la contraseña
    $cedula = trim($_POST['cedula']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    // Validaciones adicionales para asegurar la entrada de datos
    if (empty($nombre) || empty($correo) || empty($password) || empty($cedula) || empty($telefono) || empty($direccion)) {
        echo "<script>alert('Por favor, complete todos los campos.'); window.history.back();</script>";
        exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Ingrese un correo electrónico válido.'); window.history.back();</script>";
        exit();
    }

    if (!preg_match('/^\d{10}$/', $cedula)) {
        echo "<script>alert('La cédula debe contener exactamente 10 dígitos numéricos.'); window.history.back();</script>";
        exit();
    }

    if (!preg_match('/^\d{10}$/', $telefono)) {
        echo "<script>alert('El teléfono debe contener exactamente 10 dígitos numéricos.'); window.history.back();</script>";
        exit();
    }

    // Verificar si el correo, la cédula o el teléfono ya están registrados
    $query = "SELECT correo, cedula, telefono FROM usuarios WHERE correo = ? OR cedula = ? OR telefono = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    $stmt->bind_param("sss", $correo, $cedula, $telefono);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existingUser = $result->fetch_assoc();
        if ($existingUser['correo'] === $correo) {
            echo "<script>alert('El correo ya está registrado. Por favor, ingrese otro correo.'); window.history.back();</script>";
        } elseif ($existingUser['cedula'] === $cedula) {
            echo "<script>alert('Ya existe un usuario con esta cédula.'); window.history.back();</script>";
        } elseif ($existingUser['telefono'] === $telefono) {
            echo "<script>alert('Ya existe un usuario con este número de teléfono.'); window.history.back();</script>";
        }
        exit();
    }

    $stmt->close();

    // Insertar el nuevo usuario en la base de datos
    $queryUsuario = "INSERT INTO usuarios (nombre, correo, password, cedula, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtUsuario = $conn->prepare($queryUsuario);
    if (!$stmtUsuario) {
        die("Error en la preparación de la consulta de usuario: " . $conn->error);
    }
    $stmtUsuario->bind_param("ssssss", $nombre, $correo, $password, $cedula, $telefono, $direccion);

    if ($stmtUsuario->execute()) {
        // Obtener el ID del usuario recién insertado
        $usuario_id = $stmtUsuario->insert_id;

        // Insertar el usuario en la tabla clientes con datos básicos
        $queryCliente = "INSERT INTO clientes (usuario_id, nombre, ciudad, telefono, direccion) VALUES (?, ?, '', ?, ?)";
        $stmtCliente = $conn->prepare($queryCliente);
        if (!$stmtCliente) {
            die("Error en la preparación de la consulta de cliente: " . $conn->error);
        }
        $stmtCliente->bind_param("isss", $usuario_id, $nombre, $telefono, $direccion);

        if ($stmtCliente->execute()) {
            echo "<script>alert('Registro exitoso. ¡Inicia sesión para continuar!'); window.location.href = '../php/usuarios/cliente.php';</script>";
        } else {
            echo "<script>alert('Error al registrar el cliente. Inténtelo nuevamente.'); window.history.back();</script>";
        }

        $stmtCliente->close();
    } else {
        echo "<script>alert('Error al registrar usuario. Inténtelo nuevamente.'); window.history.back();</script>";
    }

    $stmtUsuario->close();
    $conn->close();
}
?>
