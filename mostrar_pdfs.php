<?php
include("conexion.php");

$sql = "SELECT titulo, descripcion, archivo, fecha FROM proyectos ORDER BY fecha DESC LIMIT 6";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        echo '
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">'.htmlspecialchars($row["titulo"]).'</h5>
                    <p class="card-text">'.htmlspecialchars($row["descripcion"]).'</p>
                    <p><strong>Fecha:</strong> '.date("d/m/Y", strtotime($row["fecha"])).'</p>
                    <a href="uploads/'.htmlspecialchars($row["archivo"]).'" target="_blank" class="btn btn-primary">Ver PDF</a>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<p class="text-center">No hay proyectos disponibles.</p>';
}

$conexion->close();
?>
