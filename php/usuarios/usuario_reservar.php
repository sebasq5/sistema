<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Por favor, inicie sesión primero.');</script>";
    echo "<script>window.location.href = 'cliente.php';</script>";
    exit;
}

// Conectar con la base de datos
require '../../includes/db_connect.php';

// Manejar el formulario de reserva
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['user_id'];
    $actividad = trim($_POST['actividad']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $personas = intval($_POST['personas']);

    // Validar los campos básicos
    if (empty($actividad) || empty($fecha) || empty($hora) || empty($personas)) {
        echo "<script>alert('Por favor, complete todos los campos.');</script>";
    } elseif ($personas <= 0) {
        echo "<script>alert('La cantidad de personas debe ser mayor a 0.');</script>";
    } else {
        $errors = [];

        // 1. Validar que la fecha no exceda los 6 meses a partir de hoy
        $fecha_reserva = DateTime::createFromFormat('Y-m-d', $fecha);
        $hoy = new DateTime();
        $seis_meses = new DateTime();
        $seis_meses->modify('+6 months');

        if (!$fecha_reserva) {
            $errors[] = 'Formato de fecha inválido.';
        } elseif ($fecha_reserva < $hoy) {
            $errors[] = 'La fecha de reserva no puede ser en el pasado.';
        } elseif ($fecha_reserva > $seis_meses) {
            $errors[] = 'Las reservas solo se pueden hacer hasta 6 meses en el futuro.';
        }

        // 2. Prevenir reservas múltiples en la misma fecha por el mismo usuario
        if (empty($errors)) {
            $check_user_query = "SELECT COUNT(*) as count FROM reservas WHERE id_usuario = ? AND fecha = ?";
            $stmt = $conn->prepare($check_user_query);
            $stmt->bind_param("is", $id_usuario, $fecha);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $stmt->close();

            if ($count > 0) {
                $errors[] = 'Ya tienes una reserva en esta fecha.';
            }
        }

        // 3. Validar disponibilidad del Acuario
        if (empty($errors) && $actividad == 'Acuario') {
            // Calcular el total de peces y cuántos no están oxigenados o no son observables
            $peces_query = "SELECT COUNT(*) as total, SUM(CASE WHEN oxigenado = 'No' OR observable = 'No' THEN 1 ELSE 0 END) as problemáticos FROM peces";
            $peces_result = $conn->query($peces_query);

            if ($peces_result) {
                $peces_data = $peces_result->fetch_assoc();
                $total_peces = $peces_data['total'];
                $problematicos = $peces_data['problemáticos'];
                $porcentaje = ($total_peces > 0) ? ($problematicos / $total_peces) * 100 : 0;

                if ($porcentaje >= 35) {
                    $errors[] = 'No se puede reservar el Acuario en este momento debido a la baja calidad del agua.';
                }
            } else {
                $errors[] = 'Error al verificar la disponibilidad del Acuario.';
            }
        }

        // 4. Control de capacidad y horarios para actividades deportivas
        if (empty($errors) && ($actividad == 'Fútbol' || $actividad == 'Vóley')) {
            // Verificar si ya existe una reserva para la misma actividad, fecha y hora
            $check_activity_query = "SELECT COUNT(*) as count FROM reservas WHERE actividad = ? AND fecha = ? AND hora = ?";
            $stmt = $conn->prepare($check_activity_query);
            $stmt->bind_param("sss", $actividad, $fecha, $hora);
            $stmt->execute();
            $result = $stmt->get_result();
            $count_activity = $result->fetch_assoc()['count'];
            $stmt->close();

            if ($count_activity > 0) {
                $errors[] = "Ya existe una reserva para $actividad en esta fecha y hora.";
            }
        }

        // 5. Control de capacidad para Piscina
        if (empty($errors) && $actividad == 'Piscina') {
            // Calcular la cantidad actual de personas reservadas en Piscina para la misma fecha y hora
            $capacidad = 45;
            $check_pool_query = "SELECT SUM(personas) as total_personas FROM reservas WHERE actividad = 'Piscina' AND fecha = ? AND hora = ?";
            $stmt = $conn->prepare($check_pool_query);
            $stmt->bind_param("ss", $fecha, $hora);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_personas = $result->fetch_assoc()['total_personas'];
            $total_personas = $total_personas ? intval($total_personas) : 0;
            $stmt->close();

            if (($total_personas + $personas) > $capacidad) {
                $errors[] = "La Piscina ya ha alcanzado su capacidad máxima de $capacidad personas en esta fecha y hora.";
            }
        }

        // 6. Para Piscina, permitir múltiples reservas pero con límite de personas
        // Esto ya está manejado en el paso anterior

        // 7. Verificar si hay reservas en la misma fecha y hora en otras actividades
        // No es necesario a menos hay restricciones específicas

        // Si hay errores, mostrarlos
        if (!empty($errors)) {
            $mensaje = implode("\\n", $errors);
            echo "<script>alert('$mensaje');</script>";
        } else {
            // Insertar la reserva en la base de datos
            $insert_query = "INSERT INTO reservas (id_usuario, actividad, fecha, hora, personas) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            if ($stmt) {
                $stmt->bind_param("isssi", $id_usuario, $actividad, $fecha, $hora, $personas);
                if ($stmt->execute()) {
                    echo "<script>alert('Reserva realizada con éxito.');</script>";
                    echo "<script>window.location.href = 'usuario_resumen.php';</script>";
                } else {
                    echo "<script>alert('Error al realizar la reserva. Inténtelo nuevamente.');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Error en la preparación de la consulta.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Lugares</title>
    <link rel="stylesheet" href="../../css/reservar_lugares.css">
    <script defer src="../../js/menu.js"></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../../assets/logo.ico" alt="Logo AcuSystem">
            <span>AcuSystem</span>
        </div>
        <nav>
            <ul class="menu">
                <li><a href="usuario_reservar.php">Reservar</a></li>
                <li><a href="usuario_restaurante.php">Restaurante</a></li>
                <li><a href="usuario_resumen.php">Resumen</a></li>
                <li><a href="usuario_contacto.php">Contacto</a></li>
                <li><a href="../../includes/logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <div class="image-box">
                <img src="../../assets/fondo.jpg" alt="Reservar Lugares" class="image-box-img">
            </div>
            <div class="form-box">
                <h1>Reservar <span class="highlight">LUGARES</span></h1>
                <form method="POST" action="usuario_reservar.php">
                    <div class="form-group">
                        <label for="actividad">Actividad:</label>
                        <select id="actividad" name="actividad" required>
                            <option value="Fútbol">Fútbol</option>
                            <option value="Vóley">Vóley</option>
                            <option value="Piscina">Piscina</option>
                            <option value="Acuario">Acuario</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha:</label>
                        <input type="date" id="fecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="hora">Hora:</label>
                        <input type="time" id="hora" name="hora" required>
                    </div>
                    <div class="form-group">
                        <label for="personas">Cantidad de Personas:</label>
                        <input type="number" id="personas" name="personas" min="1" required>
                    </div>
                    <button type="submit" class="btn">Reservar</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
