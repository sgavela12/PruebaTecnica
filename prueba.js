const url = 'http://localhost/prueba/api.php?action=listarProductos';

// Función para cargar productos 
function cargarProductos(page = 1, search = '') {
    $.ajax({
        url: url,
        type: 'GET',
        data: { page: page, limit: 10, search: search },
        dataType: 'json',
        success: function (data) {
            console.log('Datos obtenidos:', data);

            const tablaBody = $('#tablaProductos tbody');
            tablaBody.empty();

            // Mostrar los productos obtenidos
            if (data.productos.length > 0) {
                data.productos.forEach(producto => {
                    const fila = `
                        <tr>
                            <td>${producto.id}</td>
                            <td>${producto.nombre}</td>
                            <td>${producto.precio}</td>
                            <td>${producto.stock}</td>
                            <td>${producto.lastupdate}</td>
                        </tr>
                    `;
                    tablaBody.append(fila);
                });
            } else {
                // Mostrar un mensaje si no se encontraron resultados
                tablaBody.append(`
                    <tr>
                        <td colspan="5" class="text-center">No se encontraron productos</td>
                    </tr>
                `);
            }

            // Actualizar la paginación
            actualizarPaginacion(data.page, data.totalPages, search);
        },
        error: function (error) {
            console.error('Error al obtener los datos:', error);
        }
    });
}

// Función para actualizar los controles de paginación
function actualizarPaginacion(paginaActual, totalPaginas, search) {
    const paginacion = $('#paginacion');
    paginacion.empty();

    for (let i = 1; i <= totalPaginas; i++) {
        const claseActivo = i === paginaActual ? 'active' : '';
        const boton = `
            <li class="page-item ${claseActivo}">
                <a class="page-link" href="#" onclick="cargarProductos(${i}, '${search}')">${i}</a>
            </li>
        `;
        paginacion.append(boton);
    }
}

// Función para manejar la búsqueda
function realizarBusqueda() {
    const searchInput = $('#searchInput').val(); // Obtener el texto de búsqueda
    cargarProductos(1, searchInput); // Cargar productos desde la primera página con el término de búsqueda
}


// Render inicial al cargar la página
$(document).ready(() => {
    cargarProductos();
});
