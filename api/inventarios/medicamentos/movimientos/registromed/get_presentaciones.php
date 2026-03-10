<?php
require "config/db.php";

$sql = "SELECT id_presentacion_med, nombre_presentacion 
        FROM presentaciones_med 
        ORDER BY nombre_presentacion ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'. $row["id_presentacion_med"] .'">'. $row["nombre_presentacion"] .'</option>';
    }
} else {
    echo '<option value="">No hay presentaciones</option>';
}
?>
