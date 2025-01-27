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

/**
 * Clase Usuario
 */
class Usuario {
    private $user_id;
    private $nombre;
    private $email;
    private $cedula;
    private $telefono;

    public function __construct($user_id, $nombre, $email, $cedula, $telefono) {
        $this->user_id = $user_id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->cedula = $cedula;
        $this->telefono = $telefono;
    }

    // Métodos para obtener atributos
    public function getUserId() {
        return $this->user_id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getCedula() {
        return $this->cedula;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    // Métodos para gestionar la sesión
    public function iniciarSesion() {
        // Lógica para iniciar sesión
    }

    public function cerrarSesion() {
        // Lógica para cerrar sesión
    }
}

/**
 * Clase Producto
 */
class Producto {
    private $producto_id;
    private $nombre;
    private $descripcion;
    private $precio;
    private $cantidad_disponible;
    private $categoria; // 'comidas', 'bebidas' o 'postres'

    public function __construct($producto_id, $nombre, $descripcion, $precio, $cantidad_disponible, $categoria) {
        $this->producto_id = $producto_id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->cantidad_disponible = $cantidad_disponible;
        $this->categoria = $categoria;
    }

    // Métodos para obtener y actualizar atributos
    public function getProductoId() {
        return $this->producto_id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getCantidadDisponible() {
        return $this->cantidad_disponible;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    public function actualizarCantidad($cantidad) {
        $this->cantidad_disponible -= $cantidad;
    }
}

/**
 * Clase Pedido
 */
class Pedido {
    private $pedido_id;
    private $usuario_id;
    private $producto;
    private $cantidad;
    private $fecha;
    private $fecha_pedido;

    public function __construct($pedido_id, $usuario_id, Producto $producto, $cantidad, $fecha) {
        $this->pedido_id = $pedido_id;
        $this->usuario_id = $usuario_id;
        $this->producto = $producto;
        $this->cantidad = $cantidad;
        $this->fecha = $fecha;
        $this->fecha_pedido = date("Y-m-d H:i:s");
    }

    // Métodos para obtener atributos
    public function getPedidoId() {
        return $this->pedido_id;
    }

    public function getUsuarioId() {
        return $this->usuario_id;
    }

    public function getProducto() {
        return $this->producto;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getFechaPedido() {
        return $this->fecha_pedido;
    }

    // Métodos para gestionar el pedido
    public function crearPedido($conn) {
        $insert_query = "INSERT INTO pedidos (usuario_id, plato, cantidad, fecha, fecha_pedido) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $plato = $this->producto->getNombre();
        $stmt->bind_param("isis", $this->usuario_id, $plato, $this->cantidad, $this->fecha);
        if (!$stmt->execute()) {
            throw new Exception("Error al realizar el pedido: " . $stmt->error);
        }
        $this->pedido_id = $stmt->insert_id;
        $stmt->close();
    }

    public function modificarPedido($conn, $nuevaCantidad, $nuevaFecha) {
        $update_query = "UPDATE pedidos SET cantidad = ?, fecha = ? WHERE pedido_id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("isi", $nuevaCantidad, $nuevaFecha, $this->pedido_id);
        if (!$stmt->execute()) {
            throw new Exception("Error al modificar el pedido: " . $stmt->error);
        }
        $stmt->close();
    }

    public function cancelarPedido($conn) {
        $delete_query = "DELETE FROM pedidos WHERE pedido_id = ?";
        $stmt = $conn->prepare($delete_query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("i", $this->pedido_id);
        if (!$stmt->execute()) {
            throw new Exception("Error al cancelar el pedido: " . $stmt->error);
        }
        $stmt->close();
    }
}

/**
 * Clase BinarySearchTreeNode
 */
class BinarySearchTreeNode {
    public $producto;
    public $left;
    public $right;

    public function __construct(Producto $producto) {
        $this->producto = $producto;
        $this->left = null;
        $this->right = null;
    }
}

/**
 * Clase BinarySearchTree
 */
class BinarySearchTree {
    private $root;

    public function __construct() {
        $this->root = null;
    }

    public function insert(Producto $producto) {
        $this->root = $this->insertRec($this->root, $producto);
    }

    private function insertRec($root, Producto $producto) {
        if ($root === null) {
            $root = new BinarySearchTreeNode($producto);
            return $root;
        }

        if ($producto->getNombre() < $root->producto->getNombre()) {
            $root->left = $this->insertRec($root->left, $producto);
        } elseif ($producto->getNombre() > $root->producto->getNombre()) {
            $root->right = $this->insertRec($root->right, $producto);
        }

        return $root;
    }

    public function search($nombre) {
        return $this->searchRec($this->root, $nombre);
    }

    private function searchRec($root, $nombre) {
        if ($root === null || $root->producto->getNombre() === $nombre) {
            return $root;
        }

        if ($nombre < $root->producto->getNombre()) {
            return $this->searchRec($root->left, $nombre);
        }

        return $this->searchRec($root->right, $nombre);
    }
}

/**
 * Clase QueueNode
 */
class QueueNode {
    public $pedido;
    public $next;

    public function __construct(Pedido $pedido) {
        $this->pedido = $pedido;
        $this->next = null;
    }
}

/**
 * Clase Queue
 */
class Queue {
    private $front;
    private $rear;

    public function __construct() {
        $this->front = null;
        $this->rear = null;
    }

    public function enqueue(Pedido $pedido) {
        $newNode = new QueueNode($pedido);
        if ($this->rear === null) {
            $this->front = $this->rear = $newNode;
            return;
        }
        $this->rear->next = $newNode;
        $this->rear = $newNode;
    }

    public function dequeue() {
        if ($this->front === null) {
            return null;
        }
        $temp = $this->front;
        $this->front = $this->front->next;

        if ($this->front === null) {
            $this->rear = null;
        }

        return $temp->pedido;
    }

    public function isEmpty() {
        return $this->front === null;
    }
}

/**
 * Clase PedidoManager
 */
class PedidoManager {
    private $conn;
    private $inventarioBST;
    private $colaFacturacion;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->inventarioBST = new BinarySearchTree();
        $this->colaFacturacion = new Queue();
        $this->cargarInventario();
    }

    private function cargarInventario() {
        // Cargar productos desde la base de datos y agregarlos al BST
        $categorias = ['comidas', 'bebidas', 'postres'];
        foreach ($categorias as $categoria) {
            $query = "SELECT * FROM $categoria";
            $result = $this->conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $producto = new Producto(
                    $row['id'],
                    $row['nombre'],
                    $row['descripcion'],
                    $row['precio'],
                    $row['cantidad_disponible'],
                    $categoria
                );
                $this->inventarioBST->insert($producto);
            }
        }
    }

    public function procesarPedido($usuario_id, $plato, $cantidad, $fecha) {
        // Verificar disponibilidad
        $node = $this->inventarioBST->search($plato);
        if ($node === null) {
            throw new Exception("El producto '$plato' no existe.");
        }

        if ($cantidad > $node->producto->getCantidadDisponible()) {
            throw new Exception("La cantidad solicitada ($cantidad) excede la disponibilidad actual ({$node->producto->getCantidadDisponible()}) del producto '$plato'.");
        }

        // Crear el pedido
        $pedido = new Pedido(null, $usuario_id, $node->producto, $cantidad, $fecha);
        $pedido->crearPedido($this->conn);

        // Actualizar el inventario
        $node->producto->actualizarCantidad($cantidad);
        $this->actualizarInventarioBD($node->producto);

        // Encolar para facturación
        $this->colaFacturacion->enqueue($pedido);
    }

    private function actualizarInventarioBD(Producto $producto) {
        $categoria = $producto->getCategoria();
        $query = "UPDATE $categoria SET cantidad_disponible = ? WHERE nombre = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta de actualización en $categoria: " . $this->conn->error);
        }

        $cantidad = $producto->getCantidadDisponible();
        $stmt->bind_param("is", $cantidad, $producto->getNombre());
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar la disponibilidad en $categoria: " . $stmt->error);
        }
        $stmt->close();
    }

    public function procesarFacturacion() {
        while (!$this->colaFacturacion->isEmpty()) {
            $pedido = $this->colaFacturacion->dequeue();
            // Lógica para generar factura
            // Por ejemplo, crear una instancia de Factura, generar PDF, subir a Google Drive, etc.
            // Esto depende de la implementación específica de la facturación
        }
    }
}

// Incluir las clases definidas
// (Ya están definidas arriba en este mismo archivo)

// Crear una instancia de PedidoManager
$pedidoManager = new PedidoManager($conn);

// Manejar el formulario de pedidos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order'])) {
    $usuario_id = $_SESSION['user_id'];
    $plato = trim($_POST['plato']);
    $cantidad = intval($_POST['cantidad']);
    $fecha = $_POST['fecha'];

    // Validar los campos
    if (empty($plato) || empty($cantidad) || empty($fecha)) {
        echo "<script>alert('Por favor, complete todos los campos.'); window.location.href='usuario_restaurante.php';</script>";
        exit;
    } elseif ($cantidad <= 0) {
        echo "<script>alert('La cantidad debe ser mayor a 0.'); window.location.href='usuario_restaurante.php';</script>";
        exit;
    } else {
        try {
            // Procesar el pedido utilizando PedidoManager
            $pedidoManager->procesarPedido($usuario_id, $plato, $cantidad, $fecha);
            echo "<script>alert('Pedido realizado con éxito.');</script>";
            echo "<script>window.location.href = 'usuario_restaurante.php';</script>";
            exit;
        } catch (Exception $e) {
            // Manejo de errores
            $mensaje = $e->getMessage();
            echo "<script>alert('$mensaje'); window.location.href='usuario_restaurante.php';</script>";
            exit;
        }
    }
}

// Procesar la facturación (puede ser mediante una llamada separada o en otro contexto)
$pedidoManager->procesarFacturacion();

// Obtener comidas, bebidas y postres desde la base de datos
$query_comidas = "SELECT * FROM comidas";
$query_bebidas = "SELECT * FROM bebidas";
$query_postres = "SELECT * FROM postres";

$result_comidas = $conn->query($query_comidas);
$result_bebidas = $conn->query($query_bebidas);
$result_postres = $conn->query($query_postres);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante AcuSystem</title>
    <link rel="stylesheet" href="../../css/restaurante.css">
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
        <h1 class="title">Menú del Restaurante</h1>

        <!-- Comidas -->
        <section class="menu-category">
            <h2>Comidas</h2>
            <div class="menu-items">
                <?php
                while ($comida = $result_comidas->fetch_assoc()) {
                    $producto = new Producto(
                        $comida['id'],
                        $comida['nombre'],
                        $comida['descripcion'],
                        $comida['precio'],
                        $comida['cantidad_disponible'],
                        'comidas'
                    );
                ?>
                    <div class="menu-item">
                        <img src="<?php echo htmlspecialchars($comida['imagen_url']); ?>" alt="<?php echo htmlspecialchars($comida['nombre']); ?>">
                        <h3><?php echo htmlspecialchars($comida['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($comida['descripcion']); ?></p>
                        <p><strong>Precio:</strong> $<?php echo htmlspecialchars($comida['precio']); ?></p>
                        <p><strong>Disponible:</strong> <?php echo htmlspecialchars($comida['cantidad_disponible']); ?></p>
                        <?php if ($comida['cantidad_disponible'] > 0) { ?>
                            <button class="buy-btn" onclick="openOrderForm('<?php echo htmlspecialchars($comida['nombre']); ?>')">Comprar</button>
                        <?php } else { ?>
                            <button class="buy-btn" disabled>No Disponible</button>
                        <?php } ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </section>

        <!-- Bebidas -->
        <section class="menu-category">
            <h2>Bebidas</h2>
            <div class="menu-items">
                <?php
                while ($bebida = $result_bebidas->fetch_assoc()) {
                    $producto = new Producto(
                        $bebida['id'],
                        $bebida['nombre'],
                        $bebida['descripcion'],
                        $bebida['precio'],
                        $bebida['cantidad_disponible'],
                        'bebidas'
                    );
                ?>
                    <div class="menu-item">
                        <img src="<?php echo htmlspecialchars($bebida['imagen_url']); ?>" alt="<?php echo htmlspecialchars($bebida['nombre']); ?>">
                        <h3><?php echo htmlspecialchars($bebida['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($bebida['descripcion']); ?></p>
                        <p><strong>Precio:</strong> $<?php echo htmlspecialchars($bebida['precio']); ?></p>
                        <p><strong>Disponible:</strong> <?php echo htmlspecialchars($bebida['cantidad_disponible']); ?></p>
                        <?php if ($bebida['cantidad_disponible'] > 0) { ?>
                            <button class="buy-btn" onclick="openOrderForm('<?php echo htmlspecialchars($bebida['nombre']); ?>')">Comprar</button>
                        <?php } else { ?>
                            <button class="buy-btn" disabled>No Disponible</button>
                        <?php } ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </section>

        <!-- Postres -->
        <section class="menu-category">
            <h2>Postres</h2>
            <div class="menu-items">
                <?php
                while ($postre = $result_postres->fetch_assoc()) {
                    $producto = new Producto(
                        $postre['id'],
                        $postre['nombre'],
                        $postre['descripcion'],
                        $postre['precio'],
                        $postre['cantidad_disponible'],
                        'postres'
                    );
                ?>
                    <div class="menu-item">
                        <img src="<?php echo htmlspecialchars($postre['imagen_url']); ?>" alt="<?php echo htmlspecialchars($postre['nombre']); ?>">
                        <h3><?php echo htmlspecialchars($postre['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($postre['descripcion']); ?></p>
                        <p><strong>Precio:</strong> $<?php echo htmlspecialchars($postre['precio']); ?></p>
                        <p><strong>Disponible:</strong> <?php echo htmlspecialchars($postre['cantidad_disponible']); ?></p>
                        <?php if ($postre['cantidad_disponible'] > 0) { ?>
                            <button class="buy-btn" onclick="openOrderForm('<?php echo htmlspecialchars($postre['nombre']); ?>')">Comprar</button>
                        <?php } else { ?>
                            <button class="buy-btn" disabled>No Disponible</button>
                        <?php } ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </section>

        <!-- Modal para Comprar -->
        <div id="order-modal" class="modal">
            <div class="modal-content">
                <button class="close-btn" onclick="closeOrderForm()">&times;</button>
                <h2 id="order-title">Comprar Producto</h2>
                <form method="POST" action="usuario_restaurante.php">
                    <input type="hidden" name="plato" id="plato">
                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" name="cantidad" id="cantidad" min="1" placeholder="Número de productos" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha de recogida:</label>
                        <input type="date" name="fecha" id="fecha" required>
                    </div>
                    <button type="submit" name="submit_order" class="submit-btn">Confirmar Compra</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function openOrderForm(plato) {
            document.getElementById("plato").value = plato;
            document.getElementById("order-title").innerText = `Comprar "${plato}"`;
            document.getElementById("order-modal").style.display = "flex";
        }

        function closeOrderForm() {
            document.getElementById("order-modal").style.display = "none";
        }

        // Cerrar el modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            const modal = document.getElementById("order-modal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>
