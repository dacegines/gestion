$(document).ready(function() {
    // Inicializa la tabla con DataTables
    let table = $('#detallesTable').DataTable({
        "language": {
            "lengthMenu": "Mostrar " +
                `<select class="custom-select custom-select-sm form-control form-control-sm" style="font-size: 15px;">
                    <option value='10'>10</option>
                    <option value='25'>25</option>
                    <option value='50'>50</option>
                    <option value='100'>100</option>
                    <option value='-1'>Todo</option>
                </select>` +
                " registros por página",
            "zeroRecords": "No se encontró ningún registro",
            "info": "Mostrando la página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            'search': 'Buscar:',
            'paginate': {
                'next': 'Siguiente',
                'previous': 'Anterior'
            }
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "dom": '<"top"Bfl>rt<"bottom"ip><"clear">', // Cambiar la disposición para alinear en una línea
        "buttons": [
            {
                extend: 'excelHtml5',
                text: 'Exportar a Excel',
                className: 'btn btn-success'
            }
        ],
        "lengthMenu": [[-1, 10, 25, 50, 100], ['Todo', 10, 25, 50, 100]], // Configurar menú de longitud de página
        "pageLength": -1 // Preseleccionar opción "Todo"
    });

    // Filtrado personalizado por rango de fechas
    $('#filter-date-btn').on('click', function() {
        let startDate = $('#start-date').val();
        let endDate = $('#end-date').val();
        
        if (startDate && endDate) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let fechaCumplimiento = data[6]; // Índice de la columna "Fecha límite de cumplimiento"
                let date = new Date(fechaCumplimiento.split(' ').join('T')); // Conversión de la fecha
                let start = new Date(startDate);
                let end = new Date(endDate);

                return (date >= start && date <= end);
            });
            table.draw();
            $.fn.dataTable.ext.search.pop();
        } else {
            alert('Por favor seleccione ambas fechas de inicio y fin.');
        }
    });
});
