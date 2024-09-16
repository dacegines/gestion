<?php

// Configuración de la base de datos
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

// Consulta para obtener el número de evidencias activas
$sql = "SELECT COUNT(*) AS activas FROM requisitos WHERE fecha_limite_cumplimiento >= NOW()";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Devolver el número de evidencias activas
echo $row['activas'];

$conn->close();
?>