<?php
// Iniciar sesión
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Por favor, inicie sesión primero.');</script>";
    echo "<script>window.location.href = 'cliente.php';</script>";
    exit;
}

// Conectar con la base de datos
require '../../includes/db_connect.php';
$usuario_id = $_SESSION['user_id'];

/*
 ---------------------------------------------------------------------------------
 | Definición de Clases y Estructuras de Datos:
 ---------------------------------------------------------------------------------
*/

// Clase para Pedido
class Pedido {
    public $plato;
    public $cantidad;
    public $fecha;
    public $fecha_pedido;
    public $precio_unitario;
    public $subtotal;

    public function __construct($plato, $cantidad, $fecha, $fecha_pedido, $precio_unitario) {
        $this->plato = $plato;
        $this->cantidad = $cantidad;
        $this->fecha = $fecha;
        $this->fecha_pedido = $fecha_pedido;
        $this->precio_unitario = $precio_unitario;
        $this->subtotal = $this->cantidad * $this->precio_unitario;
    }
}

// Clase para Reserva
class Reserva {
    public $actividad;
    public $fecha;
    public $hora;
    public $personas;
    public $precio_por_persona;
    public $total;

    public function __construct($actividad, $fecha, $hora, $personas, $precio_por_persona) {
        $this->actividad = $actividad;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->personas = $personas;
        $this->precio_por_persona = $precio_por_persona;
        $this->total = $this->personas * $this->precio_por_persona;
    }
}

// Nodo para Árbol Binario de Búsqueda
class TreeNode {
    public $data;
    public $left;
    public $right;

    public function __construct($data) {
        $this->data = $data;
        $this->left = null;
        $this->right = null;
    }
}

// Árbol Binario de Búsqueda para Pedidos y Reservas
class BinarySearchTree {
    public $root;

    public function __construct() {
        $this->root = null;
    }

    // Insertar dato en el árbol
    public function insert($data, $key) {
        $this->root = $this->insertRec($this->root, $data, $key);
    }

    private function insertRec($root, $data, $key) {
        if ($root === null) {
            $root = new TreeNode(['key' => $key, 'data' => $data]);
            return $root;
        }

        if ($key < $root->data['key']) {
            $root->left = $this->insertRec($root->left, $data, $key);
        } elseif ($key > $root->data['key']) {
            $root->right = $this->insertRec($root->right, $data, $key);
        }

        return $root;
    }

    // Búsqueda en el árbol
    public function search($key) {
        return $this->searchRec($this->root, $key);
    }

    private function searchRec($root, $key) {
        if ($root === null || $root->data['key'] == $key) {
            return $root;
        }

        if ($key < $root->data['key']) {
            return $this->searchRec($root->left, $key);
        }

        return $this->searchRec($root->right, $key);
    }

    // Obtener todos los datos en orden
    public function inorder() {
        $result = [];
        $this->inorderRec($this->root, $result);
        return $result;
    }

    private function inorderRec($root, &$result) {
        if ($root !== null) {
            $this->inorderRec($root->left, $result);
            $result[] = $root->data['data'];
            $this->inorderRec($root->right, $result);
        }
    }
}

// Clase para Cola (Queue) para Facturación
class Queue {
    private $items = [];

    public function enqueue($item) {
        array_push($this->items, $item);
    }

    public function dequeue() {
        if (!$this->isEmpty()) {
            return array_shift($this->items);
        }
        return null;
    }

    public function isEmpty() {
        return empty($this->items);
    }

    public function size() {
        return count($this->items);
    }

    public function getItems() {
        return $this->items;
    }
}

// Inicializar Árboles y Cola
$pedidosTree = new BinarySearchTree();
$reservasTree = new BinarySearchTree();
$billingQueue = new Queue();

// Obtener pedidos del usuario
$pedidos_query = "SELECT plato, cantidad, fecha, fecha_pedido, 5.00 AS precio_unitario FROM pedidos WHERE usuario_id = ?";
$pedidos_stmt = $conn->prepare($pedidos_query);
$pedidos_stmt->bind_param("i", $usuario_id);
$pedidos_stmt->execute();
$pedidos_result = $pedidos_stmt->get_result();

while ($pedido_row = $pedidos_result->fetch_assoc()) {
    $pedido = new Pedido(
        $pedido_row['plato'],
        $pedido_row['cantidad'],
        $pedido_row['fecha'],
        $pedido_row['fecha_pedido'],
        $pedido_row['precio_unitario']
    );
    // Usar fecha_pedido como clave para el árbol
    $pedidosTree->insert($pedido, strtotime($pedido->fecha_pedido));
    // Agregar tarea de facturación a la cola
    $billingQueue->enqueue("Pedido de {$pedido->plato}, Cantidad: {$pedido->cantidad}, Subtotal: \${$pedido->subtotal}");
}

// Obtener reservas del usuario
$reservas_query = "SELECT actividad, fecha, hora, personas FROM reservas WHERE id_usuario = ?";
$reservas_stmt = $conn->prepare($reservas_query);
$reservas_stmt->bind_param("i", $usuario_id);
$reservas_stmt->execute();
$reservas_result = $reservas_stmt->get_result();

// Definir precios por actividad
$precios_reservas = [
    'Fútbol' => 0.35,
    'Vóley' => 0.45,
    'Piscina' => 0.27,
    'Acuario' => 1.75
];

while ($reserva_row = $reservas_result->fetch_assoc()) {
    $precio_por_persona = isset($precios_reservas[$reserva_row['actividad']]) ? $precios_reservas[$reserva_row['actividad']] : 0;
    $reserva = new Reserva(
        $reserva_row['actividad'],
        $reserva_row['fecha'],
        $reserva_row['hora'],
        $reserva_row['personas'],
        $precio_por_persona
    );
    // Usar fecha y hora como clave para el árbol
    $clave_reserva = strtotime("{$reserva->fecha} {$reserva->hora}");
    $reservasTree->insert($reserva, $clave_reserva);
    // Agregar tarea de facturación a la cola
    $billingQueue->enqueue("Reserva de {$reserva->actividad}, Personas: {$reserva->personas}, Total: \${$reserva->total}");
}

/*
 ---------------------------------------------------------------------------------
 | Procesamiento de la Cola de Facturación:
 | Aquí se podría implementar la lógica para procesar las facturas, como generar
 | archivos PDF, enviar correos electrónicos, etc. Por ahora, simplemente
 | registramos las tareas en un log.
 ---------------------------------------------------------------------------------
*/

function processBillingQueue($queue) {
    while (!$queue->isEmpty()) {
        $billingTask = $queue->dequeue();
        // Implementar lógica de facturación aquí
        // Por ejemplo, generar un PDF, enviar por email, etc.
        // Actualmente, solo registramos en el log
        error_log("Procesando facturación: $billingTask");
    }
}

// Procesar la cola al cargar la página
processBillingQueue($billingQueue);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de tus Compras y Reservas</title>
    <link rel="stylesheet" href="../../css/style_factura.css">
    <style>
        /* Estilos adicionales para mejorar la apariencia */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            width: 50px;
            height: auto;
        }

        .logo span {
            font-size: 24px;
            margin-left: 10px;
        }

        nav ul.menu {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        nav ul.menu li {
            margin-left: 20px;
        }

        nav ul.menu li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }

        nav ul.menu li a.active, nav ul.menu li a:hover {
            text-decoration: underline;
        }

        main.factura-container {
            padding: 20px;
        }

        .section-title {
            color: #333;
            margin-bottom: 10px;
        }

        table.styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
        }

        table.styled-table th, table.styled-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table.styled-table th {
            background-color: #4CAF50;
            color: white;
        }

        .total-section {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .total-section h3, .total-section h2 {
            margin: 10px 0;
        }

        .generate-invoice-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }

        .generate-invoice-btn:hover {
            background-color: #45a049;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            nav ul.menu {
                flex-direction: column;
                align-items: flex-start;
            }

            nav ul.menu li {
                margin-left: 0;
                margin-bottom: 10px;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
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
            <li><a href="usuario_resumen.php" class="active">Resumen</a></li>
            <li><a href="usuario_contacto.php">Contacto</a></li>
            <li><a href="../../includes/logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>
</header>

<main class="factura-container">
    <h2 class="section-title">Resumen de tus Pedidos</h2>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Plato</th>
                <th>Cantidad</th>
                <th>Fecha del Pedido</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $pedidos = $pedidosTree->inorder();
            $total_pedidos = 0;
            if (!empty($pedidos)) {
                foreach ($pedidos as $pedido) {
                    $total_pedidos += $pedido->subtotal;
                    echo "<tr>
                            <td>" . htmlspecialchars($pedido->plato) . "</td>
                            <td>" . htmlspecialchars($pedido->cantidad) . "</td>
                            <td>" . htmlspecialchars($pedido->fecha_pedido) . "</td>
                            <td>\$" . number_format($pedido->precio_unitario, 2) . "</td>
                            <td>\$" . number_format($pedido->subtotal, 2) . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No tienes pedidos registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h2 class="section-title">Resumen de tus Reservas</h2>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Actividad</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Cantidad de Personas</th>
                <th>Precio por Persona</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $reservas = $reservasTree->inorder();
            $total_reservas = 0;
            if (!empty($reservas)) {
                foreach ($reservas as $reserva) {
                    $total_reservas += $reserva->total;
                    echo "<tr>
                            <td>" . htmlspecialchars($reserva->actividad) . "</td>
                            <td>" . htmlspecialchars($reserva->fecha) . "</td>
                            <td>" . htmlspecialchars($reserva->hora) . "</td>
                            <td>" . htmlspecialchars($reserva->personas) . "</td>
                            <td>\$" . number_format($reserva->precio_por_persona, 2) . "</td>
                            <td>\$" . number_format($reserva->total, 2) . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No tienes reservas registradas.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="total-section">
        <h3>Total de Pedidos: $<?php echo number_format($total_pedidos, 2); ?></h3>
        <h3>Total de Reservas: $<?php echo number_format($total_reservas, 2); ?></h3>
        <h2>Total General: $<?php echo number_format($total_pedidos + $total_reservas, 2); ?></h2>
    </div>

    <form method="POST" action="generar_factura.php">
        <button type="submit" class="generate-invoice-btn">Generar Factura</button>
    </form>
</main>
</body>
</html>

<?php
$pedidos_stmt->close();
$reservas_stmt->close();
$conn->close();
?>
