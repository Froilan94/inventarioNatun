<?php
require "config/db.php";

$sql = "SELECT id_unidad_med, nombre_unidad 
        FROM unidades_medida_med 
        ORDER BY nombre_unidad ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'. $row["id_unidad_med"] .'">'. $row["nombre_unidad"] .'</option>';
    }
} else {
    echo '<option value="">No hay unidades</option>';
}
?>
