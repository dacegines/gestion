<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <img class="img_logo" src="../img/logo.png" alt="">
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.html">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Wizard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportes.html">Reportes</a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0 search-container">
                <input class="form-control mr-sm-2 search-input" type="search" placeholder="Buscar..." aria-label="Buscar" id="buscarInput">
                <button class="btn my-2 my-sm-0 search-button" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M23.707 22.293l-6.364-6.364A9.457 9.457 0 0 0 19 10.5C19 4.701 14.299 0 8.5 0S-2 4.701-2 10.5 2.701 21 8.5 21a9.457 9.457 0 0 0 5.429-1.657l6.364 6.364a.999.999 0 1 0 1.414-1.414zM8.5 19C4.081 19 1 15.919 1 11.5S4.081 4 8.5 4 16 7.081 16 11.5 12.919 19 8.5 19z"/>
                    </svg>
                </button>
            </form>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">Customers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Quotes / Invoices</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">A. Receivables</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">PO (Orders)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">PO (Requisition)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Requisitions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">A. Payables</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">CRM</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-10 ml-sm-auto col-lg-10 px-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h1 class="card-title">Reportes</h1>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-2">
                                <label for="desde">Desde</label>
                                <input type="date" class="form-control" id="desde" value="2023-10-01">
                            </div>
                            <div class="col-md-2">
                                <label for="hasta">Hasta</label>
                                <input type="date" class="form-control" id="hasta" value="2023-10-31">
                            </div>
                            <div class="col-md-2">
                                <label for="rangos">Rangos</label>
                                <select class="form-control" id="rangos">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="canales">Canales</label>
                                <select class="form-control" id="canales">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="frecuencia">Frecuencia</label>
                                <select class="form-control" id="frecuencia">
                                    <option value="Diario">Diario</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="grupos">Grupos</label>
                                <select class="form-control" id="grupos">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-primary w-100">Descargar</button>
                            </div>
                        </div>
                        <hr class="divider">
                        <div class="row text-center justify-content-center">
                            <div class="col-md-2 mb-3 custom-size">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <i class="fas fa-tasks fa-3x text-primary"></i>
                                        <h5 class="card-title">Obligaciones</h5>
                                        <p id="total_obligaciones" class="card-text display-4 font-weight-bold">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <i class="fas fa-comments fa-3x text-info"></i>
                                        <h5 class="card-title">Activas</h5>
                                        <p id="activas" class="card-text display-4 font-weight-bold">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <i class="fas fa-check fa-3x text-success"></i>
                                        <h5 class="card-title">Completas</h5>
                                        <p id="completas" class="card-text display-4 font-weight-bold">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <i class="fas fa-times-circle fa-3x text-danger"></i>
                                        <h5 class="card-title">Vencidas</h5>
                                        <p id="vencidas" class="card-text display-4 font-weight-bold">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <i class="fas fa-clock fa-3x text-warning"></i>
                                        <h5 class="card-title">Por vencer</h5>
                                        <p id="por_vencer" class="card-text display-4 font-weight-bold">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3 custom-size">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <i class="fas fa-chart-line fa-3x text-info"></i>
                                        <h5 class="card-title">Productividad</h5>
                                        <p id="productividad" class="card-text display-4 font-weight-bold">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="divider">
                        <div class="row" id="chartContainer">
                            <div class="col-md-6">
                                <div class="report-card">
                                    <div class="card-body">
                                        <h5>Contratos por Estatus General</h5>
                                        <canvas id="generalStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="report-card">
                                    <div class="card-body">
                                        <h5>Contratos por Estatus</h5>
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="report-card">
                                    <div class="card-body">
                                        <h5>Monto por Estatus</h5>
                                        <canvas id="statusAmountChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="report-card">
                                    <div class="card-body">
                                        <h5>Contratos por Tipo</h5>
                                        <canvas id="typeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Datos ficticios para las gráficas
        const generalStatusData = {
            labels: ['Autorizado', 'No Autorizado'],
            datasets: [{
                data: [37, 3],
                backgroundColor: ['#28a745', '#ffc107']
            }]
        };

        const statusData = {
            labels: ['Autorizado', 'En ejecución', 'Cancelado', 'Inactivo', 'Rechazado', 'Expirado', 'Concluido', 'Rescindido'],
            datasets: [{
                data: [15, 8, 4, 3, 2, 6, 1, 1],
                backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107', '#6c757d', '#ff851b', '#17a2b8', '#e83e8c']
            }]
        };

        const statusAmountData = {
            labels: ['Autorizado', 'En ejecución', 'Rechazado', 'Inactivo', 'Cancelado', 'Expirado'],
            datasets: [{
                data: [1014209.95, 120169.1, 50783, 5000, 259, 151],
                backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107', '#6c757d', '#ff851b']
            }]
        };

        const typeData = {
            labels: ['Contrato', 'Anexo'],
            datasets: [{
                data: [40, 20],
                backgroundColor: ['#17a2b8', '#343a40']
            }]
        };

        // Opciones para las gráficas
        const options = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: 'bottom'
            }
        };

        // Inicialización de las gráficas
        window.onload = function() {
            const ctxGeneralStatus = document.getElementById('generalStatusChart').getContext('2d');
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            const ctxStatusAmount = document.getElementById('statusAmountChart').getContext('2d');
            const ctxType = document.getElementById('typeChart').getContext('2d');

            new Chart(ctxGeneralStatus, {
                type: 'doughnut',
                data: generalStatusData,
                options: options
            });

            new Chart(ctxStatus, {
                type: 'pie',
                data: statusData,
                options: options
            });

            new Chart(ctxStatusAmount, {
                type: 'bar',
                data: statusAmountData,
                options: options
            });

            new Chart(ctxType, {
                type: 'line',
                data: typeData,
                options: options
            });
        };

        // Función para actualizar el texto de las tarjetas métricas
        function updateMetricCard(id, value) {
            document.getElementById(id).innerText = value;
        }

        // Ejemplo de uso

    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Función para obtener el total de obligaciones
            function obtenerTotalObligaciones(callback) {
                $.ajax({
                    url: 'obligaciones_total.php',
                    type: 'POST',
                    success: function(response) {
                        console.log("Total obligaciones:", response);
                        callback(parseInt(response));
                    },
                    error: function(error) {
                        console.error("Error al obtener el total de obligaciones:", error);
                    }
                });
            }

            // Llamar a la función para obtener el total de obligaciones
            obtenerTotalObligaciones(function(total) {
                $('#total_obligaciones').text(total);
            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Función para llenar el id="activas" con una petición AJAX a evidencias_activas.php
            $.ajax({
                url: 'evidencias_activas.php',
                type: 'POST',
                success: function(data) {
                    $('#activas').text(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', status, error);
                }
            });
        });

    </script>

    <script type="text/javascript">
        function fetchCompletas() {
            // Función para obtener el número de evidencias completas
            $.ajax({
                type: 'POST',
                url: 'evidencias_completas.php',
                success: function(data) {
                    $('#completas').text(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', status, error);
                }
            });
        }

        // Llamar a la función cuando se cargue la página
        $(document).ready(function() {
            fetchCompletas();
        });
    </script>

    <script type="text/javascript">
        function fetchVencidas() {
            // Función para obtener el número de evidencias vencidas con respecto a la fecha actual
            const fechaActual = new Date().toISOString().split('T')[0]; // Obtener la fecha actual en formato YYYY-MM-DD

            $.ajax({
                type: 'POST',
                url: 'evidencias_vencidas.php',
                data: { fecha: fechaActual },
                success: function(data) {
                    $('#vencidas').text(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', status, error);
                }
            });
        }

        // Llamar a la función cuando se cargue la página
        $(document).ready(function() {
            fetchCompletas();
            fetchVencidas();
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Obtener el número de evidencias por vencer
            function obtenerPorVencer() {
                $.ajax({
                    url: 'evidencias_por_vencer.php',
                    type: 'POST',
                    success: function(response) {
                        $('#por_vencer').text(response);
                    },
                    error: function(error) {
                        console.error("Error al obtener el número de por vencer:", error);
                    }
                });
            }

            // Llamar a la función para obtener el número de por vencer
            obtenerPorVencer();
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Función para obtener el total de evidencias
            function obtenerTotalEvidencias(callback) {
                $.ajax({
                    url: 'obligaciones_total.php',
                    type: 'POST',
                    success: function(response) {
                        console.log("Total evidencias:", response);
                        callback(parseInt(response));
                    },
                    error: function(error) {
                        console.error("Error al obtener el total de evidencias:", error);
                    }
                });
            }

            // Función para obtener el número de evidencias activas
            function obtenerActivas(callback) {
                $.ajax({
                    url: 'evidencias_activas.php',
                    type: 'POST',
                    success: function(response) {
                        console.log("Evidencias activas:", response);
                        callback(parseInt(response));
                    },
                    error: function(error) {
                        console.error("Error al obtener el número de activas:", error);
                    }
                });
            }

            // Función para obtener el número de evidencias completas
            function obtenerCompletas(callback) {
                $.ajax({
                    url: 'evidencias_completas.php',
                    type: 'POST',
                    success: function(response) {
                        console.log("Evidencias completas:", response);
                        callback(parseInt(response));
                    },
                    error: function(error) {
                        console.error("Error al obtener el número de completas:", error);
                    }
                });
            }

            // Función para obtener el número de evidencias por vencer
            function obtenerPorVencer(callback) {
                $.ajax({
                    url: 'evidencias_por_vencer.php',
                    type: 'POST',
                    success: function(response) {
                        console.log("Evidencias por vencer:", response);
                        callback(parseInt(response));
                    },
                    error: function(error) {
                        console.error("Error al obtener el número de por vencer:", error);
                    }
                });
            }

            // Función para obtener el número de evidencias vencidas
            function obtenerVencidas(callback) {
                $.ajax({
                    url: 'evidencias_vencidas.php',
                    type: 'POST',
                    success: function(response) {
                        console.log("Evidencias vencidas:", response);
                        callback(parseInt(response));
                    },
                    error: function(error) {
                        console.error("Error al obtener el número de vencidas:", error);
                    }
                });
            }

            // Función para calcular la productividad
            function calcularProductividad() {
                obtenerTotalEvidencias(function(total) {
                    obtenerCompletas(function(completas) {
                        obtenerVencidas(function(vencidas) {
                            obtenerPorVencer(function(por_vencer) {
                                if (total > 0) {
                                    let productividad = ((completas + (total - (vencidas + por_vencer))) / total) * 100;
                                    $('#productividad').text(productividad.toFixed(2) + '%');
                                } else {
                                    $('#productividad').text('N/A');
                                }
                            });
                        });
                    });
                });
            }

            // Llamar a la función para calcular la productividad
            calcularProductividad();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
