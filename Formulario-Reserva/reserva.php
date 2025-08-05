<?php
// --- 1. CONEXIÓN A LA BASE DE DATOS ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reservas";  // Base de datos "reservas"

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error . "<br>Por favor verifica:<br>
        1. Que XAMPP esté corriendo<br>
        2. Que la base de datos 'reservas' exista<br>
        3. Que el usuario y contraseña sean correctos");
}

// --- 2. OBTENER DATOS DEL FORMULARIO ---
$nombre = $_POST['name'] ?? '';  
$telefono = $_POST['telefono'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';

// --- 3. VALIDACIONES BÁSICAS ---
if (empty($nombre) || empty($telefono) || empty($departamento) || empty($fecha) || empty($hora)) {
    die("Error: Todos los campos son obligatorios. <a href='index.html'>Volver al formulario</a>");
}

// Validar formato de teléfono (10 dígitos exactos)
if (!preg_match('/^[0-9]{10}$/', $telefono)) {
    die("Error: El teléfono debe tener 10 dígitos (código de área sin 0 + número sin 15). Ejemplo: 2634318595. <a href='index.html'>Volver al formulario</a>");
}

// Validar que la fecha sea válida y futura
$fechaActual = date('Y-m-d');
if (!DateTime::createFromFormat('Y-m-d', $fecha) || $fecha < $fechaActual) {
    die("Error: La fecha debe ser válida y futura. <a href='index.html'>Volver al formulario</a>");
}

// Validar formato de hora
if (!DateTime::createFromFormat('H:i', $hora)) {
    die("Error: Formato de hora inválido. Use HH:MM. <a href='index.html'>Volver al formulario</a>");
}

// --- 4. VERIFICAR DISPONIBILIDAD ---
$sql_check = "SELECT * FROM reservas WHERE fecha = ? AND hora = ? AND departamento = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("sss", $fecha, $hora, $departamento);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    die("Error: Ya existe una reserva para ese departamento en la fecha y hora seleccionada. <a href='index.html'>Elige otro horario</a>");
}
$stmt_check->close();

// --- 5. INSERTAR DATOS EN LA BASE DE DATOS ---
$sql_insert = "INSERT INTO reservas (nombre, telefono, departamento, fecha, hora) VALUES (?, ?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("sssss", $nombre, $telefono, $departamento, $fecha, $hora);

if ($stmt_insert->execute()) {
    // Página de éxito
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Reserva Exitosa</title>
        <link rel='stylesheet' href='reserva.css'>
        <style>
            body { 
                font-family: 'Montserrat', sans-serif; 
                text-align: center; 
                padding: 50px; 
                background-color: #f5f7fa; 
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .success-container {
                background-color: #ffffff;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.07);
                max-width: 600px;
                width: 100%;
            }
            .success { 
                color: #4CAF50; 
                font-size: 24px; 
                margin-bottom: 20px; 
            }
            .details { 
                margin: 20px auto; 
                max-width: 500px; 
                text-align: left; 
                padding: 30px; 
                background: #f8fafc;
                border-radius: 10px;
                border: 1px solid #dce4e8;
            }
            .btn { 
                display: inline-block; 
                margin-top: 20px; 
                padding: 12px 24px; 
                background: #4285F4; 
                color: white; 
                text-decoration: none; 
                border-radius: 6px;
                font-weight: 600;
                transition: background-color 0.3s;
                border: none;
                cursor: pointer;
                font-size: 1rem;
            }
            .btn:hover {
                background-color: #3367d6;
            }
        </style>
    </head>
    <body>
        <div class='success-container'>
            <div class='success'>¡Reserva Registrada Exitosamente!</div>
            <div class='details'>
                <p><strong>Nombre:</strong> $nombre</p>
                <p><strong>Teléfono:</strong> $telefono</p>
                <p><strong>Departamento:</strong> $departamento</p>
                <p><strong>Fecha:</strong> $fecha</p>
                <p><strong>Hora:</strong> $hora</p>
            </div>
            <a href='index.html' class='btn'>Volver al formulario</a>
        </div>
    </body>
    </html>";
} else {
    echo "Error al registrar la reserva: " . $stmt_insert->error;
}

$stmt_insert->close();
$conn->close();
?>