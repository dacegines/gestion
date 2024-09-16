<?php

// error_reporting(0);
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

// Consulta para obtener el número de evidencias completas
$sql = "SELECT COUNT(*) AS completas FROM requisitos WHERE porcentaje = 100";

$data = mysqli_query($conn, $sql);

if ($data) {
    $row = mysqli_fetch_assoc($data);
    echo $row['completas'];
} else {
    echo "Error en la consulta: " . $conn->error;
}

$conn->close();
?>
