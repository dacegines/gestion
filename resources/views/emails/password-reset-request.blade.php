<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Recuperación de Contraseña</title>
</head>
<body>
    <h2>Solicitud de Recuperación de Contraseña</h2>
    <p>Se ha recibido una solicitud para recuperar la contraseña del siguiente usuario:</p>
    <ul>
        <li><strong>Nombre:</strong> {{ $name }}</li>
        <li><strong>Puesto:</strong> {{ $puesto }}</li>
        <li><strong>Correo Electrónico:</strong> {{ $email }}</li>
    </ul>
    <p>Por favor, proceda con las instrucciones para la recuperación de la contraseña.</p>
</body>
</html>
