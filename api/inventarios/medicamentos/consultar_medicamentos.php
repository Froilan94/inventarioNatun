<?php
include "../../../config/db.php";

$sql = "
    SELECT 
        id_medicamento,
        nombre_comercial,
        nombre_generico,
        activo
    FROM medicamentos
    ORDER BY id_medicamento ASC
";

$result = $mysqli->query($sql);

$medicamentos = [];

while ($row = $result->fetch_assoc()) {
    $medicamentos[] = $row;
}

echo json_encode($medicamentos);