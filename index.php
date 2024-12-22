<?php

/*
    EJERCICIO 1. PHP + SQL
    Haleteo Software S.L
*/


// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', 'password', 'tienda');


// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Función para obtener productos
function obtenerProductos($conexion)
{
    //****
    //CAMBIO- INICIALIZO EL ARRAY PRODUCTOS POR SI LA CONSULTA NO DEVUELVE PRODUCTOS SE DEVUELVA UN ARRAY VACIO
    //****
    $productos = [];

    //****
    //1 ERROR- EL CAMPO CORRECTO ES DE LA BD ES "STOCK" EN LUGAR DE "STOK"
    //****

    $query = "SELECT p.id, p.nombre, p.precio, p.stock, p.lastupdate,  (SELECT COUNT(*) FROM productos) AS total_productos FROM productos p";
    $resultado = $conexion->query($query);

    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }

    return $productos;
}

// Función para agregar un producto

//****
//CAMBIO- CAMBIO DEL NOMBRE DE LOS PARAMETROS DE LA FUNCION AGREGARPRODUCTO PARA MAYOR LEGIBILIDAD 
//****
function agregarProducto($conexion, $nombre, $precio, $stock)
{

    if (empty($nombre) || $precio < 0 || $stock < 0) {
        return "Datos inválidos. Verifica los valores ingresados.";
    }

    //****
    //2 ERROR- AL USAR PREPARE NO SE PUEDEN PASAR LAS VARIABLES DIRECTAMENTE, SE HAN DE PASAR USANDO "?" Y POSTERIORMENTE BINDEARLOS EN FUNCION DE SU TIPO.
    //****

    $stmt = $conexion->prepare("INSERT INTO productos (nombre, precio, stock) VALUES (?,?,?)");



    if (!$stmt) {
        return "Error al preparar la consulta.";
    }

    //****
    //USO BIND_PARAM EN ESTA PARTE PARA QUE PRIMERO ANALICE CON LA SENTENCIA PREPARE, POR SI EN ALGUN CASO EXISTESE
    // ALGUN ERROR QUE DIRECTAMENTE SE SALGA DE LA FUNCIÓN Y RETORNE EL ERROR.
    //****
    $stmt->bind_param("sdi", $nombre, $precio, $stock);


    if (!$stmt->execute()) {

        //****
        //3 ERROR (FALTABA UN ";" EN EL FINAL DE LA SENTENCIA)
        //****

        return "Error al ejecutar la consulta: " . $stmt->error;
    }

    return "Producto agregado exitosamente.";
}


// Función para exportar productos a CSV
function exportarProductosCSV($conexion)
{
    $productos = obtenerProductos($conexion);

    if (empty($productos)) {
        return "No hay productos para exportar.";
    }

    $archivoCSV = fopen('productos.csv', 'w');

    // Escribir la cabecera
    fputcsv($archivoCSV, ['ID', 'Nombre', 'Precio', 'Stock']);

    // Escribir los datos de los productos
    foreach ($productos as $producto) {
        fputcsv($archivoCSV, [$producto['id'], $producto['nombre'], $producto['precio'], $producto['stock']]);
    }

    fclose($archivoCSV);

    return "Productos exportados a productos.csv exitosamente.";
}

  // Función para actualizar el precio
function actualizarPrecioProducto($conexion, $id, $nuevoPrecio)
{
    if ($nuevoPrecio < 0) {
        echo "El precio no puede ser negativo.\n";
        return false;
    }

    // Preparar la consulta
    $stmt = $conexion->prepare("UPDATE productos SET precio = ?, lastupdate = CURRENT_TIMESTAMP WHERE id = ?");

    if (!$stmt) {
        echo "Error al preparar la consulta.\n";
        return false;
    }

    // Vincular parámetros
    $stmt->bind_param("di", $nuevoPrecio, $id);

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        echo "Error al ejecutar la consulta: " . $stmt->error . "\n";
        return false;
    }

    // Verificar filas afectadas
    if ($stmt->affected_rows === 0) {
        echo "No se encontró el producto con el ID especificado.\n";
        return false;
    }

    echo "Precio actualizado correctamente para el producto con ID: $id.\n";
    return true;
}

//****
//5 ERROR- CONTROL DE ERRORES POR SI NO HAY PRODUCTOS Y EVITAR EL ERROR AL ITERAR
//****
function mostrarProductos($productos) {
    if (!empty($productos)) {
        foreach ($productos as $producto) {
            echo "ID: {$producto['id']}, Nombre: {$producto['nombre']}, Precio: {$producto['precio']}, Stock: {$producto['stock']}, Lastupdate: {$producto['lastupdate']}";
        }
    } else {
        echo "No hay productos disponibles.";
    }
}




// TEST
$product_name = "Pantalones XL";

//****
//4 ERROR (PARA LOS NUMEROS CON DECIMALES SE USA EL ".")
//****
$product_price = 10.40;


$product_stock = 4;

//****
//6 ERROR- FALTABA UN ARGUMENTO EN LA LLAMADA DE LA FUNCION
//****
// agregarProducto($conexion, $product_name, $product_price, $product_stock);
// actualizarPrecioProducto($conexion,1,3);



// Mostrar productos
$productos = obtenerProductos($conexion);



//****
//CAMBIO- HE USADO UNA FUNCION PARA LISTAR PRODUCTOS PARA TENERLO DE UNA FORMA MAS ORDENADA
//****

mostrarProductos($productos);

exportarProductosCSV($conexion);

$conexion->close();

// FIN EJERCICIO 1


?>