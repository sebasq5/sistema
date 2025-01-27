<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['message'] = 'Por favor, inicie sesión como administrador.';
    header("Location: admin_login.php");
    exit;
}

// Conectar con la base de datos
require '../../includes/db_connect.php';

/*
 ---------------------------------------------------------------------------------
 | Definición de Clases y Estructuras de Datos:
 ---------------------------------------------------------------------------------
*/

// Clase para Factura
class Factura {
    public $id;
    public $fecha;
    public $total;
    public $id_cliente;
    public $drive_file_id;

    public function __construct($id, $fecha, $total, $id_cliente, $drive_file_id) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->total = $total;
        $this->id_cliente = $id_cliente;
        $this->drive_file_id = $drive_file_id;
    }
}

// Clase para Reserva
class Reserva {
    public $id;
    public $id_usuario;
    public $actividad;
    public $fecha;
    public $hora;
    public $personas;

    public function __construct($id, $id_usuario, $actividad, $fecha, $hora, $personas) {
        $this->id = $id;
        $this->id_usuario = $id_usuario;
        $this->actividad = $actividad;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->personas = $personas;
    }
}

// Clase para Usuario
class Usuario {
    public $id;
    public $nombre;
    public $correo;
    public $cedula;
    public $telefono;

    public function __construct($id, $nombre, $correo, $cedula, $telefono) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->cedula = $cedula;
        $this->telefono = $telefono;
    }
}

// Clase para Comida
class Comida {
    public $id;
    public $nombre;
    public $precio;
    public $cantidad_disponible;
    public $imagen_url;
    public $descripcion;

    public function __construct($id, $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->cantidad_disponible = $cantidad_disponible;
        $this->imagen_url = $imagen_url;
        $this->descripcion = $descripcion;
    }
}

// Clase para Bebida
class Bebida {
    public $id;
    public $nombre;
    public $precio;
    public $cantidad_disponible;
    public $tipo;
    public $imagen_url;
    public $descripcion;

    public function __construct($id, $nombre, $precio, $cantidad_disponible, $tipo, $imagen_url, $descripcion) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->cantidad_disponible = $cantidad_disponible;
        $this->tipo = $tipo;
        $this->imagen_url = $imagen_url;
        $this->descripcion = $descripcion;
    }
}

// Clase para Postre
class Postre {
    public $id;
    public $nombre;
    public $precio;
    public $cantidad_disponible;
    public $imagen_url;
    public $descripcion;

    public function __construct($id, $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->cantidad_disponible = $cantidad_disponible;
        $this->imagen_url = $imagen_url;
        $this->descripcion = $descripcion;
    }
}

// Clase para Pez
class Pez {
    public $id;
    public $nombre;
    public $pecera;
    public $observable;
    public $oxigenado;

    public function __construct($id, $nombre, $pecera, $observable, $oxigenado) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pecera = $pecera;
        $this->observable = $observable;
        $this->oxigenado = $oxigenado;
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

// Árbol Binario de Búsqueda para Categorías
class BinarySearchTree {
    public $root;

    public function __construct() {
        $this->root = null;
    }

    // Insertar dato en el árbol
    public function insert($data) {
        $this->root = $this->insertRec($this->root, $data);
    }

    private function insertRec($root, $data) {
        if ($root === null) {
            $root = new TreeNode($data);
            return $root;
        }

        if ($data < $root->data) {
            $root->left = $this->insertRec($root->left, $data);
        } elseif ($data > $root->data) {
            $root->right = $this->insertRec($root->right, $data);
        }

        return $root;
    }

    // Búsqueda Binaria en el árbol
    public function search($data) {
        return $this->searchRec($this->root, $data);
    }

    private function searchRec($root, $data) {
        if ($root === null || $root->data == $data) {
            return $root;
        }

        if ($data < $root->data) {
            return $this->searchRec($root->left, $data);
        }

        return $this->searchRec($root->right, $data);
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
}

// Inicializar Árbol de Categorías (Ejemplo)
$categoryTree = new BinarySearchTree();
$categoryTree->insert('Comidas');
$categoryTree->insert('Bebidas');
$categoryTree->insert('Postres');

// Inicializar Cola de Facturación
$billingQueue = new Queue();

/*
 ---------------------------------------------------------------------------------
 | Procesamiento de Formularios Utilizando Clases:
 ---------------------------------------------------------------------------------
*/

// Variable para mantener la sección activa
$active_section = isset($_GET['section']) ? $_GET['section'] : 'facturas';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sección de Comidas
    if (isset($_POST['agregar_comida'])) {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $cantidad_disponible = intval($_POST['cantidad_disponible']);
        $imagen_url = trim($_POST['imagen_url']);
        $descripcion = trim($_POST['descripcion']);

        // Validar y sanitizar datos
        if (!empty($nombre) && $precio > 0 && $cantidad_disponible >= 0 && !empty($imagen_url) && !empty($descripcion)) {
            $query = "INSERT INTO comidas (nombre, precio, cantidad_disponible, imagen_url, descripcion) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('sdiss', $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion);
                if ($stmt->execute()) {
                    // Agregar a la cola de facturación
                    $billingQueue->enqueue("Comida agregada: $nombre por \$$precio");

                    $_SESSION['message'] = 'Comida agregada correctamente.';
                    $active_section = 'restaurante';
                } else {
                    $_SESSION['error'] = 'Error al agregar la comida: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = 'Error en la preparación de la consulta: ' . htmlspecialchars($conn->error);
            }
        } else {
            $_SESSION['error'] = 'Por favor, completa todos los campos correctamente.';
        }

        header("Location: admin_inventario.php?section=restaurante");
        exit;
    }

    // Sección de Bebidas
    if (isset($_POST['agregar_bebida'])) {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $cantidad_disponible = intval($_POST['cantidad_disponible']);
        $tipo = trim($_POST['tipo']);
        $imagen_url = trim($_POST['imagen_url']);
        $descripcion = trim($_POST['descripcion']);

        // Validar y sanitizar datos
        if (!empty($nombre) && $precio > 0 && $cantidad_disponible >= 0 && !empty($tipo) && !empty($imagen_url) && !empty($descripcion)) {
            $query = "INSERT INTO bebidas (nombre, precio, cantidad_disponible, tipo, imagen_url, descripcion) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('sdisss', $nombre, $precio, $cantidad_disponible, $tipo, $imagen_url, $descripcion);
                if ($stmt->execute()) {
                    // Agregar a la cola de facturación
                    $billingQueue->enqueue("Bebida agregada: $nombre tipo $tipo por \$$precio");

                    $_SESSION['message'] = 'Bebida agregada correctamente.';
                    $active_section = 'restaurante';
                } else {
                    $_SESSION['error'] = 'Error al agregar la bebida: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = 'Error en la preparación de la consulta: ' . htmlspecialchars($conn->error);
            }
        } else {
            $_SESSION['error'] = 'Por favor, completa todos los campos correctamente.';
        }

        header("Location: admin_inventario.php?section=restaurante");
        exit;
    }

    // Sección de Postres
    if (isset($_POST['agregar_postre'])) {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $cantidad_disponible = intval($_POST['cantidad_disponible']);
        $imagen_url = trim($_POST['imagen_url']);
        $descripcion = trim($_POST['descripcion']);

        // Validar y sanitizar datos
        if (!empty($nombre) && $precio > 0 && $cantidad_disponible >= 0 && !empty($imagen_url) && !empty($descripcion)) {
            $query = "INSERT INTO postres (nombre, precio, cantidad_disponible, imagen_url, descripcion) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('sdiss', $nombre, $precio, $cantidad_disponible, $imagen_url, $descripcion);
                if ($stmt->execute()) {
                    // Agregar a la cola de facturación
                    $billingQueue->enqueue("Postre agregado: $nombre por \$$precio");

                    $_SESSION['message'] = 'Postre agregado correctamente.';
                    $active_section = 'restaurante';
                } else {
                    $_SESSION['error'] = 'Error al agregar el postre: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = 'Error en la preparación de la consulta: ' . htmlspecialchars($conn->error);
            }
        } else {
            $_SESSION['error'] = 'Por favor, completa todos los campos correctamente.';
        }

        header("Location: admin_inventario.php?section=restaurante");
        exit;
    }

    // Sección de Peces
    if (isset($_POST['agregar_pez'])) {
        $nombre = trim($_POST['nombre']);
        $pecera = trim($_POST['pecera']);
        $observable = trim($_POST['observable']);
        $oxigenado = trim($_POST['oxigenado']);

        // Validar y sanitizar datos
        if (!empty($nombre) && !empty($pecera) && !empty($observable) && !empty($oxigenado)) {
            $query = "INSERT INTO peces (nombre, pecera, observable, oxigenado) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('ssss', $nombre, $pecera, $observable, $oxigenado);
                if ($stmt->execute()) {
                    // Agregar a la cola de facturación (si aplica)
                    $billingQueue->enqueue("Pez agregado: $nombre en pecera $pecera");

                    $_SESSION['message'] = 'Pez agregado correctamente.';
                    $active_section = 'peces';
                } else {
                    $_SESSION['error'] = 'Error al agregar el pez: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = 'Error en la preparación de la consulta: ' . htmlspecialchars($conn->error);
            }
        } else {
            $_SESSION['error'] = 'Por favor, completa todos los campos correctamente.';
        }

        header("Location: admin_inventario.php?section=peces");
        exit;
    }
}

/*
 ---------------------------------------------------------------------------------
 | Obtener datos de la base de datos y almacenarlos en Objetos:
 ---------------------------------------------------------------------------------
*/

// Función para obtener datos y crear objetos
function fetchData($conn, $query, $type) {
    $result = $conn->query($query);
    $data = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            switch ($type) {
                case 'facturas':
                    $data[] = new Factura($row['id'], $row['fecha'], $row['total'], $row['id_cliente'], $row['drive_file_id']);
                    break;
                case 'reservas':
                    $data[] = new Reserva($row['id'], $row['id_usuario'], $row['actividad'], $row['fecha'], $row['hora'], $row['personas']);
                    break;
                case 'usuarios':
                    $data[] = new Usuario($row['id'], $row['nombre'], $row['correo'], $row['cedula'], $row['telefono']);
                    break;
                case 'comidas':
                    $data[] = new Comida($row['id'], $row['nombre'], $row['precio'], $row['cantidad_disponible'], $row['imagen_url'], $row['descripcion']);
                    break;
                case 'bebidas':
                    $data[] = new Bebida($row['id'], $row['nombre'], $row['precio'], $row['cantidad_disponible'], $row['tipo'], $row['imagen_url'], $row['descripcion']);
                    break;
                case 'postres':
                    $data[] = new Postre($row['id'], $row['nombre'], $row['precio'], $row['cantidad_disponible'], $row['imagen_url'], $row['descripcion']);
                    break;
                case 'peces':
                    $data[] = new Pez($row['id'], $row['nombre'], $row['pecera'], $row['observable'], $row['oxigenado']);
                    break;
                default:
                    // Manejar otros tipos si es necesario
                    break;
            }
        }
    }

    return $data;
}

// Obtener datos y almacenarlos en arrays de objetos
$facturas = fetchData($conn, "SELECT * FROM facturas ORDER BY fecha DESC", 'facturas');
$reservas = fetchData($conn, "SELECT * FROM reservas", 'reservas');
$usuarios = fetchData($conn, "SELECT * FROM usuarios", 'usuarios');
$comidas = fetchData($conn, "SELECT * FROM comidas", 'comidas');
$bebidas = fetchData($conn, "SELECT * FROM bebidas", 'bebidas');
$postres = fetchData($conn, "SELECT * FROM postres", 'postres');
$peces = fetchData($conn, "SELECT * FROM peces", 'peces');

/*
 ---------------------------------------------------------------------------------
 | Implementación de Búsqueda Binaria:
 | Nota: La búsqueda binaria se aplica sobre arrays ordenados. Asegúrate de que 
 | los arrays estén ordenados antes de realizar la búsqueda.
 ---------------------------------------------------------------------------------
*/

// Función de búsqueda binaria
function binarySearch($arr, $key, $compareFunction) {
    $low = 0;
    $high = count($arr) - 1;

    while ($low <= $high) {
        $mid = intdiv($low + $high, 2);
        $cmp = $compareFunction($arr[$mid], $key);

        if ($cmp === 0) {
            return $arr[$mid];
        } elseif ($cmp < 0) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }

    return null;
}

// Ejemplo de uso de búsqueda binaria para encontrar una comida por nombre
function findComidaByName($comidas, $nombre) {
    // Ordenar el array de comidas por nombre
    usort($comidas, function($a, $b) {
        return strcmp($a->nombre, $b->nombre);
    });

    return binarySearch($comidas, $nombre, function($comida, $nombre) {
        return strcmp($comida->nombre, $nombre);
    });
}

/*
 ---------------------------------------------------------------------------------
 | Obtener y procesar la cola de facturación:
 ---------------------------------------------------------------------------------
*/

// Procesar elementos en la cola de facturación
function processBillingQueue($queue) {
    while (!$queue->isEmpty()) {
        $billingTask = $queue->dequeue();
        // Aquí puedes implementar la lógica para procesar la facturación, 
        // como registrar en un log, enviar notificaciones, etc.
        // Por ejemplo:
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
    <title>Administrador - Inventario</title>
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .notification.error {
            background-color: #f44336; /* Rojo para errores */
        }

        .notification.hide {
            opacity: 0;
        }

        /* Estilos para las secciones */
        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        /* Estilos para la barra lateral */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 200px;
            height: 100%;
            background-color: #333;
            padding-top: 20px;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px;
            text-align: center;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar ul li a:hover {
            background-color: #575757;
            border-radius: 4px;
        }

        /* Estilos para el contenido principal */
        .content {
            margin-left: 220px;
            padding: 20px;
        }

        /* Estilos para las tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #696969;
        }

        /* Estilos para los botones */
        button {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
        }

        button:hover {
            opacity: 0.8;
        }

        button[type="submit"] {
            background-color: #4CAF50; /* Verde */
        }

        button[type="button"] {
            background-color: #008CBA; /* Azul */
        }

        /* Estilos para los formularios */
        form table {
            width: 100%;
        }

        form th,
        form td {
            padding: 10px;
        }

        form input[type="text"],
        form input[type="number"],
        form select,
        form textarea {
            width: 100%;
            padding: 5px;
            box-sizing: border-box;
        }

        /* Responsivo */
        @media screen and (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .sidebar ul li {
                display: inline;
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
    <script>
        /*
         ---------------------------------------------------------------------------------
         | Ejemplo de referencia a Pilas y Colas:
         | Podríamos implementar una cola (queue) para manejar notificaciones a usuarios,
         | y una pila (stack) para el historial de acciones administrativas.
         |
         | function pushAction(action) { ... }
         | function popAction() { ... }
         ---------------------------------------------------------------------------------
        */

        // Función para mostrar solo la sección seleccionada
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.remove('active');
            });

            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.classList.add('active');
            }
        }

        // Al cargar el DOM
        document.addEventListener('DOMContentLoaded', () => {
            // Manejar la navegación en la barra lateral
            const links = document.querySelectorAll('.sidebar ul li a');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href').substring(1);
                    showSection(targetId);

                    // Actualizar la URL sin recargar la página
                    window.history.pushState({}, '', `?section=${targetId}`);
                });
            });

            // Mostrar la sección activa basada en la URL
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section');
            if (section) {
                showSection(section);
            } else {
                showSection('facturas'); // Sección por defecto
            }

            // Manejar notificaciones
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

        // Funciones para editar y eliminar
        // Facturas
        function editarFactura(id) {
            window.location.href = `editar_factura.php?id=${id}`;
        }

        function eliminarFactura(id) {
            if (confirm('¿Estás seguro de eliminar esta factura?')) {
                // Crear y enviar un formulario POST dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_factura.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Reservas
        function editarReserva(id) {
            window.location.href = `editar_reserva.php?id=${id}`;
        }

        function eliminarReserva(id) {
            if (confirm('¿Estás seguro de eliminar esta reserva?')) {
                // Crear y enviar un formulario POST dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_reserva.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Usuarios
        function editarUsuario(id) {
            window.location.href = `editar_usuario.php?id=${id}`;
        }

        function eliminarUsuario(id) {
            if (confirm('¿Estás seguro de eliminar este usuario?')) {
                // Crear y enviar un formulario POST dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_usuario.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Restaurante - Comidas
        function editarComida(id) {
            window.location.href = `editar_comida.php?id=${id}`;
        }

        function eliminarComida(id) {
            if (confirm('¿Estás seguro de eliminar esta comida?')) {
                // Crear y enviar un formulario POST dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_comida.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Restaurante - Bebidas
        function editarBebida(id) {
            window.location.href = `editar_bebida.php?id=${id}`;
        }

        function eliminarBebida(id) {
            if (confirm('¿Estás seguro de eliminar esta bebida?')) {
                // Crear y enviar un formulario POST dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_bebida.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Restaurante - Postres
        function editarPostre(id) {
            window.location.href = `editar_postre.php?id=${id}`;
        }

        function eliminarPostre(id) {
            if (confirm('¿Estás seguro de eliminar este postre?')) {
                // Crear y enviar un formulario POST dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_postre.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Peces
        function editarPez(id) {
            window.location.href = `editar_pez.php?id=${id}`;
        }

        function eliminarPez(id) {
            if (confirm('¿Estás seguro de eliminar este pez?')) {
                // Crear y enviar un formulario POST dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_pez.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <h2>AcuSystem</h2>
        <ul>
            <li><a href="#facturas">Facturas</a></li>
            <li><a href="#reservas">Reservas</a></li>
            <li><a href="#usuarios">Usuarios</a></li>
            <li><a href="#restaurante">Restaurante</a></li>
            <li><a href="#peces">Peces</a></li>
            <li>
                <form action="../../includes/logout_admin.php" method="post">
                    <button type="submit" style="background-color: #f44336;">Cerrar Sesión</button>
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

        <!-- Facturas -->
        <section id="facturas" class="section <?php echo ($active_section == 'facturas') ? 'active' : ''; ?>">
            <h2>Facturas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>ID Cliente</th>
                        <th>Drive File ID</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($facturas)) { ?>
                        <?php foreach ($facturas as $factura) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($factura->id); ?></td>
                                <td><?php echo htmlspecialchars($factura->fecha); ?></td>
                                <td><?php echo htmlspecialchars(number_format($factura->total, 2)); ?></td>
                                <td><?php echo htmlspecialchars($factura->id_cliente); ?></td>
                                <td>
                                    <?php
                                    if (!empty($factura->drive_file_id)) {
                                        echo '<a href="https://drive.google.com/file/d/' . htmlspecialchars($factura->drive_file_id) . '/view" target="_blank">' . htmlspecialchars($factura->drive_file_id) . '</a>';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button onclick="editarFactura(<?php echo htmlspecialchars($factura->id); ?>);" style="background-color: #2196F3;">Editar</button>
                                    <button onclick="eliminarFactura(<?php echo htmlspecialchars($factura->id); ?>);" style="background-color: #f44336;">Eliminar</button>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">No hay facturas registradas.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        <!-- Reservas -->
        <section id="reservas" class="section <?php echo ($active_section == 'reservas') ? 'active' : ''; ?>">
            <h2>Reservas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Actividad</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Personas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reserva->id); ?></td>
                            <td><?php echo htmlspecialchars($reserva->id_usuario); ?></td>
                            <td><?php echo htmlspecialchars($reserva->actividad); ?></td>
                            <td><?php echo htmlspecialchars($reserva->fecha); ?></td>
                            <td><?php echo htmlspecialchars($reserva->hora); ?></td>
                            <td><?php echo htmlspecialchars($reserva->personas); ?></td>
                            <td>
                                <button onclick="editarReserva(<?php echo htmlspecialchars($reserva->id); ?>);" style="background-color: #2196F3;">Editar</button>
                                <button onclick="eliminarReserva(<?php echo htmlspecialchars($reserva->id); ?>);" style="background-color: #f44336;">Eliminar</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        <!-- Usuarios -->
        <section id="usuarios" class="section <?php echo ($active_section == 'usuarios') ? 'active' : ''; ?>">
            <h2>Usuarios</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario->id); ?></td>
                            <td><?php echo htmlspecialchars($usuario->nombre); ?></td>
                            <td><?php echo htmlspecialchars($usuario->correo); ?></td>
                            <td><?php echo htmlspecialchars($usuario->cedula); ?></td>
                            <td><?php echo htmlspecialchars($usuario->telefono); ?></td>
                            <td>
                                <button onclick="editarUsuario(<?php echo htmlspecialchars($usuario->id); ?>);" style="background-color: #2196F3;">Editar</button>
                                <button onclick="eliminarUsuario(<?php echo htmlspecialchars($usuario->id); ?>);" style="background-color: #f44336;">Eliminar</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        <!-- Restaurante -->
        <section id="restaurante" class="section <?php echo ($active_section == 'restaurante') ? 'active' : ''; ?>">
            <h2>Restaurante</h2>

            <!-- Sección de Comidas -->
            <h3>Comidas</h3>
            <form method="POST" action="">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Cantidad Disponible</th>
                            <th>Imagen URL</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="nombre" required></td>
                            <td><input type="number" step="0.01" name="precio" required></td>
                            <td><input type="number" name="cantidad_disponible" required></td>
                            <td><input type="text" name="imagen_url" required></td>
                            <td><textarea name="descripcion" required></textarea></td>
                            <td><button type="submit" name="agregar_comida">Agregar</button></td>
                        </tr>
                    </tbody>
                </table>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad Disponible</th>
                        <th>Imagen</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comidas as $comida) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comida->id); ?></td>
                            <td><?php echo htmlspecialchars($comida->nombre); ?></td>
                            <td><?php echo htmlspecialchars($comida->precio); ?></td>
                            <td><?php echo htmlspecialchars($comida->cantidad_disponible); ?></td>
                            <td><img src="<?php echo htmlspecialchars($comida->imagen_url); ?>" alt="<?php echo htmlspecialchars($comida->nombre); ?>" style="width:50px; height:auto;"></td>
                            <td><?php echo htmlspecialchars($comida->descripcion); ?></td>
                            <td>
                                <button onclick="editarComida(<?php echo htmlspecialchars($comida->id); ?>);" style="background-color: #2196F3;">Editar</button>
                                <button onclick="eliminarComida(<?php echo htmlspecialchars($comida->id); ?>);" style="background-color: #f44336;">Eliminar</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Sección de Bebidas -->
            <h3>Bebidas</h3>
            <form method="POST" action="">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Cantidad Disponible</th>
                            <th>Tipo</th>
                            <th>Imagen URL</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="nombre" required></td>
                            <td><input type="number" step="0.01" name="precio" required></td>
                            <td><input type="number" name="cantidad_disponible" required></td>
                            <td>
                                <select name="tipo" required>
                                    <option value="Jarra">Jarra</option>
                                    <option value="Vaso">Vaso</option>
                                    <option value="Lata">Lata</option>
                                </select>
                            </td>
                            <td><input type="text" name="imagen_url" required></td>
                            <td><textarea name="descripcion" required></textarea></td>
                            <td><button type="submit" name="agregar_bebida">Agregar</button></td>
                        </tr>
                    </tbody>
                </table>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad Disponible</th>
                        <th>Tipo</th>
                        <th>Imagen</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bebidas as $bebida) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bebida->id); ?></td>
                            <td><?php echo htmlspecialchars($bebida->nombre); ?></td>
                            <td><?php echo htmlspecialchars($bebida->precio); ?></td>
                            <td><?php echo htmlspecialchars($bebida->cantidad_disponible); ?></td>
                            <td><?php echo htmlspecialchars($bebida->tipo); ?></td>
                            <td><img src="<?php echo htmlspecialchars($bebida->imagen_url); ?>" alt="<?php echo htmlspecialchars($bebida->nombre); ?>" style="width:50px; height:auto;"></td>
                            <td><?php echo htmlspecialchars($bebida->descripcion); ?></td>
                            <td>
                                <button onclick="editarBebida(<?php echo htmlspecialchars($bebida->id); ?>);" style="background-color: #2196F3;">Editar</button>
                                <button onclick="eliminarBebida(<?php echo htmlspecialchars($bebida->id); ?>);" style="background-color: #f44336;">Eliminar</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Sección de Postres -->
            <h3>Postres</h3>
            <form method="POST" action="">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Cantidad Disponible</th>
                            <th>Imagen URL</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="nombre" required></td>
                            <td><input type="number" step="0.01" name="precio" required></td>
                            <td><input type="number" name="cantidad_disponible" required></td>
                            <td><input type="text" name="imagen_url" required></td>
                            <td><textarea name="descripcion" required></textarea></td>
                            <td><button type="submit" name="agregar_postre">Agregar</button></td>
                        </tr>
                    </tbody>
                </table>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad Disponible</th>
                        <th>Imagen</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($postres as $postre) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($postre->id); ?></td>
                            <td><?php echo htmlspecialchars($postre->nombre); ?></td>
                            <td><?php echo htmlspecialchars($postre->precio); ?></td>
                            <td><?php echo htmlspecialchars($postre->cantidad_disponible); ?></td>
                            <td><img src="<?php echo htmlspecialchars($postre->imagen_url); ?>" alt="<?php echo htmlspecialchars($postre->nombre); ?>" style="width:100px; height:auto;"></td>
                            <td><?php echo htmlspecialchars($postre->descripcion); ?></td>
                            <td>
                                <button onclick="editarPostre(<?php echo htmlspecialchars($postre->id); ?>);" style="background-color: #2196F3;">Editar</button>
                                <button onclick="eliminarPostre(<?php echo htmlspecialchars($postre->id); ?>);" style="background-color: #f44336;">Eliminar</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        <!-- NUEVA SECCIÓN: PECES -->
        <section id="peces" class="section <?php echo ($active_section == 'peces') ? 'active' : ''; ?>">
            <h2>Peces</h2>

            <!-- Formulario para agregar un pez -->
            <form method="POST" action="">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Pecera (Limpio / Sucio)</th>
                            <th>Observable (Sí / No)</th>
                            <th>Oxigenado (Sí / No)</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="nombre" required></td>
                            <td>
                                <select name="pecera" required>
                                    <option value="Limpio">Limpio</option>
                                    <option value="Sucio">Sucio</option>
                                </select>
                            </td>
                            <td>
                                <select name="observable" required>
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </td>
                            <td>
                                <select name="oxigenado" required>
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </td>
                            <td><button type="submit" name="agregar_pez">Agregar</button></td>
                        </tr>
                    </tbody>
                </table>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Pecera</th>
                        <th>Observable</th>
                        <th>Oxigenado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($peces)) { ?>
                        <?php foreach ($peces as $pez) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pez->id); ?></td>
                                <td><?php echo htmlspecialchars($pez->nombre); ?></td>
                                <td><?php echo htmlspecialchars($pez->pecera); ?></td>
                                <td><?php echo htmlspecialchars($pez->observable); ?></td>
                                <td><?php echo htmlspecialchars($pez->oxigenado); ?></td>
                                <td>
                                    <button onclick="editarPez(<?php echo htmlspecialchars($pez->id); ?>);" style="background-color: #2196F3;">Editar</button>
                                    <button onclick="eliminarPez(<?php echo htmlspecialchars($pez->id); ?>);" style="background-color: #f44336;">Eliminar</button>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">No hay peces registrados.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </div>

    <?php
    // Liberar resultados
    // No es necesario liberar si usamos fetchData y cerramos al final
    // Cerrar conexión
    $conn->close();
    ?>
</body>

</html>
