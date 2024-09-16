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

// Consulta para obtener el número de evidencias por vencer
$sql = "SELECT COUNT(*) AS por_vencer FROM requisitos WHERE fecha_limite_cumplimiento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)";

$data = mysqli_query($conn, $sql);

if ($data) {
    $row = mysqli_fetch_assoc($data);
    echo $row['por_vencer'];
} else {
    echo "Error en la consulta: " . $conn->error;
}

$conn->close();
?>
