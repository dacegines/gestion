<!-- resources/views/emails/account_creation_request.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Creación de Cuenta</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        h2 { color: #333; font-size: 20px; margin-bottom: 10px; }
        p { font-size: 16px; line-height: 1.5; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 10px; font-size: 16px; }
        strong { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Solicitud de Creación de Cuenta</h2>
    <p>Se ha recibido una solicitud para crear una cuenta con la siguiente información:</p>
    <ul>
        <li><strong>Nombre Completo:</strong> {{ $name }}</li>
        <li><strong>Puesto:</strong> {{ $puesto }}</li>
        <li><strong>Correo Electrónico:</strong> {{ $email }}</li>
    </ul>
    <p>Por favor, proceda con la verificación y creación de la cuenta según los procesos internos.</p>
</body>
</html>
