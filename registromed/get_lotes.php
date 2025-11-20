<?php
require "config/db.php";

$sql = "SELECT id_lote_med, medicamento_id, numero_lote, fecha_vencimiento 
        FROM lotes_med 
        WHERE cantidad_actual > 0
        ORDER BY numero_lote ASC";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $label = $row["numero_lote"] . " (Vence: " . $row["fecha_vencimiento"] . ")";

        echo '<option value="'. $row["id_lote_med"] .'">'. $label .'</option>';
    }
} else {
    echo '<option value="">No hay lotes disponibles</option>';
}
?>
