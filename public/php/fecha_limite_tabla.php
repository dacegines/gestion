<?php

// Mostrar errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('conexionbd.php');

$evidencia = $_POST['evidencia'];
$requisito = $_POST['requisito'];

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
    approved,
    porcentaje,
    avance,
    GROUP_CONCAT(DISTINCT fecha_limite_cumplimiento ORDER BY fecha_limite_cumplimiento SEPARATOR ', ') fechas
FROM requisitos
WHERE numero_requisito = $requisito
AND numero_evidencia = $evidencia
GROUP BY approved, porcentaje, avance
ORDER BY fecha_limite_cumplimiento ASC;";

$data = mysqli_query($conn, $sql);

if (!$data) {
    die('Error en la consulta: ' . mysqli_error($conn));
}

$tabla_fechas = '';

while ($row = mysqli_fetch_array($data)) {
    $fechas = explode(', ', $row['fechas']);
    $approved = $row['approved'];
    $porcentaje = $row['porcentaje'];

    foreach ($fechas as $fecha) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', trim($fecha));
        if ($fecha_obj) {
            $dia = $fecha_obj->format('d');
            $mes = nombreMes($fecha_obj->format('m'));
            $anio = $fecha_obj->format('Y');
            $fecha_formateada = "$dia de $mes de $anio";
            
            // Determinar el valor de "Cumplido"
            $cumplido = ($approved == 1) ? '<span style="color: green;">&#10004;</span>' : '';

            // Crear la barra de progreso
            $porcentaje_cumplimiento = $porcentaje; // Este valor puede ser dinámico según tus necesidades
            $barra_progreso = "
            <div class='progress' style='height: 20px; background-color: #f2f2f2;'>
                <div class='progress-bar bg-success' role='progressbar' style='width: $porcentaje_cumplimiento%;' aria-valuenow='$porcentaje_cumplimiento' aria-valuemin='0' aria-valuemax='100'>$porcentaje_cumplimiento%</div>
            </div>";

            // Añadir filas a la tabla
            $tabla_fechas .= "<tr><td style='text-align: center; padding: 2px;'>$fecha_formateada</td><td style='text-align: center; padding: 2px;'>$cumplido</td><td style='text-align: center; padding: 2px;'>$barra_progreso</td></tr>";
        } else {
            $tabla_fechas .= "<tr><td style='text-align: center; padding: 2px;'>Fecha no válida</td><td style='text-align: center; padding: 2px;'></td><td style='text-align: center; padding: 2px;'>0%</td></tr>";
        }
    }
}

// Añadir el contenedor para la suma de avances
$tabla_fechas .= "<tr><th colspan='2' style='text-align: left; padding: 4px; background-color: #343a40; color: white;'>Total de avance:</th><td style='text-align: center; padding: 2px;'><div id='suma-avance'></div></td></tr>";

echo "
<div class='table-responsive mt-2' style='max-width: 75%;'>
    <table class='table table-sm table-bordered'>
        <thead class='thead-dark'>
            <tr>
                <th style='text-align: center; padding: 4px;'>Fecha Límite</th>
                <th style='text-align: center; padding: 4px;'>Cumplido</th>
                <th style='text-align: center; padding: 4px;'>% de Cumplimiento</th>
            </tr>
        </thead>
        <tbody>
            $tabla_fechas
        </tbody>
    </table>
</div>";

mysqli_close($conn);
?>

<script>
$(document).ready(function() {
    // Cargar la suma de avance
    $.ajax({
        url: 'suma_avance.php',
        type: 'POST',
        data: {
            evidencia: '<?php echo $evidencia; ?>',
            requisito: '<?php echo $requisito; ?>'
        },
        success: function(response) {
            var totalAvance = parseFloat(response);
            var progressClass = 'bg-danger'; // Default color
            if (totalAvance >= 100) {
                progressClass = 'bg-success';
            } else if (totalAvance >= 51) {
                progressClass = 'bg-warning';
            } else if (totalAvance >= 31) {
                progressClass = 'bg-info';
            } else if (totalAvance >= 1) {
                progressClass = 'bg-danger';
            }
            $('#suma-avance').html(`
                <div class='progress' style='height: 20px; background-color: #f2f2f2;'>
                    <div class='progress-bar ${progressClass}' role='progressbar' style='width: ${totalAvance}%;' aria-valuenow='${totalAvance}' aria-valuemin='0' aria-valuemax='100'>${totalAvance}%</div>
                </div>
            `);
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener la suma de avances:', status, error);
        }
    });
});
</script>

<script>
$(document).ready(function() {
    // Cargar la suma de avance
    $.ajax({
        url: 'suma_avance.php',
        type: 'POST',
        data: {
            evidencia: '<?php echo $evidencia; ?>',
            requisito: '<?php echo $requisito; ?>'
        },
        success: function(response) {
            var totalAvance = parseFloat(response);
            var progressClass = 'bg-danger'; // Default color
            if (totalAvance >= 100) {
                progressClass = 'bg-success';
            } else if (totalAvance >= 51) {
                progressClass = 'bg-warning';
            } else if (totalAvance >= 31) {
                progressClass = 'bg-info';
            } else if (totalAvance >= 1) {
                progressClass = 'bg-danger';
            }
            $('#suma-avance').html(`
                <div class='progress' style='height: 20px; background-color: #f2f2f2;'>
                    <div class='progress-bar ${progressClass}' role='progressbar' style='width: ${totalAvance}%;' aria-valuenow='${totalAvance}' aria-valuemin='0' aria-valuemax='100'>${totalAvance}%</div>
                </div>
            `);
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener la suma de avances:', status, error);
        }
    });
});
</script>