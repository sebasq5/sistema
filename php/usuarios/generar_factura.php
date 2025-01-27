<?php
session_start();
require '../../includes/db_connect.php';
require '../../vendor/autoload.php';
require '../../fpdf/fpdf.php';

use Google\Client;
use Google\Service\Drive;

// Configuración de logs de errores (opcional)
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . '/error_log.txt');

/**
 * Función para validar Cédula
 * 
 * @param string $cedula
 * @return bool
 */
function validarCedula($cedula) {
    // Implementa aquí la lógica de validación adecuada según las reglas de tu país
    // Este es un ejemplo simplificado
    if (strlen($cedula) !== 10) {
        return false;
    }
    if (!ctype_digit($cedula)) {
        return false;
    }
    // Agrega más validaciones según sea necesario
    return true;
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Por favor, inicie sesión primero.'); window.location.href = 'login.php';</script>";
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Verificar si usuario_id está definido correctamente
if (empty($usuario_id)) {
    die("Error: usuario_id no está definido.");
}

// Asegurar que la conexión use UTF-8
if (!$conn->set_charset("utf8")) {
    error_log("Error al configurar el charset: " . $conn->error);
    die("Error en la configuración de la base de datos.");
}

// Consultar si el usuario ya tiene datos de facturación
$query = "SELECT direccion, telefono FROM datos_factura WHERE usuario_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Error en la preparación de la consulta de facturación: " . $conn->error);
    die("Error en la preparación de la consulta de facturación.");
}
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Inicializar valores de los campos
$direccion = $user_data['direccion'] ?? '';
$telefono = $user_data['telefono'] ?? '';

// Obtener el id_cliente basado en el usuario_id
$cliente_query = "SELECT usuario_id FROM clientes WHERE usuario_id = ?";
$cliente_stmt = $conn->prepare($cliente_query);
if (!$cliente_stmt) {
    die("Error en la preparación de la consulta de cliente: " . htmlspecialchars($conn->error));
}
$cliente_stmt->bind_param("i", $usuario_id);
$cliente_stmt->execute();
$cliente_result = $cliente_stmt->get_result();
$cliente_data = $cliente_result->fetch_assoc();
$cliente_stmt->close();

if (!$cliente_data) {
    die("Error: No se encontró un cliente asociado a este usuario. Por favor, registre la información del cliente.");
}

$id_cliente = $cliente_data['usuario_id'];

// Procesar la solicitud cuando se envíe el formulario de facturación
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($user_data)) {
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';

    // Validar los campos obligatorios
    if (empty($direccion) || empty($telefono)) {
        echo "<script>alert('Por favor, complete todos los campos correctamente.'); window.location.href='generar_factura.php';</script>";
        exit;
    }

    if (!preg_match('/^09\d{8}$/', $telefono)) {
        echo "<script>alert('El teléfono debe tener 10 dígitos y comenzar con 09.'); window.location.href='generar_factura.php';</script>";
        exit;
    }

    // Validar la cédula del usuario
    $cedula_query = "SELECT cedula FROM usuarios WHERE id = ?";
    $cedula_stmt = $conn->prepare($cedula_query);
    if (!$cedula_stmt) {
        die("Error en la preparación de la consulta de cédula: " . $conn->error);
    }
    $cedula_stmt->bind_param("i", $usuario_id);
    $cedula_stmt->execute();
    $cedula_result = $cedula_stmt->get_result();
    $cedula_data = $cedula_result->fetch_assoc();
    $cedula_stmt->close();

    $cedula = $cedula_data['cedula'] ?? '';

    if (!validarCedula($cedula)) {
        echo "<script>alert('La cédula ingresada no es válida.'); window.location.href='generar_factura.php';</script>";
        exit;
    }

    // Insertar o actualizar los datos de facturación
    $query = "INSERT INTO datos_factura (usuario_id, direccion, telefono, fecha) 
              VALUES (?, ?, ?, NOW()) 
              ON DUPLICATE KEY UPDATE 
              direccion = VALUES(direccion), 
              telefono = VALUES(telefono), 
              fecha = NOW()";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("iss", $usuario_id, $direccion, $telefono);
        if ($stmt->execute()) {
            echo "<script>alert('Datos guardados correctamente.'); window.location.href='generar_factura.php';</script>";
            exit;
        } else {
            error_log("Error en ejecución de inserción de facturación: " . $stmt->error);
            echo "<script>alert('Error al guardar los datos de facturación.'); window.history.back();</script>";
        }
        $stmt->close();
    } else {
        error_log("Error en la preparación de inserción de facturación: " . $conn->error);
        echo "<script>alert('Error en la preparación de la consulta de facturación.'); window.history.back();</script>";
    }
}

// Si no existen datos de facturación, mostrar formulario para ingresarlos
if (empty($user_data)) {
?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Datos de Facturación</title>
        <style>
            /* Estilos básicos para el formulario */
            body {
                font-family: Arial, sans-serif;
                background-color: #f2f2f2;
            }
            .container {
                width: 400px;
                padding: 20px;
                margin: auto;
                background-color: white;
                margin-top: 100px;
                border-radius: 10px;
                box-shadow: 0px 0px 10px 0px #0000001a;
            }
            input[type=text] {
                width: 100%;
                padding: 12px;
                margin: 8px 0 20px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            button {
                background-color: rgb(155, 96, 29);
                color: white;
                padding: 14px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                width: 100%;
            }
            button:hover {
                background-color: rgb(135, 86, 19);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Datos de Facturación</h2>
            <form method="POST">
                <label for="direccion">Direccion:</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($direccion); ?>" required>
                
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>
                
                <button type="submit">Guardar Datos</button>
            </form>
        </div>
    </body>
    </html>
<?php
    exit;
}

// Continuar con la generación de la factura

// Obtener pedidos del usuario
$pedidos_query = "SELECT plato, cantidad, fecha_pedido, 5.00 AS precio_unitario, cantidad * 5.00 AS subtotal FROM pedidos WHERE usuario_id = ?";
$pedidos_stmt = $conn->prepare($pedidos_query);
if (!$pedidos_stmt) {
    error_log("Error en la preparación de la consulta de pedidos: " . $conn->error);
    die("Error en la preparación de la consulta de pedidos.");
}
$pedidos_stmt->bind_param("i", $usuario_id);
$pedidos_stmt->execute();
$pedidos_result = $pedidos_stmt->get_result();
$pedidos_stmt->close();

// Obtener reservas del usuario
$reservas_query = "SELECT actividad, fecha, hora, personas FROM reservas WHERE id_usuario = ?";
$reservas_stmt = $conn->prepare($reservas_query);
if (!$reservas_stmt) {
    error_log("Error en la preparación de la consulta de reservas: " . $conn->error);
    die("Error en la preparación de la consulta de reservas.");
}
$reservas_stmt->bind_param("i", $usuario_id);
$reservas_stmt->execute();
$reservas_result = $reservas_stmt->get_result();
$reservas_stmt->close();

// Definir precios por actividad
$precios_reservas = [
    'Fútbol' => 0.35,
    'Vóley' => 0.45,
    'Piscina' => 0.27,
    'Acuario' => 1.75
];

// Verificar que $user_data tenga las claves necesarias
if (!isset($user_data['direccion']) || !isset($user_data['telefono'])) {
    die("Error: Datos de facturación incompletos.");
}

// Generación de la factura PDF
$direccion = $user_data['direccion'];
$fecha_actual = date('Y-m-d');
$factura_nombre = "factura_" . $usuario_id . "_" . date('dmYHis') . ".pdf";

// Crear instancia de FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Establecer fuentes y agregar título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Factura de Compra', 0, 1, 'C');
$pdf->Ln(10);

// Datos de facturación
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Dirección:', 1);
$pdf->Cell(140, 10, $direccion, 1);
$pdf->Ln();
$pdf->Cell(50, 10, 'Fecha:', 1);
$pdf->Cell(140, 10, $fecha_actual, 1);
$pdf->Ln(20);

// Tabla de Pedidos
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'Pedidos', 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 10, 'Plato', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
$pdf->Cell(40, 10, 'Precio Unitario', 1, 0, 'C');
$pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);

$subtotal_pedidos = 0;

if ($pedidos_result->num_rows > 0) {
    while ($pedido = $pedidos_result->fetch_assoc()) {
        $plato = $pedido['plato'];
        $cantidad = $pedido['cantidad'];
        $precio_unitario = number_format($pedido['precio_unitario'], 2);
        $subtotal = number_format($pedido['subtotal'], 2);
        $subtotal_pedidos += $pedido['subtotal'];

        $pdf->Cell(50, 10, $plato, 1, 0, 'C');
        $pdf->Cell(30, 10, $cantidad, 1, 0, 'C');
        $pdf->Cell(40, 10, '$' . $precio_unitario, 1, 0, 'C');
        $pdf->Cell(40, 10, '$' . $subtotal, 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, 'No hay pedidos registrados.', 1, 1, 'C');
}

$pdf->Ln(10);

// Tabla de Reservas
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'Reservas', 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 10, 'Actividad', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
$pdf->Cell(40, 10, 'Precio/Persona', 1, 0, 'C');
$pdf->Cell(40, 10, 'Total', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);

$subtotal_reservas = 0;

if ($reservas_result->num_rows > 0) {
    while ($reserva = $reservas_result->fetch_assoc()) {
        $actividad_original = $reserva['actividad'];
        $actividad = $reserva['actividad'];
        $cantidad = $reserva['personas'];
        // Obtener el precio y calcular el total
        $precio_por_persona = isset($precios_reservas[$actividad_original]) ? number_format($precios_reservas[$actividad_original], 2) : '0.00';
        $total_reserva = isset($precios_reservas[$actividad_original]) ? number_format($precios_reservas[$actividad_original] * $cantidad, 2) : '0.00';
        $subtotal_reservas += isset($precios_reservas[$actividad_original]) ? ($precios_reservas[$actividad_original] * $cantidad) : 0;

        $pdf->Cell(40, 10, $actividad, 1, 0, 'C');
        $pdf->Cell(30, 10, $cantidad, 1, 0, 'C');
        $pdf->Cell(40, 10, '$' . $precio_por_persona, 1, 0, 'C');
        $pdf->Cell(40, 10, '$' . $total_reserva, 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, 'No hay reservas registradas.', 1, 1, 'C');
}

$pdf->Ln(10);

// Totales
$total_general = $subtotal_pedidos + $subtotal_reservas;
$iva = $total_general * 0.15; // 15% IVA
$total_con_iva = $total_general + $iva;

// Mostrar totales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(160, 10, 'Subtotal Pedidos:', 1);
$pdf->Cell(30, 10, '$' . number_format($subtotal_pedidos, 2), 1, 1, 'C');

$pdf->Cell(160, 10, 'Subtotal Reservas:', 1);
$pdf->Cell(30, 10, '$' . number_format($subtotal_reservas, 2), 1, 1, 'C');

$pdf->Cell(160, 10, 'IVA (15%):', 1);
$pdf->Cell(30, 10, '$' . number_format($iva, 2), 1, 1, 'C');

$pdf->Cell(160, 10, 'Total General:', 1, 0, 'R');
$pdf->Cell(30, 10, '$' . number_format($total_con_iva, 2), 1, 1, 'C');

// Guardar el PDF en un archivo temporal en el servidor
$temp_pdf_path = sys_get_temp_dir() . '/' . $factura_nombre;
$pdf->Output('F', $temp_pdf_path);

// Inicializar Google Client
$client = new Client();
$client->setAuthConfig(__DIR__ . '/../../assets/google_credentials.json'); // Ruta al archivo JSON de la cuenta de servicio
$client->addScope(Drive::DRIVE_FILE);

// Crear el servicio de Drive
$driveService = new Drive($client);

// Crear los metadatos del archivo
$file = new Drive\DriveFile();
$file->setName($factura_nombre);
$file->setParents(["1F3KLyelaxl5aYkNbD1sPBWzVogQwSple"]); // Reemplaza con tu ID de carpeta

// Leer el contenido del archivo
$content = file_get_contents($temp_pdf_path);

// Crear la solicitud de subida
try {
    $uploadedFile = $driveService->files->create($file, [
        'data' => $content,
        'mimeType' => 'application/pdf',
        'uploadType' => 'multipart'
    ]);
    $drive_file_id = $uploadedFile->id;
} catch (Exception $e) {
    error_log('Error al subir el archivo a Google Drive: ' . $e->getMessage());
    echo "<script>alert('Factura generada pero hubo un error al subirla a Google Drive.'); window.location.href = 'usuario_resumen.php';</script>";
    unlink($temp_pdf_path);
    exit;
}

// Insertar la factura en la base de datos incluyendo el drive_file_id

// Definir valores de la factura
$serie = "001-00";
$autorizacion = "1234567890";  // Se puede generar automáticamente si es necesario
$secuencia = rand(1000, 9999);
$fecha_actual = date('Y-m-d');

// Verificar valores antes de la inserción
if (!isset($subtotal_pedidos, $subtotal_reservas, $iva, $total_general, $id_cliente, $drive_file_id)) {
    die("Error: Datos insuficientes para generar la factura.");
}

// Preparar la consulta de inserción
$insert_factura_query = "INSERT INTO facturas 
    (serie, secuencia, autorizacion, fecha, base0, baseIVA, totalIVA, total, id_cliente, drive_file_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$factura_stmt = $conn->prepare($insert_factura_query);
if (!$factura_stmt) {
    error_log("Error en la preparación de inserción de factura: " . $conn->error);
    die("Error en la preparación de la consulta de facturas.");
}

// Vincular los parámetros
$factura_stmt->bind_param(
    "sissddddis", 
    $serie, 
    $secuencia, 
    $autorizacion, 
    $fecha_actual, 
    $subtotal_pedidos, 
    $subtotal_reservas, 
    $iva, 
    $total_general, 
    $id_cliente, 
    $drive_file_id
);

// Ejecutar la consulta
if ($factura_stmt->execute()) {
    unlink($temp_pdf_path);  // Eliminar archivo temporal

    // Descargar la factura al usuario
    $pdf->Output('D', $factura_nombre);

    // Redirigir con mensaje de éxito (opcional)
    echo "<script>alert('Factura generada y subida con éxito.'); window.location.href = 'usuario_resumen.php';</script>";
} else {
    error_log("Error en ejecución de inserción de factura: " . $factura_stmt->error);
    echo "<script>alert('Error al guardar la factura en la base de datos.');</script>";
}

$factura_stmt->close();
$conn->close();
?>
