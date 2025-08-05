<?php
// --- CONEXIÓN A LA BASE DE DATOS ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registro_usuarios";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// --- PROCESAMIENTO DEL ARCHIVO ---

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Obtener el título del documento desde el formulario
    $titulo = $_POST['file_title'];

    // 2. Definir la carpeta de destino para los archivos
    $target_dir = "uploads/";

    // 3. Crear un nombre de archivo único para evitar sobreescribir archivos existentes
    // Se combina un ID único con el nombre original del archivo
    $original_filename = basename($_FILES["document"]["name"]);
    $unique_filename = uniqid() . '_' . $original_filename;
    $target_file = $target_dir . $unique_filename;

    // 4. Mover el archivo subido del directorio temporal al directorio final
    if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
        
        // 5. Si el archivo se movió con éxito, insertar su información en la base de datos
        $sql = "INSERT INTO documentos (titulo, nombre_archivo) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        
        // "ss" significa que estamos pasando dos variables de tipo string
        $stmt->bind_param("ss", $titulo, $unique_filename);

        if ($stmt->execute()) {
            echo "FELICIDADES >> El archivo ". htmlspecialchars($original_filename). " ha sido subido y registrado exitosamente.";
            echo "<br><a href='index.html'>Subir otro archivo</a>";
        } else {
            echo "Error: El archivo se subió pero no se pudo registrar en la base de datos: " . $stmt->error;
        }
        $stmt->close();

    } else {
        echo "Hubo un error al subir tu archivo.";
    }
}

$conn->close();
?>
