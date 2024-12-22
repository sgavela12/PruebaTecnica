<?php

$conexion = new mysqli('localhost', 'root', 'password', 'tienda');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

//parámetro action para decidir qué hacer 
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    
    switch ($action) {
        case 'listarProductos':
            listarProductos($conexion);
            break;

        case 'agregarProducto':
            agregarProducto($conexion);
            break;

        default:
            echo json_encode(["error" => "Acción no válida"]);
    }
} else {
    echo json_encode(["error" => "No se especificó ninguna acción"]);
}


function listarProductos($conexion)
{
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $conexion->real_escape_string($_GET['search']) : '';

    // Construir consulta base con filtro de búsqueda
    $whereClause = '';
    if (!empty($search)) {
        $whereClause = "WHERE nombre LIKE '%$search%'";
    }

    // Contar el total de productos con el filtro
    $totalQuery = "SELECT COUNT(*) as total FROM productos $whereClause";
    $totalResult = $conexion->query($totalQuery);
    $totalRow = $totalResult->fetch_assoc();
    $totalProductos = $totalRow['total'];

    // Obtener los productos con límite, desplazamiento y filtro
    $query = "SELECT * FROM productos $whereClause LIMIT $limit OFFSET $offset";
    $resultado = $conexion->query($query);

    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }

    // Preparar respuesta con datos 
    $response = [
        "productos" => $productos,
        "total" => $totalProductos,
        "page" => $page,
        "limit" => $limit,
        "totalPages" => ceil($totalProductos / $limit),
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
}




function agregarProducto($conexion)
{
    $nombre = $_POST['nombre'] ?? null;
    $precio = $_POST['precio'] ?? null;
    $stock = $_POST['stock'] ?? null;

    if (!$nombre || !$precio || !$stock) {
        echo json_encode(["error" => "Faltan datos para agregar el producto"]);
        return;
    }

    $stmt = $conexion->prepare("INSERT INTO productos (nombre, precio, stock) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $nombre, $precio, $stock);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Producto agregado exitosamente"]);
    } else {
        echo json_encode(["error" => "No se pudo agregar el producto"]);
    }
}




?>
