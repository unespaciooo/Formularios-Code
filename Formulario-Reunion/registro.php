<?php
// --- 1. CONEXIÓN A LA BASE DE DATOS ---
$servername = "localhost"; // Generalmente es "localhost" en XAMPP
$username = "root";        // Usuario por defecto de MySQL en XAMPP
$password = "";            // Contraseña por defecto está vacía
$dbname = "registro_usuarios"; // El nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hay errores de conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// --- 2. OBTENER DATOS DEL FORMULARIO ---
$user = $_POST['username'];
$email = $_POST['email'];
$pass = $_POST['password'];
$confirm_pass = $_POST['confirm_password'];

// --- 3. VALIDACIONES BÁSICAS ---

// Validar que las contraseñas coinciden
if ($pass !== $confirm_pass) {
    die("Error: Las contraseñas no coinciden. <a href='index.html'>Volver a intentar</a>");
}

// Validar que el email no esté ya registrado
$sql_email = "SELECT * FROM usuarios WHERE email = ?";
$stmt_email = $conn->prepare($sql_email);
$stmt_email->bind_param("s", $email);
$stmt_email->execute();
$result_email = $stmt_email->get_result();

if ($result_email->num_rows > 0) {
    die("Error: El correo electrónico ya está registrado. <a href='index.html'>Intenta con otro</a>");
}
$stmt_email->close();

// Validar que el nombre de usuario no esté ya registrado
$sql_user = "SELECT * FROM usuarios WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    die("Error: El nombre de usuario ya existe. <a href='index.html'>Elige otro</a>");
}
$stmt_user->close();


// --- 4. HASHEAR LA CONTRASEÑA (¡MUY IMPORTANTE!) ---
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);


// --- 5. INSERTAR DATOS EN LA BASE DE DATOS ---
// Se usa una "sentencia preparada" para evitar inyecciones SQL
$sql_insert = "INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)";

// Preparar la sentencia
$stmt_insert = $conn->prepare($sql_insert);

// Vincular los parámetros ("sss" significa que son tres strings)
$stmt_insert->bind_param("sss", $user, $email, $hashed_password);

// Ejecutar la sentencia y verificar el resultado
if ($stmt_insert->execute()) {
    echo "¡Registro exitoso! Ya puedes iniciar sesión. <a href='index.html'>Volver</a>";
} else {
    echo "Error: " . $stmt_insert->error;
}

// --- 6. CERRAR CONEXIONES ---
$stmt_insert->close();
$conn->close();

?>
