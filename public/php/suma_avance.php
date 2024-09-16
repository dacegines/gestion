<?php

// Mostrar errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('conexionbd.php');

$evidencia = $_POST['evidencia'];
$requisito = $_POST['requisito'];

// Consulta para sumar los valores de la columna "avance" y redondear a 2 dígitos decimales
$sql = "SELECT ROUND(SUM(avance), 2) AS total_avance
        FROM requisitos
        WHERE numero_requisito = $requisito
        AND numero_evidencia = $evidencia";

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_avance = $row['total_avance'];
    echo ($total_avance == 100) ? '100' : $total_avance;
} else {
    echo "Error en la consulta: " . mysqli_error($conn);
}

mysqli_close($conn);

?>
