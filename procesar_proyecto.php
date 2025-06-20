<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repositorio_usuarios";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir datos del formulario
$nombre_proyecto = $_POST['nombre_proyecto'];
$carrera = $_POST['carrera'];
$fecha_publicacion = $_POST['fecha_publicacion'];
$nombre_propietario = $_POST['nombre_propietario'];
$numero_control = $_POST['numero_control'];
$descripcion_proyecto = $_POST['descripcion_proyecto'];
$palabras_clave = $_POST['palabras_clave'];

// Manejo del archivo PDF
if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] === 0) {
    $archivo = $_FILES['archivo_pdf'];
    $nombreArchivo = $archivo['name'];
    $tipoArchivo = $archivo['type'];
    $tamanoArchivo = $archivo['size'];
    $rutaTemporal = $archivo['tmp_name'];

    // Validar tipo MIME PDF
    if ($tipoArchivo != "application/pdf") {
        die("Error: Solo se permiten archivos PDF.");
    }

    // Validar tamaño máximo 5MB (opcional)
    if ($tamanoArchivo > 5 * 1024 * 1024) {
        die("Error: El archivo es demasiado grande. Máximo 5MB.");
    }

    // Carpeta donde se guardarán los archivos
    $carpetaDestino = "uploads/";
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    // Crear nombre único para el archivo (para evitar sobreescritura)
    $nuevoNombreArchivo = uniqid() . "-" . basename($nombreArchivo);
    $rutaDestino = $carpetaDestino . $nuevoNombreArchivo;

    // Mover archivo a carpeta destino
    if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
        // Insertar datos en la BD
        $stmt = $conn->prepare("INSERT INTO proyectos (nombre_proyecto, carrera, fecha_publicacion, nombre_propietario, numero_control, descripcion_proyecto, palabras_clave, ruta_pdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $nombre_proyecto, $carrera, $fecha_publicacion, $nombre_propietario, $numero_control, $descripcion_proyecto, $palabras_clave, $rutaDestino);

        if ($stmt->execute()) {
            echo "Proyecto subido correctamente.";
            
            // Aquí puedes redirigir o mostrar un mensaje bonito
        } else {
            echo "Error al guardar en la base de datos: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error al mover el archivo.";
    }
} else {
    echo "Error al subir el archivo.";
}

$conn->close();
?>
