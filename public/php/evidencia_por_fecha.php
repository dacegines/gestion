<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('conexionbd.php');

// Obtener los parámetros de la solicitud POST
$evidencia = isset($_POST['evidencia']) ? mysqli_real_escape_string($conn, $_POST['evidencia']) : '';
$requisito = isset($_POST['requisito']) ? mysqli_real_escape_string($conn, $_POST['requisito']) : '';
$fecha_limite = isset($_POST['fecha_limite']) ? mysqli_real_escape_string($conn, $_POST['fecha_limite']) : '';




// Arreglo para mapear números de mes a nombres en español
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

// Consulta para obtener los detalles de la evidencia
$sql = "SELECT fecha_limite_cumplimiento FROM requisitos 
WHERE numero_requisito = '$requisito'
AND numero_evidencia = '$evidencia'
ORDER BY fecha_limite_cumplimiento ASC";

$data = mysqli_query($conn, $sql);

$respuesta = "";

while ($row = mysqli_fetch_array($data)) {
    $fecha = new DateTime($row['fecha_limite_cumplimiento']);
    $dia = $fecha->format('d');
    $mes = $fecha->format('m');
    $anio = $fecha->format('Y');
    $nombre_mes = $meses[$mes];

    $fecha_limite = "$dia de $nombre_mes de $anio"; // Formatear la fecha
    $respuesta .= '
        <div class="card derivation-card" data-toggle="modal" data-target="#fechaModal" data-requisito="' . $requisito . '" data-evidencia="' . $evidencia . '" data-fecha="' . $row['fecha_limite_cumplimiento'] . '">
            <div class="card-body">
                <strong class="n_evidencia"></strong> ' . $fecha_limite . '
            </div>
        </div>';
}

echo $respuesta;

?>
