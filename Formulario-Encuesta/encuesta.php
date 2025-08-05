<?php
// --- 1. CONEXIÓN A LA BASE DE DATOS ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "encuesta";  

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error . "<br>Por favor verifica:<br>
        1. Que XAMPP esté corriendo<br>
        2. Que la base de datos 'encuesta' exista<br>
        3. Que el usuario y contraseña sean correctos");
}

// --- 2. OBTENER DATOS DEL FORMULARIO ---
$rating = $_POST['rating'] ?? 0;
$aspectos = isset($_POST['aspectos']) ? implode(", ", $_POST['aspectos']) : '';
$sugerencias = $_POST['sugerencias'] ?? '';
$email = $_POST['email'] ?? '';

// --- 3. VALIDACIONES BÁSICAS ---
if (empty($rating) || $rating < 1 || $rating > 5) {
    die("Error: La calificación es obligatoria (1-5 estrellas). <a href='index.html'>Volver al formulario</a>");
}

// Validar email si fue proporcionado
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: El formato de email no es válido. <a href='index.html'>Volver al formulario</a>");
}

// --- 4. INSERTAR DATOS EN LA BASE DE DATOS ---
$sql_insert = "INSERT INTO opiniones (rating, aspectos, sugerencias, email) VALUES (?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("isss", $rating, $aspectos, $sugerencias, $email);

if ($stmt_insert->execute()) {
    // Página de éxito
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Opinión Registrada</title>
        <link rel='stylesheet' href='opinion.css'>
        <style>
            body { 
                font-family: 'Poppins', sans-serif; 
                text-align: center; 
                padding: 50px; 
                background-color: #f0f2ff; 
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .success-container {
                background-color: #ffffff;
                padding: 40px;
                border-radius: 24px;
                box-shadow: 0 15px 40px rgba(106, 53, 255, 0.15);
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
                background: #f8f7ff;
                border-radius: 10px;
                border: 1px solid #e6e5ff;
            }
            .btn { 
                display: inline-block; 
                margin-top: 20px; 
                padding: 12px 24px; 
                background: #6a35ff; 
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
                background-color: #4a00d4;
            }
            .stars {
                color: #ffd166;
                font-size: 1.5rem;
                margin: 10px 0;
            }
        </style>
    </head>
    <body>
        <div class='success-container'>
            <div class='success'>¡Gracias por tu opinión!</div>
            <div class='stars'>".str_repeat('★', $rating).str_repeat('☆', 5 - $rating)."</div>
            <div class='details'>
                <p><strong>Aspectos destacados:</strong> ".($aspectos ?: 'Ninguno seleccionado')."</p>
                <p><strong>Sugerencias:</strong> ".($sugerencias ?: 'Ninguna proporcionada')."</p>
                ".($email ? "<p><strong>Email:</strong> $email</p>" : "")."
            </div>
            <a href='index.html' class='btn'>Volver al sitio</a>
        </div>
    </body>
    </html>";
} else {
    echo "Error al registrar la opinión: " . $stmt_insert->error;
}

$stmt_insert->close();
$conn->close();
?>