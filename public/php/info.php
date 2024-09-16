<?php

// Mostrar errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_reporting(0);
$servidor= "localhost";
$usuariodb= "root";
$passdb= "";

$clavedb= "gestion_obligaciones";

$conn = new mysqli($servidor, $usuariodb, $passdb, $clavedb);

mysqli_set_charset($conn, 'utf8');

$evidencia = $_POST['evidencia'];
$requisito = $_POST['requisito'];

$respuesta = "";

// Función para convertir el número de mes a nombre de mes en español
function nombreMes($numeroMes) {
    $meses = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    ];
    return $meses[$numeroMes] ?? 'Mes no válido';
}

// Consulta para obtener los detalles de la evidencia
$sql = "SELECT
    a.numero_evidencia AS numero_evidencia,
    a.evidencia AS evidencia,
    a.numero_requisito AS numero_requisito, 
    a.periodicidad AS periodicidad,
    a.responsable AS responsable,
    a.clausula_condicionante_articulo AS clausula,
    GROUP_CONCAT(DISTINCT a.fecha_limite_cumplimiento ORDER BY a.fecha_limite_cumplimiento SEPARATOR ', ') AS fechas,
    a.origen_obligacion AS origen_obligacion,
    a.clausula_condicionante_articulo AS clausula
FROM requisitos a
WHERE a.numero_requisito = $requisito
AND a.numero_evidencia = $evidencia 
GROUP BY a.numero_evidencia, a.evidencia, a.numero_requisito, a.periodicidad, a.responsable, a.origen_obligacion, a.clausula_condicionante_articulo;";

$data = mysqli_query($conn, $sql);

if (!$data) {
    die('Error en la consulta: ' . mysqli_error($conn));
}

// Consulta para obtener las notificaciones
$sql1 = "SELECT DISTINCT nombre FROM notificaciones WHERE requsito_id = $requisito AND numero_evidencia = $evidencia";
$data1 = mysqli_query($conn, $sql1);

$notificaciones = "";
while ($row1 = mysqli_fetch_array($data1)) {
    $notificaciones .= '<p><font size="2" color=""><b>' . htmlspecialchars($row1['nombre']) . '</b></font></p>';
}

// Consulta para obtener tabla notificaciones
$sql2 = "SELECT nombre, tipo_notificacion FROM notificaciones WHERE requsito_id = $requisito AND numero_evidencia = $evidencia";
$data2 = mysqli_query($conn, $sql2);

$tNotificaciones = "";
while ($row2 = mysqli_fetch_array($data2)) {
    $dias = '';
    $tipoNotificacion = '';
    $estilo = '';

    switch ($row2['tipo_notificacion']) {
        case 'primera_notificacion':
            $dias = '30 días antes de la fecha de vencimiento';
            $tipoNotificacion = '1era Notificación';
            $estilo = 'style="background-color: #90ee90; color: black; font-size: 11px;"';  // Suavizando el verde
            break;
        case 'segunda_notificacion':
            $dias = '15 días antes de la fecha de vencimiento';
            $tipoNotificacion = '2da Notificación';
            $estilo = 'style="background-color: #ffff99; color: black; font-size: 11px;"';  // Suavizando el amarillo
            break;
        case 'tercera_notificacion':
            $dias = '5 días antes de la fecha de vencimiento';
            $tipoNotificacion = '3era Notificación';
            $estilo = 'style="background-color: #ffcc99; color: black; font-size: 11px;"';  // Suavizando el naranja
            break;
        case 'notificacion_carga_vobo':
            $dias = 'Inmediato antes de la fecha de vencimiento';
            $tipoNotificacion = '4ta Notificación';
            $estilo = 'style="background-color: #ff9999; color: black; font-size: 11px;"';  // Suavizando el rojo
            break;
    }

    $tNotificaciones .= '<tr><td style="text-align: center;"><font style="font-size: 11px;"><b>' . htmlspecialchars($row2['nombre']) . '</b></font></td><td style="text-align: center;"><font style="font-size: 11px;"><b>' . $tipoNotificacion . '</b></font></td><td ' . $estilo . ' style="text-align: center;"><font style="font-size: 11px;"><b>' . $dias . '</b></font></td></tr>';
}

while ($row = mysqli_fetch_array($data)) {
    $fechas = explode(', ', $row['fechas']);
    $fechas_formateadas = [];
    
    $tabla_fechas = '';

    foreach ($fechas as $fecha) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', trim($fecha));
        if ($fecha_obj) {
            $dia = $fecha_obj->format('d');
            $mes = nombreMes($fecha_obj->format('m'));
            $anio = $fecha_obj->format('Y');
            $fecha_formateada = "$dia de $mes de $anio";
            $fechas_formateadas[] = $fecha_formateada;


            // Crear la barra de progreso
            $porcentaje_cumplimiento = 75; // Este valor puede ser dinámico según tus necesidades
            $barra_progreso = "
            <div class='progress' style='height: 20px; background-color: #f2f2f2;'>
                <div class='progress-bar bg-info' role='progressbar' style='width: $porcentaje_cumplimiento%;' aria-valuenow='$porcentaje_cumplimiento' aria-valuemin='0' aria-valuemax='100'>$porcentaje_cumplimiento%</div>
            </div>";

            // Añadir filas a la tabla
            $tabla_fechas .= "<tr><td style='text-align: center; padding: 2px;'>$fecha_formateada</td><td style='text-align: center; padding: 2px;'></td><td style='text-align: center; padding: 2px;'>$barra_progreso</td></tr>";
        } else {
            $fechas_formateadas[] = 'Fecha no válida';
            $tabla_fechas .= "<tr><td style='text-align: center; padding: 2px;'>Fecha no válida</td><td style='text-align: center; padding: 2px;'></td><td style='text-align: center; padding: 2px;'>0%</td></tr>";
        }
    }

    // Añadir el total de avance
    $total_avance = "<tr><th colspan='2' style='text-align: left; padding: 4px; background-color: #343a40; color: white;'>Total de avance:</th><td style='text-align: center; padding: 2px;'>100%</td></tr>";

    $tabla_fechas .= $total_avance;

    $fechas_formateadas_str = implode('<br>', $fechas_formateadas);

    $respuesta .= '
    <div class="details-card " id="detallesEvidencia">
        <div class="header">
            <h5>' . htmlspecialchars($row['numero_evidencia']) . " " . htmlspecialchars($row['evidencia']) . '</h5>
        </div>
        <hr>
        <div class="info-section">
            <div class="row">
                <div class="col-md-12">
                    <div class="details-card">
                        <div class="info-section">
                            <div class="logo-container">
                                <img src="img/superva_poniente_logo.jpg" alt="Logo" class="logo">
                            </div>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-calendar"></i>
                                <span>Periodicidad:</span>
                            </div>
                            <p><font size="2" color=""><b>' . htmlspecialchars($row['periodicidad']) . '</b></font></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-user"></i>
                                <span>Responsable:</span>
                            </div>
                            <p><font size="2" color=""><b>' . htmlspecialchars($row['responsable']) . '</b></font></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Fechas límite de cumplimiento:</span>
                            </div>
                            <div id="fechas-limite-contenedor"></div> <!-- Contenedor para la tabla de fechas límite -->
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-file-alt"></i>
                                <span>Origen de la obligación:</span>
                            </div>
                            <p><font size="2" color=""><b>' . htmlspecialchars($row['origen_obligacion']) . '</b></font></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-book"></i>
                                <span>Cláusula, condicionante, o artículo:</span>
                            </div>
                            <p><font size="2" color=""><b>' . htmlspecialchars($row['clausula']) . '</b></font></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-file-pdf"></i>
                                <span>Archivo adjunto:</span>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-file-pdf fa-2x"></i>
                            </div>
                            <!-- Aquí se añaden las notificaciones -->
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-bell"></i>
                                <span>Notificaciones:</span>
                            </div>
                            ' . $notificaciones . '
                        </div>
                    </div>
                    <!-- Nuevo recuadro para agregar archivo 
                    <div class="details-card upload-card">
                        <div class="info-section">
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-file-upload"></i>
                                <span>Agregar Archivo:</span>
                            </div>
                            <form id="uploadForm" class="p-3" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="fileInput">Seleccione un archivo</label>
                                    <input type="file" class="form-control-file" id="fileInput" name="fileInput">
                                </div>
                                <button type="submit" class="btn btn-primary">Subir Archivo</button>
                            </form>
                        </div>
                    </div> -->
                    <!-- Contenedor con desbordamiento horizontal -->
                    <div class="table-responsive mt-2">
                        <table class="styled-table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Notificación</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                ' . $tNotificaciones . '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

echo $respuesta;

?>
<script>
    $(document).ready(function() {
        // Cargar la tabla de fechas límite
        $.ajax({
            url: 'fecha_limite_tabla.php',
            type: 'POST',
            data: {
                evidencia: '<?php echo $evidencia; ?>',
                requisito: '<?php echo $requisito; ?>'
            },
            success: function(response) {
                $('#fechas-limite-contenedor').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            }
        });
    });
</script>



