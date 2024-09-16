<?php

// Configuración de la conexión a la base de datos
$servidor = "localhost";
$usuariodb = "root";
$passdb = "0000";
$clavedb = "gestion";

$conn = new mysqli($servidor, $usuariodb, $passdb, $clavedb);

mysqli_set_charset($conn, 'utf8');

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener la fecha actual enviada por AJAX
$fecha_actual = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');

// Consulta para obtener el número de evidencias con fecha límite de cumplimiento menor a la fecha actual
$sql = "SELECT COUNT(*) AS vencidas FROM requisitos WHERE fecha_limite_cumplimiento < '$fecha_actual'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo $row['vencidas'];
} else {
    echo 0;
}

$conn->close();
?>
