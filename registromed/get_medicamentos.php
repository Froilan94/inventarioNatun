<?php
require "config/db.php";

$sql = "SELECT id_medicamento, nombre_comercial 
        FROM medicamentos 
        WHERE activo = 1
        ORDER BY nombre_comercial ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'. $row["id_medicamento"] .'">'. $row["nombre_comercial"] .'</option>';
    }
} else {
    echo '<option value="">No hay medicamentos</option>';
}
?>
